<?php
namespace  Mes\EGo;

include_once 'egoservice.php';
include_once 'last-error.php';
include_once 'logger.php';

class EGoService_Impl implements EGoService{
	use Last_Error;
	use Logger;

	const SUBURB_API = 'http://www.e-go.com.au/suburbAPI2';
	const CALCULATOR_API = 'http://www.e-go.com.au/calculatorAPI2';

	public function get_suburb($post_code){
		$url = self::SUBURB_API . '?postcode=' . urlencode($post_code);
		$this->log('suburbAPI2 request:', $url);
		$response = wp_remote_get($url);
		if (is_wp_error($response)) {
			$this->set_api_error($response);
			return false;
   		}
		$body = wp_remote_retrieve_body($response);
		$this->log('suburbAPI2 response', $body);
		$lines = $this->parse_response($body, ',');
		if (!$lines)
			return false;
		return $lines[0][0];
		
	}

	public function calculate_cost($params){
		if (!$this->verify_params($params)){
			return false;
		}
		$encoded_body = http_build_query($params);
		$this->log('calculatorAPI2 request:', json_encode($params));
		$response = wp_remote_post(self::CALCULATOR_API, ['body'=>$encoded_body, 'headers'=>['Content-Type'=>'application/x-www-form-urlencoded']]);
		
		if (is_wp_error($response)) {
			$this->set_api_error($response);
   			return false;
   		}
		$body = wp_remote_retrieve_body($response);
		$this->log('calculatorAPI2 response:', $body);
		$lines = $this->parse_response($body, '=');
		if (!$lines || count($lines)<3){
			$this->set_invalid_response_error($body);
			return false;
		}
		return $lines[2][1];
	}

	private function parse_response($response, $field_delimiter){
		$lines = explode("\n", $response);
		$ret = [];			
		foreach ($lines as $line) {
			$tokens = explode($field_delimiter, $line);
			if (count($tokens)<2 || strlen(trim($tokens[0]))===0 || strlen(trim($tokens[1]))===0){
					$this->set_invalid_response_error($response);
					return false;
			}
			$ret[] = [trim($tokens[0]), trim($tokens[1])];
		}
		return $ret;
	}

	private function verify_params($params){
		$keys = ['pickuppostcode', 'pickupsuburb', 'deliverypostcode', 'deliverysuburb', 'height', 'width', 'depth', 'weight', 'type', 'items'];
		$dim_keys = ['height', 'width', 'depth', 'weight', 'items'];
		foreach ($keys as $key) {
			if (!isset($params[$key])){
				$msg = sprintf('service failed. incomplete input. "%s" not set. params = %s', $key, json_encode($params));
				$this->set_last_error($msg);
				return false;
			}
		}
		$count = 0;
		foreach ($dim_keys as $key) {
			$count += count($params[$key]);
		}
		if ($count / count($params['items']) != count($dim_keys)){
			$this->set_last_error('service failed. invalid input. incorrect items count. params = ' . json_encode($params));
			return false;	
		}
		return true;
	}

	private function set_api_error($response){
		$msg = sprintf('Service failed. Network Error. %s, %d', $response->get_error_message(), $response->get_error_code());
		$this->set_last_error($msg);
	}

	private function set_invalid_response_error($response){
		$msg = sprintf('Service failed. E-Go API Error. invalid response. %s', $response);
		$this->set_last_error($msg);
	}

}