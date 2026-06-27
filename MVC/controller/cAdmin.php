<?php
// Admin controller: prepare data for admin views

include_once("controller/cProduct.php");
include_once("controller/cType.php");

class cAdmin
{
	public function getProducts()
	{
		// $p = new cProduct();
		// return $p->cListProduct();

		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		$p = new cProduct();
		$tbl = $p->cListProduct($page);
	}

	public function getTypes()
	{
		$t = new cType();
		return $t->cListType();
	}
}
