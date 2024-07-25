<?php
/**
 *   bf_productcmslinker
 *
 *   Do not copy, modify or distribute this document in any form.
 *
 *   @author     Goran <goran@blauwfruit.nl>
 *   @copyright  Copyright (c) 2013-2021 blauwfruit (https://blauwfruit.nl)
 *   @license    Proprietary Software
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class BfProductCmsLinker extends ObjectModel
{
    public $id_bf_product_cms_linker;
    public $id_cms;
    public $id_product;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bf_product_cms_linker',
        'primary' => 'id_bf_product_cms_linker',
        'fields' => array(
            'id_cms'       => array('type' => self::TYPE_INT, 'required' => true),
            'id_product'     => array('type' => self::TYPE_INT, 'required' => true),
        ),
    );

    /**
     * @param $id_lang
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     * @todo move this to CMS class
     */
    public static function getAll($id_lang)
    {
        $id_lang = (int)$id_lang;

        return Db::getInstance()->ExecuteS('
SELECT DISTINCT 
       ps_cms_lang.id_cms, 
       ps_cms_lang.id_lang, 
       ps_cms_lang.meta_title, 
       ps_cms_lang.link_rewrite 
FROM 
     ps_cms 
 JOIN ps_cms_lang 
     ON ps_cms.id_cms = ps_cms_lang.id_cms  
WHERE ps_cms.active = 1  
  AND ps_cms_lang.id_lang = ' . $id_lang);
    }

    /**
     * Gets collection of CMS pages linked to Product
     *
     * @param $id_lang
     * @param $id_product
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public static function getCmsesByIdProduct($id_lang, $id_product)
    {
        $id_lang = (int)$id_lang;
        $id_product = (int)$id_product;
        $id_shop = (int)Context::getContext()->shop->id;

        return Db::getInstance()->ExecuteS('
SELECT DISTINCT 
       ps_cms_lang.id_cms, 
       ps_cms_lang.id_lang, 
       ps_cms_lang.id_shop, 
       ps_cms_lang.meta_title, 
       ps_cms_lang.link_rewrite 
FROM 
     ps_bf_product_cms_linker 
 JOIN ps_cms_lang 
     ON ps_bf_product_cms_linker.id_cms = ps_cms_lang.id_cms 
 JOIN ps_cms 
     ON ps_bf_product_cms_linker.id_cms = ps_cms.id_cms 
WHERE ps_cms.active = 1 
  AND ps_bf_product_cms_linker.id_product = ' . $id_product . ' 
  AND ps_cms_lang.id_lang = ' . $id_lang . '
  AND ps_cms_lang.id_shop = ' . $id_shop);
    }

    /**
     * @param $idLang
     * @param $idProduct
     * @param $cmsIds
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public static function deleteByCmsIds($idLang, $idProduct, $cmsIds)
    {
        if (!is_array($cmsIds)) {
            $cmsIds = [$cmsIds];
        }

        $idLang = (int)$idLang;

        $whereIn = implode(',', $cmsIds);

        $sql = "DELETE FROM " . _DB_PREFIX_ . "bf_product_cms_linker WHERE id_product=$idProduct AND id_cms IN ($whereIn)";

        return Db::getInstance()->execute($sql);
    }

    /**
     * Add non-existing linked pages
     *
     * @param $idLang
     * @param $idProduct
     * @param $cmsIds
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function addLinkers($idLang, $idProduct, $cmsIds)
    {
        if (!is_array($cmsIds)) {
            $cmsIds = [$cmsIds];
        }

        $idLang = (int)$idLang;
        $idProduct = (int)$idProduct;

        $insertValuesString = 'VALUES ';
        foreach ($cmsIds as $k => $v) {
            $insertValuesString .= "($idProduct, $v), ";
        }

        $insertValuesString = rtrim($insertValuesString, ", ");

        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'bf_product_cms_linker' . ' (id_product, id_cms) ' . $insertValuesString;

        return Db::getInstance()->execute($sql);
    }
}
