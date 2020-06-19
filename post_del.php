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