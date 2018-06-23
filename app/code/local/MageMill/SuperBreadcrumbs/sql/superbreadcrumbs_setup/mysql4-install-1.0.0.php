<?php
/**
 * @package     Mtrgovina_MegaMenu
 * @copyright   Copyright (c) 2014 mtrgovina.com
 */
 
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_category', 'magemill_superbreadcrumbs', array(
    'group'             => 'General Information',
    'type'              => 'int',
    'backend'           => '',
    'frontend_input'    => '',
    'frontend'          => '',
    'label'             => 'Include in Breadcrumbs',
    'input'             => 'select',
    'default'           => 1,
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'             => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'frontend_class'     => '',
    'required'          => false,
    'user_defined'      => true,
    'position'            => 100,
));

$installer->endSetup();