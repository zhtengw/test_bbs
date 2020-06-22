<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';


$url_refer_arr = parse_url($_SERVER['HTTP_REFERER']);
$url_refer =$url_refer_arr['path'].'?'.$url_refer_arr['query']; 
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    skip_page('index.php', 'error', '参数错误！');
}

$link = db_connect();
$member = is_login($link);
$admin = is_admin_login($link);
if(!$member && !$admin){
    skip_page('login.php', 'error', '请先登录！');
}

$reply_query = 'select member_id,content_id from bbs_reply where id='.$_GET['id'];
$reply_result = db_exec($link,$reply_query);
$reply=mysqli_fetch_assoc($reply_result);
$child_query = 'select child.member_id from bbs_child_module child,bbs_content where child.id=bbs_content.module_id and bbs_content.id='.$reply['content_id'];
$child_result = db_exec($link,$child_query);
$child=mysqli_fetch_assoc($child_result);
// 发帖人、版主和后台管理员均有权限
if($reply['member_id']!=$member['id'] && $child['member_id'] !=$member['id'] && !$admin ){
    skip_page('index.php', 'error', '您没有权限！');
}

// 删除回复
$query = 'delete from bbs_reply where id='.$_GET['id'];
$result = db_exec($link,$query);

if(mysqli_affected_rows($link) == 1){
    skip_page('post.php?id='.$_GET['post_id'], 'ok', '删除成功！');
} else {
    skip_page($url_refer, 'error', '删除失败，请重试！');
}

?>