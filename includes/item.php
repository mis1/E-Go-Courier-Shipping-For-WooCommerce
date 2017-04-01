<?php
namespace Mes\EGo;

class Item
{
	function __construct($l, $w, $h, $weight, $weight_unit='kg', $dim_unit='cm')
	{
		$this->l=$l;
		$this->w=$w;
		$this->h=$h;
		$this->weight=$weight;
		$this->weight_unit=$weight_unit;
		$this->dim_unit = $dim_unit;
	}
	public function get_volume()
	{
		return $this->l * $this->w *  $this->h;
	}
}

class Box extends Item
{
	function __construct($l, $w, $h, $weight, $weight_unit='kg', $dim_unit='cm', $type = 'Large Carton', $el=0, $ew=0, $eh=0)
	{
		parent::__construct($l, $w, $h, $weight, $weight_unit, $dim_unit);
		$this->el = $el?$el:$l;
		$this->ew = $ew?$ew:$w;
		$this->eh = $eh?$eh:$h;
		$this->type = $type;
	}
}