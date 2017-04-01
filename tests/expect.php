<?php

trait Expect{
	function fe($mock, $times, $with=null, $ret=null){
		if ($times)
			$mock = $mock->expects($this->exactly($times));
	    if (isset($with)){
	    	if (is_array($with))    
	        	$mock = call_user_func_array([$mock, 'with'], $with);
	        else
	        	$mock = $mock->with($with);
        }
	    if (isset($ret))
	        $mock = $mock->willReturn($ret);
	    return $mock;
	}
	function oe($mock, $method, $times, $with = null, $ret = null){
		if ($times)
			$mock = $mock->expects($this->exactly($times));
		if (isset($method))
			$mock = $mock->method($method);
	    if (isset($with)){
	    	if (is_array($with))    
	        	$mock = call_user_func_array([$mock, 'with'], $with);
	        else
	        	$mock = $mock->with($with);
        }
	    if (isset($ret))
	        $mock = $mock->willReturn($ret);
	    return $mock;
	}
	function gm($class, $methods){
		return $this->getMockBuilder($class)
        	->setMethods($methods)->getMock();
	}
	
	
}
