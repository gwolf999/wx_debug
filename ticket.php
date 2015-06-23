<?php
	//获取ticket 
	
	include "func.inc.php";
	
	$access_token = get_token();
	
	
	$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
	
	
	$jsonstr = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 100}}}';
	
	$result = https_request($url, $jsonstr);
	
	$arr = json_decode($result, true);
	//
	$ticket = $arr['ticket'];
	
	$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
	
	//echo https_request($url);
	
	$imageInfo = downImage($url);
	
	$filename = "wxcode.jpg";
	
	file_put_contents($filename, $imageInfo);
	
	function downImage($url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_NOBODY, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
	
echo '<img src="'.$filename.'">';
	
	
	
	
	
	
	
	
	
	
	
	
