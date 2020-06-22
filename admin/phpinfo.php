<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';


$link = db_connect();
if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}

phpinfo();
?>