<?php
namespace  Mes\EGo;

include_once 'egoservice.php';

class EGoService_Decorator implements EGoService{
	const TRANSIENT_KEY_SUBURBS = 'mes-ego-suburbs';
	const TRANSIENT_CACHE_DURATION = WEEK_IN_SECONDS;

	private $ego_service = null;
	public function __construct($ego_service){
		$this->ego_service = $ego_service; 
	}

	public function get_suburb($post_code){
		$suburbs = get_transient(self::TRANSIENT_KEY_SUBURBS);
		if (false !== $suburbs && isset($suburbs[$post_code])){
			return $suburbs[$post_code];
		}
		$suburb = $this->ego_service->get_suburb($post_code);
		$suburbs[$post_code] = $suburb;
		set_transient(self::TRANSIENT_KEY_SUBURBS, $suburbs, self::TRANSIENT_CACHE_DURATION);
		return $suburb;
	}

	public function calculate_cost($parmas){
		$cost = $this->ego_service->calculate_cost($parmas);
		return $cost;	
	}

	public function get_last_error()
	{
		return $this->ego_service->get_last_error();
	}

	public function set_debug_mode($value)
	{
		$this->ego_service->set_debug_mode($value);
	}
	
	public function log($title, $message, $type = 'notice') 
	{
		$this->ego_service->log($title, $message, $type);	
	}
}