<?php
	header("Content-Type:text/html;charset=utf-8");
	//这个文件里用来修改组名
	
	//包含函数库文件，有三个函数可以使用
	include "func.inc.php";
	//如果用户提交了
	if(isset($_POST['dosubmit']))  {
		
		//access_token
		$access_token = get_token();
		//修改url
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$access_token}";
		//post传过去 组id和组名
		$jsonstr = '{"group":{"id":'.$_POST['id'].',"name":"'.$_POST['name'].'"}}';
		//CURL请求 post
		https_request($url, $jsonstr);
		//var_dump($result);
		//创建成功转到组列表
		header("Location:group.php");
	}
	
?>

<br>
<form action="mgroup.php" method="post">
	<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
	分组名称：<input type="text" name="name" value="<?php echo $_GET['name'] ?>"> 
	
	<input type="submit" name="dosubmit" value="修改组名">
</form>