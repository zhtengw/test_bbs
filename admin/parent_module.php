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
            skip_page('parent_module.php', 'error', '排序参数错误！');
        }
        $resort_query[] = 'update bbs_parent_module set sort='.$sort.' where id='.$id;
    }
    if(db_multiexec($link,$resort_query,$results,$error)){
        skip_page('parent_module.php', 'ok', '排序已修改！');
    } else {
        skip_page('parent_module.php', 'error', '修改失败，请重试！');
    }
}


$template['title']='父版块列表';

?>
<?php include 'include/header.php' ;?>

<div id="main" style="height:1000px;">
		<div class="title">父版块列表</div>
        <form method="post">
		<table class="list">
			<tr>
				<th>排序</th>	 	 	
				<th>版块名称</th>
				<th>操作</th>
			</tr>
            <?php 
                $query = 'select * from bbs_parent_module order by sort';
                $result = db_exec($link,$query);
                while ($data=mysqli_fetch_assoc($result)){
                    $url = rawurlencode('parent_module_del.php?id='.$data['id']);
                    $rurl = rawurlencode($_SERVER['REQUEST_URI']);
                    $msg = rawurlencode('是否删除父版块“'.$data['module_name'].'”？');
                    $del_url = '?del=y&url='.$url.'&msg='.$msg.'&rurl='.$rurl;

                    $mod_url = 'parent_module_mod.php?data='.rawurlencode(json_encode($data));
$html=<<<EOF
            <tr>
				<td><input class="sort" type="text" name="sort[{$data['id']}]" value="{$data['sort']}" /></td>
				<td>{$data['module_name']}</td>
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