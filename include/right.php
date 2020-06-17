		<div id="right">
			<div class="classList">
				<div class="title">版块列表</div>
				<ul class="listWrap">
                    <?php
                    $parent_query = 'select * from bbs_parent_module';
                    $parent_result = db_exec($link,$parent_query);
                    while($parent=mysqli_fetch_assoc($parent_result)){
                    ?>
					<li>
						<h2><a href="parent.php?id=<?php echo $parent['id'];?>"><?php echo $parent['module_name'];?></a></h2>
                        <ul>
                    <?php
                        $child_query = 'select * from bbs_child_module where parent_module_id='.$parent['id'].' order by sort';
                        $child_result = db_exec($link,$child_query);
                        while($child=mysqli_fetch_assoc($child_result)){
							echo '<li><h3><a href="child.php?id='.$child['id'].'">'.$child['module_name'].'</a></h3></li>';
                        }
                    ?>
						</ul>
                    </li>
                    <?php
                    }
                    ?>
				</ul>
			</div>
		</div>