<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 
$char_max_title = char_max_len($link,'bbs_content','title');
$member = is_login($link);
$admin = is_admin_login($link);
if(!$member && !$admin){
    skip_page('login.php', 'error', '请先登录！');
}

//删除帖子
if(isset($_GET['del']) && isset($_GET['url']) && isset($_GET['msg']) && isset($_GET['rurl'])) {
    confirm_page($_GET['url'],$_GET['msg'],$_GET['rurl']);
}

if(!isset($_GET['id'])||!is_numeric($_GET['id'])){
    skip_page('index.php', 'error', '访问错误！');
}

$post_query = 'select * from bbs_content where id='.$_GET['id'];
$post_result = db_exec($link,$post_query);
if(!$post=mysqli_fetch_assoc($post_result)){
    skip_page('index.php', 'error', '该帖子不存在！');
}

$child_query = 'select * from bbs_child_module where id='.$post['module_id'];
$child_result = db_exec($link,$child_query);
$child=mysqli_fetch_assoc($child_result);


if($post['member_id']!=$member['id'] && $child['member_id'] !=$member['id'] && !$admin ){
    skip_page('index.php', 'error', '您没有权限！');

}
// 转义html字符
$post['title']=htmlspecialchars($post['title']);
$post['content']=nl2br(htmlspecialchars($post['content']));

if(isset($_POST['publish'])){
    $post_content = real_string($link,$_POST);
    //如果帖子需要修改，会保留原填写内容
    $amend_url = 'post_edit.php?id='.$post['id'].'&post='.rawurlencode(json_encode($post_content));

    //检查各字段
    if(empty($post_content['title'])){
        skip_page($amend_url,"error","请输入标题！");
    }
    if(mb_strlen($post_content['title']) >$char_max_title){
        skip_page($amend_url, 'error', '标题超过'.$char_max_title.'个字符，请重试！');
    
    }
    
    if(mb_strlen($post_content['content']) < 15){
        skip_page($amend_url,"error",'帖子内容少于15字！');
    }
    // 更新帖子
    $query = 'update bbs_content set 
            title="'.$post_content['title'].'",
            content="'.$post_content['content'].'",
            mod_time=now()
            where id='.$post['id'];
    db_exec($link,$query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('post.php?id='.$post['id'], 'ok', '帖子修改成功 ！');
    } else {
        skip_page($amend_url, 'error', '修改失败，请重试！');
    }

}
if(isset($_GET['post'])){
    $post_content = json_decode($_GET['post'],true);
    $post['title']=$post_content['title'];
    $post['content']=$post_content['content'];
}

$parent_query = 'select * from bbs_parent_module where id='.$child['parent_module_id'];
$parent_result = db_exec($link,$parent_query);
$parent = mysqli_fetch_assoc($parent_result);

$template['title']='编辑帖子';
$template['css']=['style/public.css',
                    'style/publish.css'];

?>
<?php include 'include/header.php' ;?>
	<div id="position" class="auto">
         <a href="index.php">首页</a> &gt; <a href="parent.php?id=<?php echo $parent['id'];?>"><?php echo $parent['module_name'];?></a> 
         &gt; <a href="child.php?id=<?php echo $child['id']?>"><?php echo $child['module_name']?></a>
         &gt; <a href="post.php?id=<?php echo $post['id']?>"><?php echo $post['title']?></a>
	</div>
	<div id="publish">
		<form method="post">
			
			<input class="title" placeholder="请输入标题，不超过<?php echo $char_max_title?>个字符" name="title" type="text" value="<?php echo $post['title']?>" />
			<textarea name="content" class="content"><?php echo $post['content']?></textarea>
            <input class="publish" type="submit" name="publish" value="" /> 
            <?php 
                $url = rawurlencode('post_del.php?id='.$post['id']);
                $rurl = rawurlencode($_SERVER['REQUEST_URI']);
                $msg = rawurlencode('是否删除帖子“'.$post['title'].'”？');
                $del_url = '?del=y&url='.$url.'&msg='.$msg.'&rurl='.$rurl;
            ?>
            <span style="display:block;height:30px;margin-top:15px" > &nbsp;&nbsp;| <a href="<?php echo $del_url;?>">删除</a></span>
			<div style="clear:both;"></div>
		</form>
	</div>
    
<?php include 'include/footer.php' ;?>