<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AbbottAttributes implements DataPatchInterface
{

    /* @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /* @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['$setup' => $this->moduleDataSetup]);

        $attributes = [
            'size_or_weight' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Size or weight',
                'input'        => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true,
            ],
            'cans_x' => [
                'group'        => 'AbbottStore',
                'type'         => 'decimal',
                'label'        => 'Cans Type X',
                'input'        => 'price',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false
            ],
            'cans_y' => [
                'group'        => 'AbbottStore',
                'type'         => 'decimal',
                'label'        => 'Cans Type Y',
                'input'        => 'price',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false
            ],
            'subscription_info' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Subscription Information',
                'input'        => 'textarea',
                'used_in_product_listing' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'used_in_product_listing' => false
            ],
            'metabolic_state' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Metabolic State',
                'input'        => 'textfield',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true
            ],
            'is_rush' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Is Rush',
                'input'        => 'boolean',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'used_in_product_listing' => true,
                'user_defined' => false,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'is_used_in_grid' => true
            ],
            'active' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Active',
                'input'        => 'boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
            ],
            'brand' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Brand',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true
            ],
            'brand_name' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Brand Name',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true
            ],
            'glucerna_calories' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Calories',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false
            ],
            'glucerna_fiber' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Fiber',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false
            ],
            'glucerna_protein' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Protein',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false
            ],
            'message' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Message',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true
                ],
            'product_flavor' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Product Falvor',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true,
                'is_visible_in_advanced_search' => false
            ],
            'flavors' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'label'        => 'Flavor',
                'input'        => 'select',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => true,
                'visible_on_front' => true,
                'required'     => false,
                'comparable' => true,
                'is_html_allowed_on_front' => true,
                'filterable_in_search' => true
            ],
            'size' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'label'        => 'Size',
                'input'        => 'select',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => true,
                'visible_on_front' => false,
                'required'     => false,
                'used_for_promo_rules' => true,
                'is_html_allowed_on_front' => true
            ],
            'forms' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'label'        => 'Form',
                'input'        => 'select',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => true,
                'visible_on_front' => true,
                'required'     => false,
                'comparable'=> true,
                'is_html_allowed_on_front' => true
            ],
            'parent_sku' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Parent Sku',
                'input'        => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false
            ]
        ];

        foreach ($attributes as $attributeCode => $attributeParam) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParam);
        }

        $flavor_attribute = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'flavors');
        $flavor_options = [
            'values' => ["Chocolate","Strawberry","Vanilla","Creamy Vanilla","Milk Chocolate","Vanilla Cream","Chocolate Fudge","Strawberry Cream","French Vanilla","Rich Dark Chocolate","Blue Raspberry","Chocolate Ice Cream","Vanilla Cake","Fruit Punch","Orange","Lemonberry","Red Apple","Chocolate Almond Raisin","Chocolate Chip Cookie Dough","Chocolate Mint","Chocolate Peanut Butter","Cinnamon Bun Cookie Dough","Cinnamon Roll","Cookies and Cream Cookie Dough","Dark Chocolate Almond","Double Dark Chocolate","Fudge Graham","Yellow Cupcake","Oatmeal Chocolate Chunk","Salted Caramel Brownie","Strawberry Yogurt","Butter Pecan","Coffee Latte","Dark Chocolate","Blueberry Pomegranate","Mixed Fruit","Butterscotch Delight","Chocolate Chip","Peanut Chocolate Chip","Chocolate Caramel","Chocolate Peanut","Oatmeal Raisin","Classic Butter Pecan","Creamy Strawberry","Homemade Vanilla","Rich Chocolate","Unflavored","Cherry Punch","Strawberry Lemonade","Tropical Fruit","Bubble Gum","Variety - Strawberry, Fruit Punch, Grape & Apple","Grape","Cherry","Banana","Berry","Mixed Berry","Cinnamon Bun","Strawberry Banana","Chocolate Cream","Pear","Pumpkin","Mango","Sweet Potato","Butternut Squash","Kiwi Berry Mist","Berry Frost","Chiseled Chocolate","Muscle Mocha","Cinnamon Swole","Hot Chocolate Marshmallow","Salted Caramel Latte","Strawberry Shortcake","Chocolate Cupcake","Sugar Cookie","Apple","Milk-Based","Ocean Side Lemon Lime","Cold Brew Coffee","Big Island Orange","Chocolate Marshmallow","Orange Cream","Berry Jacked","Flavor Finder ","Triple Chocolate","Rich Chocolate & Homemade Vanilla","Rich Chocolate & Creamy Strawberry","Homemade Vanilla & Creamy Strawberry","Banana Nut","Cookies & Cream","Cafe Mocha","German Chocolate","Pina Colada","Strawberry & Vanilla","S&#039;mores","Fruity Cereal Milk","Iced Grape","Chilled Cherry Pomegranate","Salted Peanut Butter Chocolate Chip","Blueberry Crumble","Banana Nut Crunch","Pucker Punch","Orange Frenzy","Strawberry Freeze","Vanilla Ice Cream","Chocolate Caramel Cluster","Sweet & Salty Cashew Pretzel","Crispy Oats & Nuts","Creamy Caramel","White Chocolate Coconut","Pineapple Coconut","Butter Coffee","Vanilla Frosting","White Chocolate Cream","Orange Breeze","Strawberry Chill","Berry Freeze","Peppermint Bark","Pumpkin Spice","Coconut Burst","Fruity Cereal","Classic Vanilla","White Chocolate Peanut Butter","Birthday Cake","Variety - Cherry, Strawberry Lemonade, Orange & Grape","Variety - Berry Frost & Strawberry Freeze","Variety - Strawberry","Variety - Grape, Blue Raspberry, Cherry & Orange","Pear, Blueberry, Spinach","Pumpkin, Banana, Carrot","Mango, Pear, Spinach","Mango, Apple, Butternut Squash, Spinach","Sweet Potato, Apple, Carrot","Mango, Sweet Potato, Pear","Butternut Squash, Pumpkin, Banana, Carrot, Spinach, Broccoli","Apple, Butternut Squash, Banana, Blueberry"],
                        'attribute_id' => $flavor_attribute,
        ];

        $size_attribute = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'size');

        $size_options = [
            'values' => ['2 lb container / Case of 2','0.7 oz packet / Case of 60','0.8 oz Packet / Case of 60','11 fl oz / 3-4 pks','11 oz Tetra / 3-4 pks','1.7 lb canister / Case of 2','11 fl oz Tetra / 3-4 pks','1.3 lb Canister / Case of 4','2 lb Canister / Case of 2','28 oz bottle / ','1.19 lb tub / Case of 4','18g Stickpack / 6-6 Packs','17 fl oz / Case of 12','2 lb tub / Case of 4','1.65 lb tub / Case of 4','17g stickpack / Case of 6-6 ct Cartons','12 oz tub / Case of 4','17g stickpack / 6-6 Packs','1.4 lb container / Case of 4','1.76 oz / Case of 36','1.58 oz / Case of 36','1.58 oz / 36 count','1.26 oz / Case of 30','1.41 oz / Case of 36','8 fl oz bottle / Case of 24','8 fl oz / Case of 16','10 fl oz bottle / Case of 12','14 oz can / Case of 6','4 oz cup / Case of 48','8 fl oz can / Case of 24','8 fl oz bottle / Case of 16','1.41 oz / Case of 24','0.70 oz / Case of 36','8 fl oz can / Case of 16','4.0 ml / 2-pack','1-Pack','25 count','50 count','100-pack','12-pack Glucerna Hunger Smart Homemade Vanilla','12-pack Glucerna Hunger Smart Rich Chocolate','4.0 ml / 1 Pack','10-Count','14.1 oz can / Case of 6','1 L bottle / Case of 8','1 L bottle / Case of 4','2.1 oz packet / Case of 64','0.6 oz / 6-6 Packs','0.3 oz / 8-8 Packs','2 fl oz bottle / Case of 48','1 QT bottle / Case of 6','13 fl oz can / Case of 12','1.45 lb container / Case of 6','12.4 oz can / Case of 6','30.8 oz can / Case of 6','17.4 g packets / Case of 64','19.8 oz can / Case of 4','12.1 oz can / Case of 6','Case of 250','4 fl oz bottle / Case of 12','4 fl oz bottle / Case of 144','60 mL bottle / Case of 100','60 mL bottle / 10-pack','1.41 lb container / Case of 6','12 oz can / Case of 6','1.5 lb can / Case of 6','1.5 lb container / Case of 4','1.46 lb can / Case of 4','10 servings (8.7g each)  / 4-10 packs','1.5 lb can / Case of 4','0.61 oz packets / Case of 64','0.9 g / Case of 150','Case of 250','Case of 50','13.1 oz can / Case of 6','1.45 lb container / Case of 4','1.41 lb container / Case of 4','12.4 oz can / Case of 4','31.8 oz can / Case of 4','29.8 oz can / Case of 6','Case of 100','2 lb container','5 lb container','1.7 lb canister','1.3 lb Canister','2 lb Canister ','2 lb canister','1.19 lb tub','18g Stickpack / 6-pack','2.29 oz / 14 Count','1.9 oz  / Case of 20','2 lb tub','1.65 lb tub','2.7 oz packet / Case of 20','17g stickpack / 6 ct Carton','12 oz tub','17g stickpack / 6-pack','1.76 oz / 12 Pack','1.58 oz / 12 Pack','1.41 oz / 12 Pack','14 oz can','30 day supply','3-pack / 90 day supply','6-pack','100-pack','5 Batteries','14.1 oz can','1.45 lb container','30.8 oz can','19.8 oz can','19.8 oz','12.1 oz can','13.1 oz can','1.41 lb container','1.5 lb container','1.46 lb can','10 servings (8.7g each)','1.5 lb can','0.9 g / Pack of 50','12.4 oz can','31.8 oz can','31.8 oz ','29.8 oz can','29.8 oz ','2 fl oz bottle / Pack of 8','22.5 oz','1 QT bottle','Case of 12','10 fl oz bottle / Case of 4','16 fl oz / Case of 12','1.41 oz / Case of 12','Case of 30','8 fl oz Tetra / Case of 24','4 fl oz bottle / Case of 24','8 fl oz Tetra / Case of 15','114g / Case of 12','30.8 oz can / Case of 4','30.8 oz ','29.8 oz can / Case of 4','1.93 lb can / Case of 6','1.93 lb container','8 fl oz carton / Case of 24','0.61 oz packet / Case of 64','12-pack Glucerna Hunger Smart Creamy Strawberry','0.97 oz packet / 8-pack','0.97 oz packet / 30-pack','1.02 oz packet / 30-pack','1.02 oz packet / 8-pack','0.81 oz packet / 30-pack','7.9g stickpack / 4-10 packs','7.9g stickpack / 10-pack','0.92 lb bag / Case of 4','0.92 lb bag / 1 Bag','3 Shakes & 3 Bars','6 Rich Chocolate & 6 Homemade Vanilla Shakes ','6-pack of each (12 total)','6 Rich Chocolate & 6 Creamy Strawberry Shakes','6 Homemade Vanilla & 6 Creamy Strawberry Shakes','12-pack Glucerna Hunger Smart Creamy Strawberry / Case of 12','10 fl oz bottle','12-pack Glucerna Hunger Smart Homemade Vanilla / Case of 12','12-pack Glucerna Hunger Smart Rich Chocolate / Case of 12','16.4g packets / Case of 64','11 fl oz Tetra / Case of 12','14.1 oz can / Case of 3','11 fl oz / Case of 12','2-10 fl oz bottles & 15-8 fl oz cartons','2.12 oz bar / Case of 24','2.12 oz bar / 8 Pack','22.5 oz container / Case of 4','22.5 oz container / ','32g sachet (5 gummies) / 36 count','32g sachet (5 gummies) / 12 Count','1 Starter Pack','320g Tub / Case of 4','320g Tub','1 QT bottle / Case of 4','17.4g stickpack / 4-16 packs','Small','Medium','Large','X-Large','1.76 oz / Case of 30','1.58 oz / Case of 30','1.4 oz / Case of 20','16 fl oz Tetra / Case of 12','1.1 oz packet / Case of 20','22.8 oz can / Case of 4','22.8 oz can','1.41 oz / Case of 30','3-10 fl oz bottles & 10-8 fl oz cartons','3-10 fl oz bottles & 20-8 fl oz cartons','3-10 fl oz bottles & 28-8 fl oz cartons','3-10 fl oz bottles & 56-8 fl oz cartons','2 fl oz bottle / Pack of 4','24.7 oz can / Case of 4','24.7 oz can','1.4 lb container / Case of 2','0.6 oz / Case of 80','1 L bottle','0.6 oz / 6 Pack','2.13 lb container / Case of 6','0.3 oz / 8 Pack','2 fl oz bottle / 4 Pack','2.25 lb can / Case of 3','3 Pre-Surgery Drinks & 56 Immunonutrition Shakes & 4 Ensure Enlive Shakes','3 Pre-Surgery Drinks & 56 Immunonutrition Shakes & 6 Glucerna Hunger Smart Shakes','3 Pre-Surgery Drinks & 28 Immunonutrition Shakes & 4 Ensure Enlive Shakes','3 Pre-Surgery Drinks & 28 Immunonutrition Shakes & 6 Glucerna Hunger Smart Shakes','0.70 oz / Case of 24','13.2 oz can / Case of 6','5.3 oz can / Case of 6','144 pouches / Case of 144','18g Stickpack','17g stickpack / Case of 6','0.6 oz / (6) 6-packs','0.3 oz / (8) 8-packs','10 servings (8.7g each) ','0.61 oz packets / Case of 4','17g stickpack','1.58 oz / 12 count','17g stickpack','1.41 oz','8 fl oz Tetra','8 fl oz carton','8 fl oz Tetra Pak / Case of 24','1.01 oz packet / 30-pack','1.01 oz packet / 8-pack','Case of 16','Case of 4','17.4g stickpack / 64 count','Case of 13','Case of 23','Case of 31','Case of 59','0.6 oz','0.3 oz','2 fl oz bottle','Case of 63','Case of 65','Case of 35','Case of 37'],
                        'attribute_id' => $size_attribute,
        ];

        $form_attributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'forms');

        $form_options = [
            'values' => ['Powder','Shake','Ready-to-Drink','Nutrition Bar','Nutrition Shake','Nutritional Drink','Liquid','Snack Bar','Mini Treat Bar','Test Strip','Powder Packs','Shake Mix','Ready-to-Feed','Concentrated Liquid','Softgels','group_sku_of_5743076e','Puree','Bar','Gummy','Macros Bar'],'attribute_id' => $form_attributeId,
        ];

        $eavSetup->addAttributeOption($flavor_options);
        $eavSetup->addAttributeOption($size_options);
        $eavSetup->addAttributeOption($form_options);
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
