<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 


$member = is_login($link);

$thispage=basename($_SERVER['SCRIPT_NAME']);

if(empty($_GET['keyword'])){
    $search_result = '';
    $search_count = 0;

    $result_msg = '请输入关键字搜索！';
}else{
    $keyword = real_string($link,$_GET['keyword']);

    $search_query = 'select id from bbs_content WHERE title like "%'.$keyword.'%" OR content like "%'.$keyword.'%"';
    $search_count = record_count($link,$search_query);

    $result_msg = '含有 <span style="font-weight:bolder;">'.$_GET['keyword'].'</span> 的帖子搜到 <span style="font-weight:bolder;">'.$search_count.'</span> 条结果';
}


// 每页帖子数，
// Todo：增加一个下拉列表通过页面设置
$slice = 20;
$page_btns = 5; // 显示的最大页码按钮数
$paging = paging($search_count,$slice,$_GET['page'],$page_btns);


$template['title']=$keyword." 的搜索结果";
$template['css']=['style/public.css',
                'style/list.css'];
?>
<?php include 'include/header.php' ;?>
     <div id="position" class="auto">
         <a href="index.php">首页</a> &gt; 搜索
       
	</div>
	<div id="main" class="auto">
		<div id="left">
			<div class="box_wrap">
                <?php echo $result_msg;?>
				
				<div class="pages_wrap">
						<?php echo $paging['html'];?>
					<div style="clear:both;"></div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<ul class="postsList">
                <?php
                if($search_count){
                // 为了添加分页，重新查询
                    $search_query = 'select id,module_id,title,member_id,pub_time,views 
                                from bbs_content WHERE title like "%'.$keyword.'%" OR content like "%'.$keyword.'%" '.$paging['sql'];
                    $search_result = db_exec($link,$search_query);
                    $search_count = mysqli_num_rows($search_result);
                    while($post=mysqli_fetch_assoc($search_result)){
                        $post['title']=htmlspecialchars($post['title']);
                        $post['title_highlight']=str_replace($keyword,'<span style="color:red">'.$keyword.'</span>',$post['title']);

						$reply_query = 'select * from bbs_reply where content_id='.$post['id'].' order by pub_time desc';
                        $reply_result = db_exec($link,$reply_query);
                        $reply_count = mysqli_num_rows($reply_result);
						if($reply=mysqli_fetch_assoc($reply_result)){
							$last_reply = $reply['pub_time'];
						} else {
							$last_reply = '无';
						}

                        $query = 'select member.id member_id,
                                member.name member_name,
                                member.photo,
                                child.id child_id,
                                child.module_name child_module_name,
                                parent.id parent_id,
                                parent.module_name parent_module_name
                                from bbs_member member,
                                bbs_child_module child,
                                bbs_parent_module parent
                                WHERE member.id='.$post['member_id'].'
                                and child.id='.$post['module_id'].'
                                and parent.id=child.parent_module_id
                                ';
                        $info_result = db_exec($link,$query);
                        $info = mysqli_fetch_assoc($info_result);

                ?>
                <li>
					<div class="smallPic">
						<a href="member.php?id=<?php echo $info['member_id'];?>">
							<img width="45" height="45" src="<?php $avatar= empty($info['photo'])? 'style/photo.jpg': $info['photo']; echo $avatar; ?>">
						</a>
					</div>
					<div class="subject">
						<div class="titleWrap">
                            <a href="parent.php?id=<?php echo $info['parent_id'];?>">[<?php echo $info['parent_module_name'];?>]</a>
                            <a href="child.php?id=<?php echo $info['child_id'];?>">[<?php echo $info['child_module_name'];?>]</a>
                            &nbsp;&nbsp;<h2><a href="post.php?id=<?php echo $post['id'];?>"><?php echo $post['title_highlight'];?></a></h2>
                        </div>
						<p>
                            楼主：<a style="color:#999" href="member.php?id=<?php echo $info['member_id'];?>"><?php echo $info['member_name'];?></a>
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
                }
                ?>
				
				
			</ul>
			<div class="pages_wrap">
						<?php echo $paging['html'];?>
				<div style="clear:both;"></div>
			</div>
		</div>
		
        <?php include 'include/right.php' ;?>
		<div style="clear:both;"></div>
	</div>
<?php include 'include/footer.php' ;?>
