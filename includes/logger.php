<?php
namespace Mes\EGo;

trait Logger{
	public $debug_mode = false;
	
	public function set_debug_mode($value)
	{
		$this->debug_mode = $value;
	}
	
	public function log($title, $message, $type = 'notice' ) {
        if ($this->debug_mode) {
        	$msg = "<strong>$title</strong><br>$message";
        	if (!wc_has_notice($msg, $type))
        		wc_add_notice( $msg, $type );
        }
    }
}