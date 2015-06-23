<?php echo $_GET['name']."({$_GET['count']})"; ?>用户列表:<br>
<?php
	include "func.inc.php";
	
	//获取所有用户
	$access_token = get_token();
	
	$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}";
	
	$result = https_request($url);

	$users = json_decode($result, true);

	echo '<ul>';
	foreach($users['data']['openid'] as $g) {
		//查询每个用户所在的分组
		
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token={$access_token}";
		
		$jsonstr = '{"openid":"'.$g.'"}';
		
		$result = https_request($url, $jsonstr);
	
		$group = json_decode($result, true);
		
		$groupid = $group['groupid'];
		
		if($groupid == $_GET['id']) {
			//获取用户基本信息
			
			$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$g}&lang=zh_CN";
		
		
			$result = https_request($url);
	
			$user = json_decode($result, true);
		
			echo '<li><img width="60" src="'.$user['headimgurl'].'">'.$user['nickname'].'所在地：'.$user['province'].$user['city'].'----<a href="togroup.php?curg='.$_GET['name'].'&username='.$user['nickname'].'&opeinid='.$g.'">移动到...</a></li>';
		}
	}
	echo '</ul>';
?>





<br><a href="group.php">返回组列表</a><br>