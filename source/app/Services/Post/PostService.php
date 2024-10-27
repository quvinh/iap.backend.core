<?php

namespace App\Services\Post;

use App\DataResources\PaginationInfo;
use App\DataResources\SortInfo;
use App\Exceptions\Business\ActionFailException;
use App\Exceptions\DB\CannotDeleteDBException;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\SupportedLanguages;
use App\Helpers\Utils\StorageHelper;
use App\Helpers\Utils\StringHelper;
use App\Models\Post;
use App\Repositories\Post\IPostRepository;
use App\Repositories\PostLang\IPostLangRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class PostService extends \App\Services\BaseService implements IPostService
{
    private const DEFAULT_FOLDER_TO_UPLOAD_IMAGES = "images/posts";
    private ?IPostRepository $postRepos = null;
    private ?IPostLangRepository $postLangRepos = null;

    public function __construct(IPostRepository $repos, IPostLangRepository $postLangRepos)
    {
        $this->postRepos = $repos;
        $this->postLangRepos = $postLangRepos;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return Post
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): Post
    {
        try {
            $query = $this->postRepos->queryOnAField(['id', $id]);
            $query = $this->postRepos->with($withs, $query);
            $record = $query->first();
            if (!is_null($record)) return $record;
        } catch (Exception $e) {
            throw new RecordIsNotFoundException(
                message: 'get single object: ' . json_encode(['id' => $id, 'withs' => $withs]),
                previous: $e
            );
        }
    }

    /**
     * Search list of items
     *
     * @param array $rawConditions
     * @param PaginationInfo|null $paging
     * @param array<string> $withs
     * @return Collection<int,Post>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->postRepos->search();
            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->postRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->postRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->postRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->postRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->postRepos->sort($query, $sort)->get();
            }
            return $query->get();
        } catch (Exception $e) {
            throw new ActionFailException(
                message: 'search: ' . json_encode(['conditions' => $rawConditions, 'paging' => $paging, 'withs' => $withs]),
                previous: $e
            );
        }
    }

    /**
     * Create new item
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Post
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Post
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->postRepos->create($param, $commandMetaInfo);

            # Update photo if needed

            if ($commandMetaInfo->lang !== SupportedLanguages::DEFAULT_LOCALE) {
                # Forward to create its translation of the corresponding language
                $param = array_merge(['lang' => $commandMetaInfo->lang, 'post_id' => $record->id], $param);
                $rLang = $this->postLangRepos->create($param, $commandMetaInfo);
                if ($rLang == null) throw new CannotSaveToDBException(message: 'create translation: ' . $commandMetaInfo->lang);
            }

            DB::commit();
            #2 Return
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotSaveToDBException(
                message: 'create: ' . json_encode(['param' => $param]),
                previous: $e
            );
        }
    }

    /**
     * @param int $id
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Post
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): Post
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->postRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            if ($commandMetaInfo->lang !== SupportedLanguages::DEFAULT_LOCALE) { // if locale is not the default value
                $rLang = $record->locale($commandMetaInfo->lang);
                if (!empty($rLang)) { // Has locale record => update
                    $param = array_merge($param, ['id' => $rLang->id, 'lang' => $commandMetaInfo->lang, 'post_id' => $record->id]);
                    $rLang = $this->postLangRepos->update($param, $commandMetaInfo);
                } else { // No locale record => create
                    $param = array_merge(['lang' => $commandMetaInfo->lang, 'post_id' => $record->id], $param);
                    $rLang = $this->postLangRepos->create($param, $commandMetaInfo);
                }
                if ($rLang == null) throw new CannotSaveToDBException(message: 'create translation: ' . $commandMetaInfo->lang);
            } else {
                $param = array_merge(['id' => $record->id], $param);
                $record = $this->postRepos->update($param, $commandMetaInfo);
            }
            // update picture if needed
            // code here
            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotUpdateDBException(
                message: 'update: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Delete item from resources
     *
     * @param int $id
     * @param bool $softDelete
     * @param MetaInfo|null $commandMetaInfo
     * @return bool
     * @throws CannotDeleteDBException
     */
    public function delete(int $id, bool $softDelete = true, MetaInfo $commandMetaInfo = null): bool
    {
        DB::beginTransaction();
        try {
            $record = $this->postRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->postRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
            DB::commit();
            return $result;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new CannotDeleteDBException(
                message: 'update: ' . json_encode(['id' => $id, 'softDelete' => $softDelete]),
                previous: $ex
            );
        }
    }
}
