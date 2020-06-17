<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(isset($_GET['del']) && isset($_GET['url']) && isset($_GET['msg']) && isset($_GET['rurl'])) {
    confirm_page($_GET['url'],$_GET['msg'],$_GET['rurl']);
}

$link = db_connect();

if(isset($_POST['submit'])){
    foreach($_POST['sort'] as $id => $sort){
        if(!is_numeric($id) || !is_numeric($sort)){
            skip_page('child_module.php', 'error', '排序参数错误！');
        }
        $resort_query[] = 'update bbs_child_module set sort='.$sort.' where id='.$id;
    }
    if(db_multiexec($link,$resort_query,$results,$error)){
        skip_page('child_module.php', 'ok', '排序已修改！');
    } else {
        skip_page('child_module.php', 'error', '修改失败，请重试！');
    }
}

$template['title']='子版块列表';

?>
<?php include 'include/header.php' ;?>

<div id="main" style="height:1000px;">
		<div class="title">子版块列表</div>
        <form method="post">
		<table class="list">
			<tr>
				<th>排序</th>	 	 	
				<th>版块名称</th>
				<th>所属父版块</th>
				<th>版主</th>
				<th>描述</th>
				<th>操作</th>
			</tr>
            <?php 
            /*
                这里要同时查两张表，可以用多表查询加别名的方式获得数据
             */
                $query = 'select
                        child.id,
                        child.module_name,
                        child.member_id,
                        child.info,
                        child.sort,
                        parent.module_name parent_module_name
                        from bbs_child_module child,
                        bbs_parent_module parent
                        where child.parent_module_id=parent.id
                        order by parent.id, child.sort
                        ';
                //$query = 'select * from bbs_child_module';
                $result = db_exec($link,$query);
                while ($data=mysqli_fetch_assoc($result)){
                    /*
                    $parent_query = 'select module_name from bbs_parent_module where id='.$data['parent_module_id'];
                    $parent_result = db_exec($link,$parent_query);
                    $parent_module_name = mysqli_fetch_assoc($parent_result)['module_name'];
                    */
                    $member_query = 'select name from bbs_member where id='.$data['member_id'];
                    $member_result = db_exec($link,$member_query);
                    $member_info = '';
                    if($member=mysqli_fetch_assoc($member_result)){
                        $member_info = $member['name'].'(id: '.$data['member_id'].')';
                    }

                    $url = rawurlencode('child_module_del.php?id='.$data['id']);
                    $rurl = rawurlencode($_SERVER['REQUEST_URI']);
                    $msg = rawurlencode('是否删除子版块“'.$data['module_name'].'”？');
                    $del_url = '?del=y&url='.$url.'&msg='.$msg.'&rurl='.$rurl;

                    $mod_url = 'child_module_mod.php?data='.rawurlencode(json_encode($data));

                    // 把标签的name属性写为 字符串加[]的形式，表单会自动作为数组提交，中括号中有字符的话，会作为数组的key
$html=<<<EOF
            <tr>
				<td><input class="sort" type="text" name="sort[{$data['id']}]" value="{$data['sort']}" /></td>
				<td>{$data['module_name']}</td>
				<td>{$data['parent_module_name']}</td>
				<td>{$member_info}</td>
				<td>{$data['info']}</td>
				<td><a href="#">[访问]</a>&nbsp;&nbsp;<a href="{$mod_url}">[编辑]</a>&nbsp;&nbsp;<a href="{$del_url}">[删除]</a></td>
            </tr>
EOF;
                    echo $html;
                }
            ?>
		</table>
        <?php 
        // 如果有版块，显示排序按钮
            if (mysqli_num_rows($result)){
                echo '<input style="cursor:pointer" class="btn" type="submit" name="submit" value="排序" />';
            }
        ?>
        </form>
		
		
	</div>


<?php include 'include/footer.php' ;?>