<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$template['title']='站点设置';

$link = db_connect();

if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}

$char_max_title = char_max_len($link,'bbs_info','title');
$char_max_keywords = char_max_len($link,'bbs_info','keywords');
$char_max_desc = char_max_len($link,'bbs_info','description');

$query = 'select title,keywords,description from bbs_info';
$result = db_exec($link,$query);
$info = mysqli_fetch_assoc($result);

if(isset($_POST['submit'])){
    $info = real_string($link,$_POST);
    $amend_url = 'web_config.php?post='.rawurlencode(json_encode($info));


    if(mb_strlen($info['title'])>$char_max_title){
        skip_page($amend_url,'error','标题超过'.$char_max_title.'个字符！');
    }

    if(mb_strlen($info['keywords'])>$char_max_keywords){
        skip_page($amend_url,'error','关键字超过'.$char_max_keywords.'个字符！');
    }

    if(mb_strlen($info['description'])>$char_max_desc){
        skip_page($amend_url,'error','描述超过'.$char_max_desc.'个字符！');
    }
    if(record_count($link,$query)){
        $mod_query = 'update bbs_info set 
                title="'.$info['title'].'",
                keywords="'.$info['keywords'].'",
                description="'.$info['description'].'"
                where id=1';
    } else {
        $mod_query = 'insert into bbs_info (title,keywords,description) values ( '.
                '"'.$info['title'].'",'.
                '"'.$info['keywords'].'",'.
                '"'.$info['description'].'")';
    }
    db_exec($link,$mod_query);
    if(mysqli_affected_rows($link) == 1){
        skip_page('../index.php', 'ok', '设置成功，跳转到首页！');
    } else {
        skip_page($amend_url, 'error', '设置失败，请重试！');
    }
}

if(isset($_GET['post'])){
    $info = json_decode($_GET['post'],true);
}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >网站设置</div>
        <form method="post">
            <table class="au">
                
                <tr>
                    <td>网站标题</td>
                    <td><input name="title" type="text" value="<?php echo $info['title']?>" /></td>
                    <td>
                        最多<?php echo $char_max_title?>个字符
                    </td>
                </tr>
                <tr>
                    <td>描述</td>
                    <td>
                        <textarea name="description" type="text"><?php echo $info['description']?></textarea>
                    </td>
                    <td>
                        网站描述，最多<?php echo $char_max_desc?>个字符
                    </td>
                </tr>
                
                <tr>
                    <td>关键字</td>
                    <td><input name="keywords" type="text" value="<?php echo $info['keywords']?>"/></td>
                    <td>
                        关键字，最多<?php echo $char_max_keywords?>个字符
                    </td>
                </tr>
            </table>
            <input style="cursor:pointer" class="btn" type="submit" name="submit" value="设置" />
        </form>
</div>
<?php include 'include/footer.php' ;?>