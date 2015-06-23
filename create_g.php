<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/3/26
 * Time: 23:05
 * 	这个文件里创建分组
 */
//require_once "ufun_g.inc.php";
require_once "curl.php";
header("Content-Type:text/html;charset=utf-8");
if(isset($_POST["groupsubmit"])){//判断如果存在递交，等下测试($_POST["groupname"]))
    //url上用的accesstoken
    $access_token = get_token();
    /*http请求方式: POST（请使用https协议）
https://api.weixin.qq.com/cgi-bin/groups/create?access_token=ACCESS_TOKEN
POST数据格式：json  POST数据例子：{"group":{"name":"test"}}*/
    $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$access_token}";
  // $jsongoupid={"group":{"name":"曹涛华为"}};//这是直接设置
    $jsongoupname='{"group":{"name":"'.$_POST['groupname'].'"}}';
  // $jsonstr = '{"group":{"name":"'.$_POST['name'].'"}}';
   // echo "jsongoupname=".$jsongoupname.'<br/>';
    //请求这个接口，返回 id 和 组名的 json
    $result = https_request($url, $jsongoupname);
    //var_dump($result);
    //创建成功转到组列表
    header("Location:group_g.php");

}


?>
<form action="create_g.php" method="post" name="creat" >
    请输入分组的名称<input name="groupname" type="text" value="">
    <input type="submit" name="groupsubmit" value="递交分组">

</form>
</body>
</html>