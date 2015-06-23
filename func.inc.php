<?php

	//获取access_token
	$access_token = get_token();
	//CURL请求的函数http_request() 
	//通过https 中的get 或 post
	function https_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
	//获取access_token函数
	//获取access_token
	function get_token() {
			

				
			/*	$appid="wx7929d98545171160";
				$secret="78db3f0842902e2e6f321c3409d3afc6";*///bro的appid
        $appid="wx38cea81d646bde71";
        $secret="8e05953e1f06927504447de3c8eeff3f";
	
				$json=https_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}");
	
				$arr= json_decode($json, true);

				$access_token = $arr["access_token"];
	
				return $access_token;
	}
	
	//my_json_decode() 将数组转成json
	function my_json_encode($p, $type="text") {
  		 if (PHP_VERSION >= '5.4') {
     			   $str = json_encode($p, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
   		 } else {
   		    	 switch ($type)
       		    	 {
          			  case 'text':
              			  	isset($p['text']['content']) && ($p['text']['content'] = urlencode($p['text']['content']));
              			 	 break;
       		  	  }
        	 	  $str = urldecode(json_encode($p));
  		  }
 		   return $str;
	}

	//通过扫描二维码添加用户进入指定的组， 能数为一个组的ID, 和这个关注用户的openid
	function adduser( $openid, $groupid=0, $subscribe=true) {
		//包含这个文件连接数据库
		include "conn.inc.php";
		//使用全局的access_token
		global $access_token;
		//如果参数subscribe=true就移到分组，否则只在本数地加个用户
		if($subscribe) {					
			//接口是移动组的接口， 如果关注时，用指定组的能数，直接将用户分到指定的组中
			$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$access_token}";
	
			//参数post json 
			$jsonstr = '{"openid":"'.$openid.'","to_groupid":'.$groupid.'}';

			$result = https_request($url, $jsonstr);
		}
		
		//通过自己写的一个函数getUserInfo()获取用户的详细信息
		$user = getUserInfo($openid);
		$user['groupid'] = $groupid;


		//如果已经是关注过又取消的，则已经有记录了， 有记录了更新关注字段、组和时间即可
		$sql = "select count(*) as num from wuser where openid='{$openid}'";
		$result = mysql_query($sql);
		$count = mysql_fetch_assoc($result);
		//如果根据openid在表中查到有记录，就不要再插入数据
		if($count['num']  > 0) {
			$sql = "update wuser set subscribe='1', groupid='{$groupid}', subscribe_time='{$user['subscribe_time']}' where openid='{$openid}'";
		
			mysql_query($sql);
		}else{
			//第一次关注时加一条记录
			
			$sql = "insert into wuser(openid, groupid, subscribe, nickname, sex, city, country, province, headimgurl, subscribe_time) values('{$user['openid']}','{$user['groupid']}','{$user['subscribe']}','{$user['nickname']}','{$user['sex']}','{$user['city']}','{$user['country']}','{$user['province']}','{$user['headimgurl']}','{$user['subscribe_time']}')";

		
			$result=mysql_query($sql);

		}

		
	
	}


	//获取用户的信息， 参数是openid， 通过openid访问获取用户的接口获取用户的全部信息。
	function getUserInfo($openid) {
			
			//$access_token=get_token();
			global $access_token;
			
			$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
		
		
			$result = https_request($url);
	
			$user = json_decode($result, true);
		
			return $user;
	}


	//通过指定openid取销用户表的关注
	function deluser($openid) {
		include "conn.inc.php";
		$sql = "update wuser set subscribe='0' where openid='{$openid}'";
		mysql_query($sql);
	}


	//通过指定openid取销用户表的关注
	function modgroup($openid, $groupid) {
		include "conn.inc.php";
		$sql = "update wuser set groupid='{$groupid}' where openid='{$openid}'";
		mysql_query($sql);
	}
