<?php
session_start();
include_once 'common/tools.php';
$vcode=vcode(100,40,4,24);
$_SESSION['vcode'] = $vcode;

?>