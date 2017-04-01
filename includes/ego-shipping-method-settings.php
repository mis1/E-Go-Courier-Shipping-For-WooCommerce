<?php

/**
 * Array of settings
 */
return [
     self::SETTING_KEY_TITLE => array(
        'title' => 'Method Title',
        'description' => 'Enter title for shipping method. This is shown to user on cart page',
        'default' => 'E-GO Courier',
        'desc_tip'    => true,
        'type' => 'text'),
    self::SETTING_KEY_POSTCODE => array(
        'title' => 'Origin Postcode',
        'description' => 'Enter postcode for the <strong>Shipper</strong>.',
        'desc_tip'    => true,
        'type' => 'text'),
    self::SETTING_KEY_SUBURB => array(
        'title' => 'Origin Suburb ',
        'description' => 'Enter suburb for the <strong>Shipper</strong>.',
        'desc_tip'    => true,
        'type' => 'text'),
	self::SETTING_KEY_DEBUG => array(
		'title' => 'Debug Mode',
        'label' => 'Enable',
		'description' => 'Enable this option to turn on debugging.',
        'desc_tip'    => true,
		'type' => 'checkbox',
        'default' => 'no',),
];