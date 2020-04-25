<?php
$con = mysqli_connect("localhost","root","","dbName");

if (mysqli_connect_errno())
  {
  die "Error connecting to MySQL: " . mysqli_connect_error();
  }


$fb_page = '******'; //your page name
$access_token = '******************************************************'; //your access token

function Curl_Req($url){
    $curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($curl);
	curl_close($curl);
	$details = json_decode($result,true);
	return $details['data'];
}

function sendPrivateRep($url, $message) {

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$json = '{
            message: "'.$message.'"
          }';
	curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
	curl_exec($curl);
	curl_close($curl);

}

function randCouponGen($con) {

$result = mysqli_query($con,"");

$arr_a = range('A', 'Z');
$arr_b = range('A', 'Z');
$arr_c = range('A', 'Z');

}



$posts = Curl_Req('https://graph.facebook.com/v2.11/'.$fb_page.'/posts?access_token='.$access_token);

foreach ($posts as $post)
{

$post_value = mysqli_real_escape_string($con, $post['id']);
$post_name = mysqli_real_escape_string($con, $post['name']);
//check DB for Post_ID
$if_post_exist = mysqli_query($con,"SELECT * FROM `dbName` WHERE `Post_ID`='$post_value'");
if(mysql_num_rows($if_post_exist) > 0) {//post exist
//get latest comments from MySQL
$comments = Curl_Req('https://graph.facebook.com/v2.11/'.$post['id'].'/comments?access_token='.$access_token);
$comments = array_filter($comments);
if (!empty($comments)){
//get old comments from MySQL

$old_comment_list = mysqli_query($con,"SELECT `Comment_List` FROM `dbName`");
if(mysql_num_rows($old_comment_list) > 0) {
$old_comment_list = explode(',', $old_comment_list);
$flag = true;
}
	
	foreach ($comments as $comment) {
	$comment_value = mysqli_real_escape_string($con, $comment['id']);
	$comment_value .= ',';
		if ($flag) {
			if (!in_array($comment, $old_comment_list)) {
			//send private_reply 
				sendPrivateRep('https://graph.facebook.com/v2.11/'.$comment['id'].'/private_replies?access_token='.$access_token, 'hello');
			//Update comment_list
		mysqli_query($con, "UPDATE `dbName` SET `Comment_List` = IFNULL(CONCAT(`Comment_List`, '$comment_value'), '$comment_value') WHERE `Post_ID` = '$post_value'");

		}
		}
		else {

			//send private_reply and Update comment_list
			sendPrivateRep('https://graph.facebook.com/v2.11/'.$comment['id'].'/private_replies?access_token='.$access_token, 'hello');
			mysqli_query($con, "UPDATE `dbName` SET `Comment_List` = '$comment_value' WHERE `Post_ID` = '$post_value'");
		}

	}

}

}
else {//post does not exist
mysqli_query($con,"INSERT INTO `dbName` (`Post_ID`,`Post_Name`) VALUES ('$post_value','$post_name')");
//loop through all the comments recieved through API

$comments = Curl_Req('https://graph.facebook.com/v2.11/'.$post['id'].'/comments?access_token='.$access_token);
$comments = array_filter($comments);
if (!empty($comments)){
	foreach ($comments as $comment) {
		$comment_value = mysqli_real_escape_string($con, $comment['id']);
		$comment_value .= ',';
		sendPrivateRep('https://graph.facebook.com/v2.11/'.$comment['id'].'/private_replies?access_token='.$access_token, 'hello');
		mysqli_query($con, "UPDATE `dbName` SET `Comment_List` = '$comment_value' WHERE `Post_ID` = '$post_value'");

	}

}


}

}

mysqli_close($con);
?>
	