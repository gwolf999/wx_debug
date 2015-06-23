<?php
          //只传地址则为get方式，带$data数据则为POST方式
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