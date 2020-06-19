<?php
    $pagename = pathinfo($thispage)['filename'];
?>
    <div id="position" class="auto">
         <a href="index.php">首页</a> &gt; <a href="parent.php?id=<?php echo $parent['id'];?>"><?php echo $parent['module_name'];?></a> 
         <?php
             if( $pagename == 'child') echo '&gt; <a href="child.php?id='.$child['id'].'">'.$child['module_name'].'</a>'
         ?>
	</div>
	<div id="main" class="auto">
		<div id="left">
			<div class="box_wrap">
                <h3><?php echo $$pagename['module_name'];?></h3>
				<div class="num">
				    今日：<span><?php echo $today_count;?></span>&nbsp;&nbsp;&nbsp;
				    总帖：<span><?php echo $post_count;?></span>
                </div>