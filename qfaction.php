<?php
	header("Content-Type:text/html;charset=utf-8");
	include "func.inc.php";
	
	$access_token = get_token();
	
	if($_POST['dtype'] == "group") {
		
		switch($_POST['type']) {
			//图文
			case "news":
				$jsondata = '
{
   "filter":{
      "group_id":"'.$_POST['group'].'"
   },
   "mpnews":{
      "media_id":"'.$_POST['content'].'"
   },
    "msgtype":"mpnews"
}';

			break;
			//文本
			case "text":
				$jsondata = '
{
   "filter":{
      "group_id":'.$_POST['group'].'"
   },
   "text":{
      "content":"'.$_POST['content'].'"
   },
    "msgtype":"text"
}';

			break;
			//语音
			case "voice":
				$jsondata = '
{
   "filter":{
      "group_id":'.$_POST['group'].'"
   },
   "voice":{
      "media_id":"'.$_POST['content'].'"
   },
    "msgtype":"voice"
}';

			break;
			//图片
			case "image":
				$jsondata = '
{
   "filter":{
      "group_id":'.$_POST['group'].'"
   },
   "image":{
      "media_id":"'.$_POST['content'].'"
   },
    "msgtype":"image"
}';

			break;
	
		}

		//调用接口发送
		
		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$access_token}";

		$result = https_request($url, $jsondata);
	
	}else {
		$openids = "";
		foreach($_POST['openid'] as $openid) {
			$openids .='"'.$openid.'",';
		}
		$openids = rtrim($openids, ",");
		switch($_POST['type']) {
			//图文
			case "news":
				$jsondata = '
{
   "touser":[
   '.$openids.'
   ],
   "mpnews":{
      "media_id":"'.$_POST['content'].'"
   },
    "msgtype":"mpnews"
}';

			break;
			//文本
			case "text":
				$jsondata = '
					
{
   "touser": [
	   '.$openids.'
	   ], 
	"msgtype": "text", 
	"text": { "content": "'.$_POST['content'].'"}
}
';

			break;
			//语音
			case "voice":
				$jsondata = '
{
   "touser":[
 '.$openids.'
   ],
   "voice":{
      "media_id":"'.$_POST['content'].'"
   },
    "msgtype":"voice"
}';

			break;
			//图片
			case "image":
				$jsondata = '
{
   "touser":[
 '.$openids.'
   ],
   "image":{
      "media_id":"'.$_POST['content'].'"
   },
    "msgtype":"image"
}
';

			break;
	
		}

		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$access_token}";
		$result = https_request($url, $jsondata);
	}

		echo $url;
		echo '<br>';
		echo $jsondata;
		echo '<br>';
		var_dump($result);
//Array ( [group] => 104 [content] => adsadsadsa [dtype] => group [type] => news [dosubmit] => 缇ゅ彂 ) 

