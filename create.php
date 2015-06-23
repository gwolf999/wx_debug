<?php
	header("Content-Type:text/html;charset=utf-8");
	//这个文件里创建分组
	
	//包含函数库文件，有三个函数可以使用
	include "func.inc.php";
	//如果用户提交了
	if(isset($_POST['dosubmit']))  {
		//url上用的accesstoken
		$access_token = get_token();
		//url
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$access_token}";
		//post方式将组名传过过
		$jsonstr = '{"group":{"name":"'.$_POST['name'].'"}}';
		//请求这个接口，返回 id 和 组名的 json
		$result = https_request($url, $jsonstr);
		
		//var_dump($result);
		//创建成功转到组列表
		header("Location:group.php");
	}
	
?>

<br>
<form action="create.php" method="post">
	分组名称：<input type="text" name="name" value=""> 
	
	<input type="submit" name="dosubmit" value="添加分组">
</form>