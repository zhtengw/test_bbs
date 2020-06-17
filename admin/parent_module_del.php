
<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    skip_page('parent_module.php', 'error', '参数错误！');
}

$link = db_connect();
// 查询子版块
$check_child_query = 'select * from bbs_child_module where parent_module_id='.$_GET['id'];
if(record_count($link,$check_child_query)){
    skip_page('parent_module.php', 'error', '该父版块下面存在子版块，请先删除对应子版块再做此操作！');
}

// 执行删除
$query = 'delete from bbs_parent_module where id='.$_GET['id'];
$result = db_exec($link,$query);

if(mysqli_affected_rows($link) == 1){
    skip_page('parent_module.php', 'ok', '删除成功！');
} else {
    skip_page('parent_module.php', 'error', '删除失败，请重试！');
}

?>