<?php
include("/etc/upload.conf");
if( empty($_GET['log']) || $_GET['log'] == 0 ){
	session_start();
	$_SESSION['mail']=$_GET['fm'];
	header("Location: ".$conf['url_redirection_upload']."after_auth.php?fm=".$_GET['fm']."&iv=".@$_GET['iv']."&id=".@$_GET['id']."&key=".@$_GET['key']);
	exit();
}
?>