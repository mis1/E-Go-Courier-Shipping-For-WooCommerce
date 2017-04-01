<?php
namespace  Mes\EGo;

interface EGoService{
	function get_suburb($post_code);
	function calculate_cost($post_code);	
}