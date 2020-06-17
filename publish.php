<?php
include_once 'common/config.php';
include_once 'common/tools.php';
include_once 'common/mysql.php';

$link = db_connect(); 

if(!$member = is_login($link)){
    skip_page('login.php', 'error', '请先登录！');
}

$char_max_title = char_max_len($link,'bbs_content','title');

if(isset($_POST['publish'])){
    $post_content = real_string($link,$_POST);
    $amend_url = 'publish.php?post='.rawurlencode(json_encode($post_content));

    //检查各字段
    if(empty($post_content['title'])){
        skip_page($amend_url,"error","请输入标题！");
    }
    if(mb_strlen($post_content['title']) >$char_max_title){
        skip_page($amend_url, 'error', '标题超过'.$char_max_title.'个字符，请重试！');
    
    }
    if(empty($post_content['module_id']) || !is_numeric($post_content['module_id'])){
        skip_page($amend_url,"error","请选择发帖版块！");
    }
    $search_query = 'select * from bbs_child_module where id='.$post_content['module_id'];
    if(!record_count($link,$search_query)){
        skip_page($amend_url,"error","请选择发帖版块！");
    }
    
    if(mb_strlen($post_content['content']) < 15){
        skip_page($amend_url,"error",'帖子内容少于15字！');
    }
    // 检查是否有该用户
    $query = 'insert into bbs_content (module_id, title, content, member_id, pub_time, mod_time) values ('.
            $post_content['module_id'].','.
            '"'.$post_content['title'].'",'.
            '"'.$post_content['content'].'",'.
            $member['id'].','.
            'now(),
            now());'
            ;
    db_exec($link,$query);

    if(mysqli_affected_rows($link) == 1){
        skip_page('index.php', 'ok', '发帖成功 ！');
    } else {
        skip_page($amend_url, 'error', '发帖失败，请重试！');
    }

}
if(isset($_GET['post'])){
    $post_content = json_decode($_GET['post'],true);
}

$template['title']='发表新帖';
$template['css']=['style/public.css',
                    'style/publish.css'];

?>
<?php include 'include/header.php' ;?>
	<div id="position" class="auto">
		 <a href="index.php">首页</a> &gt; 发表新帖
	</div>
	<div id="publish">
		<form method="post">
			<select name="module_id">
                <option value=""  disabled="" selected>请选择一个版块</option>
                            <?php
                                $query = 'select
                                        child.id,
                                        child.module_name,
                                        parent.module_name parent_module_name
                                        from bbs_child_module child,
                                        bbs_parent_module parent
                                        where child.parent_module_id=parent.id
                                        order by parent.id
                                        ';
                                
                                $result = db_exec($link,$query);
                                while($data=mysqli_fetch_assoc($result)){
                                    if(!isset($group_name)){                                        
                                        $group_name = $data['parent_module_name'];
                                        echo '<optgroup label="'.$group_name.'">';
                                    }
                                    if ($data['parent_module_name'] != $group_name){
                                        echo '</optgroup>';
                                        $group_name = $data['parent_module_name'];
                                        echo '<optgroup label="'.$group_name.'">';
                                    }
                                    if ($data['id'] == $post_content['module_id']){
                                        echo '<option value="'.$data['id'].'" selected>'.$data['module_name'].'</option>';
                                    }else {
                                        echo '<option value="'.$data['id'].'">'.$data['module_name'].'</option>';
                                    }
                                }
                                if(mysqli_num_rows($result)!=0)echo '</optgroup>';

                            
                            ?>
			</select>
			<input class="title" placeholder="请输入标题，不超过<?php echo $char_max_title?>个字符" name="title" type="text" value="<?php echo $post_content['title']?>" />
			<textarea name="content" class="content"><?php echo $post_content['content']?></textarea>
			<input class="publish" type="submit" name="publish" value="" />
			<div style="clear:both;"></div>
		</form>
	</div>
    
<?php include 'include/footer.php' ;?>