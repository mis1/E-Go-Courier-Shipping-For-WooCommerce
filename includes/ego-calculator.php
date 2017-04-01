<?php
namespace Mes\EGo;

include_once 'last-error.php';

class EGo_Calulator
{
	use Last_Error;
	public function __construct($ego_service)
	{
		$this->ego_service = $ego_service;
	}
	
	public function calculate_cost($boxes, $pickup_postcode, $pickup_suburb, $delivery_postcode)
	{
		$delivery_suburb = $this->ego_service->get_suburb($delivery_postcode);

		if (!$delivery_suburb){
			$this->set_last_error($this->ego_service->get_last_error());
			return false;
		}

		if (count($boxes) == 1){
			$params['width']=ceil($boxes[0]->l);
			$params['height']=ceil($boxes[0]->w);
			$params['depth']=ceil($boxes[0]->h);
			$params['weight']=ceil($boxes[0]->weight);
			$params['items'] = 1;
			$params['type'] = $boxes[0]->type;

		}
		else{
			foreach ($boxes as $box) {
				$params['width'][] = ceil($box->l);
				$params['height'][] = ceil($box->w);
				$params['depth'][] = ceil($box->h);
				$params['weight'][] = ceil($box->weight);
				$params['items'][] = 1;
				$params['type'][] = $box->type;
			}
		}

		$params['pickuppostcode'] = $pickup_postcode;
		$params['pickupsuburb'] = $pickup_suburb;
		$params['deliverypostcode'] = $delivery_postcode;
		$params['deliverysuburb'] = $delivery_suburb;
		
		if (FALSE === $cost = $this->ego_service->calculate_cost($params)){
			$this->set_last_error($this->ego_service->get_last_error());
		}
		return $cost;
	}
}