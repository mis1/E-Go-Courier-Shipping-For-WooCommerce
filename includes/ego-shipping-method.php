<?php

include_once 'item.php';
include_once 'egoservice-impl.php';
include_once 'egoservice-decorator.php';
include_once 'ego-calculator.php';
include_once 'logger.php';

use Mes\EGo\Item;
use Mes\EGo\Box;
use Mes\EGo\EGo_Calulator;
use Mes\EGo\EGoService_Impl;
use Mes\EGo\Logger;
use Mes\EGo\EGoService_Decorator;

class EGo_Shipping_Method extends WC_Shipping_Method {
    use Logger;

	const SETTING_KEY_ENABLED = 'enabled';
    const SETTING_KEY_TITLE = 'title';
    const SETTING_KEY_DEBUG = 'debug';
    const SETTING_KEY_POSTCODE = 'postcode';
    const SETTING_KEY_SUBURB = 'suburb';

    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
	public function __construct($instance_id = 0 ) {
     	$this->id                 = 'mes-ego-shipping-method'; 
        $this->instance_id = absint( $instance_id );
		$this->method_title       = 'E-Go Courier';
		$this->method_description = 'Integrates with E-Go Courier allowing you to provide accurate shipping quotes for your customers.'; 
		$this->supports = array(
            'shipping-zones',
            'instance-settings',
             //'instance-settings-modal',
        );
		//add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		
		$this->init_form_fields();
        $this->init_instance_settings();
        
        $this->enabled = isset( $this->instance_settings[self::SETTING_KEY_ENABLED] ) ? $this->instance_settings[self::SETTING_KEY_ENABLED]: 'no';
        $title = $this->instance_settings[self::SETTING_KEY_TITLE];
        $this->title = isset($title) && !empty($title) ? $title: $this->method_title;
        $this->debug_mode = isset( $this->instance_settings[self::SETTING_KEY_DEBUG] ) ? $this->instance_settings[self::SETTING_KEY_DEBUG] == 'yes': false;
        $this->pickup_postcode = isset( $this->instance_settings[self::SETTING_KEY_POSTCODE] ) ? $this->instance_settings[self::SETTING_KEY_POSTCODE] : false;
        $this->pickup_suburb = isset( $this->instance_settings[self::SETTING_KEY_SUBURB] ) ? $this->instance_settings[self::SETTING_KEY_SUBURB] : false;
        if ('yes' == $this->enabled && (!$this->pickup_postcode || !$this->pickup_suburb)){
            $this->enabled = 'no';
        }
	}
	
	/**
     * Define settings field for this shipping
     * @return void 
     */
	public function init_form_fields() {
		$this->instance_form_fields = include( 'ego-shipping-method-settings.php' );
	}
	
	/**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
	public function calculate_shipping( $package = [] ) {
        $this->log($this->title, 'Debug mode is on - to hide these messages, turn debug mode off in the settings.');
        if (!$this->pickup_postcode || !$this->pickup_suburb){
            $this->log($this->title, 'Either Origin Postcode or Suburb is missing. Aborting', 'error');
            return;
        }

        $country = $package["destination"]["country"];    
        $delivery_postcode = $package["destination"]["postcode"];
        if (empty($delivery_postcode)){
            $this->log($this->title, 'Deliverly post code is missing. Aborting.','error');
            return;
        }
        
        $items = [];
        $index = 0;
        foreach ( $package['contents'] as $item_id => $values ) 
        { 
            $index++;
            $product = $values['data'];
            if ($this->is_virtual($product, $index)){
                continue;
            }
            if (!$this->is_shippable($product, $index)){
                return;
            }
            for($i=0; $i<$values['quantity']; $i++){
	           	$length = wc_get_dimension($product->get_length(), 'cm');
	           	$width  = wc_get_dimension($product->get_width(), 'cm');
	           	$height = wc_get_dimension($product->get_height(), 'cm');
	            $weight = wc_get_weight($product->get_weight(), 'kg');
                
        		$items[] = new Box($length, $width, $height, $weight);
        	}
        }
        
        $ego_service = new EGoService_Decorator(new EGoService_Impl());
        $ego_calculator = new EGo_Calulator($ego_service);
        $ego_service->set_debug_mode($this->debug_mode);
    
        $boxes = $items;
        $cost = $ego_calculator->calculate_cost($items, $this->pickup_postcode, $this->pickup_suburb, $delivery_postcode);
        
        if (FALSE === $cost){
            $this->log($this->title, $ego_calculator->get_last_error(), 'error');
            return;
        }
        $rate = array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $cost
        );
        $this->add_rate($rate);
    }

    private function is_virtual($product, $index){
        if ( !$product->needs_shipping() ) {
            $this->log($this->title, sprintf('Product # %d in cart is virtual. Skipping it.', $index));
            return true;
        }
        return false;
    }

    private function is_shippable($product, $index){
        if (!$product->get_weight() || !$product->get_length() || !$product->get_width() || !$product->get_height()) {
                $this->log($this->title, sprintf('Product # %d in cart is missing weight or dimension. Aborting.', $index ),'error');
                return false;
        }
        return true;
    }
}
