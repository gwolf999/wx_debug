<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/3/24
 * Time: 22:39
 *
 * 此程序只执行一次，为获得所有已关注用户的列表；
 * 下次再修改insertUser();依据已经获得用户详细信息更改userinfo表，如果没有用户则追加。
 */
header("Content-Type:text/html;charset=utf-8");
require_once "conn.inc.php";
//设置传的字符集为utf8
mysql_query("set names utf8");
function getUserInfoList()
{
    require_once "curl.php";
    $access_token=get_token();
    //第一个拉取的OPENID，不填默认从头开始拉取;正确时返回JSON数据包：{"total":2,"count":2,"data":{"openid":["","OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
    //当公众号关注者数量超过10000时，可通过填写next_openid的值，从而多次拉取列表的方式来满足需求。具体而言，就是在调用接口时，将上一次调用得到的返回中的next_openid值，作为下一次调用中的next_openid值。
    $next_openid="";
    //http请求方式: GET
    //$url="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
    $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid={$next_openid}";
    echo "usl=".$url.'<br/>';
    /* 请求获取用户信息的接口， 返回这个openid对应的用户信息，json格式;
    调用CURL.php文件中https_request($url, $data=null)函数，只传地址则为get方式，带$data数据则为POST方式*/
    $jsoninfo=https_request($url);
    //转化为数组;json_decode函数带true则转为数组；否则转化为对象
    $user=json_decode($jsoninfo,true);
    return $user;
}

$sql="CREATE TABLE if not exists userinfo (openid varchar(28) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
nickname varchar(60) NOT NULL COMMENT '昵称' ,
sex varchar(1)  CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '性别',
city VARCHAR( 20 ) CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '城市',
province VARCHAR( 20 ) CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '省',
headimgurl varchar(100)  CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '头像网址',
subscribe_time datetime NOT NULL comment '用户最后一次关注公众号时间',
utime datetime NOT NULL comment '记录更新时间',
message varchar(1)  CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '消息类别值为1或0'
)";
$result=mysql_query($sql);
if(!$result){
    echo "程序出错了！". '<br/>';
    echo "出错语句是".$sql. '<br/>';
}
$userList=getUserInfoList();
//用pre标签更直观。
echo "<pre>";
var_dump($userList);
echo "</pre>";
//结果显示此数组为三维数组，openid为最里面
foreach($userList as $data){
    echo $data. '<br/>';
    if (is_array($data)){

    foreach ($data as $userArr) {
           foreach($userArr as $userOpenid) {
               // $sql = "insert into userinfo('openid') VALUES ({$userOpenid['openid']})";
               // echo "userOpenid=".$userOpenid . '<br/>';
               $sql = "insert into userinfo(openid) VALUES ('{$userOpenid}')";//字段前面不要加'';userinfo('openid')就出错。
              // echo $sql . '<br/>';
              $result=mysql_query($sql);
               if(!$result){
                   echo "程序出错了！". '<br/>';
                   echo "出错语句是".$sql. '<br/>';
               }
                                                 }
                                      }
                            }
    }