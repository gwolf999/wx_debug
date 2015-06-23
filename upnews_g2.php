<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/4/17
 * Time: 10:21
 */
header("Content-Type:text/html;charset=utf-8");
define('WX_HOST','DX');
require_once 'fileupload.class.php';
//包含连接数据库的代码
require_once "conn.inc.php";
//函数库
require_once "ufun_g.inc.php";
$access_token=get_token();
//单引号内的双引号相当于普通字符了。双引号内的变量会计算

//echo '$article='.$article.'<br>';
//$num=5;
echo '<form action="upnews_g2.php" method="post">';
for($i=1;$i<=$num;$i++) {
    $formstr = <<<form
    <p>第{$i}个图文消息</p>
    缩略图({$i}):media_id<input type="text" name="thumb_media_id_{$i}"><br>
    作者({$i}):<input type="text" name="author_{$i}"><br>
    标题({$i}):<input type="text" name="title_{$i}"><br>
    阅读原文({$i}):<input type="text" name="content_source_url_{$i}"><br>
    图文内容({$i}):<textarea rows="5" cols="50" name=""content_{$i}"></textarea><br>
    消息描述({$i}):<input type="text" name="digest_{$i}"><br>
form;
    if ($i == 1) {
        $radiostr = <<<radio
  封面({$i}):<input checked type="radio" name="show_cover_pic_{$i}" value="1">是<input type="radio" name="show_cover_pic_{$i}" value="0">否 <br>
radio;
    } else {
        $radiostr = <<<radio
  封面({$i}):<input type="radio" name="show_cover_pic_{$i}" value="1">是<input checked type="radio" name="show_cover_pic_{$i}" value="0">否 <br>
radio;
    }

    echo $formstr.$radiostr.'<br>';
}



?>

<input type="hidden" name="num" value="<?php echo $num; ?>">
<input type="submit" name="dosubmit" value="提交">


  </form>
