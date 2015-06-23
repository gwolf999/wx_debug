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
 * Time: 23:32
 * 显示分组列表
 */
require_once "ufun_g.inc.php";
require_once "curl.php";
header("Content-Type:text/html;charset=utf-8");
echo "显示用户分组:".'<br/>';
//获取access_token
$access_token = get_token();
/*查询所有分组接口调用请求说明
http请求方式: GET（请使用https协议）
https://api.weixin.qq.com/cgi-bin/groups/get?access_token=ACCESS_TOKEN*/
$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
/*返回说明
{
    "groups": [
        {
            "id": 0,
            "name": "未分组",
            "count": 72596
        },
        {
            "id": 1,
            "name": "黑名单",
            "count": 36
        }
    ]
}
参数	说明
groups	 公众平台分组信息列表
id	 分组id，由微信分配
name	 分组名字，UTF8编码
count	 分组内用户数量
 * */

$result	= https_request($url);
//将返回来的json转成数组操作
$groups = json_decode($result, true);
//遍历数组形成分组列表
echo '<ul>';
foreach($groups['groups'] as $g) {
    echo '<li>'.$g['name'].'('.$g['count'].')&nbsp;&nbsp;<a href="mgroup_g.php?name='.$g['name'].'&id='.$g['id'].'">修改</a></li>';
    echo "当前分组的id为".$g['id'];
   // echo "<br/>";
  //  echo "<a href="."mgroup_g.php?name='".$g['name']."'&id='".$g['id']."'>"."修改</a>";
  //  echo "<a href="."mgroup.php".">"."修改</a>";
}
echo '</ul>';

echo '<br><a href="create_g.php">创建分组</a>';

?>


</body>
</html>
