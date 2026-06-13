<?php
// Admin controller: prepare data for admin views

include_once("controller/cProduct.php");
include_once("controller/cType.php");

class cAdmin
{
	public function getProducts()
	{
		$p = new cProduct();
		return $p->cListProduct();
	}

	public function getTypes()
	{
		$t = new cType();
		return $t->cListType();
	}
}
