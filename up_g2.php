<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/4/16
 * Time: 9:31
 */
header("Content/Type:text/html;charset=utf-8");
define("WX_HOST",'DX');
require_once 'fileupload_g.class.php';
//包含连接数据库的代码
include "conn.inc.php";
//函数库
include "ufun_g.inc.php";
if(isset($_POST['dosubmit'])){//如果有递交存在
    //创建上传类的对象,也就是实例化类
    $up= new FileUpload();
    //设置上传的类型
    /*FileUpload()类本身定义的只是初始值，下面的赋值是每次调用可以修改的*/
     $up->set('allowtype',array('jpg','jpeg', 'mp3', 'mp4','amr'));
    //开始上传
    if($up->upload('res')){
        //获取上传后的名子
        $filename= $up->getFileName();//上传成功后得到新的文件名
        //获取access_token
        $access_token = get_token();
        /*原“上传多媒体文件”接口变为新增临时素材        http请求方式: POST/FORM,需使用https
           https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE
           调用示例（使用curl命令，用FORM表单方式上传一个多媒体文件）：
           curl -F media=@test.jpg "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE"*/
        /*参数说明  type	 是	 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
                   media	 是	 form-data中媒体文件标识，有filename、filelength、content-type等信息
         返回说明  正确情况下的返回JSON数据包结果如下：
{"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}  media_id	 媒体文件上传后，获取时的唯一标识 created_at	 媒体文件上传时间戳
错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：{"errcode":40004,"errmsg":"invalid media type"}*/
        //钟志勇2版P99;既可以用<form>表单格式上传文件，也可以用本案例curl方式上传文件
         $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$_POST['rtype']}";
        /*新增其他类型永久素材  接口调用请求说明
通过POST表单来调用接口，表单id为media，包含需要上传的素材内容，有filename、filelength、content-type等信息。请注意：图片素材将进入公众平台官网素材管理模块中的默认分组。
http请求方式: POST http://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN
调用示例（使用curl命令，用FORM表单方式新增一个其他类型的永久素材）： */
       // 新增永久图文素材
       //    $url =" https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=ACCESS_TOKEN";
        /*新增其他类型永久素材
       $url ="https://file.api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN";//这是微信官方文档，其实是错误的
             $url ="https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN";*/ //网友提供的，ok
  /*      上传公众号一定要使用绝对路径;php中定义了一个很有用的常数，即__file__这个内定常数是当前php程序的就是完整路径（路径+文件名）。
即使这个文件被其他文件引用(include或require)，__file__始终是它所在文件的完整路径，而不是引用它的那个文件完整路径  __FILE__
     返回当前 路径+文件名  dirname(__FILE__) 返回当前文件路径的 路径部分   dirname(dirname(__FILE__));得到的是文件上一层目录名（不含最后一个“/”号）*/
        $filepath=dirname(__FILE__)."/uploads/".$filename;
        //形成上传的数据,
        $result=https_request($url,$filepath);//只传地址则为get方式，带$data数据则为POST方式 $data应该是依据接口要求可以是json，也可是数组，甚至字符串?
        //将返回信息变成数组
        $data=json_decode($result,true);
        //如上传的是缩略图，其返回的json格式虽然也是3个，但media_id 变为 thumb_media_id 需要处理一下(闫小坤 P93)
        if($_POST['rtype']=="thumb") {
            $data['media_id']=$data['thumb_media_id'];//这个需要测试？这样赋值是否可以
             }
        $sql = "insert into media(filename, rtype, media_id, created_at) values('{$filename}','{$data['type']}','{$data['media_id']}','{$data['created_at']}')";
        mysql_query($sql) or die("error sql=".$sql);
    }else{
        echo $up->getErrorMsg();
    }

}

//根据用户选择设置从数据库查询条件，如果没有选择则返回全部信息
if(!empty($_GET['type'])) {
    $type="where rtype='{$_GET['type']}'";
} else {
    $type="";
}
//设置SQL语句，并执行
$sql = "select * from media {$type} order by created_at desc";
$result=mysql_query($sql);

?>
<h1>媒体列表</h1>
<p><a href="up.php?type=image">图片</a> |<a href="up.php?type=voice">语音</a>|<a href="up.php?type=vedio">视频</a>|<a href="up.php?type=thumb">缩略图</a>|<a href="up.php?type=news">图文</a> </p>
<table bgcolor="#faebd7" border="1" width="80%" align="left" >
    <th>Id</th><th>文件名</th><th>类型</th><th>media_id</th><th>上传时间</th>
<!--    <tr><td>测试</td><td>测试2</td><td>测试3</td><td>测试4</td><td>测试5</td></tr>-->
</table>
<br>
<form action="up.php?type=<?php echo @$_GET['type'];?>" method="post" enctype="multipart/form-data">
    <br>请选择文件:
    <input name="res" id="res" type="file">
    <select name="rtype">
        <option value="image">图片</option>
        <option value="voice">语音</option>
        <option value="vedio">视频</option>
        <option value="thumb">缩略图</option>
    </select>
    <input type="submit" name="dosubmit" value="上传">
</form>
<!--blank -- 在新窗口中打开链接
_parent -- 在父窗体中打开链接
_self -- 在当前窗体打开链接,此为默认值
_top -- 在当前窗体打开链接，并替换当前的整个窗体(框架页)
一个对应的框架页的名称 -- 在对应框架页中打开-->
<a target="_blank" href="upnews.php?num=2">上传图文消息</a>