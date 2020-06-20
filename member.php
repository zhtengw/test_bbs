<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

$member = is_login($link);

if(!isset($_GET['id'])||!is_numeric($_GET['id'])){
    skip_page('index.php', 'error', '访问错误！');
}
$member_browse_query = 'select * from bbs_member where id='.$_GET['id'];
$member_browse_result = db_exec($link,$member_browse_query);
if(!$member_browse=mysqli_fetch_assoc($member_browse_result)){
    skip_page('index.php', 'error', '该用户不存在！');
}

$post_query = 'select * from bbs_content where member_id='.$member_browse['id'];
$post_result = db_exec($link,$post_query);
$post_count = mysqli_num_rows($post_result);

$slice = 10;
$page_btns = 5; // 显示的最大页码按钮数
$paging = paging($post_count,$slice,$_GET['page'],$page_btns);

$template['title']='会员中心';
$template['css']=['style/public.css',
                    'style/list.css',
                    'style/member.css'];

?>
<?php include 'include/header.php' ;?>
	<div id="position" class="auto">
		<a href="index.php">首页</a> &gt; <?php echo $member_browse['name'];?>
	</div>
	<div id="main" class="auto">
		<div id="left">
			<ul class="postsList">
                <?php
                    $post_query = 'select * from bbs_content where member_id='.$member_browse['id'].' '.$paging['sql'];
                    $post_result = db_exec($link,$post_query);
                    while($post=mysqli_fetch_assoc($post_result)){
                        $post['title']=htmlspecialchars($post['title']);
                        
						$reply_query = 'select bbs_reply.*,bbs_member.name from bbs_reply, bbs_member where content_id='.$post['id'].' and bbs_reply.member_id=bbs_member.id order by pub_time desc';
						$reply_result = db_exec($link,$reply_query);
                        $reply_count = mysqli_num_rows($reply_result);
						if($reply=mysqli_fetch_assoc($reply_result)){
							$last_reply = $reply['name'];
							$last_reply_time = $reply['pub_time'];
						} else {
                            $last_reply = '无';
							$last_reply_time = '';
                        }
                    
                ?>
				<li>
					<div class="smallPic">
						<img width="45" height="45" src="<?php $avatar= empty($member_browse['photo'])? 'style/photo.jpg': $member_browse['photo']; echo $avatar; ?>" />
					</div>
					<div class="subject">
						<div class="titleWrap"><h2><a target="_blank" href="post.php?id=<?php echo $post['id'];?>"><?php echo $post['title'];?></a></h2></div>
						<p>
                        <?php
                            if($member_browse['id']==$member['id']){
                        ?>
                            <a target='_blank' href='post_edit.php?id=<?php echo $post['id'];?>'>编辑</a>
                        <?php }?>

							最后回复：<a style="color:#999" href="post.php?id=<?php echo $post['id'];?>&reply_id=<?php echo $reply['id'];?>" target="_blank"><?php echo $last_reply.'  '.$last_reply_time;?></a>
						</p>
					</div>
					<div class="count">
						<p>
							回复<br /><span><?php echo $reply_count;?></span>
						</p>
						<p>
							浏览<br /><span><?php echo $post['views'];?></span>
						</p>
					</div>
					<div style="clear:both;"></div>
                </li>
                <?php }?>
			</ul>
			<div class="pages">
                <?php echo $paging['html'];?>
            </div>
			<div style="clear:both;"></div>
		</div>
		<div id="right">
			<div class="member_big">
				<dl>
					<dt>
						<img width="180" height="180" src="<?php $avatar= empty($member_browse['photo'])? 'style/photo.jpg': $member_browse['photo']; echo $avatar; ?>" />
					</dt>
					<dd class="name"><?php echo $member_browse['name'];?></dd>
                    <dd>帖子总计：<?php echo $post_count;?></dd>
                    <?php
                        if($member_browse['id']==$member['id']){
                    ?>
                    <dd>操作：<a target="_blank" href="avatar_upload.php">修改头像</a> | <a target="_blank" href="">修改密码</a></dd>
                    <?php }?>
				</dl>
				<div style="clear:both;"></div>
			</div>
		</div>
		<div style="clear:both;"></div>

    </div>
<?php include 'include/footer.php' ;?>
