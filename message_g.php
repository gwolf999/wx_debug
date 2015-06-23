<?php
	header("Content-Type:text/html;charset=utf-8");
	
	
	include "conn.inc.php";
	include "ufun_g.inc.php";
	
	//通过openid获取用户的消息
	$openid = $_GET['openid'];
	//如果用户提交了
	if(isset($_POST['dosubmit'])) {
		sendText($openid, $_POST['text']);
	
		//用户法写入表， 1表示是公众号
		insertmessage($openid, $_POST['text'], 1, "text");
		
	}

//ALTER TABLE  `user` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin//修改user表的字符集
	//将查看过的状态字段message（user）改成0，说明这个消息我看了
     //设置传的字符集为utf8，否则传出来乱码
    mysql_query("set names utf8");
	$sql = "update user set  message='0' where openid='{$_GET['openid']}'";
	mysql_query($sql);

	$user = getUserInfo($openid);
	//查询所有这个用户和公众号对话的消息
	$sql = "select * from message where openid='{$openid}'";
	
	$result = mysql_query($sql);

	echo "<table border='1' width='600'>";
	
	while($mess = mysql_fetch_assoc($result)) {
		echo '<tr>';
		if($mess['who']==0) {
			echo '<td align="left"><img width="60" src="'.$user['headimgurl'].'"> '.$user['nickname'].'<br>'.$mess['message'].'</td>';
		}else{
			echo '<td align="right">'.$mess['message'].' :[公众号]</td>';
		}
		echo '</tr>';
	}
	
	echo '</table>';
	
?>
<form action="message.php?openid=<?php echo $openid ?>" method="post">
	<textarea name="text" rows="6" cols="40"></textarea><br>
	<input type="submit" name="dosubmit" value="回复"><br>

</form>
<br>

<a href="userinfo_g.php">返回用户列表</a>
	
	
	
	

	