<?php
include("/etc/upload.conf");
if( $conf['download_auth'] == 0 ){
	header("Location: ".$conf['url_redirection_download']."after_auth.php?&folder=".$_GET['folder']."&psw=".trim($_GET['psw']));
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<?php
require($conf['absolute_path_upload']."include_languages.php");
header('x-ua-compatible: ie=edge');
/* Définition de la langue */
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	$lang_key= array_keys($vocables);
	$lang_existe = false;
	foreach($lang_key as $var => $valeur) {
		if($lang != $valeur && $lang_existe != true){
			$lang_existe = false;
		}
		else {
			$lang_existe = true;
		}
	}
	if($lang_existe == true){
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
	else{
		$lang="en";
	}
	$key = $_GET['key'];
	$iv= $_GET['iv'];
	$id=$_GET['id'];
	$mail=htmlspecialchars(trim(@$_GET['mail']));
?>
	<!-- Force latest IE rendering engine or ChromeFrame if installed -->
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	<meta http-equiv="Pragma" content="no-cache">
	<title><?php echo $conf['title_download']; ?> - Authentification</title>
	<meta name="description" content="File Upload widget with multiple file selection, drag&amp;drop support, progress bars, validation and preview images, audio and video for jQuery. Supports cross-domain, chunked and resumable file uploads and client-side image resizing. Works with any server-side platform (PHP, Python, Ruby on Rails, Java, Node.js, Go etc.) that supports standard HTML form file uploads.">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/blueimp-gallery.min.css">
	<link rel="stylesheet" href="css/jquery.fileupload.css">
	<link rel="stylesheet" href="css/jquery.fileupload-ui.css">
		<script src="js/jquery.min.js"></script>
	<style>
		#popin{display:none;width:200px;height:100px;margin:auto;position:absolute;top:0;left:0;bottom:0;right:0;z-index:10000;}
		#modal{display:none;top:0;left:0;bottom:0;right:0;position:fixed;width:100%;height:100%;z-index:9999;background-color:rgba(0, 0, 0, 0.85);}
		#header{display:block;margin-top:10px;margin-bottom:10px;height:100px;}
		.left{float:left;display:inline-block;width:50%;margin-bottom:30px;}
		.right{float:right;display:inline-block;width:50%;margin-bottom:30px;}
	</style>
</head>
<body>
	<center>
		<div id="header" style="width:100%;text-align: center;">
			<img alt="Elonet" width="220" src="<?php echo $conf['img_logo']; ?>" border=0/><br/>
			<font size=4 face="Verdana" style="margin-left:15px;"><?php echo $conf['title_download']; ?></font>
		</div>
		<div class="container-fluid" style="margin-top:40px;margin-left:30px;">

			<span id="mail_checker" >
				<p><?php echo $vocables["$lang"]["attachment_index_1"]; ?></p>
				<span>
					<input type='text' id='mail' name='mail' style='width:50%;' placeholder='<?php echo $vocables["$lang"]["attachment_index_value_1"]; ?>'/>
					<input type="button" class="btn btn-large btn-primary " id="submit" value="<?php echo $vocables["$lang"]["attachment_index_button_1"]; ?>"/>
				</span>
			</span>
			<span id="key_checker" style="display:none;">
				<p><?php echo $vocables["$lang"]["attachment_index_2"]; ?></p>
				<span>
					<input type="text" id="active" name="passwd" placeholder='<?php echo $vocables["$lang"]["attachment_index_value_2"]; ?>'/>		
					<input type="button" class="btn btn-large btn-primary " id="enter" value="<?php echo $vocables["$lang"]["attachment_index_button_2"]; ?>"/>
				</span>
			</span>
			<span id="error" style="display:none"></span>
		</div>
		
	</center>
	<script>
	function setCookie() {
    var cname = "mail";
    var cvalue = document.getElementById("mail").value;
    var exdays = 7;
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname+"="+cvalue+"; "+expires;
	}

	function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++) {
			var c = ca[i].trim();
			if (c.indexOf(name)==0) return c.substring(name.length,c.length);
		}
		return "";
	}

	function checkCookie() {
		var user = getCookie("mail");
		if (user != "") {
			return user;
		}
	}
	jQuery(document).ready(function(){
	$('#mail').val(checkCookie());
		jQuery('#submit').click(function(){
			var data = "mail="+$('#mail').val()+"&iv=<?php echo $_GET['iv']; ?>&folder=<?php echo $_GET['folder']; ?>&key=<?php echo $_GET['key']; ?>"+'&lang=<?php echo $lang;?>';
			$.ajax({
				url : 'send_activation.php',
				data : data,
				type : 'GET',
				dataType : 'text',
			success : function(html){
					if( html == "0" ) {
						$('#error').html("<p style='color:green'><?php echo $vocables["$lang"]["attachment_index_3"]; ?></p>");
						setCookie();
						$('#error').show().delay(20000).fadeOut(4500, function() {
							 window.close();
                        });
					}
					else if( html == "email invalid"){
                            $('#error').html("<p style='color:red'><?php echo $vocables["$lang"]["attachment_index_4"]; ?></p>");
                            $('#error').show().delay(20000).fadeOut(4000, function() {
                            window.close();
                        });

                    }
					else{
						$('#error').html("<p style='color:red'><?php echo $vocables["$lang"]["attachment_index_5"]; ?></p>");
						$('#error').show().delay(1000).fadeOut(10000, function() {});
					}
				}
			});
			 
		});
		
	});
	</script>
	<!--[if (gte IE 8)&(lt IE 10)]>
		<script src="js/cors/jquery.xdr-transport.js"></script>
	<![endif]-->
</body>
</html>
