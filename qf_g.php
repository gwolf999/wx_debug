<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2015/4/19
 * Time: 20:54
 */
header("Content-Type:text/html;charset=utf-8");
require_once "ufun_g.inc.php";
define('WX_HOST','DX');

?>
    <h2><a href="qf_g.php?type=group">按群组来发</a>||<a href="qf_g.php?type=openid">按指定用户来发</a> </h2>
    <p>
    <h3>
        <a href="qf_g.php?type=<?php echo $type; ?>&message=news">图文</a> ||
        <a href="qf_g.php?type=<?php echo $type; ?>&message=text">文本</a> ||
        <a href="qf_g.php?type=<?php echo $type; ?>&message=voice">语音</a> ||
        <a href="qf_g.php?type=<?php echo $type; ?>&message=image">图片</a> ||
        <a href="qf_g.php?type=<?php echo $type; ?>&message=video">视频</a> ||
    </h3>
    </p>


    <input type="radio" name="$g['id']">

<?php
echo '<form action="qfaction.php" method="post">';
//type为空或者是group都默认$TYPE=GROUP
if(empty($_GET['type'])||$_GET['type']=='group'){
    $type='group';
    //获取access_token
    $access_token = get_token();
    ////查询所有分组  接口调用请求说明
    $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
    /*    //分组群发接口
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$access_token}";*/
    $result=https_request($url);
    //将返回来的json转成数组操作
    $groups=json_decode($result,true);
    /*{
     "groups": [
         {
             "id": 0,
             "name": "未分组",
             "count": 72596
         },
                 {
             "id": 106,
             "name": "★不测试组★",
             "count": 1
         }
     ]
 }*/

    echo '<ul>';
    foreach($groups['groups'] as $g){
        echo '<li><input type="radio" name="group" value="'.$g['id'].'">'.$g['name'].'('.$g['count'].')</li>';
    }

    echo '</ul>';
}
else{
    $type="openid";
    require_once 'conn.inc.php';//需要调用已关注用户的本地数据库
    echo '<table border="1" width="60%">';
    //要全部关注的subscribe=1的
  //  $sql ="select * from wuser where subscribe='1'";
    $sql="select * from wuser";
    $result = mysql_query($sql) or die("run error $sql=".$sql);
    while($user=mysql_fetch_assoc($result)){
        echo '<tr>';
        echo '<td><input type="checkbox" name="openid[]" value="'.$user['openid'].'"></td>';
        echo '<td><img width="60" src="'.$user['headimgurl'].'"></td>';
        echo '<td>'.$user['nickname'].'</td>';
        switch($user['sex']){
            case 1:
                echo "男";
				break;
            case 2:
                echo "女";
                break;
            case 0:
                echo "末知";
                break;
        }

        echo '</td>';
        echo '<td>'.$user['country'].'</td>';
        echo '<td>'.$user['province'].'</td>';
        echo '<td>'.$user['city'].'</td>';
        echo '<td>'.$user['subscribe_time'].'</td>';
        echo '</tr>';
    }
    echo '</table>';
}
switch($_GET['message']) {
    case 'text':
        echo '请输入要群发的文本:<br><textarea name="content" rows="4" cols="40"></textarea>';
        break;
    case 'voice':
        echo '请输入要群发的语音media_id:<br><textarea name="content" rows="4" cols="40"></textarea>';
        break;
    case 'image':
        echo '请输入要群发的图片media_id:<br><textarea name="content" rows="4" cols="40"></textarea>';
        break;
    case 'video':
        echo '请输入要群发的视频media_id:<br><textarea name="content" rows="4" cols="40"></textarea>';
        break;
    default:
        echo '请输入要群发的图文media_id:<br><textarea name="content" rows="4" cols="40"></textarea>';
        break;
}
echo '<input type="hidden" name="dtype" value="'.$type.'">';
echo '<input type="hidden" name="type" value="'.$_GET['message'].'">';

echo '<br><input type="submit" name="dosubmit" value="群发">';
echo '</form>';