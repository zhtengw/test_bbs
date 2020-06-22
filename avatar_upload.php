<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

if(!$member = is_login($link)){
    skip_page('login.php', 'error', '请先登录！');
}

if(isset($_POST['upload'])){
	$save_path = 'uploads/'.date('Y/m/d/');
	$upload = file_upload($save_path,'avatar','1M');

	if($upload['status']){
		$query = 'update bbs_member set photo="'.real_string($link,$upload['path']).'" where id='.$member['id'];
		$result=db_exec($link,$query);
		if(mysqli_affected_rows($link)==1){
			skip_page('member.php?id='.$member['id'],'ok','头像修改成功！');
		} else {
			unlink($upload['path']);
			skip_page($_SERVER['REQUEST_URI'],'error','头像修改失败，请重试！');
		}
	} else {
		skip_page($_SERVER['REQUEST_URI'],'error',$upload['message']);
	}
}

$template['title']='上传头像';
$template['css']=['style/public.css',
                    'style/avatar.css'];

?>
<?php include 'include/header.php' ;?>

	<div id="main" class="auto">
		<h2>更改头像</h2>
		<div class='upload'>
			<h3>原头像：</h3>
			<img width="200" height="200" src="<?php $avatar= empty($member['photo'])? 'style/photo.jpg': $member['photo']; echo $avatar; ?>" />
		</div>
		<div class='upload'>
    		<form enctype="multipart/form-data" action="" method="POST">
				<input style="cursor:pointer;" width="100" type="file" name="avatar"/><br /><br />
				<input class="submit" type="submit" name="upload" value="保存" />
			</form>
		</div>
	</div>
<?php include 'include/footer.php' ;?>