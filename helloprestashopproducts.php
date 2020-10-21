<?php

require_once __DIR__.'/vendor/autoload.php';

use PsProducts\Hooks as ProductHooks;
use PsProducts\AlternativeDescription;
use PsProducts\ProductsCollection;

/*
This project is based on the [Friends of Presta/products](https://github.com/friends-of-presta/products) module.
Thanks to [Friends of Presta/Mickaël Andrieu](https://github.com/friends-of-presta).
*/

/**
 * Module to present how Prestashop developers
 * can customize Product pages.
 */
class HelloPrestaShopProducts extends Module
{
    /**
     * @var array list of available Product hooks.
     */
    private $productHooks;

    public function __construct()
    {
        $this->name = 'helloprestashopproducts';
        $this->version = '1.0.0';
        $this->author = 'PHP-Prefixer';
        parent::__construct();
        $this->displayName = 'Hello PrestaShop Products';
        $this->description = 'Hello PrestaShop Products module';
        $this->ps_versions_compliancy = [
            'min' => '1.7.4.0',
            'max' => _PS_VERSION_,
        ];

        $this->productHooks = array_merge(ProductHooks::PRODUCT_LIST_HOOKS, ProductHooks::PRODUCT_FORM_HOOKS);
    }

    /**
     * Module installation.
     *
     * @return bool Success of the installation
     */
    public function install()
    {
        return parent::install()
            && AlternativeDescription::addToProductTable()
            && $this->registerHook($this->productHooks)
        ;
    }

    /**
     * Uninstall the module.
     *
     * @return bool Success of the uninstallation
     */
    public function uninstall()
    {
        return parent::uninstall()
            && AlternativeDescription::removeToProductTable()
        ;
    }

    /**
     * Display "alternative" in Product page.
     * @param type $hookParams
     * @return string
     */
    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($hookParams)
    {
        $productId = $hookParams['id_product'];
        $formFactory = $this->get('form.factory');
        $twig = $this->get('twig');


        $form = AlternativeDescription::addToForm($productId, $formFactory);

        // You don't need to design your form, call only form_row(my_field) in
        // your template.
        return AlternativeDescription::setTemplateToProductPage($twig, $form);
    }

    /**
     * Add the field "alternative_description to Product table.
     */
    public function hookActionDispatcherBefore()
    {
        AlternativeDescription::addToProductDefinition();
    }

    /**
     * Manage the list of product fields available in the Product Catalog page.
     * @param type $hookParams
     */
    public function hookActionAdminProductsListingFieldsModifier(&$hookParams)
    {
        $hookParams['sql_select']['alternative_description'] = [
            'table' => 'p',
            'field' => 'alternative_description',
            'filtering' => "LIKE '%%%s%%'",
        ];
    }

    /**
     * Manage the list of products available in the Product Catalog page.
     * @param type $hookParams
     */
    public function hookActionAdminProductsListingResultsModifier(&$hookParams)
    {
        $hookParams['products'] = ProductsCollection::make($hookParams['products'])
            ->sortBy('alternative_description')
            ->all()
        ;
    }

    /**
     * Manage the information in a specific tab of Product Page.
     * @param type $hookParams
     * @return string
     */
    public function hookDisplayAdminProductsExtra(&$hookParams)
    {
        return $this->get('twig')->render('@PrestaShop/Products/module_panel.html.twig');
    }
}
