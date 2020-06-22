<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 


$member = is_login($link);


$template['title']='首页';
$template['css']=['style/public.css',
                'style/list.css',
                'style/index.css'];

?>
<?php include 'include/header.php' ;?>
   <div id="hot" class="auto">
		<div class="title">热门动态</div>
   <div id="main" class="auto" style="background: white;">
		<!--<ul class="newlist">-->
		<ul class="postsList" style="padding:0 20px 0 20px;">
            <?php
            $max_num = 10;
            //$post_query = 'select id,title,member_id,pub_time from bbs_content order by pub_time desc;';
            $post_query = 'select post.id,
                                post.title,
                                post.views,
                                post.pub_time,
                                 member.name member_name,
                                 child.id child_id,
                                 child.module_name child_module_name,
                                 parent.id parent_id,
                                 parent.module_name parent_module_name
                                 from bbs_content post, 
                                 bbs_member member,
                                 bbs_child_module child,
                                 bbs_parent_module parent 
                                 where post.member_id=member.id
                                 and child.parent_module_id=parent.id
                                 and post.module_id=child.id
                                 order by post.mod_time desc ';
            $post_result = db_exec($link,$post_query);
            $hot_count=1;
            while($post=mysqli_fetch_assoc($post_result)){
                $post['title']=htmlspecialchars($post['title']);

                $reply_query = 'select id,pub_time from bbs_reply where content_id='.$post['id'].' order by pub_time desc';
                $reply_result = db_exec($link,$reply_query);
                $reply_count = mysqli_num_rows($reply_result);
                $reply=mysqli_fetch_assoc($reply_result);
				$last_reply = $reply['pub_time'];
                if ( $reply_count >= 2 && $hot_count <= $max_num){
                    ?>
                <li>
                    <div class="subject">
						<div class="titleWrap">
                            <a href="parent.php?id=<?php echo $post['parent_id'];?>">[<?php echo $post['parent_module_name'];?>]</a>
                            <a href="child.php?id=<?php echo $post['child_id'];?>">[<?php echo $post['child_module_name'];?>]</a>&nbsp;&nbsp;<h2><a href="post.php?id=<?php echo $post['id'];?>"><?php echo $post['title'];?></a></h2>
                        </div>
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
                    $hot_count++;
                }


            }
            ?>
            
  
		</ul>
    </div>
		<div style="clear:both;"></div>
    </div>
    <?php
        $parent_query = 'select * from bbs_parent_module order by sort';
        $parent_result = db_exec($link,$parent_query);
        while($parent=mysqli_fetch_assoc($parent_result)){
    ?>
	    <div class="box auto">
            <div class="title">
                <a href="parent.php?id=<?php echo $parent['id']?>" style="color:#105cb6"><?php echo $parent['module_name'];?></a>
            </div>
    	    <div class="classList">
    <?php
                $child_query = 'select * from bbs_child_module where parent_module_id='.$parent['id'].' order by sort';
                $child_result = db_exec($link,$child_query);
                if(mysqli_num_rows($child_result)!=0){
                    while($child=mysqli_fetch_assoc($child_result)){
                        $post_query = 'select * from bbs_content where module_id='.$child['id'];
                        $num_post = record_count($link,$post_query);
                        $today_query = 'select * from bbs_content where module_id='.$child['id'].' and pub_time > CURDATE()';
                        $num_today = record_count($link,$today_query);
                   $child_title=<<<EOF
                    <div class="childBox new">
                        <h2><a href="child.php?id={$child['id']}">{$child['module_name']}</a> <span>(今日{$num_today})</span></h2>
                        帖子：{$num_post}<br />
                    </div>
EOF;
                        echo $child_title;
                    }
                }else{
                ?>
			        <div style="padding:10px 0;">暂无子版块...</div>
                    <div style="clear:both;"></div>
               <?php } ?>

                <div style="clear:both;"></div>

            </div>
        </div>
       <?php } ?>
 
<?php include 'include/footer.php' ;?>
