<?php 
	$thispage = basename($_SERVER['SCRIPT_NAME']);
	//exit ($thispage);
    
$query = 'select title,keywords,description from bbs_info';
$result = db_exec($link,$query);
$info = mysqli_fetch_assoc($result);
    
if (!isset($template['title'])) $template['title']=$info['title'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title><?php echo $template['title'] ?> - <?php echo $info['title'];?></title>
<meta name="keywords" content="<?php echo $info['keywords'];?>" />
<meta name="description" content="<?php echo $info['description'];?>" />
<?php foreach ($template['css'] as $css){?>
<link rel="stylesheet" type="text/css" href="<?php echo $css ?>" />
<?php }?>
</head>
<body>
	<div class="header_wrap">
		<div id="header" class="auto">
			<div class="logo"><?php echo $info['title'];?></div>
			<div class="nav">
				<a <?php if($thispage == "index.php") echo 'class="hover"'; ?> href="index.php">首页</a>
				<a <?php if($thispage == "publish.php") echo 'class="hover"'; ?> href="publish.php">新帖</a>
				<!--<a>话题</a>-->
			</div>
			<div class="search">
				<form action="search.php">
					<input class="keyword" type="text" name="keyword" placeholder="输入关键字搜索" value="<?php echo $_GET['keyword'];?>" />
					<input class="submit" type="submit" name="submit" value="" />
				</form>
			</div>
			<div class="login">
				<?php if($member){?>
					欢迎您，
					<a href="member.php?id=<?php echo $member['id']?>"><?php echo $member['name'] ?></a>&nbsp;
					|<a href="logout.php" id="link">注销</a>
				<?php } else { ?>
				<a <?php if($thispage == "register.php") echo 'class="hover"'; ?> href="register.php" id="link">注册</a>
				<a <?php if($thispage == "login.php") echo 'class="hover"'; ?> href="login.php" id="link">登录</a>
				<?php }?>
			</div>
		</div>
	</div>
    <div style="margin-top:55px;"></div>