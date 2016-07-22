<?php

include_once("SDKRuntimeException.class.php");
include_once("WxPaypubconfig.class.php");
class  SDKRuntimeException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}

}

?>