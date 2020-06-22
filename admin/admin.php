<?php
include_once '../common/config.php';
include_once '../common/tools.php';
include_once '../common/mysql.php';

if(isset($_GET['del']) && isset($_GET['url']) && isset($_GET['msg']) && isset($_GET['rurl'])) {
    confirm_page($_GET['url'],$_GET['msg'],$_GET['rurl']);
}

$link = db_connect();
if(!$admin=is_admin_login($link)){
    header('Location:admin_login.php');
}


$template['title']='管理员';

?>
<?php include 'include/header.php' ;?>

<div id="main" style="height:1000px;">
		<div class="title">管理员</div>
        <form method="post">
		<table class="list">
			<tr>
				<th>名称</th>	 	 	
				<th>级别</th>
				<th>创建时间</th>
				<th>最后登录时间</th>
				<th>操作</th>
			</tr>
            <?php 
                $levels = [
                    0 => '超级管理员',
                    1 => '普通管理员',
                ];
                $query = 'select * from bbs_admin';
                $result = db_exec($link,$query);
                while ($data=mysqli_fetch_assoc($result)){
                    $url = rawurlencode('admin_del.php?id='.$data['id']);
                    $rurl = rawurlencode($_SERVER['REQUEST_URI']);
                    $msg = rawurlencode('是否删除管理员“'.$data['name'].'”？');
                    $del_url = '?del=y&url='.$url.'&msg='.$msg.'&rurl='.$rurl;

                    $mod_url = 'admin_mod.php?data='.rawurlencode(json_encode($data));
$html=<<<EOF
            <tr>
				<td>{$data['name']}</td>
				<td>{$levels[$data['level']]}</td>
				<td>{$data['create_time']}</td>
				<td>{$data['last_time']}</td>
EOF;
                    echo $html;
                ?>
				<td>
                <?php
                if($data['id']==$admin['id'] || $admin['level'] == 0){
                    echo '<a href="'.$mod_url.'">[编辑]</a>&nbsp;&nbsp;';
                }
                if($admin['level'] == 0){
                    echo '<a href="'.$del_url.'">[删除]</a>';
                }
                ?>
                </td>
            </tr>
            <?php
                }
            ?>
		</table>
		</form>
		
	</div>


<?php include 'include/footer.php' ;?>