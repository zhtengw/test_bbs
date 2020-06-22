<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$link = db_connect(); 

if($admin=is_admin_login($link)){
    skip_page('index.php', 'attention', '您已登录！');
}

if(isset($_POST['login'])){
    $login_info = $_POST;
    $login_info['name'] = real_string($link,$login_info['name']);

    //检查各字段
    if(empty($login_info['name']) || empty($login_info['pwd'])){
        skip_page("admin_login.php","error","请填写用户名和密码！");
    }
    if(strtolower($login_info['vcode']) != strtolower($_SESSION['vcode'])){
		skip_page("admin_login.php","error",'验证码错误，请重新输入！');
	}
    
	$search_query = 'select id from bbs_admin where name="'.$login_info['name'].'" and pwd="'.md5($login_info['pwd']).'"';

	if(record_count($link,$search_query) == 1){
		$_SESSION['admin']['name']=$login_info['name'];
		$_SESSION['admin']['pwd']=sha1(md5($login_info['pwd']));

    	$lasttime_query = 'update bbs_admin set last_time=now() where name="'.$login_info['name'].'"';
    	db_exec($link,$lasttime_query);
		skip_page('index.php', 'ok', '管理员登录成功！');
	} else {
		skip_page('admin_login.php', 'error', '管理员名称或密码错误，请重试！');
	}
	
}
	
$template['title']='管理员登录';
$template['css']=['style/public.css',
                    'style/login.css'];
?>
<?php include 'include/header.php' ;?>
	<div id="main">
		<div class="title">管理登录</div>
		<form method="post">
			<label>用户名：<input class="text" type="text" name="name" /></label>
			<label>密　码：<input class="text" type="password" name="pwd" /></label>
			<label>验证码：<input class="text" type="text" placeholder=" 不区分大小写" name="vcode" /></label>
            <label><a style="cursor:pointer" title="看不清？点击换一张" onclick="img.src='../vcode.php?id='+Math.random();"><img id="img" class="vcode" src="../vcode.php" /></a></label>
			<label><input class="submit" type="submit" name="login" value="登录" /></label>
		</form>
	</div>
<?php include 'include/footer.php' ;?>