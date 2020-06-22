<?php 
    $thispage = basename($_SERVER['SCRIPT_NAME']);
    
    if (!isset($template['title'])) $template['title']='后台管理';
    
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title><?php echo $template['title'] ?> - 后台管理</title>
<meta name="keywords" content="后台界面" />
<meta name="description" content="后台界面" />
<?php
if (isset($template['css'])) { 
	foreach ($template['css'] as $css){?>
	<link rel="stylesheet" type="text/css" href="<?php echo $css ?>" />
<?php }
 } else { ?>
	<link rel="stylesheet" type="text/css" href="style/public.css" />
<?php
 }?>
</head>
<body>
	<div id="top">
		<div class="logo">
			管理中心
		</div>
		<ul class="nav">
			<li><a href="https://www.aiaten.com" target="_blank">Aten Zhang</a></li>
		</ul>
		<div class="login_info">
			<a href="../index.php" style="color:#fff;">网站首页</a>&nbsp;|&nbsp;
			<?php if($admin){?>
			管理员： <?php echo $admin['name'] ?> <a href="admin_logout.php">[注销]</a>
			<?php } else { ?>
			<a href="admin_login.php">管理员登录</a>
			<?php }?>
		</div>
	</div>
	<div id="sidebar">
		<ul>
			<li>
				<div class="small_title">系统</div>
				<ul class="child">
					<li><a <?php if($thispage == "index.php") echo 'class="current"'; ?> href="index.php">系统信息</a></li>
					<li><a <?php if($thispage == "admin.php") echo 'class="current"'; ?> href="admin.php">管理员</a></li>
					<li><a <?php if($thispage == "admin_add.php") echo 'class="current"'; ?> href="admin_add.php">添加管理员</a></li>
					<li><a <?php if($thispage == "web_config.php") echo 'class="current"'; ?> href="web_config.php">站点设置</a></li>
				</ul>
			</li>
			<li><!--  class="current" -->
				<div class="small_title">内容管理</div>
				<ul class="child">
					<li><a <?php if($thispage == "parent_module.php") echo 'class="current"'; ?> href="parent_module.php">父版块列表</a></li>
					<li><a <?php if($thispage == "parent_module_add.php") echo 'class="current"'; ?> href="parent_module_add.php">添加父版块</a></li>
					<li><a <?php if($thispage == "child_module.php") echo 'class="current"'; ?> href="child_module.php">子版块列表</a></li>
					<li><a <?php if($thispage == "child_module_add.php") echo 'class="current"'; ?> href="child_module_add.php">添加子版块</a></li>
					<li><a href="../index.php">帖子管理</a></li>
				</ul>
			</li>
			<li>
				<div class="small_title">用户管理</div>
				<ul class="child">
					<li><a href="#">用户列表</a></li>
				</ul>
			</li>
		</ul>
	</div>