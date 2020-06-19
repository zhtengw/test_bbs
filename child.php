<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 


$member = is_login($link);

$thispage=basename($_SERVER['SCRIPT_NAME']);

$template['title']='子版块列表';
$template['css']=['style/public.css',
                'style/list.css'];

if(!isset($_GET['id'])||!is_numeric($_GET['id'])){
    header('Location:index.php');
}
$child_query = 'select * from bbs_child_module where id='.$_GET['id'];
$child_result = db_exec($link,$child_query);
if(!$child=mysqli_fetch_assoc($child_result)){
    header('Location:index.php');
}
$parent_query = 'select * from bbs_parent_module where id='.$child['parent_module_id'];
$parent_result = db_exec($link,$parent_query);
$parent = mysqli_fetch_assoc($parent_result);

$post_query = 'select * from bbs_content where module_id='.$child['id'];
$post_result = db_exec($link,$post_query);
$post_count = mysqli_num_rows($post_result);

$today_query = 'select * from bbs_content where module_id='.$child['id'].' and pub_time > CURDATE()';
$today_count = record_count($link,$today_query);

$moderator_query = 'select name from bbs_member where id='.$child['member_id'];
$moderator_result = db_exec($link,$moderator_query);
$moderator=mysqli_fetch_assoc($moderator_result);

// 每页帖子数，
// Todo：增加一个下拉列表通过页面设置
$slice = 20;
$page_btns = 5; // 显示的最大页码按钮数
$paging = paging($post_count,$slice,$_GET['page'],$page_btns);
?>
<?php include 'include/header.php' ;?>
	<?php include 'include/left.php' ;?>

				<div class="moderator">版主：<span><?php if($moderator){echo $moderator['name'];}else{echo '暂无版主';}?></span></div>
				<div class="notice"><?php echo $child['info']?></div>
				<div class="pages_wrap">
					<a class="btn publish" href="publish.php?child_id=<?php echo $child['id'] ?>" target="_blank"></a>
						<?php echo $paging['html'];?>
					<div style="clear:both;"></div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<ul class="postsList">
				<?php
					
                    $post_query = 'select post.*,
                                 member.name member_name,
                                 member.photo
                                 from bbs_content post, 
                                 bbs_member member
                                 where post.member_id=member.id
								 and post.module_id='.$child['id'].'
                                 order by post.mod_time desc '.
								 $paging['sql'];
                    $post_result = db_exec($link,$post_query);
                    while($post=mysqli_fetch_assoc($post_result)){
						$post['title']=htmlspecialchars($post['title']);

						$reply_query = 'select * from bbs_reply where content_id='.$post['id'].' order by pub_time desc';
						$reply_result = db_exec($link,$reply_query);
                        $reply_count = mysqli_num_rows($reply_result);
						if($reply=mysqli_fetch_assoc($reply_result)){
							$last_reply = $reply['pub_time'];
						} else {
							$last_reply = '无';
						}
                ?>
                <li>
					<div class="smallPic">
						<a href="member.php?id=<?php echo $post['member_id'];?>">
							<img width="45" height="45" src="<?php $avatar= empty($post['photo'])? 'style/photo.jpg': $post['photo']; echo $avatar; ?>">
						</a>
					</div>
					<div class="subject">
						<div class="titleWrap"><h2><a href="post.php?id=<?php echo $post['id'];?>"><?php echo $post['title'];?></a></h2></div>
						<p>
							楼主：<a style="color:#999" href="member.php?id=<?php echo $post['member_id'];?>"><?php echo $post['member_name'];?></a>
							&nbsp;<?php echo $post['pub_time'];?>&nbsp;&nbsp;&nbsp;&nbsp;
							最后回复：<a style="color:#999" href="post.php?id=<?php echo $post['id'];?>&reply_id=<?php echo $reply['id'];?>"><?php echo $last_reply ;?></a>
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
                <?php
                    }
                ?>
			</ul>
			<div class="pages_wrap">
				<a class="btn publish" href="publish.php?child_id=<?php echo $child['id'] ?>" target="_blank"></a>
						<?php echo $paging['html'];?>
				<div style="clear:both;"></div>
			</div>
		</div>
        <?php include 'include/right.php' ;?>
		<div style="clear:both;"></div>
	</div>
<?php include 'include/footer.php' ;?>
