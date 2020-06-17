<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 


$member = is_login($link);


$template['title']='首页';
$template['css']=['style/public.css',
                'style/index.css'];

?>
<?php include 'include/header.php' ;?>
   <div id="hot" class="auto">
		<div class="title">热门动态</div>
		<ul class="newlist">
			<!-- 20条 -->
			<li><a href="#">[库队]</a> <a href="#">私房库实战项目录制中...</a></li>
			
		</ul>
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
