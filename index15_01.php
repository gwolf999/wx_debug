<?php
//通过Wechat类，才可以创建一个对象，才可使用类中的方法,类是不含（）的。
define("TOKEN","caotao20150205");
define("WX_HOST","DX");//定义微信服务器为DX、新浪云：SAE


$wechatObj = new wechat();
/*开发者提交信息后，微信服务器将发送GET请求到填写的服务器地址URL上，GET请求携带四个参数：
开发者通过检验signature对请求进行校验（下面有校验方式）。若确认此次GET请求来自微信服务器，
请原样返回echostr参数内容，则接入生效，成为开发者成功，否则接入失败。以后，用户发信息就不用get方法了。*/
$wechatObj->traceHttp();//调用接口追踪函数
if (isset($_GET['echostr'])){
    //在调用接口的时候，后面不相干的任何一个错误，都会导致程序错误，导致接口接入失败
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class wechat
{
    public function valid()
    {
        $echoStr = $_GET['echostr'];
        if($this->checkSignature())
        {   //$this->logger("R_echo_index05_01:".$echoStr."\r\n");//屏蔽此语句，则接口成功
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        /*也就是说,基本上$GLOBALS['HTTP_RAW_POST_DATA'] 和 $_POST是一样的。但是如果post过来的数据不是PHP能够识别的，你可以用 $GLOBALS['HTTP_RAW_POST_DATA']来接收，
       比如 text/xml 或者 soap 等等。PHP默认识别的数据类型是application/x-www.form-urlencoded标准的数据类型。用Content-Type=text/xml 类型，提交一个xml文档内容给了php server,要怎么获得这个POST数据。
The     RAW / uninterpreted HTTP POST information can be accessed with: $GLOBALS['HTTP_RAW_POST_DATA'] This is useful in cases where the post Content-Type is not something PHP understands (such as text/xml).
由于   PHP默认只识别application/x-www.form-urlencoded标准的数据类型，因此，对型如text/xml的内容无法解析为$_POST数组，故保留原型，交给$GLOBALS['HTTP_RAW_POST_DATA'] 来接收。
另外   还有一项 php://input 也可以实现此这个功能php://input 允许读取 POST 的原始数据。和 $HTTP_RAW_POST_DATA 比起来，它给内存带来的压力较小，并且不需要任何特殊的 php.ini
      设置。php://input 不能用于 enctype="multipart/form-data"。
        */
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if(!empty($postStr)){
            //  $result="";//返回的结果消息XML
//接受的消息写入日志 XML
            $this->logger("R \r\n".$postStr);//win下换行为\r\n,linux下为\n
//simplexml_load_string 与 simplexml_load_file区别为如果include的xml文件，则选择后者，xml在当前文件则用string
            $postObj = simplexml_load_string($postStr,'SimpleXMLElement', LIBXML_NOCDATA);
              //从消息对象中获取消息的类型 text  image location voice vodeo link
            $RX_TYPE =trim($postObj->MsgType);
            switch($RX_TYPE){
                case "text"://文本
                    $result = $this->receiveText($postObj);
                    break;
                case "video"://视频
                    $result=$this->receiveVideo($postObj);
                    break;
                case "link"://链接消息类型，调测通过收藏转发
                    $result=$this->receiveLink($postObj);
                    break;
                case "voice"://语音消息类型,调测时，写为Voice,结果失败
                    $result=$this->receiveVoice($postObj);
                    //$result=$this->receiveVoice_fb($postObj);
                    //this->logger("responseMsg返回的语音result1=".$result1.'\r\n');
                 /* $result="<xml>
<ToUserName><![CDATA[oe_ERszIKUZUbf0xQJP0XujZs5s8]]></ToUserName>
<FromUserName><![CDATA[gh_f2d4f1156c6b]]></FromUserName>
<CreateTime>1426178270</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>4</ArticleCount>
<Articles>
<item>
<Title><![CDATA[现在的天气预报]]></Title>
<Description><![CDATA[]]></Description>
<PicUrl><![CDATA[]]></PicUrl>
<Url><![CDATA[]]></Url>
</item> <item>
<Title><![CDATA[实况 温度：天气情况： 风速：级]]></Title>
<Description><![CDATA[]]></Description>
<PicUrl><![CDATA[]]></PicUrl>
<Url><![CDATA[]]></Url>
</item> <item>
<Title><![CDATA[广州现在的天气预报]]></Title>
<Description><![CDATA[]]></Description>
<PicUrl><![CDATA[]]></PicUrl>
<Url><![CDATA[]]></Url>
</item> <item>
<Title><![CDATA[实况 温度：18℃~13℃天气情况：小雨 风速：微风小于3级级]]></Title>
<Description><![CDATA[]]></Description>
<PicUrl><![CDATA[]]></PicUrl>
<Url><![CDATA[]]></Url>
</item> </Articles></xml>";*/
                    break;
              /*  case "Music"://接受类型里面无音乐类型，但回复消息里面哟音乐类型
                    $result=$this->receiveMusic($postObj);
                    break;*/
                case "location":
                    $result=$this->receiveLocation($postObj);
                    break;
                case "image":
                    $result=$this->receiveImage($postObj);
                    break;
                case "event":
                    $result=$this->receiveEvent($postObj);//接受事件函数，下面再细分各种事件
                    break;
                default:
                    $result="未知的上传消息类型".$RX_TYPE; //未知的消息类型
                    break;
            }
//回复给用户之前写入日志 XML
            $this->logger("T \r\n".$result);
            //输出消息给微信
            echo $result;
        }else{
            echo "";
            exit;//退出程序
        }
    }
    /*处理事件接受函数*/
    private function receiveEvent($object)
    {
        //定义$content变量为回复变量，针对不同事件，回复不同的信息
        $content="";
      switch(trim($object->Event))//就是这么一个Event写成event，总出错了
      {
          case"subscribe":
                    require_once "ufun_g.inc.php";
                    require_once "curl.php";
                    /*EventKey	 事件KEY值，qrscene_为前缀，后面为二维码的参数值 Ticket	 二维码的ticket，可用来换取二维码图片
                    <EventKey><![CDATA[qrscene_123123]]></EventKey> <Ticket><![CDATA[TICKET]]></Ticket>*/
                    $content = "欢迎通过二维码扫描进来的，我已经帮你分好组了";
                    $access_token=get_token();
                    /*str_replace() 函数使用一个字符串替换字符串中的另一些字符。
                    语法   str_replace(find,replace,string,count)  参数	描述
                             find	必需。规定要查找的值。replace	必需。规定替换 find 中的值的值。
                            string	必需。规定被搜索的字符串。    count	可选。一个变量，对替换数进行计数。
                            可以替换数组，并统计替换次数*/
                    $groupid=str_replace("qrscene_","",$object->EventKey);//把XML$object->EventKey格式中如果是通过扫描进来的$object->EventKey中的qrscene前缀替换为空
                    // $groupid所取得id实际是由微信分配的。
                    $openid = $object->FromUserName;
                    //修改分组名的接口;POST数据格式：json   POST数据例子：{"group":{"id":108,"name":"test2_modify2"}}
                    //access_token	 调用接口凭证    id	 分组id，由微信分配  name	 分组名字（30个字符以内）
                   $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$access_token}";
                   $jsonstr = '{"openid":"'.$openid.'","to_groupid":'.$groupid.'}';

                    $result = https_request($url, $jsonstr);

                    //如果用户传来EventKey事件， 则是扫描二维码的
                    $content .= (!empty($object->EventKey))?"\n来自二维码场景 ".$scan:"";
                    break;
                    //取消关注时触发的事件

              case "SCAN":
            //如果用户已经关注公众号，则微信会将带场景值扫描事件推送给开发者。与上面的区别是一个扫之前未关注，一个已关注
                /*MsgTyp 消息类型，event  Event	 事件类型，SCAN EventKey	 事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id Ticket	 二维码的ticket，可用来换取二维码图片
                <MsgType><![CDATA[event]]></MsgType> <Event><![CDATA[SCAN]]></Event> <EventKey><![CDATA[SCENE_VALUE]]></EventKey> <Ticket><![CDATA[TICKET]]></Ticket>*/
                $content = "扫描场景".$object->EventKey;
                break;
          case "unsubscribe":
                $content = "你已取消关注";
                break;
          case "LOCATION":
                $content = "事件分支的地理参数分享\r\n" . "上传位置：纬度 " . $object->Latitude .";\r\n经度".$object->Longitude.";\r\n精度".$object->Precision;
                break;
          case "VIEW":
            //点击菜单跳转链接时的事件推送
                $content = "跳转链接".$object->EventKey;
                //EventKey	 事件KEY值，设置的跳转URL;EventKey><![CDATA[www.qq.com]]></EventKey>
                break;
          case "CLICK":
          //点击菜单拉取消息时的事件推送;EventKey	 事件KEY值，与自定义菜单接口中KEY值对应.
                //<Event><![CDATA[CLICK]]></Event> <EventKey><![CDATA[EVENTKEY]]></EventKey>
              //"V1001_TODAY_MUSIC"
              $this->logger("T \r\n 已经进入CLICK事件:".$object->EventKey);
              switch($object->EventKey){
                  case "V1001_TODAY_MUSIC":
                      $content=array();//如果是图文，则创建一个数组
                      $content[]=array("Title"=>"GWOLF单图文","Description"=>"这是曹涛用来调测兄弟连第三天下的单图文测试","PicUrl"=>"https://mmbiz.qlogo.cn/mmbiz/YxzRcqT139c8H9Y2J6KVAeWEiatXdYwd2gehK3ZLzxXL0vOheMEic5FSPkGhGEmlzL9zrgUvEgbrR8bXFEVyyp3A/0","Url"=>"http://mp.weixin.qq.com/s?__biz=MzA4NjcyMTQwNA==&mid=203905516&idx=2&sn=2a380a22897fedccad8596294661a20c&3rd=MzA3MDU4NTYzMw==&scene=6#rd");
                   //   $content="进入V1001_TODAY_MUSIC菜单的值\r\n".$object->EventKey;
                      break;
                  default:
                      $content = "20150326点击菜单：".$object->EventKey;
                      break;
              }

             //  $content = "点击菜单：" . $object->EventKey;
             // $this->logger("T \r\n 已经进入CLICK事件:".$content);
                break;
          case "MASSSENDJOBFINISH":
              /*Event	 事件信息，此处为MASSSENDJOBFINISH   MsgID	 群发的消息ID
Status	 群发的结构，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：
err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
TotalCount	 group_id下粉丝数；或者openid_list中的粉丝数   FilterCount	 过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
SentCount	 发送成功的粉丝数   ErrorCount	 发送失败的粉丝数
 * <Event><![CDATA[MASSSENDJOBFINISH]]></Event><MsgID>1988</MsgID><Status><![CDATA[sendsuccess]]></Status><TotalCount>100</TotalCount><FilterCount>80</FilterCount><SentCount>75</SentCount><ErrorCount>5</ErrorCount>* */
              $content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
              break;

          default:
              $content = "receive a new event: ".$object->Event;
              $this->logger("T \r\n 总进入default:".$content);
              break;
        }

             //下面最终判断$content是否是数组，如果是则意味着要回复图文、音频、视频等其他消息；否则直接回复文本；统一在下面调用回复接口
            if(is_array($content))
        {
           // $result = $this->transmitText($object, $content);
            if(isset($content[0])){
                $result = $this->transmitNews($object, $content);//如果存在第一个数组
                                  }else if (isset($content['MusicUrl'])){//如果回复的是音乐
                $result = $this->transmitMusic($object, $content);
            }

        }else {
            $result = $this->transmitText($object, $content);
        }
        return $result;

    }

    /*接受文本信息函数,并回复消息。内部调用回复处理函数。$object为形参，由前面的receiveText调用函数来把生成的$postObj
    对象传递过来*/
    private function receiveText($object){
        //从接收到的消息中获取用户输入的文本内容， 作为一个查询的关键字， 使用trim()函数去两边的空格
        $keyword=trim($object->Content);
       /* strstr() 函数搜索一个字符串在另一个字符串中的第一次出现。该函数返回字符串的其余部分（从匹配点）。如果未找到所搜索的字符串，则返回 false。
        strstr(string,search)  string	必需。规定被搜索的字符串。 search	必需。
       规定所搜索的字符串。如果该参数是数字，则搜索匹配数字 ASCII 值的字符。*/
        //自动回复模式,strstr()查询是否包含文本

        if (strstr($keyword,"文本")){
            $content = "这是曹涛程序回复的文本消息";}
        else if(strstr($keyword,"单图文")){
            $content=array();//如果是图文，则创建一个数组
            //给数组赋值
            //360*200的图片格式
            //$content[]=array("Title"=>"GWOLF单图文","Description"=>"这是曹涛用来调测兄弟连第三天下的单图文测试","PicUrl"=>"https://mmbiz.qlogo.cn/mmbiz/YxzRcqT139c8H9Y2J6KVAeWEiatXdYwd2gehK3ZLzxXL0vOheMEic5FSPkGhGEmlzL9zrgUvEgbrR8bXFEVyyp3A/0","Url"=>"http://www.ifeng.com");
            //200*200的图片格式
            $content[]=array("Title"=>"GWOLF单图文","Description"=>"这是曹涛用来调测兄弟连第三天下的单图文测试","PicUrl"=>"https://mmbiz.qlogo.cn/mmbiz/YxzRcqT139c8H9Y2J6KVAeWEiatXdYwd2gehK3ZLzxXL0vOheMEic5FSPkGhGEmlzL9zrgUvEgbrR8bXFEVyyp3A/0","Url"=>"http://mp.weixin.qq.com/s?__biz=MzA4NjcyMTQwNA==&mid=203905516&idx=2&sn=2a380a22897fedccad8596294661a20c&3rd=MzA3MDU4NTYzMw==&scene=6#rd");

        }else if(strstr($keyword,"多图") || strstr($keyword,"多文") ){
                $content=array();
                $content[]=array("Title"=>"四轴飞行diy全套入门教程（从最基础的开始）","Description"=>"2015-01-24电子工程专辑","PicUrl"=>"https://mmbiz.qlogo.cn/mmbiz/YxzRcqT139c8H9Y2J6KVAeWEiatXdYwd2N1Py8wY2qGgpwhk0gLicUmpribiaQR9ZBLqsMHHGS06EGPibnkrJCLAMpw/0","Url"=>"http://mp.weixin.qq.com/s?__biz=MjM5MTIwMjY1Mg==&mid=203579714&idx=5&sn=3e0c88437cf45bd108ab5270f3c8c6d5&3rd=MzA3MDU4NTYzMw==&scene=6#rd");
                $content[]=array("Title"=>"四轴飞行器DIY入门 篇三：FPV设备介绍与实战","Description"=>"2015-02-12北京创客空间","PicUrl"=>"https://mmbiz.qlogo.cn/mmbiz/YxzRcqT139c8H9Y2J6KVAeWEiatXdYwd23XRpGohcQRg1S4NxbLAmviaxzcDGHwK1ManmvuqIh4jutau3iaVcLcUw/0","Url"=>"http://mp.weixin.qq.com/s?__biz=MjM5MzQwMjMwMQ==&mid=202816092&idx=2&sn=164eceebb81e087af95107bd5d8fcbd0&3rd=MzA3MDU4NTYzMw==&scene=6#rd");
                $content[]=array("Title"=>"【创客工坊】DIY 四轴飞行器如何入门？","Description"=>"2015-02-09电子匠人创客星球","PicUrl"=>"https://mmbiz.qlogo.cn/mmbiz/YxzRcqT139c8H9Y2J6KVAeWEiatXdYwd2gehK3ZLzxXL0vOheMEic5FSPkGhGEmlzL9zrgUvEgbrR8bXFEVyyp3A/0","Url"=>"http://mp.weixin.qq.com/s?__biz=MzA4NjcyMTQwNA==&mid=203905516&idx=2&sn=2a380a22897fedccad8596294661a20c&3rd=MzA3MDU4NTYzMw==&scene=6#rd");
        }else if(strstr($keyword,"音乐")){
            $content=array();
          //  $content=array("Title"=>"曹涛音乐","Description"=>"这是曹涛用来测试音乐，低品质格式是怕你为自己流泪,高品质的是爱的人","MusicUrl"=>"http://202.101.231.120/wxkf/music/怕你为自己流泪.mp3","HQMusicUrl"=>"http://202.101.231.120/wxkf/music/爱的人.mp3");
            $content = array("Title"=>"曹涛音乐", "Description"=>"歌手：不是高洛峰", "MusicUrl"=>"http://wx.buqiu.com/app/hlw.mp3", "HQMusicUrl"=>"http://wx.buqiu.com/app/hlw.mp3");

            $this->logger("音乐一进入");
        }
        else if(strstr($keyword,"曹乐")){
            $content=array();
             $content=array("Title"=>"曹涛音乐","Description"=>"这是曹涛用来测试音乐，低品质格式是怕你为自己流泪,高品质的是爱的人","MusicUrl"=>"http://202.101.231.120/wxkf/music/怕你为自己流泪.mp3","HQMusicUrl"=>"http://202.101.231.120/wxkf/music/爱的人.mp3");
            //$content = array("Title"=>"曹涛音乐", "Description"=>"歌手：不是高洛峰", "MusicUrl"=>"http://wx.buqiu.com/app/hlw.mp3", "HQMusicUrl"=>"http://wx.buqiu.com/app/hlw.mp3");

         //   $this->logger("音乐一进入");
        }
        else{


            $content = date("Y-m-d H:i:s",time())."\n技术支持 曹涛";
        }
        //this调用前面要加$吗？

      if(is_array($content)){
           if(isset($content[0]["PicUrl"])){
               $result = $this->transmitNews($object,$content);
              } else if (isset($content["MusicUrl"]))
              {  // $this->logger("音乐二进入");
                  $result = $this->transmitMusic($object, $content);
                 // $this->logger("音乐三进入");}
           }else
          {   //这里是前面已经处理完该有的文本关键字后，最后处理收集用户消息
              include "ufun_g.inc.php";

              //调用一个方法， 将openid和你输入的文传入， 使用这个函数处理
              $user=getUserInfo($object->FromUserName);

              //用户一回话就将用户信息放入表user
              insertuser($user);
              //用户一回话就将用户回话放入message表中
              insertmessage($object->FromUserName, $keyword, 0, 'text');

              //给你回复的内容
              $result = $this->transmitText($object, $content);
          }


        return $result;

       }




    // 处理回复文本消息的函数,
    private function transmitText($object,$content)
    {
    //定义$xmlTpl回复文本消息模板，其中<MsgType>为文本消息表示，无需替换。调试过程中把%s 写成s%
       /* $xmlTpl="<xml>
        <ToUserName><![CDATA[%s]]></ToU serName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        </xml>";*/
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
       //替换$xmlTpl模板，其中接受用户及发送者正好相反。用time（）函数取当前时间。$content为所传递过来的文本。
        $result = sprintf($xmlTpl,$object->FromUserName,$object->ToUserName,time(),$content);
        $this->logger("FromUserName \r\n".$object->FromUserName);//调试这个是非有输出

        return $result;
    }

    //回复处理图文的消息函数,后面接受的为传进来的数组
    private function transmitNews ($object,$newsArray)
    {
        if (!is_array($newsArray)) {
            //如果传进来的非数组，则退出
            //   return null;
            return;
        }
// 此处的图文消息模板要分成两部分来处理，其中先定义并替换item部分，然后再依据条数，循环后组成标准的xml图文信息格式
        $itemTpl = "<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item> ";

        $item_str = "";
        /*foreach()有两种用法：1： foreach(array_name as $value){ statement; }
这里的array_name是你要遍历的数组名，每次循环中，array_name数组的当前元素的值被赋给$value,并且数组内部的下标向下移一步，也就是下次循环回得到下一个元素。
2：foreach(array_name as $key => $value){   statement;   }
这里跟第一种方法的区别就是多了个$key,也就是除了把当前元素的值赋给$value外，当前元素的键值也会在每次循环中被赋给变量$key。键值可以是下标值，也可以是字符串。
           比如book[0]=1中的“0”，book[id]="001"中的“id”.	      */
        foreach($newsArray as $item){
            $item_str .= sprintf($itemTpl,$item['Title'],$item['Description'],$item['PicUrl'],$item['Url']);//组装 item信息
        }

      /*  为了方便字符串内变量替换。 例如:$name = "hi"; echo "hello, my name is $name";
          如果没有$作为变量前缀，无法区分字符串name/和变量$name。 */
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";
   /*     $this->logger("item_str:".$item_str);
        $this->logger("xmlTpl:".$xmlTpl);*/

        $result = sprintf($xmlTpl,$object->FromUserName,$object->ToUserName,time(),count($newsArray));
        $this->logger("图文消息中返回的result:". $result);
        return $result;

    }

    //接受的视频,形参只有一个就是传过来的$postobj
    private function receiveVideo($object)
    {
        $this->logger("接受视频消息的MediaId：".$object->MediaId);
        $this->logger("接受视频消息的ThumbMediaId：".$object->ThumbMediaId);
        $this->logger("接受视频消息的ThumbMsgId：".$object->MsgId);

        $content=array();
      $content=array("MediaId"=>$object->MediaId,"Title"=>"GWOLF返回视频","Description"=>'这是曹涛返回的素材库的视频');
      //"MediaId"直接取传入的消息中的，因为暂时没有学会获得上次媒体文件，如果学会了则可以直接写入所上次的MediaId。
        $result=$this->transmitVideo($object,$content);//这里写$content,非$videoArray;
        return $result;
    }
    //处理回复的视频
    private function transmitVideo($object,$videoArray)
    {
        $itemTpl="<Video>
<MediaId><![CDATA[%s]]></MediaId>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
</Video> ";
        $item_str=sprintf($itemTpl,$videoArray["MediaId"],$videoArray["Title"],$videoArray["Description"]);
$xmlTpl="<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";
        $result=sprintf($xmlTpl,$object->FromUserName,$object->ToUserName,time());
        return $result;
    }


    //接受的链接,形参只有一个就是传过来的$postobj;微信回复类型里面无链接的类型
    private function receiveLink($object)
    {$content="曹涛告诉你发的链接标题是:".$object->Title.";\r\n内容是:".$object->Description.";\r\n链接网址是:".$object->Url;
        $result=$this->transmitText($object,$content);
        return $result;
    }


/*    接受的语音,形参只有一个就是传过来的$postobj;开通语音识别功能，且该功能设置处于开启状态的公众帐号，
用户每次发送语音给公众号时，微信会在推送的语音消息XML数据包中，增加一个Recongnition字段。
<xml>
    <ToUserName><![CDATA[toUser]]></ToUserName>
    <FromUserName><![CDATA[fromUser]]></FromUserName>
    <CreateTime>1357290913</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    <MediaId><![CDATA[media_id]]></MediaId>
    <Format><![CDATA[Format]]></Format>
    <Recognition><![CDATA[深圳天气怎么样]]></Recognition>
    <MsgId>1234567890123456</MsgId>
</xml>
参数说明：

参数    描述
ToUserName    开发者微信号
FromUserName     发送方帐号（一个OpenID）
CreateTime     消息创建时间 （整型）
MsgType     语音为voice
MediaID     语音消息媒体id，可以调用多媒体文件下载接口拉取该媒体
Format     语音格式：amr
Recognition     语音识别结果，UTF8编码
MsgID     消息id，64位整型
*/
    private function receiveVoice($object)
    {
        $this->logger("进入了语音接口\r\n");
      //  $object->Recognition="广州坐飞机去北京上海大约多长时间呢宜春";
        //包含函数库文件， 里面有分词函数 fci()
        $this->logger("人工添加的测试".$object->Recognition."\r\n");
        $this->logger("isset=".isset($object->Recognition)."\r\n");
        $this->logger("!empty=".!empty($object->Recognition)."\r\n");
       //包含函数库文件， 里面有分词函数 fci()
        require_once "fc_gb.php";
        if(isset($object->Recognition) && !empty($object->Recognition))
       {
          // $content = "语音开启成功";
           $this->logger("接收的语音:".$object->Recognition."\r\n");
           /*$result = $this->transmitText($object, $content);
            return $result;*/

           $text=$object->Recognition;//返回微信处理过后的语音
           $this->logger("text=:".$text."\r\n");
          //通过语言识别返回的文件放到fci函数分词后返回数组（多个词）
            $carr=fengci_g($text);
           foreach($carr as $carr1){
               $this->logger("carr1=:".$carr1."\r\n");
           }

           $citycodes = getcity($carr);//返回数据库中，相应中文城市的代码，返回格式为数组
           //数组传入获取天气的的函数，两个参数，一个数组一个数组中元素数目
           foreach($citycodes as $citycodes1){
               //如果getcity返回的是数组，则一个个显示出$citycodes的结果
               $this->logger("查询的城市代码为:".$citycodes1."\r\n");
           }

           if(empty($citycodes)){
               $this->logger("citycodes为空:"."\r\n");
               $content="没有找到你说的:(".$text.")中的天气信息";
               $result=$this->transmitText($object,$content);
               return $result;
           }
           //如果citycodes是一个数组,循环调用函数获取多个天气的图文
           if(is_array($citycodes)) {
               $this->logger("citycodes进入了数组判断:"."\r\n");
               $content=array();

               foreach($citycodes as $code)
               {
                   $this->logger("citycodes返回的code =".$code."\r\n");
                   //查看函数getWeatherInfo返回的数组内容,他妈的是多维数组。
                   $content_d= getWeatherInfo($code,count($citycodes));
                   foreach($content_d as $content_d1)
                   {    foreach($content_d1 as $content_d2)
                     {
                       $this->logger("\r\n" . "每个getWeatherInfo函数返回的content_d2=" . $content_d2 . "\r\n");
                     }
                   }

                   $content = array_merge($content, getWeatherInfo($code,count($citycodes)));

               }

             //  $this->logger("\r\n"."citycodes后面的content =".var_dump($content)."\r\n");
           }

           $result = $this->transmitNews($object, $content);
           $this->logger("\r\n"."citycodes后面的result =:".$result."\r\n");
       }else{
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitVoice($object, $content);
        }


     return $result;
    }

    //方倍工作室测试语音接口是否开启的代码
    private function receiveVoice_fb($object)
    {       $str=isset($object->Recognition);
            $str1=!empty($object->Recognition);

        if (isset($object->Recognition) && !empty($object->Recognition)){
            $contentStr = "你发送的是语音，内容为：".$object->Recognition;
        }else{
            $contentStr = "未开启语音识别功能或者识别内容为空\r\n"."isset(Recognition)值为".$str."\r\n!empty(Recognition)值为:".$str1."\r\nRecognition值为:".$object->Recognition;
        }
        if (is_array($contentStr)){
            $resultStr = $this->transmitNews($object, $contentStr);
        }else{
            $resultStr = $this->transmitText($object, $contentStr);
        }
        return $resultStr;
    }

    //处理回复的语音
    private function transmitVoice($object,$voiceArray)
    {
        $itemTpl = "<Voice>
        <MediaId><![CDATA[%s]]></MediaId>
         </Voice>";
        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $xmlTpl = "<xml>
       <ToUserName><![CDATA[%s]]></ToUserName>
      <FromUserName><![CDATA[%s]]></FromUserName>
     <CreateTime>%s</CreateTime>
     <MsgType><![CDATA[voice]]></MsgType>
     $item_str
     </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }


    //处理回复的音乐
    private function transmitMusic($object,$musicArray)
    {
    $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);
        $xmlTpl="<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";
    /*     $this->logger("回复音乐 musicArray[Title]:".$musicArray['Title']);
         $this->logger("回复音乐item_str:".$item_str);
         $this->logger("回复音乐xmlTpl:".$xmlTpl);*/
         $result=sprintf($xmlTpl,$object->FromUserName,$object->ToUserName,time());//sprintf前面写成了$sprintf,结果不显示。

         $this->logger("回复音乐result:".$result);
        return $result;

    }
    //

    //接受的位置,形参只有一个就是传过来的$postobj,无回复位置的消息类型
    private function receiveLocation($object)
    {   $content="你地理位置维度是".$object->Location_X.";\r\n你地理位置经度是".$object->Location_Y.";\r\n地图缩放大小是:".$object->Scale.";\r\n地理位置信息是:".$object->Label;
        $result=$this->transmitText($object,$content);
        return $result;
    }


    //接受的图片,形参只有一个就是传过来的$postobj
    private function receiveImage($object)
    {   $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;

    }
    //处理回复的图片,原图返回
    private function transmitImage($object,$imageArray)
    { $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;

    }


    //日志记录
    public  function logger($log_content)
    {
        ini_set('date.timezone','Asia/Shanghai');//设置时区,否则服务器显示时区不对
        $max_size = 100000;   //声明日志的最大尺寸
        $log_filename = "log.xml";  //日志名称
        //如果文件存在并且大于了规定的最大尺寸就删除了
        if(file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size))
        {
            unlink($log_filename);
        }
        //写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入

       if(defined("WX_HOST") && WX_HOST=='DX'){
    file_put_contents("log.xml",date("Y-m-d H:i:s")." ".$log_content."\r\n",FILE_APPEND);
   }else{
           $saelog=new SaeStorage();
           $saelog->write("log","log.xml",date("H:i:s")." ".$log_content."\r\n",FILE_APPEND);
       }

    }

    //追踪函数，分sae及本地，尤其用在开始的接口调试。方倍工作室p23;
    function traceHttp(){
        ini_set('date.timezone','Asia/Shanghai');//设置时区,否则服务器显示时区不对
        $content=date('Y-m-d H:i:s')."\r\nREMOTE_ADDR:".$_SERVER["REMOTE_ADDR"]."\r\nQUERY_STRING:".$_SERVER["QUERY_STRING"]."\r\n";//\r\n window下换行，\n\n linux下
        if(isset($_SERVER['HTTP_APPNAME'])){//SAE
            sae_set_display_errors(false);
            sae_debug(trim($content));
            sae_set_display_errors(true);
        }else{
            $max_size=100000;
            $log_filename="tracelog.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename))>$max_size)){
                unlink($log_filename);    }
            file_put_contents($log_filename,$content,FILE_APPEND);
        }
    }
}



?>

