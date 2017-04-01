<?php
namespace Mes\EGo;

trait Last_Error{
	public function set_last_error($value)
	{
		$this->last_error = $value;
	}
	public function get_last_error()
	{
		return $this->last_error;
	}
}