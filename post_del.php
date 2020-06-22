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

$post_query = 'select member_id,module_id from bbs_content where id='.$_GET['id'];
$post_result = db_exec($link,$post_query);
$post=mysqli_fetch_assoc($post_result);
$child_query = 'select member_id from bbs_child_module where id='.$post['module_id'];
$child_result = db_exec($link,$child_query);
$child=mysqli_fetch_assoc($child_result);
// 发帖人、版主和后台管理员均有权限
if($post['member_id']!=$member['id'] && $child['member_id'] !=$member['id'] && !$admin ){
    skip_page('index.php', 'error', '您没有权限！');

}

// 删除帖子及回复
$query = [
        'delete from bbs_content where id='.$_GET['id'],
        'delete from bbs_reply where content_id='.$_GET['id']
        ];
if(db_multiexec($link,$query,$result,$error)){
    skip_page('index.php', 'ok', '删除成功！');
} else {
    skip_page($url_refer, 'error', '删除失败，请重试！');
}

?>