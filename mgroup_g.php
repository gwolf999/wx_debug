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
 * Time: 23:40
 * 这个文件里用来修改组名
 */
require_once "ufun_g.inc.php";
require_once "curl.php";
header("Content-Type:text/html;charset=utf-8");

//如果用户提交了
if(isset($_POST['dosubmit'])) {//如果没有这个判断，因为有41行的header跳转，所以总是会到原来的调用上去。
//获取access_token
    $access_token = get_token();
    /*http请求方式: POST（请使用https协议）
    https://api.weixin.qq.com/cgi-bin/groups/update?access_token=ACCESS_TOKEN
    POST数据格式：json
    POST数据例子：{"group":{"id":108,"name":"test2_modify2"}}
    参数	说明
    access_token	 调用接口凭证
    id	 分组id，由微信分配
    name	 分组名字（30个字符以内）*/
    $url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$access_token}";

//post传过去 组id和组名
    $jsonstr = '{"group":{"id":' . $_POST['hideid'] . ',"name":"' . $_POST['mgroup'] . '"}}';
//CURL请求 post
    https_request($url, $jsonstr);
//var_dump($result);
//创建成功转到组列表
    header("Location:group_g.php");
}
?>
<form action="mgroup_g.php" name="mgroup" method="post">

    <input type="hidden" name="hideid" value="<?php echo $_GET['id'] ?>">
    输入要修改的分组名称
    <input type="text" name="mgroup" value="<?php echo $_GET['name'] ?>">
    <input type="submit" name="dosubmit" value="递交修改分组">

</form>

</body>
</html>