<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

$template['title']='添加子版块';

$link = db_connect();

$char_max_num = char_max_len($link,'bbs_child_module','module_name');
$char_max_info = char_max_len($link,'bbs_child_module','info');

if(isset($_POST['submit'])){
    $parent_module_id = $_POST['parent_module_id'];
    $module_name = $_POST['module_name'];
    $member_id = $_POST['member_id'];
    $info = $_POST['info'];
    $sort = $_POST['sort'];

    //var_dump($_POST);
    //exit;
    if(!is_numeric($parent_module_id)){
        skip_page('child_module_add.php', 'error', '未选择父版块！');
    }
    $parent_query = 'select module_name from bbs_parent_module where id='.$parent_module_id;
    if(!record_count($link,$parent_query)){
        skip_page('child_module_add.php', 'error', '所属父版块不存在！');
    }
    if($module_name == null){
        skip_page('child_module_add.php', 'error', '版块名称不能为空！');
    }
    if(iconv_strlen($module_name, "UTF-8") > $char_max_num){
        skip_page('child_module_add.php', 'error', '版块名称超过'.$char_max_num.'个字符，请重试！');
    }
    if(iconv_strlen($info, "UTF-8") > $char_max_info){
        skip_page('child_module_add.php', 'error', '版块描述超过'.$char_max_info.'个字符，请重试！');
    }
    if(!is_numeric($sort)){
        //skip_page('parent_module_add.php', 'error', '排序不是合法数字！');
        $sort = 'DEFAULT';
    }

    // 检查是否有同名版块
    $search_query = 'select * from bbs_child_module where module_name="'.real_string($link,$module_name).'" and parent_module_id='.$parent_module_id;
    if(record_count($link,$search_query)){
        skip_page('child_module_add.php', 'attention', '版块 '.$module_name.' 已存在！');
    }

    $add_query = 'insert into bbs_child_module (parent_module_id, module_name, member_id, info ,sort) values ('.
        $parent_module_id.','.
        '"'.real_string($link,$module_name).'",'.
        $member_id.','.
        '"'.real_string($link,$info).'",'.
        $sort.
        ');';
    db_exec($link,$add_query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('child_module.php', 'ok', '已新增版块 '.$module_name.' ！');
    } else {
        skip_page('child_module_add.php', 'error', '添加失败，请重试！');
    }

}


?>
<?php include 'include/header.php' ;?>
<div id="main" style="height:1000px;">
		<div class="title" style="margin-bottom:20px" >添加子版块</div>
        <form method="post">
            <table class="au">
                <tr>
                    <td>所属父版块<span style="color:red">*</span></td>
                    <td>
                        <select autocomplete="off" name="parent_module_id" >
                        <!-- 给select标签添加了autocomplete='off'属性后，
                            option标签的selected属性才会在Firefox中有效果
                            参考：https://stackoverflow.com/a/8258154/260080
                        -->
                            <option value=""  disabled="" selected>===请选择父版块===</option>
                            <?php
                                
                                $query = 'select id,module_name from bbs_parent_module';
                                $result = db_exec($link,$query);
                                while($data_parent=mysqli_fetch_assoc($result)){
                                    echo '<option value="'.$data_parent['id'].'">'.$data_parent['module_name'].'</option>';
                                }

                            
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>版块名称<span style="color:red">*</span></td>
                    <td><input name="module_name" type="text" /></td>
                    <td>
                        最多<?php echo $char_max_num?>个字符
                    </td>
                </tr>
                <tr>
                    <td>描述</td>
                    <td>
                        <textarea name="info" type="text"></textarea>
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