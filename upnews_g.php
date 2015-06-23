<?php
	header("Content-Type:text/html;charset=utf-8");
	include "conn.inc.php";
	include "func.inc.php";
	
	$access_token = get_token();


	
	if(isset($_POST['dosubmit'])) {
		$article ="";

		for($i=0; $i<$_POST['num']; $i++) {
			if(!empty($_POST["thumb_media_id_{$i}"])) {

			$article.='	
				 {
                        "thumb_media_id":"'.$_POST["thumb_media_id_{$i}"].'",
                        "author":"'.$_POST["author_{$i}"].'",
			 "title":"'.$_POST["title_{$i}"].'",
			 "content_source_url":"'.$_POST["content_source_url_{$i}"].'",
			 "content":"'.$_POST["content_{$i}"].'",
			 "digest":"'.$_POST["digest_{$i}"].'",
                        "show_cover_pic":"'.$_POST["show_cover_pic_{$i}"].'"
			},';//单引号内的双引号相当于普通字符了。双引号内的变量会计算

              echo '$article='.$article.'<br>';
			}
		
		}
		
		$jsonstr = '
{
   "articles": [
	'.rtrim($article, ",").'
   ]
}';




	//echo $jsonstr;

	$url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token={$access_token}";

	$result=https_request($url, $jsonstr);

	$arr = json_decode($result, true);

	//var_dump($result);


			$sql = "insert into media(filename, rtype, media_id, created_at) values('no','news','{$arr['media_id']}','{$arr['created_at']}')";

			mysql_query($sql);

			header("Location:up.php?type=news");
			
	
	}




	$num=isset($_GET['num']) ? $_GET['num'] : 1;

	if($num > 10)
	{
		$num=10;
	}

	echo '<form action="" method="post">';
	for($i=0; $i<$num; $i++) {

$form=<<<form
	
	缩略图({$i})：media_id<input type="text" name="thumb_media_id_{$i}"> <br>
	作者({$i})：<input type="text" name="author_{$i}"> <br>
	标题({$i})：<input type="text" name="title_{$i}"> <br>
	阅读原文({$i})：<input type="text" name="content_source_url_{$i}"> <br>
	图文内容({$i})：<textarea rows="5" cols="50" name="content_{$i}"></textarea> <br>
	消息描述({$i})：<input type="text" name="digest_{$i}"> <br>
	封面({$i})：<input checked type="radio" name="show_cover_pic_{$i}" value="1">是 <input type="radio" name="show_cover_pic_{$i}" value="0">否 <br>
form;


	echo '<p>'.$form.'</p>';
	}

?>
	
	<input type="hidden" name="num" value="<?php echo $num; ?>">
	<input type="submit" name="dosubmit" value="提交" >
</form>
