<?php

namespace App\Repositories;

use App\Repositories\BusinessPartner\BusinessPartnerRepository;
use App\Repositories\BusinessPartner\IBusinessPartnerRepository;
use App\Repositories\Company\CompanyRepository;
use App\Repositories\Company\ICompanyRepository;
use App\Repositories\CompanyDetail\CompanyDetailRepository;
use App\Repositories\CompanyDetail\ICompanyDetailRepository;
use App\Repositories\CategoryPurchase\CategoryPurchaseRepository;
use App\Repositories\CategoryPurchase\ICategoryPurchaseRepository;
use App\Repositories\CategorySold\CategorySoldRepository;
use App\Repositories\CategorySold\ICategorySoldRepository;
use App\Repositories\CompanyDetailTaxFreeVoucher\CompanyDetailTaxFreeVoucherRepository;
use App\Repositories\CompanyDetailTaxFreeVoucher\ICompanyDetailTaxFreeVoucherRepository;
use App\Repositories\CompanyDocument\CompanyDocumentRepository;
use App\Repositories\CompanyDocument\ICompanyDocumentRepository;
use App\Repositories\CompanyType\CompanyTypeRepository;
use App\Repositories\CompanyType\ICompanyTypeRepository;
use App\Repositories\FirstAriseAccount\FirstAriseAccountRepository;
use App\Repositories\FirstAriseAccount\IFirstAriseAccountRepository;
use App\Repositories\Formula\FormulaRepository;
use App\Repositories\Formula\IFormulaRepository;
use App\Repositories\FormulaCategoryPurchase\FormulaCategoryPurchaseRepository;
use App\Repositories\FormulaCategoryPurchase\IFormulaCategoryPurchaseRepository;
use App\Repositories\FormulaCategorySold\FormulaCategorySoldRepository;
use App\Repositories\FormulaCategorySold\IFormulaCategorySoldRepository;
use App\Repositories\FormulaCommodity\FormulaCommodityRepository;
use App\Repositories\FormulaCommodity\IFormulaCommodityRepository;
use App\Repositories\FormulaMaterial\FormulaMaterialRepository;
use App\Repositories\FormulaMaterial\IFormulaMaterialRepository;
use App\Repositories\Invoice\IInvoiceRepository;
use App\Repositories\Invoice\InvoiceRepository;
use App\Repositories\InvoiceDetail\IInvoiceDetailRepository;
use App\Repositories\InvoiceDetail\InvoiceDetailRepository;
use App\Repositories\InvoiceMedia\IInvoiceMediaRepository;
use App\Repositories\InvoiceMedia\InvoiceMediaRepository;
use App\Repositories\InvoiceTask\IInvoiceTaskRepository;
use App\Repositories\InvoiceTask\InvoiceTaskRepository;
use App\Repositories\ItemCode\IItemCodeRepository;
use App\Repositories\ItemCode\ItemCodeRepository;
use App\Repositories\ItemGroup\IItemGroupRepository;
use App\Repositories\ItemGroup\ItemGroupRepository;
use App\Repositories\OpeningBalanceVat\IOpeningBalanceVatRepository;
use App\Repositories\OpeningBalanceVat\OpeningBalanceVatRepository;
use App\Repositories\PdfTableKey\IPdfTableKeyRepository;
use App\Repositories\PdfTableKey\PdfTableKeyRepository;
use App\Repositories\Permission\IPermissionRepository;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Post\IPostRepository;
use App\Repositories\Post\PostRepository;
use App\Repositories\PostCategory\IPostCategoryRepository;
use App\Repositories\PostCategory\PostCategoryRepository;
use App\Repositories\PostCategoryLang\IPostCategoryLangRepository;
use App\Repositories\PostCategoryLang\PostCategoryLangRepository;
use App\Repositories\PostLang\IPostLangRepository;
use App\Repositories\PostLang\PostLangRepository;
use App\Repositories\Role\IRoleRepository;
use App\Repositories\Role\RoleRepository;
use App\Repositories\TaxFreeVoucher\ITaxFreeVoucherRepository;
use App\Repositories\TaxFreeVoucher\TaxFreeVoucherRepository;
use App\Repositories\TaxFreeVoucherRecord\ITaxFreeVoucherRecordRepository;
use App\Repositories\TaxFreeVoucherRecord\TaxFreeVoucherRecordRepository;
use App\Repositories\Template\ITemplateRepository;
use App\Repositories\Template\TemplateRepository;
use App\Repositories\User\IUserRepository;
use App\Repositories\User\UserRepository;

class RepositoryRegistration
{
    /**
     * Register injectable instance
     */

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        $app->singleton(IUserRepository::class, UserRepository::class);
        $app->singleton(IRoleRepository::class, RoleRepository::class);
        $app->singleton(IPermissionRepository::class, PermissionRepository::class);
        $app->singleton(ICompanyRepository::class, CompanyRepository::class);
        $app->singleton(ICompanyDetailRepository::class, CompanyDetailRepository::class);
        $app->singleton(ICompanyTypeRepository::class, CompanyTypeRepository::class);
        $app->singleton(IFirstAriseAccountRepository::class, FirstAriseAccountRepository::class);
        $app->singleton(ICategoryPurchaseRepository::class, CategoryPurchaseRepository::class);
        $app->singleton(ICategorySoldRepository::class, CategorySoldRepository::class);
        $app->singleton(IFormulaRepository::class, FormulaRepository::class);
        $app->singleton(IFormulaCategoryPurchaseRepository::class, FormulaCategoryPurchaseRepository::class);
        $app->singleton(IFormulaCategorySoldRepository::class, FormulaCategorySoldRepository::class);
        $app->singleton(IFormulaCommodityRepository::class, FormulaCommodityRepository::class);
        $app->singleton(IFormulaMaterialRepository::class, FormulaMaterialRepository::class);
        $app->singleton(ITaxFreeVoucherRepository::class, TaxFreeVoucherRepository::class);
        $app->singleton(ICompanyDetailTaxFreeVoucherRepository::class, CompanyDetailTaxFreeVoucherRepository::class);
        $app->singleton(IInvoiceTaskRepository::class, InvoiceTaskRepository::class);
        $app->singleton(IItemCodeRepository::class, ItemCodeRepository::class);
        $app->singleton(IInvoiceRepository::class, InvoiceRepository::class);
        $app->singleton(IInvoiceDetailRepository::class, InvoiceDetailRepository::class);
        $app->singleton(IItemGroupRepository::class, ItemGroupRepository::class);
        $app->singleton(ITaxFreeVoucherRecordRepository::class, TaxFreeVoucherRecordRepository::class);
        $app->singleton(IInvoiceMediaRepository::class, InvoiceMediaRepository::class);
        $app->singleton(IPdfTableKeyRepository::class, PdfTableKeyRepository::class);
        $app->singleton(IOpeningBalanceVatRepository::class, OpeningBalanceVatRepository::class);
        $app->singleton(ITemplateRepository::class, TemplateRepository::class);
        $app->singleton(ICompanyDocumentRepository::class, CompanyDocumentRepository::class);
        $app->singleton(IPostRepository::class, PostRepository::class);
        $app->singleton(IPostLangRepository::class, PostLangRepository::class);
        $app->singleton(IPostCategoryRepository::class, PostCategoryRepository::class);
        $app->singleton(IPostCategoryLangRepository::class, PostCategoryLangRepository::class);
        $app->singleton(IBusinessPartnerRepository::class, BusinessPartnerRepository::class);
    }
}
