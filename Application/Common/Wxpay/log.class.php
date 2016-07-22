<?php

class Log
{
	// 打印log
	function  logresult($file,$word) 
	{
	    $fp = fopen($file,"a");
	    flock($fp, LOCK_EX) ;
	    fwrite($fp,"执行日期：". date("Y-m-d H:i:s") ."\n".$word."\n\n");
	    flock($fp, LOCK_UN);
	    fclose($fp);
	}
}

?>