<?php
	//移动用户分组
	header("Content-Type:text/html;charset=utf-8");
	//包含函数库文件，有三个函数可以使用
	include "func.inc.php";
	
	//获取access_token
	$access_token = get_token();

	
	if(isset($_POST['dosubmit'])) {
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$access_token}";
	
		//参数post json
		$jsonstr = '{"openid":"'.$_POST['openid'].'","to_groupid":'.$_POST['gid'].'}';
	
	
		$result = https_request($url, $jsonstr);

		//将表移动一下
		modgroup($_POST['openid'], $_POST['gid']);

		var_dump($result);
	}
	
?>

<br>

<form action="togroup.php" method="post">
	<input type="hidden" name="openid" value="<?php echo $_GET['openid'] ?>">
	移动到: 
		<select name="gid">
			<?php
					
				$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
				
				$result	= https_request($url);
				//将返回来的json转成数组操作
				$groups = json_decode($result, true);
				
				//遍历数组形成分组列表
				
					foreach($groups['groups'] as $g) {
							echo '<option value="'.$g['id'].'">'.$g['name'].'</option>';
					}
				
			?>
		</select>
	
	<input type="submit" name="dosubmit" value="移动">
</form>


<a href="group.php">组列表</a>
