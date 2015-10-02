<!DOCTYPE html>
<html>
<head>
<?php 
require("/etc/upload.conf");
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
$dirname = explode('/', htmlspecialchars($_GET['folder']));
$file = htmlspecialchars($_GET['file']);
$login = htmlspecialchars($_GET['mail']);
$psw = htmlspecialchars($_GET['psw']);
?>
<meta charset="utf-8">
<meta http-equiv="Pragma" content="no-cache">
<title><?php echo $conf['title']; ?> - File uploading</title>
<meta name="description" content="File Upload widget with multiple file selection, drag&amp;drop support, progress bars, validation and preview images, audio and video for jQuery. Supports cross-domain, chunked and resumable file uploads and client-side image resizing. Works with any server-side platform (PHP, Python, Ruby on Rails, Java, Node.js, Go etc.) that supports standard HTML form file uploads.">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap styles -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<!-- Generic page styles -->
<link rel="stylesheet" href="css/style.css">
<style>
#header{display:block;margin-top:10px;margin-bottom:10px;height:100px;}
.left{float:left;display:inline-block;width:50%;margin-bottom:30px;}
.right{float:right;display:inline-block;width:50%;margin-bottom:50px;}
input[type="text"]{width:90%;color: #000000; font-family: Verdana; font-weight: bold; font-size: 14px; background-color: #F8FFF5;}
input[type="textarea"]{color: #000000; font-family: Verdana; font-weight: bold; font-size: 14px; background-color: #F8FFF5;}
.line{border-bottom:1px solid lightgrey;margin-top:10px;margin-bottom:10px;}
#resultsto{display:none;width:90%;border:1px solid #AAA;border-top-width:0;background-color:#FFF;}
#resultsto div{width:100%;padding:2px 4px;text-align:left;border:0;background-color:#FFF;}
#resultsto div:hover,.result_focus{background-color:#DDD!important;}
#resultsfrom{display:none;width:90%;border:1px solid #AAA;border-top-width:0;background-color:#FFF;}
#resultsfrom div{width:100%;padding:2px 4px;text-align:left;border:0;background-color:#FFF;}
#resultsfrom div:hover,.result_focus{background-color:#DDD!important;}
.popin_add{display: none;width: 629px;background-color:white;margin:100px auto;z-index: 100000;padding: 10px;}
.modal{display: none;top: 0;left: 0;bottom: 0;right: 0;position: fixed;width: 100%;height: 100%;z-index: 10000;background-color: rgba(0, 0, 0, 0.85);}
.box{display:none;}
</style>
</head>
<body>
<div class="container">
	<div id="header" style="width:100%;text-align: center;">
		<img alt="Elonet" width="220" src="<?php echo $conf['img_logo']; ?>" border=0/><br/>
		<font size=4 face="Verdana" style="margin-left:15px;"><?php echo $conf['title']; ?></font>
	</div>
	<div style="height:60px;"></div>
	<div class="line"></div>
	<div id="all">
		<font size="2"><?php echo $vocables[$lang]['issue_1']; ?></font><br/>
		<input type="text" name="phone" id="phone" autocomplete="off" style="width:50%;"/>
		<div class="line"></div>
		<font size="2"><?php echo $vocables[$lang]['issue_2']; ?></font><br/>
		<textarea name="message" id="message" style="width:100%" rows=4 ></textarea>
		<div class="line"></div>
		<center>
			<input type="checkbox" name="recup" id="recup" style="top: 2px;left: -2px;position: relative;"/><?php echo $vocables[$lang]['issue_3']; ?>
			<br/><br/>
			<input type="button" class="btn btn-primary" value="<?php echo $vocables[$lang]['issue_4']; ?>" id="submit_info"/>
		</center>
	</div>
	<div id="result" style="display:none;margin-top:40px;">
		<center>
			<h4><?php echo $vocables[$lang]['issue_5']; ?></h4>
		</center>
	</div>
</div>
<script src="js/jquery.min.js"></script>
<script>
$(document).ready(function(){
	$('#submit_info').click(function(){
		var phone = $("#phone").val();
		var message = $("#message").val();
		var recup = $("#recup").is(':checked');
		var lang = '<?php echo $lang; ?>';
		var data = 'p='+ phone +'&m='+ message +'&r='+ recup +'&l='+ lang +'&fo=<?php echo $dirname[1];?>&fi=<?php echo $file;?>&lo=<?php echo $login;?>&psw=<?php echo $psw;?>';
		$.ajax({
			type: "POST",
			url: "issue_mail.php",
			data: data,
			success: function(html){
				$("#all").fadeOut();
				$("#result").fadeIn();
			}
		});
	});
});
</script>
</body> 
</html>