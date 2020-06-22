
<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    skip_page('admin.php', 'error', '参数错误！');
}

$link = db_connect();
if(!$admin = is_admin_login($link)){
    header('Location:admin_login.php');
}

if(!isset($admin['level']) || $admin['level']!=0){
    skip_page('admin.php', 'error', '您没有权限！');
}

$query = 'delete from bbs_admin where id='.$_GET['id'];
$result = db_exec($link,$query);

if(mysqli_affected_rows($link) == 1){
    skip_page('admin.php', 'ok', '删除成功！');
} else {
    skip_page('admin.php', 'error', '删除失败，请重试！');
}

?>