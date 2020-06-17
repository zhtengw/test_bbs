
<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect();
if($member=is_login($link)) {
    $query = 'select * from bbs_member where id='.$member['id'];
    if(record_count($link,$query)){
        del_cookie();
        skip_page('index.php', 'ok', '注销成功，现在跳转到首页！');
    }

}

skip_page('index.php', 'error', '您没有权限！');


?>