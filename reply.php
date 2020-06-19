<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

if(!$member = is_login($link)){
    skip_page('login.php', 'error', '请先登录！');
}

$url_refer_arr = parse_url($_SERVER['HTTP_REFERER']);
$url_refer =$url_refer_arr['path'].'?'.$url_refer_arr['query']; 

if(!isset($_GET['post_id'])||!is_numeric($_GET['post_id'])){
    header('Location:'.$url_refer);
}
    
$quote_col='';
$quote_query='';
$quote_id='';
if(isset($_GET['quote_id']) && is_numeric($_GET['quote_id'])){
    $quote_query='select * from bbs_reply where id='.$_GET['quote_id'];
    if(record_count($link,$quote_query)){
        $quote_col=' quote_id,';
        $quote_query=$_GET['quote_id'].',';
        $quote_id=$_GET['quote_id'];
    }
}

if(isset($_POST['reply'])){
    $reply_content = real_string($link,$_POST);
    //如果帖子需要修改，会保留原填写内容
    $amend_url = 'reply.php?post_id='.$_GET['post_id'].'&reply='.rawurlencode(json_encode($reply_content));

    //检查各字段
    if(mb_strlen($reply_content['content']) < 3){
        skip_page($amend_url,"error",'回帖内容不得少于3字！');
    }

    $query = 'insert into bbs_reply (content_id,'.$quote_col.' content, member_id, pub_time, mod_time) values ('.
            $_GET['post_id'].','.
            $quote_query.
            '"'.$reply_content['content'].'",'.
            $member['id'].','.
            'now(),
            now());'
            ;
    db_exec($link,$query);

    if(mysqli_affected_rows($link) == 1){
        $newid = mysqli_insert_id($link);
        skip_page('post.php?id='.$_GET['post_id'].'&reply_id='.$newid, 'ok', '发帖成功 ！');
    } else {
        skip_page($amend_url, 'error', '发帖失败，请重试！');
    }

}
if(isset($_GET['reply'])){
    $reply_content = json_decode($_GET['reply'],true);
}

$post_query = 'select * from bbs_content where id='.$_GET['post_id'];
$post_result = db_exec($link,$post_query);
if(!$post=mysqli_fetch_assoc($post_result)){
    header('Location:'.$url_refer);
}

// 转义html字符
$post['title']=htmlspecialchars($post['title']);
$post['content']=nl2br(htmlspecialchars($post['content']));

$child_query = 'select * from bbs_child_module where id='.$post['module_id'];
$child_result = db_exec($link,$child_query);
$child=mysqli_fetch_assoc($child_result);

$parent_query = 'select * from bbs_parent_module where id='.$child['parent_module_id'];
$parent_result = db_exec($link,$parent_query);
$parent = mysqli_fetch_assoc($parent_result);

$member_query = 'select * from bbs_member where id='.$post['member_id'];
$member_result = db_exec($link,$member_query);
if(!$post_member=mysqli_fetch_assoc($member_result)){
    $post_member['name'] = '[已注销]';
}

$template['title']='回复 -- '.$post['title'];
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
			if(!empty($quote_id)){
				$quote_query='select * from bbs_reply where id='.$quote_id;
				$quote_result = db_exec($link,$quote_query);
				if($quote=mysqli_fetch_assoc($quote_result)){
					$quote['content']=nl2br(htmlspecialchars($quote['content']));

					$quote_member_query = 'select * from bbs_member where id='.$quote['member_id'];
					$quote_member_result = db_exec($link,$quote_member_query);
					if(!$quote_member=mysqli_fetch_assoc($quote_member_result)){
						$quote_member['name'] = '[已注销]';
                    }	
        ?>

			<div class="quote">
			<h2>引用 <?php echo $_GET['floor'];?>楼 <?php echo $quote_member['name'];?> 发表的: </h2>
            <?php echo $quote['content']; ?>
			</div>
        <?php
         		}
			} ?>
        
        <form method="post">
			<textarea name="content" class="content"><?php echo $reply_content['content']?></textarea>
			<input class="reply" type="submit" name="reply" value="" />
			<div style="clear:both;"></div>
		</form>
		
	</div>
    
<?php include 'include/footer.php' ;?>