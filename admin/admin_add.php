<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$template['title']='添加管理员';

$link = db_connect();
$char_max_num = char_max_len($link,'bbs_admin','name');

if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}
if(!isset($admin['level']) || $admin['level']!=0){
    skip_page('admin.php', 'error', '您没有权限！');
}

if(isset($_POST['submit'])){
    $name = real_string($link,$_POST['name']);
    $pwd = $_POST['pwd'];
    $level = $_POST['level'];
    $allow_level = ['0','1'];

    if($name == null){
        skip_page('admin_add.php', 'error', '名称不能为空！');
    }
    if(iconv_strlen($name, "UTF-8") > $char_max_num){
        skip_page('admin_add.php', 'error', '管理员名称超过'.$char_max_num.'个字符！');
    }

    $search_query = 'select * from bbs_admin where name="'.$name.'"';
    if(record_count($link,$search_query)){
        skip_page('admin_add.php', 'attention', '管理员 '.$name.' 已存在！');
    }

    if(mb_strlen($pwd) < 6){
        skip_page("admin_add.php","error",'密码少于6个字符，请重新输入！');
    }
    
    if(!in_array($level,$allow_level)){
        $level='1';
    }


    $add_query = 'insert into bbs_admin (name, pwd, create_time, level) values (
                "'.$name.'",
                "'.md5($pwd).'",
                now(),
                '.$level.'
                );';
    db_exec($link,$add_query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('admin.php', 'ok', '已新增管理员 '.$name.' ！');
    } else {
        skip_page('admin.php', 'error', '添加失败，请重试！');
    }

}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >添加管理员</div>
        <form method="post">
            <table class="au">
                <tr>
                    <td>管理员名称<span style="color:red">*</span></td>
                    <td><input name="name" type="text" /></td>
                    <td>
                        最多<?php echo $char_max_num?>个字符
                    </td>
                </tr>
                <tr>
                    <td>登录密码<span style="color:red">*</span></td>
                    <td><input name="pwd" type="text" /></td>
                    <td>
                        输入密码，必须包含xxx，不少于6个字符
                    </td>
                </tr>
                <tr>
                    <td>管理员等级</td>
                    <td>
                        <select name="level" >
                            <option value="1" selected> &nbsp;&nbsp;普通管理员&nbsp;&nbsp; </option>
                            <option value="0"> &nbsp;&nbsp;超级管理员&nbsp;&nbsp; </option>
                        </select>
                    </td>
                    <td>
                        默认是普通管理员
                    </td>
                </tr>
                <tr>
                    <td>带<span style="color:red">*</span>为必填项。</td>
                </tr>
            </table>
            <input style="cursor:pointer" class="btn" type="submit" name="submit" value="添加" />
        </form>
</div>
<?php include 'include/footer.php' ;?>