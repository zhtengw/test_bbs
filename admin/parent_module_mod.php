<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(!isset($_GET['data'])){
        skip_page('parent_module.php', 'error', '访问错误！');
}

$data = json_decode($_GET['data'],true);
$link = db_connect();
if(!isset($data['id']) || !is_numeric($data['id'])){
    skip_page('parent_module.php', 'error', '参数错误！');
}
$query = 'select * from bbs_parent_module where id='.$data['id'];
$result = db_exec($link,$query);
if(!mysqli_num_rows($result)){
    skip_page('parent_module.php', 'error', '参数错误！');
}

$template['title']='父版块--编辑';

// 取得版块信息的当前值
if(!isset($data['module_name']) || !isset($data['sort'])){
    $result_arr = mysqli_fetch_assoc($result);
    $data['module_name'] = $result_arr['module_name'];
    $data['sort'] = $result_arr['sort'];
}
mysqli_free_result($result);

$char_max_num = char_max_len($link,'bbs_parent_module','module_name');

if(isset($_POST['submit'])){
    $module_name = $_POST['module_name'];
    $sort = $_POST['sort'];

    if(empty($module_name)){
        $module_name = $data['module_name'];
    }
    if(!is_numeric($sort) || $sort == null){
        $sort = $data['sort'];
    }
    if($module_name == $data['module_name'] && $sort == $data['sort']){
           //skip_page('parent_module.php', 'attention', '未作修改！');
           confirm_page($_SERVER['REQUEST_URI'], '未作修改，是否重试？' ,'parent_module.php');
    }
    if(iconv_strlen($module_name, "UTF-8") > $char_max_num){
        skip_page($_SERVER['REQUEST_URI'], 'error', '版块名称超过'.$char_max_num.'个字符，请重新输入！');
    }

    $search_query = 'select * from bbs_parent_module where module_name="'.real_string($link,$module_name).'"';
    if(record_count($link,$search_query)){
        skip_page($_SERVER['REQUEST_URI'], 'attention', '父版块 '.$module_name.' 已存在，请重新输入！');
    }

    $mod_query =  'update bbs_parent_module set module_name="'.real_string($link,$module_name).'", sort='.$sort.' where id='.$data['id'];
    db_exec($link,$mod_query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('parent_module.php', 'ok', '父版块 '.$module_name.' 信息已修改！');
    } else {
        skip_page($_SERVER['REQUEST_URI'], 'error', '修改失败，请重试！');
    }

}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >编辑父版块--<?php echo $data['module_name']?></div>
        <form method="post">
            <table class="au">
                <tr>
                    <td>版块名称</span></td>
                    <td><input value="<?php echo $data['module_name']?>" name="module_name" type="text" /></td>
                    <td>
                        最多<?php echo $char_max_num?>个字符
                    </td>
                </tr>
                <tr>
                    <td>排序</td>
                    <td><input value="<?php echo $data['sort']?>" name="sort" type="text" /></td>
                    <td>
                        填数字
                    </td>
                </tr>
            </table>
            <input style="cursor:pointer" class="btn" type="submit" name="submit" value="修改" />
        </form>
</div>
<?php include 'include/footer.php' ;?>