<?php

namespace App\Services;

use App\Services\Auth\AuthService;
use App\Services\Auth\IAuthService;
use App\Services\CategoryPurchase\CategoryPurchaseService;
use App\Services\CategoryPurchase\ICategoryPurchaseService;
use App\Services\CategorySold\CategorySoldService;
use App\Services\CategorySold\ICategorySoldService;
use App\Services\Company\CompanyService;
use App\Services\Company\ICompanyService;
use App\Services\CompanyDetail\CompanyDetailService;
use App\Services\CompanyDetail\ICompanyDetailService;
use App\Services\CompanyDetailTaxFreeVoucher\CompanyDetailTaxFreeVoucherService;
use App\Services\CompanyDetailTaxFreeVoucher\ICompanyDetailTaxFreeVoucherService;
use App\Services\CompanyDocument\CompanyDocumentService;
use App\Services\CompanyDocument\ICompanyDocumentService;
use App\Services\CompanyType\CompanyTypeService;
use App\Services\CompanyType\ICompanyTypeService;
use App\Services\FirstAriseAccount\FirstAriseAccountService;
use App\Services\FirstAriseAccount\IFirstAriseAccountService;
use App\Services\Formula\FormulaService;
use App\Services\Formula\IFormulaService;
use App\Services\FormulaCategoryPurchase\FormulaCategoryPurchaseService;
use App\Services\FormulaCategoryPurchase\IFormulaCategoryPurchaseService;
use App\Services\FormulaCategorySold\FormulaCategorySoldService;
use App\Services\FormulaCategorySold\IFormulaCategorySoldService;
use App\Services\FormulaCommodity\FormulaCommodityService;
use App\Services\FormulaCommodity\IFormulaCommodityService;
use App\Services\FormulaMaterial\FormulaMaterialService;
use App\Services\FormulaMaterial\IFormulaMaterialService;
use App\Services\Invoice\IInvoiceService;
use App\Services\Invoice\InvoiceService;
use App\Services\InvoiceDetail\IInvoiceDetailService;
use App\Services\InvoiceDetail\InvoiceDetailService;
use App\Services\InvoiceMedia\IInvoiceMediaService;
use App\Services\InvoiceMedia\InvoiceMediaService;
use App\Services\InvoiceTask\IInvoiceTaskService;
use App\Services\InvoiceTask\InvoiceTaskService;
use App\Services\ItemCode\IItemCodeService;
use App\Services\ItemCode\ItemCodeService;
use App\Services\ItemGroup\IItemGroupService;
use App\Services\ItemGroup\ItemGroupService;
use App\Services\OpeningBalanceVat\IOpeningBalanceVatService;
use App\Services\OpeningBalanceVat\OpeningBalanceVatService;
use App\Services\PdfTableKey\IPdfTableKeyService;
use App\Services\PdfTableKey\PdfTableKeyService;
use App\Services\Permission\IPermissionService;
use App\Services\Permission\PermissionService;
use App\Services\Post\IPostService;
use App\Services\Post\PostService;
use App\Services\PostCategory\IPostCategoryService;
use App\Services\PostCategory\PostCategoryService;
use App\Services\Role\IRoleService;
use App\Services\Role\RoleService;
use App\Services\TaxFreeVoucher\ITaxFreeVoucherService;
use App\Services\TaxFreeVoucher\TaxFreeVoucherService;
use App\Services\TaxFreeVoucherRecord\ITaxFreeVoucherRecordService;
use App\Services\TaxFreeVoucherRecord\TaxFreeVoucherRecordService;
use App\Services\Template\ITemplateService;
use App\Services\Template\TemplateService;
use App\Services\User\IUserService;
use App\Services\User\UserService;

class ServiceRegistration
{
    /**
     * Register injectable instance
     */
    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        $app->singleton(IAuthService::class, AuthService::class);

        $app->singleton(IUserService::class, UserService::class);
        $app->singleton(IRoleService::class, RoleService::class);
        $app->singleton(IPermissionService::class, PermissionService::class);
        $app->singleton(ICompanyService::class, CompanyService::class);
        $app->singleton(ICompanyDetailService::class, CompanyDetailService::class);
        $app->singleton(ICompanyTypeService::class, CompanyTypeService::class);
        $app->singleton(IFirstAriseAccountService::class, FirstAriseAccountService::class);
        $app->singleton(ICategoryPurchaseService::class, CategoryPurchaseService::class);
        $app->singleton(ICategorySoldService::class, CategorySoldService::class);
        $app->singleton(IFormulaService::class, FormulaService::class);
        $app->singleton(IFormulaCategoryPurchaseService::class, FormulaCategoryPurchaseService::class);
        $app->singleton(IFormulaCategorySoldService::class, FormulaCategorySoldService::class);
        $app->singleton(IFormulaCommodityService::class, FormulaCommodityService::class);
        $app->singleton(IFormulaMaterialService::class, FormulaMaterialService::class);
        $app->singleton(ITaxFreeVoucherService::class, TaxFreeVoucherService::class);
        $app->singleton(ICompanyDetailTaxFreeVoucherService::class, CompanyDetailTaxFreeVoucherService::class);
        $app->singleton(IInvoiceTaskService::class, InvoiceTaskService::class);
        $app->singleton(IItemCodeService::class, ItemCodeService::class);
        $app->singleton(IInvoiceService::class, InvoiceService::class);
        $app->singleton(IInvoiceDetailService::class, InvoiceDetailService::class);
        $app->singleton(IItemGroupService::class, ItemGroupService::class);
        $app->singleton(ITaxFreeVoucherRecordService::class, TaxFreeVoucherRecordService::class);
        $app->singleton(IInvoiceMediaService::class, InvoiceMediaService::class);
        $app->singleton(IPdfTableKeyService::class, PdfTableKeyService::class);
        $app->singleton(IOpeningBalanceVatService::class, OpeningBalanceVatService::class);
        $app->singleton(ITemplateService::class, TemplateService::class);
        $app->singleton(ICompanyDocumentService::class, CompanyDocumentService::class);
        $app->singleton(IPostService::class, PostService::class);
        $app->singleton(IPostCategoryService::class, PostCategoryService::class);
    }
}
