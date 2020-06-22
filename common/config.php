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
//if ($_SERVER['DOCUMENT_ROOT'] == $_SERVER['CONTEXT_DOCUMENT_ROOT']){
//    define('SUB_URL',str_replace($_SERVER['DOCUMENT_ROOT'],'',FULL_HOST));
//} else {
    define('SUB_URL',$_SERVER['CONTEXT_PREFIX'].str_replace($_SERVER['CONTEXT_DOCUMENT_ROOT'],'',FULL_HOST));
//}

?>