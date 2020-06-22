<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$data = json_decode($_GET['data'],true);
$link = db_connect();
if(!isset($data['id']) || !is_numeric($data['id'])){
    skip_page('admin.php', 'error', '参数错误！');
}
if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}
if($admin['id']!=$data['id']){
    if ($admin['level']!=0){
        skip_page('admin.php', 'error', '您没有权限！');
    }
}
$query = 'select * from bbs_admin where id='.$data['id'];
$result = db_exec($link,$query);
if(!record_count($link,$query)){
    skip_page('admin.php', 'error', '参数错误！');
}


$template['title']='编辑管理员';

$link = db_connect();
$char_max_num = char_max_len($link,'bbs_admin','name');


if(isset($_POST['submit'])){
    $name = real_string($link,$_POST['name']);
    $pwd = $_POST['pwd'];
    $level = $_POST['level'];
    $allow_level = ['0','1'];

    if($name == null){
        skip_page($_SERVER['REQUEST_URI'], 'error', '名称不能为空！');
    }
    if(iconv_strlen($name, "UTF-8") > $char_max_num){
        skip_page($_SERVER['REQUEST_URI'], 'error', '管理员名称超过'.$char_max_num.'个字符！');
    }
    if($pwd == null){
        $mod_pwd = ' ';
    } else {
        if(mb_strlen($pwd) < 6){
            skip_page($_SERVER['REQUEST_URI'],"error",'密码少于6个字符，请重新输入！');
        }
        $mod_pwd = 'pwd="'.md5($pwd).'",';
    }


    
    if(!in_array($level,$allow_level)){
        $level='1';
    }


    $mod_query = 'update bbs_admin set 
                name="'.$name.'",'.
                $mod_pwd.
                'level='.$level.'
                where id='.$data['id'];
    db_exec($link,$mod_query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('admin.php', 'ok', '管理员 '.$name.' 的信息更新！');
    } else {
        skip_page($_SERVER['REQUEST_URI'], 'error', '修改失败，请重试！');
    }

}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >编辑管理员</div>
        <form method="post">
            <table class="au">
                <tr>
                    <td>管理员名称</td>
                    <td><input name="name" value="<?php echo $data['name'];?>" type="text" /></td>
                    <td>
                        最多<?php echo $char_max_num?>个字符
                    </td>
                </tr>
                <tr>
                    <td>登录密码</td>
                    <td><input name="pwd" type="text" /></td>
                    <td>
                        输入密码，必须包含xxx，不少于6个字符，留空为不修改。
                    </td>
                </tr>
                <tr>
                    <td>管理员等级</td>
                    <td>
                        <select name="level" >
                            <option value="1" <?php if($data['level'] == 1) echo 'selected';?>> &nbsp;&nbsp;普通管理员&nbsp;&nbsp; </option>
                            <option value="0" <?php if($data['level'] == 0) echo 'selected';?>> &nbsp;&nbsp;超级管理员&nbsp;&nbsp; </option>
                        </select>
                    </td>
                    <td>
                        默认是普通管理员
                    </td>
                </tr>
            </table>
            <input style="cursor:pointer" class="btn" type="submit" name="submit" value="修改" />
        </form>
</div>
<?php include 'include/footer.php' ;?>