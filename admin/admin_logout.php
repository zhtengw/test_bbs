
<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$link = db_connect();

$url_refer_arr = parse_url($_SERVER['HTTP_REFERER']);
$url_refer =$url_refer_arr['path'].'?'.$url_refer_arr['query']; 

if($admin=is_admin_login($link)) {
    session_unset();
    session_destroy();
    header('Location:admin_login.php');
}

skip_page('../index.php', 'error', '您没有权限！');


?>