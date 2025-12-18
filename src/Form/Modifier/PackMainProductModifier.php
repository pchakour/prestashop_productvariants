<?php

declare(strict_types=1);

namespace PackVariants\Form\Modifier;

use PrestaShopBundle\Form\FormBuilderModifier;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShopBundle\Form\Admin\Type\Product\ProductSearchType;

use Db;
use Tools;
use Image;


final class PackMainProductModifier
{
    /**
     * @var FormBuilderModifier
     */
    private $formBuilderModifier;

    /**
     * @param FormBuilderModifier $formBuilderModifier
     */
    public function __construct(
        FormBuilderModifier $formBuilderModifier
    ) {
        $this->formBuilderModifier = $formBuilderModifier;
    }

    private function getVariantsForPack($context, int $idProduct): array
    {
        $sql = 'SELECT p.id_product, pl.name, i.id_image
            FROM ' . _DB_PREFIX_ . 'pc_packvariants pv
            INNER JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = pv.id_variant
            INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = ' . (int)$context->language->id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'image i ON (i.id_product = p.id_product AND i.cover = 1)
            WHERE pv.id_product = ' . (int)$idProduct;

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as &$row) {
            $idImage = (int) $row['id_image'];

            if ($idImage) {
                $image = new \Image($idImage);
                $imagePath = $image->getExistingImgPath();
                $row['image'] = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $imagePath . '-home_default.jpg';
            } else {
                $row['image'] = _PS_IMG_ . 'p/default.jpg';
            }
        }
        return $rows;
    }

    /**
     * @param FormBuilderInterface $productFormBuilder
     */
    public function modify(
        ProductId $idProduct,
        $context,
        FormBuilderInterface $productFormBuilder
    ): void {

        $variants = $this->getVariantsForPack($context, $idProduct->getValue());
        $productTabFormBuilder = $productFormBuilder->get('description');

        $this->formBuilderModifier->addAfter(
            $productTabFormBuilder,
            'related_products',
            'variants',
            FormType::class,
            [
                'label' => false,
                'mapped' => false,
                'allow_extra_fields' => true,
                'attr' => [
                    'class' => 'js-variant-products entity-search-widget', // important for JS init
                    'data-variants' => json_encode($variants),
                ],
            ]
        );
    }
}
