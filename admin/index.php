<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';


$link = db_connect();
if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}

$levels = [
    0 => '超级管理员',
    1 => '普通管理员',
];

$parent_query = 'select id from bbs_parent_module';
$parent_count = record_count($link,$parent_query);

$child_query = 'select id from bbs_child_module';
$child_count = record_count($link,$child_query);

$post_query = 'select id from bbs_content';
$post_count = record_count($link,$post_query);

$reply_query = 'select id from bbs_reply';
$reply_count = record_count($link,$reply_query);

$member_query = 'select id from bbs_member';
$member_count = record_count($link,$member_query);

$admin_query = 'select id from bbs_admin';
$admin_count = record_count($link,$admin_query);

$template['title']='系统信息';

?>
<?php include 'include/header.php' ;?>

<div id="main" style="height:1000px;">
		<div class="title">系统信息</div>
        
        <div class="explain">
		<ul>
			<li>|- 您好，<?php echo $admin['name']?></li>
			<li>|- 所属角色：<?php echo $levels[$admin['level']]?> </li>
			<li>|- 创建时间：<?php echo $admin['create_time']?></li>
			<li>|- 上次登录时间：<?php echo $admin['last_time']?></li>
		</ul>
	</div>
	<div class="explain">
		<ul>
			<li>|- 父版块(<?php echo $parent_count?>) 子版块(<?php echo $child_count?>) 帖子(<?php echo $post_count?>) 回复(<?php echo $reply_count?>) 会员(<?php echo $member_count?>) 管理员(<?php echo $admin_count?>)</li>
		</ul>
	</div>
	<div class="explain">
		<ul>
			<li>|- 服务器操作系统：<?php echo PHP_OS?> </li>
			<li>|- 服务器软件：<?php echo $_SERVER['SERVER_SOFTWARE']?> </li>
			<li>|- MySQL 版本：<?php echo mysqli_get_server_info($link)?></li>
			<li>|- 最大上传文件：<?php echo ini_get('upload_max_filesize')?></li>
			<li>|- 内存限制：<?php echo ini_get('memory_limit')?></li>
			<li>|- <a target="_blank" href="phpinfo.php">PHP 配置信息</a></li>
		</ul>
	</div>
	
	<div class="explain">
		<ul>
			<li>|- 程序安装位置(绝对路径)：<?php echo FULL_HOST?></li>
			<li>|- 程序在web根目录下的位置(首页的url地址)：<?php echo SUB_URL?></li>
			<li>|- 程序版本：test_bbs V1.0 <a target="_blank" href="https://github.com/zhtengw/test_bbs">[查看最新版本]</a></li>
			<li>|- 程序作者：Aten Zhang</li>
			<li>|- 网站：<a target="_blank" href="https://www.aiaten.com">www.aiaten.com</a></li>
		</ul>
	</div>
		
	</div>


<?php include 'include/footer.php' ;?>