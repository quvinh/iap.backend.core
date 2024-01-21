<?php

namespace App\Console\Commands;

use App\Helpers\Common\MetaInfo;
use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $models = $this->models();
        $actions = $this->actions();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            $this->info('Start generate permissions...');
            DB::table('permissions')->truncate();

            $metaInfo = new MetaInfo('cmd', 'command');
            foreach ($models as $model) {
                foreach ($actions as $action) {
                    $permission = new Permission();
                    $permission->slug = "$model.$action";
                    $permission->name = "$model.$action";
                    $permission->setMetaInfo($metaInfo);
                    $permission->save();
                    $this->info("Done: {$permission->slug}");
                }
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            $this->error('Action failed');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return Command::SUCCESS;
    }

    public function models(): array
    {
        return [
            'category_purchase',
            'category_sold',
            'company',
            'company_type',
            'first_arise_account',
            'formula_commodity',
            'formula_material',
            'formula',
            'invoice_media',
            'invoice_task',
            'invoice',
            'item_code',
            'item_group',
            'opening_balance_vat',
            'pdf_table_key',
            'permission',
            'role',
            'tax_free_voucher',
            'user',
        ];
    }

    public function actions(): array
    {
        return [
            'search',
            'create',
            'update',
            'delete',
        ];
    }
}
