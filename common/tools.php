<?php
// 跳转页面
function skip_page($url,$icon,$msg){
$page = <<<EOF
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>跳转中...</title>
<meta name="keywords" content="跳转页" />
<meta name="description" content="跳转页" />
<meta http-equiv="refresh" content="3;url={$url}"> 
<link rel="stylesheet" type="text/css" href="style/remind.css" />
</head>
<body>
<div class="notice"><span class="pic {$icon}"></span> {$msg} 3秒后自动跳转... <a href="{$url}"> 立即跳转</a>  </div>
</body>
</html>
EOF;

    echo $page;
    exit;
}


// 确认页面
function confirm_page($url,$message,$rurl){
$page = <<<EOF
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>确认页</title>
<meta name="keywords" content="确认页" />
<meta name="description" content="确认页" />
<link rel="stylesheet" type="text/css" href="style/remind.css" />
</head>
<body>
<div class="notice"><span class="pic ask"></span>{$message} <a style="color:red" href="{$url}">确认</a> | <a style="color:green" href="{$rurl}">取消</a></div>
</body>
</html>
EOF;

    echo $page;
    exit;
}

// 保存cookie
function save_cookie($link,$name,$pwd,$time=0){
    $time = $time == 0 ? $time : time()+$time;
    //echo $time;
    //exit;
    //setcookie("BBS[name]",$name,$time,'/');
    //setcookie('BBS[pwd]',sha1(md5($pwd)),$time,'/');
    setcookie("BBS[name]",$name,$time);
    setcookie('BBS[pwd]',sha1(md5($pwd)),$time);
    $lasttime_query = 'update bbs_member set last_time=now() where name="'.$name.'"';
    db_exec($link,$lasttime_query);
    return true;
}
// 删除cookie
function del_cookie(){
    setcookie("BBS[name]",'',time()-3600);
    setcookie('BBS[pwd]','',time()-3600);
    return true;
}
// 登录状态验证
function is_login($link){
    if(isset($_COOKIE['BBS']['name']) && isset($_COOKIE['BBS']['pwd'])){
        $search_query = 'select * from bbs_member where name="'.$_COOKIE['BBS']['name'].'" and sha1(pwd)="'.$_COOKIE['BBS']['pwd'].'"';
        $result = db_exec($link,$search_query); 
        if(mysqli_num_rows($result) == 1){
            $member = mysqli_fetch_assoc($result);
            return $member;
        } else {
            return false;
        }
    } else {
        return false;

    }

}

// 显示验证码
function vcode($width=120,$height=40,$text_num=4,$fsize=24,$pixel_num=100,$line_num=6){
    //告诉浏览器显示图片格式，不然默认以文本模式输出
    //header('Content-type:image/jpeg');
    header('Content-type:image/png');
    $img = imagecreatetruecolor($width,$height);

    // 设置背景色，背景色要最先绘制，不然会随机出现黑块
    $bgColor=imagecolorallocate($img,rand(200,255),rand(200,255),rand(200,255));
    imagefill($img,0,0,$bgColor);


    // 用正方形绘制边框
    //$bordercolor=imagecolorallocate($img,rand(150,200),rand(150,200),rand(150,200));
    //imagerectangle($img,0,0,$width-1,$height-1,$bordercolor);

    // 混淆像素点
    for($i=0;$i<$pixel_num;$i++){
        $pixelcolor=imagecolorallocate($img,rand(0,100),rand(0,100),rand(0,100));
        imagesetpixel($img,rand(0,$width-1),rand(0,$height-1),$pixelcolor);
    }


    // 混淆线段
    for($i=0;$i<$line_num;$i++){
        $linecolor=imagecolorallocate($img,rand(150,200),rand(150,200),rand(150,200));
        imageline($img,rand(0,$width/3),rand(0,$height-1),rand($width*2/3,$width-1),rand(0,$height-1),$linecolor);

    }

    // 验证码字符
    $wqyzh='fonts/wqy-microhei.ttc';
    $vcode='';
    for ($i=0;$i<$text_num;$i++){
        $fontcolor=imagecolorallocate($img,rand(0,150),rand(0,150),rand(0,150));
        /*
        mt_rand(97,122) 生成随机数比 rand(97,122)更快
        */
        // 使用chr()函数将ascii码转成字符，97-122为字母a-z，65-90为A-Z
        $textx=15*($i+1);
        $texty=27;
        if(mt_rand()%2 == 0){
            $char=chr(rand(65,90));
        } else {
            $char=chr(rand(97,122));
        }
        imagettftext($img,$fsize,rand(-10,10),$textx,$texty,$fontcolor,$wqyzh,$char);
        $vcode.=$char;
    }

    // 输出图片
    //imagejpeg($img);
    imagepng($img);

    // 释放图片内存
    imagedestroy($img);

    return $vcode;
}
?>