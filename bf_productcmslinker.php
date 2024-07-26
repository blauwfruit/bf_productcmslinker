<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Bf_productcmslinker extends Module
{
    protected $config_form = false;

    protected $isUpdated = false;

    public function __construct()
    {
        $this->name = 'bf_productcmslinker';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'blauwfruit';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product CMS linker');
        $this->description = $this->l('Shows products in CMS pages and CMS pages in product page.');

        $this->confirmUninstall = $this->l('Are you sure you want to lose all data related to ' . $this->displayName . ' module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        require_once __DIR__ . '/classes/BfProductCmsLinker.php';
    }

    /**
     * Installing the module
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     *
     * @return bool
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayProductInCMS');
    }

    /**
     * Uninstalling the module
     *
     * @return bool
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitBf_productcmslinkerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBf_productcmslinkerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'BF_PRODUCTCMSLINKER_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'BF_PRODUCTCMSLINKER_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'BF_PRODUCTCMSLINKER_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'BF_PRODUCTCMSLINKER_LIVE_MODE' => Configuration::get('BF_PRODUCTCMSLINKER_LIVE_MODE', true),
            'BF_PRODUCTCMSLINKER_ACCOUNT_EMAIL' => Configuration::get('BF_PRODUCTCMSLINKER_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'BF_PRODUCTCMSLINKER_ACCOUNT_PASSWORD' => Configuration::get('BF_PRODUCTCMSLINKER_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if ('AdminProducts' !== $this->context->controller->php_self) {
            return;
        }

        // CSS
        $this->context->controller->addCSS($this->_path.'views/css/tagify.css');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
        // JS
        // $this->context->controller->addJS($this->_path.'views/js/jquery-1.12.4.js');
        $this->context->controller->addJS($this->_path.'views/js/tagify.min.js');
        $this->context->controller->addJS($this->_path.'views/js/back.js');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * @param $params
     * @return false|string|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        // early return
        if ('AdminProducts' !== $this->context->controller->php_self) {
            return;
        }

        $id_lang = (int)$this->context->language->id;
        $id_product = (int)$params['id_product'];

        $allCmses = BfProductCmsLinker::getAll($id_lang);

        $productCmses = BfProductCmsLinker::getCmsesByIdProduct($id_lang, $id_product);

        $this->context->smarty->assign([
            'all_cmses' => $allCmses,
            'product_cmses' => $productCmses
        ]);

        return $this->display(__FILE__, 'display_admin_products_extra.tpl');
    }

    /**
     * Update product action
     *
     * @param $params
     * @return null
     * @throws PrestaShopDatabaseException
     */
    public function hookActionProductUpdate($params)
    {

        if (true === $this->isUpdated) {
            return null;
        }

        $idProduct = (int)$params['id_product'];

        if (0 >= $idProduct) {
            return null;
        }

        $bfProductCmsLinker = $_POST['bf_product_cms_linker'];

        if (!isset($bfProductCmsLinker)) {
            return null;
        }

        $input = json_decode($bfProductCmsLinker);
        $idLang = (int)$this->context->language->id;

        $cmsPages = [];
        foreach ($input as $value) {
            $cmsPages[] = (int) $value->id_cms;

            if ($value->id_lang) {
                $idLang = $value->id_lang;
            }
        }

        $existingCmses = BfProductCmsLinker::getCmsesByIdProduct($idLang, $idProduct);

        // if existing linked cmses are not part of cmsPages array
        // we are going to collect those and delete it at once after loop
        $cmsForDeleting = [];
        $existingCmsIds = [];
        foreach ($existingCmses as $k => $value) {
            $idCms = (int)$value['id_cms'];
            if (!in_array($idCms, $cmsPages)) {
                // collect IDs for deleting
                $cmsForDeleting[] = $idCms;
            } else {
                // collect IDs that are already the same to ones from input
                $existingCmsIds[] = $idCms;
            }
        }

        if (!empty($cmsForDeleting)) {
            BfProductCmsLinker::deleteByCmsIds($idLang, $idProduct, $cmsForDeleting);
        }

        $cmsIdsToStore = array_diff($cmsPages, $existingCmsIds);

        if (count($cmsIdsToStore)) {
            // add these IDs
            BfProductCmsLinker::addLinkers($idLang, $idProduct, $cmsIdsToStore);
        }

        $this->isUpdated = true;
    }

    /**
     * @param int $id_cms
     * @return CMS|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getCmsById(int $id_cms): ?CMS
    {
        if (!$cms = new CMS($id_cms, $this->context->language->id)) {
            return null;
        }

        return $cms;
    }

    /**
     * Get image for CMS page
     *
     * @param Cms $cms
     * @return string
     */
    private function getCmsFeaturedImage(Cms $cms)
    {
        // default value
        $imageSrc = null;
        $cmsContent = $cms->content;
        $dom = new DOMDocument();
        $dom->loadHTML($cmsContent);
        $img = $dom->getElementsByTagName('img');
        if (!$img) {
            return $imageSrc;
        }
        $firstImg = $img->item(0);
        if ($firstImg) {
            $imageSrc = $firstImg->getAttribute('src');
        }
        return $imageSrc;
    }

    /**
     * Helper method to log message into log file
     *
     * @param $message
     */
    private function logFileMessage($message)
    {
        $fileLogger = new FileLogger(0);

        $fileLogger->setFilename(_PS_ROOT_DIR_ . '/var/logs/dev.log');
        $fileLogger->logDebug($message);
    }

    /**
     * @param $params
     * @return false|string
     * @throws PrestaShopDatabaseException
     */
    public function hookDisplayFooterProduct($params)
    {
        $id_lang = (int)$this->context->language->id;
        $id_product = (int)Tools::getValue('id_product');

        $cmsPages = BfProductCmsLinker::getCmsesByIdProduct($id_lang, $id_product);

        if (count($cmsPages)) {
            foreach ($cmsPages as &$cmsPage) {
                $cms = new CMS((int)$cmsPage['id_cms'], $id_lang);
                $cmsPage['image'] = $this->getCmsFeaturedImage($cms);
                $cmsPage['link'] = $this->context->link->getCMSLink($cmsPage['id_cms']);
            }
        }

        $this->context->smarty->assign([
            'cms_pages' => $cmsPages
        ]);

        return $this->context->smarty->fetch('module:bf_productcmslinker/views/templates/hook/cms.tpl');
    }

    /**
     * @param $params
     * @return false|string|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public function hookDisplayProductInCMS($params)
    {
        if ($this->context->controller->php_self !== 'cms') {
            return;
        }

        $products = $this->getProductsByIdCms((int)$params['cms']['id']);

        if ($products === false) {
            return;
        }

        $this->context->smarty->assign([
            'products' => $products
        ]);

        return $this->context->smarty->fetch('module:bf_productcmslinker/views/templates/hook/products.tpl');
    }

    /**
     * @param $id_cms
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws ReflectionException
     */
    protected function getProductsByIdCms($id_cms)
    {
        $cms_products = DB::getInstance()->executeS('SELECT id_product FROM ps_bf_product_cms_linker WHERE id_cms=' . (int)$id_cms);

        if ($cms_products === false) {
            return false;
        }

        foreach ($cms_products as &$cms_product) {
            $cms_product = ['product_id' => $cms_product['id_product']];
        }


        $showPrice = (bool) Configuration::get('CROSSSELLING_DISPLAY_PRICE');

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $productsForTemplate = array();

        $presentationSettings->showPrices = $showPrice;

        foreach ($cms_products as $productId) {
            $productsForTemplate[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct(array('id_product' => $productId['product_id'])),
                $this->context->language
            );
        }

        return $productsForTemplate;
    }
}
