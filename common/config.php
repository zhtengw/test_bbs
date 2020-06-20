<?php
session_start();
date_default_timezone_set('Asia/Shanghai');//设置时区
header('Content-type:text/html;charset=utf-8');
define('FULL_HOST',dirname(__FILE__,2));
define('DB_HOST','localhost');
define('DB_USER','aten');
define('DB_PWD','123456');
define('DB_NAME','test_bbs');
define('DB_PORT',3306);

?>