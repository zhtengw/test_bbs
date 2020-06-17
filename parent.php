<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 


$member = is_login($link);


$template['title']='父版块列表';
$template['css']=['style/public.css',
                'style/list.css'];

if(!isset($_GET['id'])||!is_numeric($_GET['id'])){
    header('Location:index.php');
}
$parent_query = 'select * from bbs_parent_module where id='.$_GET['id'];
$parent_result = db_exec($link,$parent_query);
if(!$parent=mysqli_fetch_assoc($parent_result)){
    header('Location:index.php');
}
$child_query = 'select * from bbs_child_module where parent_module_id='.$parent['id'].' order by sort';
$child_result = db_exec($link,$child_query);
if(mysqli_num_rows($child_result)!=0){
    $post_count = 0;
    $today_count = 0;
    $child_ids = ''; 
    while($child=mysqli_fetch_assoc($child_result)){
        $child_modules[$child['id']] = $child['module_name'];
        $child_ids .=$child['id'].',';

        /* 查太多次数据库，不好
        $post_query = 'select * from bbs_content where module_id='.$child['id'];
        $post_count += record_count($link,$post_query);

        $today_query = 'select * from bbs_content where module_id='.$child['id'].' and pub_time > CURDATE()';
        $today_count += record_count($link,$post_query);
        */
    }
    $child_ids = trim($child_ids,',');
    $post_query = 'select * from bbs_content where module_id in('.$child_ids.')';
    $post_count = record_count($link,$post_query);

    $today_query = 'select * from bbs_content where module_id in('.$child_ids.') and pub_time > CURDATE()';
    $today_count = record_count($link,$today_query);
}else{
    skip_page('index.php','attention','“'.$parent['module_name'].'”下没有子版块，跳转到首页。');
}


?>
<?php include 'include/header.php' ;?>
	<?php include 'include/left.php' ;?>
				  <div class="moderator"> 子版块： 
                      <?php
                        if(isset($child_modules)){
                            foreach ($child_modules as $id => $name){ 
                                echo '<a style="color:#666" href="child.php?id='.$id.'">'.$name.'</a> ';
                            }
                        }
                      ?>
                  </div>
				<div class="pages_wrap">
					<a class="btn publish" href=""></a>
					<div class="pages">
						<a>« 上一页</a>
						<a>1</a>
						<span>2</span>
						<a>3</a>
						<a>4</a>
						<a>...13</a>
						<a>下一页 »</a>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<ul class="postsList">
                <?php
                    $post_query = 'select post.*,
                                 member.name member_name,
                                 member.photo,
                                 child.id child_id,
                                 child.module_name child_module_name
                                 from bbs_content post, 
                                 bbs_member member,
                                 bbs_child_module child 
                                 where post.member_id=member.id
                                 and post.module_id in('.$child_ids.')
                                 and post.module_id=child.id
                                 order by post.mod_time desc';
                    $post_result = db_exec($link,$post_query);
                    while($post=mysqli_fetch_assoc($post_result)){
                        /*
                        $child_query = 'select * from bbs_child_module where id='.$post['module_id'];
                        $child_result = db_exec($link,$child_query);
                        $child = mysqli_fetch_assoc($child_result);
                        */
                ?>
                <li>
					<div class="smallPic">
						<a href="#">
							<img width="45" height="45"src="<?php $avatar= empty($post['photo'])? 'style/photo.jpg': $post['photo']; echo $avatar; ?>">
						</a>
					</div>
					<div class="subject">
						<div class="titleWrap"><a href="child.php?id=<?php echo $post['child_id'];?>">[<?php echo $post['child_module_name'];?>]</a>&nbsp;&nbsp;<h2><a href="#"><?php echo $post['title'];?></a></h2></div>
						<p>
							楼主：<a style="color:#999" href="member.php?id=<?php echo $post['member_id'];?>"><?php echo $post['member_name'];?></a>&nbsp;<?php echo $post['pub_time'];?>&nbsp;&nbsp;&nbsp;&nbsp;最后回复：2014-12-08
						</p>
					</div>
					<div class="count">
						<p>
							回复<br /><span>41</span>
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
				<a class="btn publish" href=""></a>
				<div class="pages">
					<a>« 上一页</a>
					<a>1</a>
					<span>2</span>
					<a>3</a>
					<a>4</a>
					<a>...13</a>
					<a>下一页 »</a>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
		
        <?php include 'include/right.php' ;?>
		<div style="clear:both;"></div>
	</div>
<?php include 'include/footer.php' ;?>
