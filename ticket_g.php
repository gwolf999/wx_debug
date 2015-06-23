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
 * Time: 17:46
 * 获取ticket，生成二维码
 */
require_once "ufun_g.inc.php";
require_once "curl.php";
header("Content-Type:text/html;charset=utf-8");
/*
 * 生成带参数的二维码
为了满足用户渠道推广分析的需要，公众平台提供了生成带参数二维码的接口。使用该接口可以获得多个带不同场景值的二维码，用户扫描后，公众号可以接收到事件推送。
目前有2种类型的二维码，分别是临时二维码和永久二维码，前者有过期时间，最大为1800秒，但能够生成较多数量，后者无过期时间，数量较少（目前参数只支持1--100000）。两种二维码分别适用于帐号绑定、用户来源统计等场景。
用户扫描带场景值二维码时，可能推送以下两种事件：
如果用户还未关注公众号，则用户可以关注公众号，关注后微信会将带场景值关注事件推送给开发者。
如果用户已经关注公众号，在用户扫描后会自动进入会话，微信也会将带场景值扫描事件推送给开发者。
获取带参数的二维码的过程包括两步，首先创建二维码ticket，然后凭借ticket到指定URL换取二维码。
创建二维码ticket
每次创建二维码ticket需要提供一个开发者自行设定的参数（scene_id），分别介绍临时二维码和永久二维码的创建二维码ticket过程。
临时二维码请求说明
http请求方式: POST
URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN
POST数据格式：json
POST数据例子：{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
永久二维码请求说明
http请求方式: POST
URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN
POST数据格式：json
POST数据例子：{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
或者也可以使用以下POST数据创建字符串形式的二维码参数：
{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}}
参数	说明
expire_seconds	 该二维码有效时间，以秒为单位。 最大不超过1800。
action_name	 二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
action_info	 二维码详细信息
scene_id	 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
scene_str	 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64，仅永久二维码支持此字段
正确的Json返回结果:
{"ticket":"gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==","expire_seconds":60,"url":"http:\/\/weixin.qq.com\/q\/kZgfwMTm72WWPkovabbI"}
参数	说明   ticket	 获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
expire_seconds	 二维码的有效时间，以秒为单位。最大不超过1800。
url	 二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
错误的Json返回示例:
{"errcode":40013,"errmsg":"invalid appid"}
全局返回码说明
通过ticket换取二维码
获取二维码ticket后，开发者可用ticket换取二维码图片。请注意，本接口无须登录态即可调用。
请求说明
HTTP GET请求（请使用https协议）  https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=TICKET
提醒：TICKET记得进行UrlEncode
返回说明  ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。

HTTP头（示例）如下：
Accept-Ranges:bytes
Cache-control:max-age=604800
Connection:keep-alive
Content-Length:28026
Content-Type:image/jpg
Date:Wed, 16 Oct 2013 06:37:10 GMT
Expires:Wed, 23 Oct 2013 14:37:10 +0800
Server:nginx/1.4.1
错误情况下（如ticket非法）返回HTTP错误码404。*/
$access_token = get_token();
$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
$jsonstr = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 101}}}';//设置永久二维码获取字符串

$result = https_request($url, $jsonstr);
$arr = json_decode($result, true);
$ticket = $arr['ticket'];//获得ticke值，再去换二维码图片
//换二维码图片的网址，但是urlencode编码？
$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
$imgInfo=downImage($url);
$filename = "wxcode.jpg";//二维码存盘文件名
file_put_contents($filename,$imgInfo);//把调用函数downImage所获得的二维码存入文件名。

function downImage($url){
    $curl = curl_init($url); // 这是你想用PHP取回的URL地址。你也可以在用curl_init()函数初始化时设置这个选项。
    curl_setopt($curl, CURLOPT_HEADER, 0);//如果你想把一个头包含在输出中，设置这个选项为一个非零值。
    curl_setopt($curl, CURLOPT_NOBODY, 0);//如果你不想在输出中包含body部分，设置这个选项为一个非零值。

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//

    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//设定是否显示头信息
    $output=curl_exec($curl);
    curl_close($curl);
    return $output;
}

?>
</body>
</html>