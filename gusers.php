<?php	
	header("Content-Type:text/html;charset=utf-8");

	include "func.inc.php";

	//获取所有用户
	$access_token = get_token();


//	$next_openid="";
	//获取所有关注用户
	$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}";
	
	$result = https_request($url);

	$users = json_decode($result, true);

	
	foreach($users['data']['openid'] as $openid ) {
		
		echo "---{$openid}--<br>";
		adduser($openid, 0, false);
	}

	echo "导入成功！";
