<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

$member = is_login($link);
$template['title']='看帖';
$template['css']=['style/public.css',
                    'style/show.css'];

if(!isset($_GET['id'])||!is_numeric($_GET['id'])){
    header('Location:index.php');
}
$post_query = 'select * from bbs_content where id='.$_GET['id'];
$post_result = db_exec($link,$post_query);
if(!$post=mysqli_fetch_assoc($post_result)){
    header('Location:index.php');
}
// 浏览计数
$views_query = 'update bbs_content set views=views+1 where id='.$_GET['id'];
db_exec($link,$views_query);

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

$reply_query = 'select * from bbs_reply where content_id='.$_GET['id'].' order by pub_time';
$reply_result = db_exec($link,$reply_query);

$reply_count = mysqli_num_rows($reply_result);

$slice = 4;

// 如果有回复id，当前页为这条回复所在页面
if(isset($_GET['reply_id']) && is_numeric($_GET['reply_id']) && !isset($_GET['page'])){
	$cur_reply_query = 'select * from bbs_reply where id<='.$_GET['reply_id'];
	$cur_reply_count = record_count($link,$cur_reply_query);
	$_GET['page'] = ceil($cur_reply_count/$slice);
}
$page_btns = 5; // 显示的最大页码按钮数
$paging = paging($reply_count,$slice,$_GET['page'],$page_btns);

?>
<?php include 'include/header.php' ;?>

	<div id="position" class="auto">
         <a href="index.php">首页</a> &gt; <a href="parent.php?id=<?php echo $parent['id'];?>"><?php echo $parent['module_name'];?></a> 
         &gt; <a href="child.php?id=<?php echo $child['id']?>"><?php echo $child['module_name']?></a>
         &gt; <a href="post.php?id=<?php echo $post['id']?>"><?php echo $post['title']?></a>
	</div>
	<div id="main" class="auto">
		<div class="wrap1">
			    <?php echo $paging['html'];?>
			<a class="btn reply" href="reply.php?post_id=<?php echo $post['id'];?>"></a>
			<div style="clear:both;"></div>
		</div>
		<?php 
		// 只在第一页显示楼主帖子
		if($paging['cur_page'] == 1){
		?>
		<div class="wrapContent">
			<div class="left">
				<div class="face">
					<a target="_blank" href="member.php?id=<?php echo $post['member_id'];?>">
						<img width="120" height="120" src="<?php $avatar= empty($post_member['photo'])? 'style/photo.jpg': $post_member['photo']; echo $avatar; ?>" />
					</a>
				</div>
				<div class="name">
					<a href="member.php?id=<?php echo $post_member['id']?>"><?php echo $post_member['name'];?></a>
				</div>
			</div>
			<div class="right">
				<div class="title">
					<h2><?php echo $post['title']?></h2>
					<span>阅读：<?php echo $post['views']?>&nbsp;|&nbsp;回复：<?php echo $reply_count;?></span>
					<div style="clear:both;"></div>
				</div>
				<div class="pubdate">
                    <span class="date">发布于：<?php echo $post['pub_time']?> </span>&nbsp;&nbsp;
                    <?php 
                        if($post['pub_time'] != $post['mod_time']){
                    ?>
                        &nbsp;&nbsp;<span class="date">编辑于：<?php echo $post['mod_time']?> </span>&nbsp;&nbsp;
                    <?php   
                    }
                    ?>
                    <?php
                        if($post_member['id']==$member['id']){
                    ?>
                        <span class="date">&nbsp;&nbsp;<a target='_blank' href='post_edit.php?id=<?php echo $post['id'];?>'>编辑</a></span>
                    <?php }?>
					<span class="floor" style="color:red;font-size:14px;font-weight:bold;">楼主</span>
				</div>
				<div class="content">
                    <?php echo $post['content']?>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div>
		<?php } ?>


		<?php 
		// 显示回帖
		$reply_query = 'select * from bbs_reply where content_id='.$_GET['id'].' order by pub_time '.$paging['sql'];
		$reply_result = db_exec($link,$reply_query);
		$i=1+($paging['cur_page']-1)*$slice;//每页起始的回复计数，也即起始楼层
		//$floors=[]; //储存楼层的回复id
		while($reply=mysqli_fetch_assoc($reply_result)){
			//$floors[$reply['id']]=$i;
			$reply['content']=nl2br(htmlspecialchars($reply['content']));
			
			$reply_member_query = 'select * from bbs_member where id='.$reply['member_id'];
			$reply_member_result = db_exec($link,$reply_member_query);
			if(!$reply_member=mysqli_fetch_assoc($reply_member_result)){
			    $reply_member['name'] = '[已注销]';
			}
			?>
		<div class="wrapContent">
			<div class="left">
				<div class="face">
					<a target="_blank" href="member.php?id=<?php echo $reply['member_id'];?>">
						<img width="120" height="120" src="<?php $avatar= empty($reply_member['photo'])? 'style/photo.jpg': $reply_member['photo']; echo $avatar; ?>" />
					</a>
				</div>
				<div class="name">
					<a target="_blank" href="member.php?id=<?php echo $reply_member['id']?>"><?php echo $reply_member['name'];?></a>
				</div>
			</div>
			<div class="right">
				
				<div class="pubdate">
					<span class="date">回复时间：<?php echo $reply['pub_time']?> </span>&nbsp;&nbsp;
					<?php 
                        if($reply['pub_time'] != $reply['mod_time']){
                    ?>
                        &nbsp;&nbsp;<span class="date">编辑于：<?php echo $reply['mod_time']?> </span>&nbsp;&nbsp;
                    <?php   
                    }
                    ?>
                    <?php
                        if($reply_member['id']==$member['id']){
                    ?>
                        <span class="date">&nbsp;&nbsp;<a target='_blank' href='reply_edit.php?id=<?php echo $reply['id'];?>'>编辑</a></span>
                    <?php }?>
					<span class="floor"><?php echo $i;?>楼&nbsp;|&nbsp;<a href="reply.php?post_id=<?php echo $post['id'];?>&floor=<?php echo $i; ?>&quote_id=<?php echo $reply['id'];?>">引用</a></span>
				</div>
				<div class="content">
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

				<!-- 回帖内容-->
                    <?php echo $reply['content'];?>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div>
		<?php 
			$i++;
		}
		?>

		<div class="wrap1">
			<?php echo $paging['html'];?>
			<a class="btn reply" href="reply.php?post_id=<?php echo $post['id'];?>"></a>
			<div style="clear:both;"></div>
		</div>
	</div>
<?php include 'include/footer.php' ;?>
	