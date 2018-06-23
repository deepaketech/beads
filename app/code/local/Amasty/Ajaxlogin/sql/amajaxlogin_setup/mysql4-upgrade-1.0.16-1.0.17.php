<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$this->startSetup();
$installer->addAttribute('catalog_category', 'extra_description', array(
        'group'             => 'General Information',
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Extra Description',
        'wysiwyg_enabled' => true,
        'visible_on_front' => true,
        'is_html_allowed_on_front' => true,
        'input'             => 'textarea',
        'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => 1,
        'required'          => 0,
        'user_defined'      => 0,
        'default'           => '',
        'searchable'        => 0,
        'filterable'        => 0,
        'comparable'        => 0,
        'visible_on_front'  => 0,
        'unique'            => 0,
        'position'          => 1,
));
$installer->updateAttribute('catalog_category', 'extra_description', 'is_wysiwyg_enabled', 1);
$installer->updateAttribute('catalog_category', 'extra_description', 'is_html_allowed_on_front', 1);

$this->endSetup();
