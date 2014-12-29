<?php

$connect = mysql_connect ('127.0.0.1','root','');
if(!$connect)
{
	die('NO Connection');
}
mysql_select_db("rainbow_candy",$connect);





?>