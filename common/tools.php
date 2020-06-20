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

// 分页
/* 参数说明：
    $sum: 总记录数
    $slice: 每页记录数
    $page: 当前页码
    $page_btns: 页码按钮数
   返回值：数组类型
    ['count']: 页面数
    ['cur_page']: 当前页码
    ['sql']: 用于SQL查询的limit参数
    ['html']: 分页器html 
*/
function paging($sum, $slice,$page,$page_btns=10){
    // $page 为当前页码
    if(!isset($page) || !is_numeric($page) || $page<1){
        $page=1;
    }
    
    if($sum < 1) return ['count' => '',
            'cur_page' => $page,
            'sql' => '',
            'html' => ''];
;
    // 总页数
    $pages_count = ceil($sum/$slice);
    $page = $page > $pages_count ? $pages_count: $page;

    $sql_limit = 'limit '.($page-1)*$slice.','.$slice;
    // 页码条显示
        $arr_url=parse_url($_SERVER['REQUEST_URI']);
        $url_path=$arr_url['path'];

        $url='';
        if(isset($arr_url['query'])){
            parse_str($arr_url['query'],$arr_query);
            unset($arr_query['page']);
            if(!empty($arr_query)){
                $other_query=http_build_query($arr_query);
                $url=$url_path.'?'.$other_query.'&page=';
            } else {
                $url=$url_path.'?page=';
            }
        } else {
            $url=$url_path.'?page=';
        }
        
        
        $page_btns = isset($page_btns) || $page_btns > 0?$page_btns: 10; // 显示的最多页码按钮数

        $page_array=[];
        if($pages_count<=$page_btns){
		    for ($p=1;$p<=$pages_count;$p++){
		    	if ($p == $page) {
			    	$page_array[$p] ='<span>'.$p.'</span>';
		    	} else {
			    	$page_array[$p] = '<a href="'.$url.$p.'">'.$p.'</a>';
			    }
            }
        } else {
            $left_btns = floor(($page_btns-1)/2);

            $start = $page-$left_btns;
            
            // 最大页码不能超过页码数
            if (($start+$page_btns-1)>$pages_count) $start=$pages_count-($page_btns-1);

            // 起始页码不小于1
            if ($start <1) $start = 1;

            // 最大页码
            $end = $start+$page_btns-1;
            for ($i=0;$i<$page_btns;$i++){
                if ($start == $page) {
                    $page_array[$start] = '<span>'.$start.'</span>';
                } else{
			    	$page_array[$start] = '<a href="'.$url.$start.'">'.$start.'</a>';
                }
                $start++;
            }
        }
        if(count($page_array)>=3){
            reset($page_array);
            $first = key($page_array);
            end($page_array);
            $last = key($page_array);
            // 如果起始页码不是首页，显示为首页页码
            if ($first != 1){
                array_shift($page_array);
                array_unshift($page_array,'<a href="'.$url.'1">...1</a>');
            }
            // 如果最大页码小于总页码，显示为末页页码
            if ($last != $pages_count){
                array_pop($page_array);
                array_push($page_array,'<a href="'.$url.$pages_count.'">...'.$pages_count.'</a>');
            }
        }

        if($page > 1) {
            array_unshift($page_array,'<a href="'.$url.($page-1).'">« 上一页</a>');
        }
		if($page < $pages_count) {
            array_push($page_array,'<a href="'.$url.($page+1).'">下一页 »</a>');
        }
        
        array_unshift($page_array,'<div class="pages">');
        array_push($page_array,'</div>');
        $page_html=implode(' ',$page_array);

    return ['count' => $pages_count,
            'cur_page' => $page,
            'sql' => $sql_limit,
            'html' => $page_html];

}
function file_upload($save_path,$post_name,$max_size='1M',$allow_type=['jpg','jpeg','png','gif']){
    $return_data=[];
    $phpini_upload_size=to_bytes(ini_get('upload_max_filesize'));
    $max_size_bytes=to_bytes($max_size);

    if ($max_size_bytes>$phpini_upload_size) {
        $return_data['status'] = false;
        $return_data['message'] = 'Uploaded size '.$max_size.' beyond the limit';
        return $return_data;
    }

    $arr_upload_status=[
        0 => 'OK',
        1 => 'EXCEED INI SIZE',
        2 => 'EXCEED FORM SIZE',
        3 => 'PARTIAL UPLOADED',
        4 => 'NO FILE',
        6 => 'NO TMP DIR',
        7 => 'CANT WRITE',
    ];

    
    if(!isset($_FILES[$post_name]['error'])){
        $return_data['status'] = false;
        $return_data['message'] = 'Unknown error, no variable $_FILES[\''.$post_name.'\'][\'error\']';
        return $return_data;
    }
    if($_FILES[$post_name]['error']){
        $return_data['status'] = false;
        $return_data['message'] = $arr_upload_status[$_FILES[$post_name]['error']];
        return $return_data;
    }
    if(!is_uploaded_file($_FILES[$post_name]['tmp_name'])){
        $return_data['status'] = false;
        $return_data['message'] = 'Possible file upload attack: '. $_FILES[$post_name]['tmp_name'] .'.';
        return $return_data;
    }

    if($_FILES[$post_name]['size']>$max_size_bytes){
        $return_data['status'] = false;
        $return_data['message'] = 'Uploaded File exceed '.$max_size;
        return $return_data;
    }
       
    $fileinfo = pathinfo($_FILES[$post_name]['name']);
    if(!isset($fileinfo['extension'])) $fileinfo['extension'] = '';

    if(!in_array($fileinfo['extension'],$allow_type)){
        $return_data['status'] = false;
        $return_data['message'] = 'Not allowed file type';
        return $return_data;
    }

    if(!file_exists($save_path)){
        if(!mkdir($save_path,0777,true)){
            $return_data['status'] = false;
            $return_data['message'] = 'Save path create failed.';
            return $return_data;
        }
    }

    $new_name = mt_rand(10000,99999).uniqid();
    if(isset($fileinfo['extension'])) $new_name .= '.'.$fileinfo['extension'];
    $full_path = rtrim($save_path,'/').'/'.$new_name;

    if(!move_uploaded_file($_FILES[$post_name]['tmp_name'],$full_path)){
        $return_data['status'] = false;
        $return_data['message'] = 'Uploaded file save failed.';
        return $return_data;
    }

    $return_data['status'] = true;
    $return_data['path'] = $full_path;
    $return_data['name'] = $new_name;
    return $return_data;

}

function to_bytes($size){
    /* Convert to bytes */
    $unit = strtoupper(substr($size,-1));
    $number = substr($size,0,-1);
    $multiple=1;
    switch ($unit) {
        case 'K':
            $multiple = 1024;
            break;
        case 'M':
            $multiple = 1024*1024;
            break;
        case 'G':
            $multiple = 1024*1024;
            break;
        case 'T':
            $multiple = 1024*1024*1024;
            break;
        default:
            return false;
    }
    return $multiple*$number;

}
?>