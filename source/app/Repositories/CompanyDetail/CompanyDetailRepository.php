<?php

namespace App\Repositories\CompanyDetail;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetail;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\CompanyDetailAriseAccount;
use App\Models\CompanyDetailTaxFreeVoucher;
use App\Models\Formula;
use App\Models\FormulaCategoryPurchase;
use App\Models\FormulaCategorySold;
use App\Models\FormulaCommodity;
use App\Models\FormulaMaterial;
use App\Models\OpeningBalanceVat;
use App\Models\TaxFreeVoucherRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use function Spatie\SslCertificate\starts_with;

class CompanyDetailRepository extends BaseRepository implements ICompanyDetailRepository
{
    /**
     * Get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CompanyDetail::class;
    }

    /**
     * Get company_detail_arise_accout
     */
    public function getSinglePropertyObject(mixed $idCom, mixed $idAcc): Builder
    {
        $query = (new CompanyDetailAriseAccount())->query();
        return $query->where([
            ['company_detail_id', $idCom],
            ['arise_account_id', $idAcc],
        ]);
    }

    /**
     * Create company_detail_arise_accout
     * @param array $param
     */
    public function createAriseAccount(array $param): Model
    {
        $entity = new CompanyDetailAriseAccount();
        $entity->setCompanyDetailAriseAccount($param['value_from'], $param['value_to']);
        $entity->company_detail_id = $param['company_detail_id'];
        $entity->arise_account_id = $param['arise_account_id'];
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Update company_detail_arise_accout
     * @param array $param
     */
    public function updateAriseAccount(array $param): Model
    {
        if (!in_array('id', array_keys($param))) throw new IdIsNotProvidedException();
        $entity = (new CompanyDetailAriseAccount())->query()->where('id', $param['id'])->first();
        if ($entity === null)
            throw new DBRecordIsNotFoundException();
        $entity->setCompanyDetailAriseAccount($param['value_from'], $param['value_to']);
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotUpdateDBException();
        }
    }

    /**
     * Delete id not in ids company_detail_arise_accout
     * @param array $ids
     */
    public function deleteAriseAccount(mixed $idCom, array $ids): bool
    {
        $list = (new CompanyDetailAriseAccount())->query()->where('company_detail_id', $idCom)->get(['id', 'arise_account_id'])->toArray();

        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['arise_account_id'], $ids);
        });

        foreach ($needDelete as $item) {
            (new CompanyDetailAriseAccount())->query()->where('id', $item['id'])->delete();
        }
        return true;
    }

    /**
     * Get company_detail_tax_free_voucher
     */
    public function getSingleVoucherPropertyObject(mixed $idCom, mixed $idTax): Builder
    {
        $query = (new CompanyDetailTaxFreeVoucher())->query();
        return $query->where([
            ['company_detail_id', '=', $idCom],
            ['tax_free_voucher_id', '=', $idTax],
        ]);
    }

    /**
     * Create company_detail_tax_free_voucher
     * @param array $param
     */
    public function createTaxFreeVoucher(array $param): Model
    {
        $entity = new CompanyDetailTaxFreeVoucher();
        $entity->company_detail_id = $param['company_detail_id'];
        $entity->tax_free_voucher_id = $param['tax_free_voucher_id'];
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Delete id not in ids company_detail_tax_free_voucher
     * @param array $ids
     */
    public function deleteTaxFreeVoucher(mixed $idCom, array $ids): bool
    {
        $list = (new CompanyDetailTaxFreeVoucher())->query()->where('company_detail_id', $idCom)->get(['id', 'tax_free_voucher_id'])->toArray();

        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['tax_free_voucher_id'], $ids);
        });

        foreach ($needDelete as $item) {
            (new CompanyDetailTaxFreeVoucher())->query()->where('id', $item['id'])->delete();
        }
        return true;
    }

    /**
     * Delete id not in ids company_detail_tax_free_voucher
     * @param array $ids
     */
    public function clone(array $param): mixed
    {
        $user = Auth::user();
        $author = $user->name ?? 'Unknown';
        $createdBy = "{$user->id}|{$author}";

        $company_detail_id = $param['company_detail_id'];
        $new_year = $param['new_year'];
        $companyDetail = CompanyDetail::findOrFail($company_detail_id);
        $cloneAccounts = [];
        $cloneCompanyDetailTaxFreeVouchers = [];
        $cloneOpeningBalanceVats = [];
        $cloneTaxFreeVoucherRecords = [];
        $cloneFormulas = [];

        # CompanyDetail
        $cloneCompanyDetail = $companyDetail->replicate();
        $cloneCompanyDetail->year = $new_year;
        $cloneCompanyDetail->save();

        # CompanyDetailAriseAccount
        $accounts = $companyDetail->accounts;
        foreach ($accounts as $account) {
            $clonedAccount = $account->replicate();
            $clonedAccount->company_detail_id = $cloneCompanyDetail->id;
            $accountData = $clonedAccount->toArray();

            // Loại bỏ các trường không cần thiết nếu có
            unset($accountData['id']); // Đảm bảo id tự tăng
            // unset($accountData['created_at']);
            // unset($accountData['updated_at']);

            // Cập nhật timestamps nếu cần
            // $accountData['created_at'] = now();
            // $accountData['updated_at'] = now();
            $cloneAccounts[] = $accountData;
        }
        CompanyDetailAriseAccount::insert($cloneAccounts);

        # CompanyDetailTaxFreeVoucher
        $companyDetailTaxFreeVoucher = $companyDetail->tax_free_vouchers;
        foreach ($companyDetailTaxFreeVoucher as $companyDetailTaxFreeVoucher) {
            $clonedCompanyDetailTaxFreeVoucher = $companyDetailTaxFreeVoucher->replicate();
            $clonedCompanyDetailTaxFreeVoucher->company_detail_id = $cloneCompanyDetail->id;
            $companyDetailTaxFreeVoucherData = $clonedCompanyDetailTaxFreeVoucher->toArray();
            unset($companyDetailTaxFreeVoucherData['id']); // Đảm bảo id tự tăng
            $cloneCompanyDetailTaxFreeVouchers[] = $companyDetailTaxFreeVoucherData;
        }
        CompanyDetailTaxFreeVoucher::insert($cloneCompanyDetailTaxFreeVouchers);

        # OpeningBalanceVat
        $openingBalanceVats = $companyDetail->openingBalanceVats;
        foreach ($openingBalanceVats as $openingBalanceVat) {
            $clonedOpeningBalanceVat = $openingBalanceVat->replicate();
            $clonedOpeningBalanceVat->company_detail_id = $cloneCompanyDetail->id;
            $openingBalanceVatData = $clonedOpeningBalanceVat->toArray();
            unset($openingBalanceVatData['id']); // Đảm bảo id tự tăng
            $cloneOpeningBalanceVats[] = $openingBalanceVatData;
        }
        OpeningBalanceVat::insert($cloneOpeningBalanceVats);

        # TaxFreeVoucherRecord
        $taxFreeVoucherRecords = $companyDetail->taxFreeVoucherRecords;
        foreach ($taxFreeVoucherRecords as $taxFreeVoucherRecord) {
            $clonedTaxFreeVoucherRecord = $taxFreeVoucherRecord->replicate();
            $clonedTaxFreeVoucherRecord->company_detail_id = $cloneCompanyDetail->id;
            $taxFreeVoucherRecordData = $clonedTaxFreeVoucherRecord->toArray();

            // Loại bỏ các trường không cần thiết nếu có
            unset($taxFreeVoucherRecordData['id']); // Đảm bảo id tự tăng
            unset($taxFreeVoucherRecordData['created_at']);
            unset($taxFreeVoucherRecordData['updated_at']);
            unset($taxFreeVoucherRecordData['created_by']);

            // Cập nhật timestamps nếu cần
            $taxFreeVoucherRecordData['created_at'] = now();
            $taxFreeVoucherRecordData['updated_at'] = now();
            $taxFreeVoucherRecordData['created_by'] = $createdBy;
            $cloneTaxFreeVoucherRecords[] = $taxFreeVoucherRecordData;
        }
        TaxFreeVoucherRecord::insert($cloneTaxFreeVoucherRecords);

        # Formula
        $formulars = $companyDetail->formulars;
        foreach ($formulars as $formular) {
            $clonedFormular = $formular->replicate();
            $clonedFormular->company_detail_id = $cloneCompanyDetail->id;
            $formularData = $clonedFormular->toArray();

            // Loại bỏ các trường không cần thiết nếu có
            unset($formularData['id']); // Đảm bảo id tự tăng
            unset($formularData['created_at']);
            unset($formularData['updated_at']);
            unset($formularData['created_by']);

            // Cập nhật timestamps nếu cần
            $formularData['created_at'] = now();
            $formularData['updated_at'] = now();
            $formularData['created_by'] = $createdBy;
            $cloneFormulars[] = $formularData;
        }
        Formula::insert($cloneFormulars);

        // Lấy các ID mới của formulars vừa chèn
        $newFormulars = Formula::where('company_detail_id', $cloneCompanyDetail->id)
            ->latest()
            ->take(count($formulars))
            ->get();

        // Tạo bản đồ từ ID gốc sang ID mới
        foreach ($newFormulars as $index => $newFormular) {
            $originalFormular = $formulars[$index];
            $formularIdMap[$originalFormular->id] = $newFormular->id;
        }

        // Chuẩn bị dữ liệu children mới
        foreach ($formulars as $formular) {
            # FormulaCategoryPurchase
            foreach ($formular->category_purchases as $child) {
                $childData = $child->replicate()->toArray();
                $childData['formula_id'] = $formularIdMap[$formular->id];
                // $childData['created_at'] = now();
                // $childData['updated_at'] = now();
                unset($childData['id']);
                $clonedFormulaCategoryPurchase[] = $childData;
            }

            # FormulaCategorySold
            foreach ($formular->category_solds as $child) {
                $childData = $child->replicate()->toArray();
                $childData['formula_id'] = $formularIdMap[$formular->id];
                // $childData['created_at'] = now();
                // $childData['updated_at'] = now();
                unset($childData['id']);
                $clonedFormulaCategorySold[] = $childData;
            }

            # FormulaCommodity
            foreach ($formular->commodities as $child) {
                $childData = $child->replicate()->toArray();
                $childData['formula_id'] = $formularIdMap[$formular->id];
                $childData['created_at'] = now();
                $childData['updated_at'] = now();
                $childData['created_by'] = $createdBy;
                unset($childData['id']);
                $clonedFormulaCommodity[] = $childData;
            }

            # FormulaMaterial
            foreach ($formular->materials as $child) {
                $childData = $child->replicate()->toArray();
                $childData['formula_id'] = $formularIdMap[$formular->id];
                $childData['created_at'] = now();
                $childData['updated_at'] = now();
                $childData['created_by'] = $createdBy;
                unset($childData['id']);
                $clonedFormulaMaterial[] = $childData;
            }
        }

        // Chèn bulk children
        if (!empty($clonedFormulaCategoryPurchase)) {
            FormulaCategoryPurchase::insert($clonedFormulaCategoryPurchase);
        }
        if (!empty($clonedFormulaCategorySold)) {
            FormulaCategorySold::insert($clonedFormulaCategorySold);
        }
        if (!empty($clonedFormulaCommodity)) {
            FormulaCommodity::insert($clonedFormulaCommodity);
        }
        if (!empty($clonedFormulaMaterial)) {
            FormulaMaterial::insert($clonedFormulaMaterial);
        }

        return $cloneCompanyDetail;
    }
}
