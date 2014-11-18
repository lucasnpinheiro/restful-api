<?php 
require_once("base/RestfulApiBase.php");

class Api extends RestfulApiBase
{
	function __contruct()
	{

	}

	function execute()
	{
		$this->run($this);
	}

	function cajas_get()
	{
		return $this->collectionParams;
	}
}

$obj = new Api();

$obj->execute();

?>