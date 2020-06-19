<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

if($member=is_login($link)){
    skip_page('index.php', 'attention', '您已登录，跳转到首页！');
}

if(isset($_POST['login'])){
    $login_info = real_string($link,$_POST);

    //检查各字段
    if(empty($login_info['name']) || empty($login_info['pwd'])){
        skip_page("login.php","error","请填写用户名和密码！");
    }
    if(strtolower($login_info['vcode']) != strtolower($_SESSION['vcode'])){
        skip_page("login.php","error",'验证码错误，请重新输入！');
    }
    // 检查是否有该用户
    $search_query = 'select * from bbs_member where name="'.$login_info['name'].'" and pwd="'.md5($login_info['pwd']).'"';

    if(record_count($link,$search_query) == 1){
        $time = !isset($login_info['keep_login']) ? 0: (60*60*24*30); 
        save_cookie($link,$login_info['name'],$login_info['pwd'],$time);


        // Todo: 登录后跳转到登录前页面
        skip_page('index.php', 'ok', '用户登录成功，跳转到首页！');
    } else {
        skip_page('login.php', 'error', '用户名或密码错误，请重试！');
    }
}

$template['title']='登录页';
$template['css']=['style/public.css',
                    'style/register.css'];

?>
<?php include 'include/header.php' ;?>
	<div id="register" class="auto">
		<h2>会员登录</h2>
		<form method="post">
			<label>用户名：<input name="name" type="text"  /><span>*输入用户名</span></label>
			<label>密码：<input name="pwd" type="password"  /><span>*输入密码</span></label>
            <label>验证码：<input name="vcode" type="text"  /><span>*请输入下方验证码，不区分大小写</span></label>
            <!--不知原因，img标签的onclick事件不生效，还得用a标签括起来
            <img id="code" style="cursor:pointer" class="vcode" src="vcode.php" οnclick="this.src='vcode.php?id='+Math.random();"/>
            -->
            <a style="cursor:pointer" title="看不清？点击换一张" onclick="img.src='vcode.php?id='+Math.random();"><img id="img" class="vcode" src="vcode.php" /></a>
            <div style="clear:both;"></div>
            <input class="btn" name="login" type="submit" value="登录" />
            <label style="float:none;margin:20px 0 60px 20px;vertical-align:middle;display:inline-block;">
                <input style="width:15px;" name="keep_login" type="checkbox" value="on" />&nbsp;保持登录状态
            </label>
        </form>
	</div>
    
<?php include 'include/footer.php' ;?>