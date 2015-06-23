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
 * Date: 2015/3/27
 * Time: 0:35
 * 移动用户分组
 */
require_once "ufun_g.inc.php";
require_once "curl.php";
header("Content-Type:text/html;charset=utf-8");

//获取access_token
$access_token = get_token();

if(isset($_POST['dosubmit'])) {
    $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$access_token}";

    //参数post json
    $jsonstr = '{"openid":"'.$_POST['openid'].'","to_groupid":'.$_POST['selectopenid'].'}';


    $result = https_request($url, $jsonstr);

    var_dump($result);
}


?>
<form method="post" action="togroup_g.php">
    <!--先通过$_GET['openid']取得传过来的openid，再转化为$_post方式递交-->
    <input type="hidden" name="openid" value="<?php echo $_GET['openid'] ?>">
    移动到:
    <select name="selectopenid">
     <!--   <option value="1"> 一号</option>
         <option value="2">二号</option>
        <option value="3">三号</option>-->
        <?php
        /*http请求方式: POST（请使用https协议）
https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=ACCESS_TOKEN
POST数据例子：{"openid":"oDF3iYx0ro3_7jD4HFRDfrjdCM58","to_groupid":108}
参数	说明   access_token	 调用接口凭证  openid	 用户唯一标识符  to_groupid	 分组id*/
        $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";//先查询分组
        $result	= https_request($url);
        /*查询返回的json格式
         *  {    "groups": [
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
}*/
        //将返回来的json转成数组操作
        $groups = json_decode($result, true);
        echo "<pre>";
        var_dump($groups);
        echo "</br>";

        foreach($groups['groups'] as $g){//$groups as $g，难怪出错。
            echo '<option value="'.$g['id'].'">'.$g['name'].'</option>';
        }



        ?>

    </select>


<input type="submit" name="dosubmit" value="移动所选用户">
</form>

</body>
</html>
