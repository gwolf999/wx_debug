<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
</head>
<body>
<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/3/19
 * Time: 21:37
 */

/*通过传入的openid，获得与你聊天的用户的基本信息*/
function getUserInfo($openid)
{
    require_once "curl.php";
    $access_token=get_token();
    //http请求方式: GET
    $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
    /* 请求获取用户信息的接口， 返回这个openid对应的用户信息，json格式;
    调用CURL.php文件中https_request($url, $data=null)函数，只传地址则为get方式，带$data数据则为POST方式*/
    $jsoninfo=https_request($url);
    //转化为数组;json_decode函数带true则转为数组；否则转化为对象
    $user=json_decode($jsoninfo,true);
    return $user;
}

//用户一回话就将用户信息放入表user
function insertUser($user)
{   var_dump($user);
   require_once "conn.inc.php";
  // require_once "conn.inc.php";
    //设置传的字符集为utf8
    mysql_query("set names utf8");
     //conn.inc.php中已经包含选择数据库语句了。

    //插入字段为openid,昵称，性别,省份,头像地址,用户关注时间,当前时间
   // $sql="insert into user(openid, nickname,sex,city,province,headimgurl,subscribe_time,utime) VALUES ('{$user["openid"]}','{$user["nickname"]}','{$user["sex"]}','{$user["city"]}','{$user["province"]}','{$user["headimgurl"]}','{$user["subscribe_time"]}','".time()."')";
    //UNIX时间戳转换为日期用函数： FROM_UNIXTIME(),与下面的php方法一样成功。日期转换为UNIX时间戳用函数： UNIX_TIMESTAMP(),例如  Select UNIX_TIMESTAMP(’2006-11-04 12:23:00′);  unix_timestamp(now())
 //   $sql="insert into user(openid, nickname,sex,city,province,headimgurl,subscribe_time,utime) VALUES ('{$user["openid"]}','{$user["nickname"]}','{$user["sex"]}','{$user["city"]}','{$user["province"]}','{$user["headimgurl"]}',FROM_UNIXTIME({$user["subscribe_time"]}),'".time()."')";
    //now函数是mysql中的；time（）是PHP中的函数。用now()效率更高
    $sql="insert into user(openid, nickname,sex,city,province,headimgurl,subscribe_time,utime) VALUES ('{$user["openid"]}','{$user["nickname"]}','{$user["sex"]}','{$user["city"]}','{$user["province"]}','{$user["headimgurl"]}',FROM_UNIXTIME({$user["subscribe_time"]}),"."now(".")".")";

    //   $this->logger("\r\n insertUser函数中的SQL=".$sql);
    echo '<br/>'."insertUser函数的sql语句是".$sql.'<br/>';
    mysql_query($sql);
    return true;

}



//回话给用户，第一个参数： $openid用户的编号.第二个参数： $text.第三个参数:你说的和公众号数据标记  1 表示公众号  0表示用户消息,默认为0.第四个参数：消息类型
function insertMessage($openid, $text, $who="0", $mtype="text")
{
    require_once "conn.inc.php";
    //设置传的字符集为utf8
    mysql_query("set names utf8");
   // $sql="insert into message(openid, mess, who, utime, mtype) values('{$openid}','{$text}','{$who}','".time()."','{$mtype}')";
    $sql="insert into message(openid, message, who, utime, mtype) values('{$openid}','{$text}','{$who}',"."now(".")".",'{$mtype}'".")";
    echo "insertMessage插入message的sql语句=".$sql."<br/>";
   // $this->logger("\r\n insertMessage函数中的SQL=".$sql);
        mysql_query($sql);
    //存入消息后，同时更新用户列表的时间，以便排序查询。
    $sql = "update user set utime="."now(".")".", message='1' where openid='{$openid}'";
    echo "insertMessage更新message的sql语句=".$sql."<br/>";
    	mysql_query($sql);


}
//my_json_decode() 将数组转成json
function my_json_encode($type, $p)
{
    if (PHP_VERSION >= '5.4')
    {
        $str = json_encode($p, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    else
    {
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


function sendText($openid,$text){
    require_once "curl.php";//里面有get_token()函数
    //照样先获取token并设置好服务接口的URL
    $access_token = get_token();
    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
    /*//传递服务接口的json格式示例
    			{
    "touser":"OPENID",
    "msgtype":"text",
    "text":
    {
         "content":"Hello World"
    }
}
		*/
    //先设置好数组，到时候在调用自定义函数my_json_encode把数组转化为json格式
    $textarr = array('touser'=>$openid, "msgtype"=>"text", "text"=>array("content"=>$text));
    $jsontext = my_json_encode("text", $textarr);
        //自定义函数https_request，带参数为post方式，否则为get

    $result  = https_request($url,$jsontext);
}

//客服向用户发送文本.参数为openid,参数二为回复内容
/*function sendText($openid, $text){

    $access_token = get_token();
    //调用客服接口的地址
    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";

}*/
/*require_once "conn.inc.php";
//require_once "conn.inc.php";
$userArr=array();
$userArr=array('openid'=>'abcdefghijklmnopqrstuvwxyz1','nickname'=>'天知道','sex'=>'0','city'=>'赣州','province'=>'江西','headimgurl'=>'https://www.baidu.com','subscribe_time'=>'1426756864');
//PHP中进行转换,UNIX时间戳转换为日期用函数：date(),date('Y-m-d H:i:s',1156219870);下面的最后关注事件就是这样的。
//$userArr=array('openid'=>'abcdefghijklmnopqrstuvwxyz1','nickname'=>'天知道','sex'=>'0','city'=>'常德','province'=>'湖南','headimgurl'=>'https://www.baidu.com','subscribe_time'=>date('Y-m-d H:i:s',1426782864));

//echo 'userArr='.$userArr.'<br/>';

//$sql="DROP TABLE IF EXISTS user2";
/*$sql="DROP TABLE user";
mysql_query($sql);*/
/*$sql="CREATE TABLE if not exists user (openid varchar(28) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL primary key,
nickname varchar(60) NOT NULL COMMENT '昵称' ,
sex varchar(1)  CHARACTER SET utf8  COLLATE utf8_bin NOT NULL COMMENT  '性别',
city VARCHAR( 20 ) CHARACTER SET gb2312 COLLATE gb2312_chinese_ci NOT NULL COMMENT  '城市',
province VARCHAR( 20 ) CHARACTER SET gb2312 COLLATE gb2312_chinese_ci NOT NULL COMMENT  '省',
headimgurl varchar(100)  CHARACTER SET utf8  COLLATE utf8_bin NOT NULL COMMENT  '头像网址',
subscribe_time datetime NOT NULL comment '用户最后一次关注公众号时间',
utime datetime NOT NULL comment '记录更新时间'
)";*/
//因为在调试过程中设置了主键，所以只能插入一条记录
/*$sql="CREATE TABLE if not exists user (openid varchar(28) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
nickname varchar(60) NOT NULL COMMENT '昵称' ,
sex varchar(1)  CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '性别',
city VARCHAR( 20 ) CHARACTER SET gb2312 COLLATE gb2312_chinese_ci COMMENT  '城市',
province VARCHAR( 20 ) CHARACTER SET gb2312 COLLATE gb2312_chinese_ci COMMENT  '省',
headimgurl varchar(100)  CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '头像网址',
subscribe_time datetime NOT NULL comment '用户最后一次关注公众号时间',
utime datetime NOT NULL comment '记录更新时间',
message varchar(1)  CHARACTER SET utf8  COLLATE utf8_bin COMMENT  '消息类别值为1或0'
)";*/
/*mysql_query($sql);
$flag=insertUser($userArr);
echo $flag.'<br/>';
$sql="select * from user";
$sqlquery=mysql_query($sql);*/
/*
while($sqlarr=mysql_fetch_assoc($sqlquery)){
    echo $sqlarr["province"]."===".$sqlarr["city"]."时间为".$sqlarr["subscribe_time"]."记录更新时间为".$sqlarr["utime"]."<br/>";
}

$messArr=array();
$messArr=array('openid'=>'caotao2hijklmnopqrstuvwxyz1','message'=>'这是曹涛2用来测试消息的text','who'=>'0','utime'=>'2015-03-22 00:16:14','mtype'=>'text');*/

/*$sql="CREATE TABLE if not exists message (openid varchar(28) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
message TEXT CHARACTER SET gbk COLLATE gbk_chinese_ci NOT NULL COMMENT '用户所传信息' ,
who varchar(1)  CHARACTER SET utf8  COLLATE utf8_bin NOT NULL COMMENT  '谁发的信息',
utime datetime NOT NULL comment '消息发送时间',
mtype VARCHAR( 20 )CHARACTER SET utf8  COLLATE utf8_bin NOT NULL COMMENT  '消息类型'
)";*/
/*echo 'sql='.'$sql'.'<br/>';
mysql_query($sql);
insertMessage($messArr["openid"],$messArr["message"],0,'text');
echo "ok".'<br/>';*/


//只传地址则为get方式，带$data数据则为POST方式 $data应该是依据接口要求可以是json，也可是数组，甚至字符串?
function https_request($url, $data=null) {
    $curl = curl_init();
    // 这是你想用PHP取回的URL地址。你也可以在用curl_init()函数初始化时设置这个选项。
    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);


    if(!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);//启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//传递一个作为HTTP “POST”操作的所有数据的字符串。
    }
    //参数为1;如果成功只将结果返回，不自动输出任何内容。如果失败返回FALSE
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
//两次分别在不同机器上调用http://202.101.231.120/wxkf/curl.php;显示出的access_token都不一样。实在不行只有放入数据库中了。但这样效率太低了。
//http://www.cnblogs.com/txw1958/p/weixin-access_token-memcache.html方倍的案例利用memcache来缓存$access_token;
function get_token() {
    /*php中的变量作用范围的另一个重要特性就是静态变量（static 变量）。
    静态变量仅在局部函数域中存在且只被初始化一次,当程序执行离开此作用域时，
    其值不会消失,会使用上次执行的结果。*/
    //没有用，但是在书本易伟的九大接口p25中其利用新浪的数据memcache_init()来存储成功。类似存在数据库中。
    /*static变量实现缓存的优缺点：优点：
速度快，效率高，实现简单。由于是PHP内部变量，是所有缓存中执行效率最高的。
缺点：灵活性差，只在本次连接中有效，执行区域小，只在同一个函数中有效，无法跨函数运作（可以使用全局变量替代）。
总结：static变量做缓存非常好用，而且耗费的资源不多，对于要查询数据库的，且在一次连接中可能执行多次的，不妨加上。虽然可能效果有限。*/
    static $access_token;//定义$access_token的静态变量
    static $expires_time;//定义静态变量，用来存储access_token，即使初始值为0，但只要执行了一次，则下次永远调用上次的结果。
    $appid="wx38cea81d646bde71";
    $secret="8e05953e1f06927504447de3c8eeff3f";
    /* echo "expires_time=".$expires_time.'</br>';
     echo"time()=".time().'</br>';
     echo "老的access_token=" . $access_token.'</br>';
     echo "isset($access_token)=".isset($access_token).'</br>';
     echo "time()<$expires_time=".time()<$expires_time.'</br>';*/
    $arr=array();
    //传入的函数为'access'，表示读取存文件中的token
    $arr_access=get_access_token('access');
    if(!empty($arr_access)){//这里读出来，下面的if来判断。
        $access_token=$arr_access[0];
        $expires_time=$arr_access[1];
    }

    if(isset($access_token) && time()<$expires_time)
    {  // echo "memory access_token=".$access_token.'</br>';
        return $access_token;
    }else {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
        $json = https_request($url);
        //  echo "json=" . $json . '</br>';
        $arr = json_decode($json, true);
        /*  echo "<pre>";
          var_dump($arr);
          echo "</pre>";*/

        $expires_time = time() + intval($arr['expires_in']);
        /*   echo "重新生成的expires_time=" . $expires_time.'</br>';
           echo "重新生成的$access_token=" . $arr['access_token'].'</br>';*/
        $access_token=$arr['access_token'];
        // return $arr['access_token'];
        $content=$access_token.','.$expires_time;
        //  echo '$content='.$content.'<br>';
        get_access_token($content);
        return $access_token;
    }
}


$jsonmenu=<<<json
		 {
     "button":[
     {
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "name":"菜单1",
           "sub_button":[
           {
               "type":"view",
               "name":"搜索1",
               "url":"http://www.soso.com/"
            },
            {
               "type":"view",
               "name":"视频",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 }
json;

/*存取token，如果超过两个小时，则重新读并写新的token;传进来的参数值如果为'access'，表示读出token，否则为写*/
function get_access_token($log_content)
{   $log=null;
    $log= $log_content;
    ini_set('date.timezone','Asia/Shanghai');//设置时区,否则服务器显示时区不对
    $max_size = 100000;   //声明日志的最大尺寸
    $log_filename = "token.txt";  //日志名称
    //如果文件存在并且大于了规定的最大尺寸就删除了
    if(file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size))
    {
        unlink($log_filename);
    }
    //写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
    IF($log=='access'){//如传进来是，表示是读token及时间
        /*file_get_contents() 函数把整个文件读入一个字符串中。和 file() 一样，不同的是 file_get_contents() 把文件读入一个字符串。*/
        $result=file_get_contents('token.txt');
        $arr=array();
        $arr=explode(",",$result);
        return $arr;
    }else{
        if(WX_HOST=='DX'){
            /*FILE_USE_INCLUDE_PATH (默认,相当于重新生成) FILE_APPEND LOCK_EX  */
            file_put_contents( $log_filename,$log);
        }else{
            /*   $saelog=new SaeStorage();
               $saelog->write("log","log.xml",date("H:i:s")." ".$log_content."\r\n",FILE_APPEND);*/
        }

    }


}
//define("WX_HOST",'DX');
/*$content="222222eerewarqwe".","."12345678123456";
get_access_token($content);
$arr=array();
$arr=get_access_token('access');
echo '$arr[0]='.$arr[0].'<br>';
echo '$arr[1]='.$arr[1].'<br>';*/
/*$tmp=get_token();
echo '$tmp='.$tmp.'<br>';*/
?>
</body>