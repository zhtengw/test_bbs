<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$template['title']='添加父版块';

$link = db_connect();
//$query = 'select module_name from bbs_parent_module';
//$result = db_exec($link,$query);

//mysqli_fetch_all($result)获得列module_name的所有行，
//但每一行的数据都是一个数组，可以用array_column按列号把数据提取成一维数组
//$module_name_arr = array_column(mysqli_fetch_all($result),0);

$char_max_num = char_max_len($link,'bbs_parent_module','module_name');


if(isset($_POST['submit'])){
    $module_name = $_POST['module_name'];
    $sort = $_POST['sort'];

    if($module_name == null){
        skip_page('parent_module_add.php', 'error', '版块名称不能为空！');
    }
    if(iconv_strlen($module_name, "UTF-8") > $char_max_num){
        skip_page('parent_module_add.php', 'error', '版块名称超过'.$char_max_num.'个字符！');
    }
    if(!is_numeric($sort)){
        //skip_page('parent_module_add.php', 'error', '排序不是合法数字！');
        $sort = 'DEFAULT';
    }

    // 检查是否有同名版块
    // ver1: 导出所有module_name，在PHP中查找数组值
    /*
    if(in_array($module_name,$module_name_arr)){
        skip_page('parent_module_add.php', 'attention', '父版块 '.$module_name.' 已存在！');
    }
    */
    // ver2: 用SQL语句在数据库中查询
    $search_query = 'select * from bbs_parent_module where module_name="'.real_string($link,$module_name).'"';
    if(record_count($link,$search_query)){
        skip_page('parent_module_add.php', 'attention', '父版块 '.$module_name.' 已存在！');
    }

    $add_query = 'insert into bbs_parent_module (module_name, sort) values ("'.real_string($link,$module_name).'",'.$sort.');';
    db_exec($link,$add_query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('parent_module.php', 'ok', '已新增父版块 '.$module_name.' ！');
    } else {
        skip_page('parent_module_add.php', 'error', '添加失败，请重试！');
    }

}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >添加父版块</div>
        <form method="post">
            <table class="au">
                <tr>
                    <td>版块名称<span style="color:red">*</span></td>
                    <td><input name="module_name" type="text" /></td>
                    <td>
                        最多<?php echo $char_max_num?>个字符
                    </td>
                </tr>
                <tr>
                    <td>排序</td>
                    <td><input name="sort" placeholder="0" type="text" /></td>
                    <td>
                        填数字，默认是0
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