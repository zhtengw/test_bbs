<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 
$member = is_login($link);
$admin = is_admin_login($link);
if(!$member && !$admin){
    skip_page('login.php', 'error', '请先登录！');
}

//删除回复
if(isset($_GET['del']) && isset($_GET['url']) && isset($_GET['msg']) && isset($_GET['rurl'])) {
    confirm_page($_GET['url'],$_GET['msg'],$_GET['rurl']);
}

if(!isset($_GET['id'])||!is_numeric($_GET['id'])){
    skip_page('index.php', 'error', '访问错误！');
}

$reply_query = 'select * from bbs_reply where id='.$_GET['id'];
$reply_result = db_exec($link,$reply_query);
if(!$reply=mysqli_fetch_assoc($reply_result)){
    skip_page('index.php', 'error', '该回帖不存在！');
}
$post_query = 'select * from bbs_content where id='.$reply['content_id'];
$post_result = db_exec($link,$post_query);
$post=mysqli_fetch_assoc($post_result);

// 转义html字符
$post['title']=htmlspecialchars($post['title']);

$child_query = 'select * from bbs_child_module where id='.$post['module_id'];
$child_result = db_exec($link,$child_query);
$child=mysqli_fetch_assoc($child_result);
// 发帖人、版主和后台管理员均有权限
if($reply['member_id']!=$member['id'] && $child['member_id'] !=$member['id'] && !$admin ){
    skip_page('index.php', 'error', '您没有权限！');
}
    

if(isset($_POST['reply'])){
    $reply_content = real_string($link,$_POST);
    //如果帖子需要修改，会保留原填写内容
    $amend_url = 'reply_edit.php?id='.$_GET['id'].'&reply='.rawurlencode(json_encode($reply_content));

    //检查各字段
    if(mb_strlen($reply_content['content']) < 3){
        skip_page($amend_url,"error",'回帖内容不得少于3字！');
    }
    
    $quote_query='';
    if(isset($reply_content['del_quote'])){
        $quote_query=' quote_id=null, ';
    }

    $query = 'update bbs_reply set 
            '.$quote_query.' 
            content="'.$reply_content['content'].'",
            mod_time=now()
            where id='.$reply['id'];
    db_exec($link,$query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('post.php?id='.$reply['content_id'].'&reply_id='.$reply['id'], 'ok', '修改成功 ！');
    } else {
        skip_page($amend_url, 'error', '发帖失败，请重试！');
    }

}
if(isset($_GET['reply'])){
    $reply_content = json_decode($_GET['reply'],true);
    $reply['content'] = $reply_content['content'];
}


$parent_query = 'select * from bbs_parent_module where id='.$child['parent_module_id'];
$parent_result = db_exec($link,$parent_query);
$parent = mysqli_fetch_assoc($parent_result);

$member_query = 'select * from bbs_member where id='.$post['member_id'];
$member_result = db_exec($link,$member_query);
if(!$post_member=mysqli_fetch_assoc($member_result)){
    $post_member['name'] = '[已注销]';
}

$template['title']='编辑回复 -- '.$post['title'];
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
        <div>回复：由 <?php echo $post_member['name'];?> 发布的 <?php echo $post['title'];?></div>
        <?php
			// 如果回帖有引用，先显示引用的帖子
			if(!empty($reply['quote_id'])){
				$quote_query='select * from bbs_reply where id='.$reply['quote_id'];
				$quote_result = db_exec($link,$quote_query);
				if($quote=mysqli_fetch_assoc($quote_result)){
                    
					$quote['content']=nl2br(htmlspecialchars($quote['content']));

                    $quote_count_query = 'select * from bbs_reply where content_id='.$quote['content_id'].' and id<='.$quote['id'];
                    $floor = record_count($link,$quote_count_query);
                    
                    $quote_member_query = 'select * from bbs_member where id='.$quote['member_id'];
					$quote_member_result = db_exec($link,$quote_member_query);
					if(!$quote_member=mysqli_fetch_assoc($quote_member_result)){
						$quote_member['name'] = '[已注销]';
                    }	
        ?>

			<div class="quote">
			<h2>引用 <?php echo $floor;?>楼 <?php echo $quote_member['name'];?> 发表的: </h2>
            <?php echo $quote['content']; ?>
			</div>
        <?php
                } else {
        ?>
                    <div class="quote">
			        <h2>引用回帖已删除</h2>
			        </div>
        <?php
                }
                 
			} ?>
        
        <form method="post">
            <?php 
			if(!empty($reply['quote_id'])){
            ?>
            <input style="width:15px;vertical-align:middle;margin-bottom:2px" name="del_quote" type="checkbox" value="on" />&nbsp;删除引用
            <?php }?>
			<textarea name="content" class="content"><?php echo $reply['content']?></textarea>
			<input class="reply" type="submit" name="reply" value="" />
            <?php 
                $url = rawurlencode('reply_del.php?id='.$reply['id'].'&post_id='.$reply['content_id']);
                $rurl = rawurlencode($_SERVER['REQUEST_URI']);
                $msg = rawurlencode('是否删除此回复？');
                $del_url = '?del=y&url='.$url.'&msg='.$msg.'&rurl='.$rurl;
            ?>
            <span style="display:block;height:30px;margin-top:15px" > &nbsp;&nbsp;| <a href="<?php echo $del_url;?>">删除</a></span>
			<div style="clear:both;"></div>
		</form>
		
	</div>
    
<?php include 'include/footer.php' ;?>