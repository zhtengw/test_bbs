
<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    skip_page('child_module.php', 'error', '参数错误！');
}
if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}

$link = db_connect();
$query = 'delete from bbs_child_module where id='.$_GET['id'];
$result = db_exec($link,$query);

if(mysqli_affected_rows($link) == 1){
    skip_page('child_module.php', 'ok', '删除成功！');
} else {
    skip_page('child_module.php', 'error', '删除失败，请重试！');
}

?>