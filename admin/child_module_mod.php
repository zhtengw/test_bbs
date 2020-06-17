<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(!isset($_GET['data'])){
        skip_page('child_module.php', 'error', '访问错误！');
}

$data = json_decode($_GET['data'],true);
$link = db_connect();
if(!isset($data['id']) || !is_numeric($data['id'])){
    skip_page('child_module.php', 'error', '参数错误！');
}
$query = 'select * from bbs_child_module where id='.$data['id'];
$result = db_exec($link,$query);
if(!record_count($link,$query)){
    skip_page('child_module.php', 'error', '参数错误！');
}

$template['title']='子版块--编辑';

// 取得版块信息的当前值
$result_arr = mysqli_fetch_assoc($result);
if(!isset($data['parent_module_id'])){ 
    $data['parent_module_id'] = $result_arr['parent_module_id'];
}
if(!isset($data['module_name'])){ 
    $data['module_name'] = $result_arr['module_name'];
}
if(!isset($data['member_id'])){ 
    $data['member_id'] = $result_arr['member_id'];
}
if(!isset($data['info'])){ 
    $data['info'] = $result_arr['info'];
}
if(!isset($data['sort'])){
    $data['sort'] = $result_arr['sort'];
}
mysqli_free_result($result);

$char_max_num = char_max_len($link,'bbs_child_module','module_name');
$char_max_info = char_max_len($link,'bbs_child_module','info');

if(isset($_POST['submit'])){
    $parent_module_id = $_POST['parent_module_id'];
    $module_name = $_POST['module_name'];
    $member_id = $_POST['member_id'];
    $info = $_POST['info'];
    $sort = $_POST['sort'];

    if(empty($module_name)){
        $module_name = $data['module_name'];
    }
    if(!is_numeric($sort) || $sort == null){
        $sort = $data['sort'];
    }
    if($module_name == $data['module_name'] 
        && $parent_module_id == $data['parent_module_id']
        && $member_id == $data['member_id']
        && $info == $data['info']
        && $sort == $data['sort']){
           confirm_page($_SERVER['REQUEST_URI'], '未作修改，是否重试？' ,'child_module.php');
    }
    if(iconv_strlen($module_name, "UTF-8") > $char_max_num){
        skip_page($_SERVER['REQUEST_URI'], 'error', '版块名称超过'.$char_max_num.'个字符，请重新输入！');
    }
    if(iconv_strlen($info, "UTF-8") > $char_max_info){
        skip_page($_SERVER['REQUEST_URI'], 'error', '版块描述超过'.$char_max_num.'个字符，请重试！');
    }

    if(!is_numeric($parent_module_id)){
        skip_page($_SERVER['REQUEST_URI'], 'error', '未选择父版块！');
    }
    $parent_query = 'select module_name from bbs_parent_module where id='.$parent_module_id;
    if(!record_count($link,$parent_query)){
        skip_page($_SERVER['REQUEST_URI'], 'error', '所属父版块不存在！');
    }
    if($parent_module_id != $data['parent_module_id'] ){
        // 当父版块有修改时，检查是否有同名版块
        $search_query = 'select * from bbs_child_module where module_name="'.real_string($link,$module_name).'" and parent_module_id='.$parent_module_id;
        if(record_count($link,$search_query)){
            skip_page($_SERVER['REQUEST_URI'], 'attention', '子版块 '.$module_name.' 已存在，请重新输入！');
        }
    }      

    $mod_query =  'update bbs_child_module set 
        parent_module_id='.$parent_module_id.',
        module_name="'.real_string($link,$module_name).'", 
        member_id='.$member_id.',
        info="'.real_string($link,$info).'",
        sort='.$sort.' 
        where id='.$data['id'];
    db_exec($link,$mod_query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('child_module.php', 'ok', '版块 '.$module_name.' 信息已修改！');
    } else {
        skip_page($_SERVER['REQUEST_URI'], 'error', '修改失败，请重试！');
    }

}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >编辑子版块--<?php echo $data['module_name']?></div>
        <form method="post">
            <table class="au">
                <tr>
                    <td>所属父版块</td>
                    <td>
                        <select autocomplete="off" name="parent_module_id" >
                        <!-- 给select标签添加了autocomplete='off'属性后，
                            option标签的selected属性才会在Firefox中有效果
                            参考：https://stackoverflow.com/a/8258154/260080
                        -->
                            <option value=""  disabled="">===请选择父版块===</option>
                            <?php
                                
                                $query = 'select id,module_name from bbs_parent_module';
                                $result = db_exec($link,$query);
                                while($data_parent=mysqli_fetch_assoc($result)){
                                    if($data_parent['id'] == $data['parent_module_id']){
                                        echo '<option value="'.$data_parent['id'].'" selected>'.$data_parent['module_name'].'</option>';
                                    } else {
                                        echo '<option value="'.$data_parent['id'].'">'.$data_parent['module_name'].'</option>';
                                    }   
                                }

                            
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>版块名称</td>
                    <td><input value="<?php echo $data['module_name']?>" name="module_name" type="text" /></td>
                    <td>
                        最多<?php echo $char_max_num?>个字符
                    </td>
                </tr>
                <tr>
                    <td>描述</td>
                    <td>
                        <textarea name="info" type="text"><?php echo $data['info']?></textarea>
                    </td>
                    <td>
                        版块描述，最多<?php echo $char_max_info?>个字符
                    </td>
                </tr>
                <tr>
                    <td>版主</td>
                    <td>
                        <table class="sub">
                        <tr>
                        <td>
                        <select name="member_id" >
                            <option value="0">===请选择会员ID===</option>
                            <?php
                                
                                $query = 'select id,name from bbs_member';
                                $result = db_exec($link,$query);
                                while($member=mysqli_fetch_assoc($result)){
                                    if($member['id'] == $data['member_id']){
                                        echo '<option value="'.$member['id'].'" selected>'.$member['name'].'</option>';
                                    } else {
                                        echo '<option value="'.$member['id'].'">'.$member['name'].'</option>';
                                    }   
                                }

                            
                            ?>
                        </select>
                        </td>
                        </tr>
                        
                        <!--
                            Todo: 选择多个会员作为版主，
                            数据表member_id也要可以存多个数据
                        <tr>
                        <td>
                        <select name="member_id" >
                            <option value="0">===请选择会员ID===</option>
                        </select>
                        </td>
                        <td>
                        <input style="cursor:pointer" class="btn"  type="submit" name="addline" value="+" />
                        </td>
                        </tr>
                        -->
                        </table>
                    </td>
                    <td>
                        选择一个会员作为版主
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