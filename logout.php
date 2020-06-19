
<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect();

$url_refer_arr = parse_url($_SERVER['HTTP_REFERER']);
$url_refer =$url_refer_arr['path'].'?'.$url_refer_arr['query']; 

if($member=is_login($link)) {
    $query = 'select * from bbs_member where id='.$member['id'];
    if(record_count($link,$query)){
        del_cookie();
        skip_page($url_refer, 'ok', '注销成功，现在跳转到之前浏览页面！');
    }

}

skip_page('index.php', 'error', '您没有权限！');


?>