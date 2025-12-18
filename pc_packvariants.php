<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PackVariants\Form\Modifier\PackMainProductModifier;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;

class Pc_PackVariants extends Module 
{
    public function __construct()
    {
        $this->name = 'pc_packvariants';
        $this->version = '0.0.1';
        $this->author = 'Pharès CHAKOUR';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Pack Variants');
        $this->description = $this->l('Link pack products together as variants (bidirectional).');
    }

    public function install()
    {
        return parent::install()
            && $this->installDatabase()
            && $this->registerHook('actionProductFormBuilderModifier')
            && $this->registerHook('actionProductSave')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayProductPriceBlock');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDatabase();
    }

    protected function installDatabase()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pc_packvariants` (
            `id_product` INT(11) NOT NULL,
            `id_variant` INT(11) NOT NULL,
            PRIMARY KEY (`id_product`, `id_variant`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';
        return Db::getInstance()->execute($sql);
    }

    protected function uninstallDatabase()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pc_packvariants`';
        return Db::getInstance()->execute($sql);
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path.'views/js/front/redirect_variant.js');
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $controller = $this->context->controller;

        if ($controller->controller_name === 'AdminProducts') {
            // Your initialization script
            $controller->addJS($this->_path.'views/js/admin/variants_selector.js');
            $controller->addCss($this->_path.'/views/css/admin_variants_selector.css');
        }
    }

    public function hookActionProductFormBuilderModifier(array $params)
    {
         /** @var PackMainProductModifier $productFormModifier */
        $productFormModifier = $this->get(PackMainProductModifier::class);
        $productId = isset($params['id']) ? new ProductId((int) $params['id']) : null;
        $productFormModifier->modify($productId, $this->context, $params['form_builder']);
    }

    public function hookActionProductSave(array $params)
    {
        $product = $params['product'] ?? null;
        if (!$product) return;

        $id_product = (int)$product->id;
        $variants = Tools::getValue('product')['description']['variants'] ?? [];

        // Delete existing where this product is pack or variant (clean)
        Db::getInstance()->delete('pc_packvariants', 'id_product = ' . $id_product . ' OR id_variant = ' . $id_product);

        if (is_array($variants)) {
            foreach ($variants as $variant) {
                $variant_id = (int)$variant['id'];
                if ($variant_id <= 0) continue;
                Db::getInstance()->insert('pc_packvariants', ['id_product' => $id_product, 'id_variant' => $variant_id]);
                Db::getInstance()->insert('pc_packvariants', ['id_product' => $variant_id, 'id_variant' => $id_product]);
            }
        }
    }

    public function hookActionProductDelete(array $params)
    {
        $product = $params['product'] ?? null;
        if (!$product) return;

        $id_product = (int)$product->id;

        // Delete existing where this product is pack or variant (clean)
        Db::getInstance()->delete('pc_packvariants', 'id_product = ' . $id_product . ' OR id_variant = ' . $id_product);
    }

    public function hookDisplayProductPriceBlock(array $params)
    {
        if ($this->context->controller->php_self !== 'product') {
            return '';
        }
        
        if ($params['type'] !== 'after_price') {
            return '';
        }

        $product = $params['product'] ?? null;
        if (!$product) return '';
        
        $id_product = (int)$product->id;
        $variants = Db::getInstance()->executeS('
            SELECT DISTINCT pv.id_variant AS id_product, pl.name
            FROM `' . _DB_PREFIX_ . 'pc_packvariants` pv
            INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pv.id_variant = pl.id_product AND pl.id_lang = ' . (int)$this->context->language->id . '
            WHERE pv.id_product = ' . $id_product . '
            ORDER BY pl.name ASC
        ');
        
        if (empty($variants)) return '';

        foreach ($variants as &$variant) {
            $variant['url'] = $this->context->link->getProductLink($variant['id_product']);
        }

        $this->context->smarty->assign([
            'product_variants' => $variants,
        ]);

        return $this->fetch('module:pc_packvariants/views/templates/hook/variants_dropdown.tpl');
    }
}
