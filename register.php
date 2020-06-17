<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

if($member = is_login($link)){
    skip_page('index.php', 'attention', '您已登录，请注销后注册账号！');
}

$char_max_name = char_max_len($link,'bbs_member','name');

if(isset($_POST['register'])){
    $reg_info = real_string($link,$_POST);

    //检查各注册字段
    if(empty($reg_info['name']) || empty($reg_info['pwd'])){
        skip_page("register.php","error","注册信息不完整，请重试！");
    }
    if(mb_strlen($reg_info['name']) > $char_max_name){
        skip_page("register.php","error","用户名超过".$char_max_name.'个字符，请重新输入！');
    }
    // 检查是否有同名用户
    $search_query = 'select * from bbs_member where name="'.$reg_info['name'].'"';
    if(record_count($link,$search_query)){
        confirm_page('login.php', '用户'.$reg_info['name'].' 已存在，直接登录？','register.php');
    }
    if(mb_strlen($reg_info['pwd']) < 6){
        skip_page("register.php","error",'密码少于6个字符，请重新输入！');
    }
    // TODO：用正则表达式规定密码格式


    if($reg_info['pwd'] != $reg_info['confirm_pwd']){
        skip_page("register.php","error",'两次输入的密码不匹配，请重新输入！');
    }

    if(strtolower($reg_info['vcode']) != strtolower($_SESSION['vcode'])){
        skip_page("register.php","error",'验证码错误，请重新输入！');
    }

    $query = 'insert into bbs_member (name,pwd,register_time) values ("'.$reg_info['name'].'","'.md5($reg_info['pwd']).'",now())';
    db_exec($link,$query);

    if(mysqli_affected_rows($link) == 1){
        save_cookie($link,$reg_info['name'],$reg_info['pwd']);
        skip_page('index.php', 'ok', '用户注册成功，跳转到首页！');
    } else {
        skip_page('register.php', 'error', '用户注册失败，请重试！');
    }
}

$template['title']='会员注册';
$template['css']=['style/public.css',
                    'style/register.css'];

?>
<?php include 'include/header.php' ;?>
	<div id="register" class="auto">
		<h2>欢迎注册成为 Aten BBS会员</h2>
		<form method="post">
			<label>用户名：<input name="name" type="text"  /><span>*不得超过<?php echo $char_max_name?>个字符</span></label>
			<label>密码：<input name="pwd" type="password"  /><span>*输入密码，必须包含xxx，不少于6个字符</span></label>
			<label>确认密码：<input name="confirm_pwd" type="password"  /><span>*重新输入密码</span></label>
			<label>验证码：<input name="vcode" type="text"  /><span>*请输入下方验证码，不区分大小写</span></label>
            <a style="cursor:pointer" title="看不清？点击换一张" onclick="img.src='vcode.php?id='+Math.random();"><img id="img" class="vcode" src="vcode.php" /></a>
			<div style="clear:both;"></div>
			<input class="btn" name="register" type="submit" value="确定注册" />
		</form>
	</div>
    
<?php include 'include/footer.php' ;?>