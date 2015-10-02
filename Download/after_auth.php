<!DOCTYPE html>
<html>
<head>
<?php
require("/etc/secure_attachment.conf");
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
$folder = htmlspecialchars($_GET['folder']);
$mail = htmlspecialchars($_GET['mail']);
$password=htmlspecialchars($_GET['psw']);
$logpas = false;
$onlyone = false;
if (($handle = fopen($conf['name_folder_log']."url_attachment.log", "r")) !== FALSE) {
	while (($data = fgets($handle)) !== FALSE) {
		$explode_data = explode (" | ",$data);
		if($explode_data[0]==$folder){
			$folder = htmlspecialchars($explode_data[1]);
			$dirname='files/'.$folder;
			if (($handle = fopen($conf['absolute_path_download']."files/".$folder."/.htpasswd", "r")) !== FALSE) {
				while (($data = fgets($handle)) !== FALSE) {
					if($data == $mail.':'.$password."\n")
						$logpas = true;
				}
			}
		}
	}
	fclose($handle);
}
openlog("uploadLog", 0, LOG_LOCAL0);
syslog(LOG_INFO, ";EMAIL-OPEN;".date("d/m/Y-G:i").";".htmlspecialchars($folder).";".$_SERVER['REMOTE_ADDR'].";".$mail.";;;;");
closelog();
?>
	<!-- Force latest IE rendering engine or ChromeFrame if installed -->
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	<meta http-equiv="Pragma" content="no-cache">
	<title><?php echo $conf['title_download']; ?> - File downloading</title>
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
		#message{color: #000000; font-family: Verdana; font-weight: bold; font-size: 14px; background-color: #F8FFF5;text-align:justify;}
	</style>
	<noscript><link rel="stylesheet" href="css/jquery.fileupload-noscript.css"></noscript>
	<noscript><link rel="stylesheet" href="jquery.fileupload-ui-noscript.css"></noscript>
	<?php
	function assocExt($ext) {
	  $e = array('' => "Format inconnu",'doc' => "Microsoft Word",'xls' => "Microsoft Excel",'ppt' => "Microsoft Power Point",'pdf' => "Adobe Acrobat",'zip' => "Archive WinZip",
		'txt' => "Document texte",'gif' => "Image GIF",'jpg' => "Image JPEG",'png' => "Image PNG",'php' => "Script PHP",'php3' => "Script PHP",'htm' => "Page web",'html' => "Page web",
		'css' => "Feuille de style",'js' => "JavaScript",'avi' => "Video",'bmp' => "Image BITMAP",'flv' => "Flash Video",'fla' => "Flash",'ico' => "Favicon",'mp3' => "Audio Mpeg layer 3",
		'svg' => "Dessin vectoriel",'swf' => "Flash web",'wav' => "Audio",'wma' => "Video Microsoft",'rar' => "Archive WinRar",'tar.gz' => "Archive",'mp4' => "Video Mpeg layer 4",
		'exe' => "Executable",'iso' => "Image disque");
	  if(in_array($ext, array_keys($e))) {
		return $e[$ext];
	  } else {
		return $e[''];
	  }
	}
	function formatSize($s) {
		$u = array('octets','Ko','Mo','Go','To');
		$i = 0;
		$m = 0;
		while($s >= 1) {
			$m = $s;
			$s /= 1024;
			$i++;
		}
		if(!$i) $i=1;
		$d = explode(".",$m);
		if($d[0] != $m) {
			$m = number_format($m, 2, ",", " ");
		}
		return $m." ".$u[$i-1];
	}
	function sortext($e,$dirname,$file) {
		if($e == "mp3" || $e == "ogg" || $e == "wav" || $e == "wma") {
			return ("<audio src=\"$dirname/$file\" controls=''></audio>");
		}
		elseif ($e == "avi" || $e == "flv" || $e == "mp4" ||$e == "wmv") {
			return ("<video src=\"$dirname/$file\" controls=''></video>");
		}
		elseif ($e == "jpe" || $e == "jpeg" || $e == "jpg" || $e == "png" || $e == "gif" || $e == "bmp" || $e == "tiff") {
			return ("<a href=\"$dirname/$file\" title='$file' download='$file' data-gallery><img src='$dirname/thumbnail/$file'></a>");
		}
		else {
			return " ";
		}
	}
	function check_only($file,$login){
		$login_found = 0;
		if(!file_exists($file.".lock")){
			return false;
		} else {
			if (($handle = fopen($file.".lock", "r")) !== FALSE) {
				while (($data = fgets($handle)) !== FALSE) {
					if($data == $login."\n"){
						$login_found++;
					}
				}
			}
			if($login_found>=1){return true;}else{return false;}
		}
	}
	?>
</head>
<body>
	<div class="left" align="left" style="margin-top:-40px;margin-left:30px;">
	</div>
	<div id="header" style="width:100%;text-align: center;">
		<img alt="Elonet" width="220" src="<?php echo $conf['img_logo']; ?>" border=0/><br/>
		<font size=4 face="Verdana" style="margin-left:15px;"><?php echo $conf['title_download']; ?></font>
	</div>
	<div style="height:30px;"></div>
	<?php
		if($logpas == true):
	?>
	<?php if( file_exists($conf['absolute_path_download']."files/".$folder."/.messenger")){
		$messenger = file_get_contents($conf['absolute_path_download']."files/".$folder."/.messenger");
		if($messenger != "" ||$messenger != NULL) {
			echo "<div style='width:80%;margin:0px auto;'><p>".$vocables["$lang"]["message_from"].$mail." :</p><p id='message'>".$messenger."</p></div>";
		}
	}
	?>
	<div style="height:30px;"></div>
	<!-- The template to display files available for download -->
	<table role="presentation" class="table table-striped">
		<tbody class="files">
			<?php
			$dir = opendir($dirname);
			while($file = readdir($dir)) {
				$filetype_explode = explode(".", $file);
				$fileext = $filetype_explode[count($filetype_explode)-1];
				if($file != '.' && $file != '..' && $file != 'index.php' && !is_dir($dirname.'/'.$file) && $file != 'all_files_list.zip' && $file != '.htaccess' && $file != '.htpasswd' && !check_only($dirname.'/'.$file,$mail) && $fileext != "lock" && $file != 'mail.json' && $file != '.sender' && $file!= '.messenger') :
					$filetype = assocExt($fileext);
					$filesize = formatSize(filesize($dirname.'/'.$file));
			?>
			<tr class="template-download fade in" id="<?php echo $filetype_explode[0]; ?>">
				<td>
					<span class="preview">
						<?php //echo sortext($fileext,$dirname,$file); ?>
					</span>
				</td>
				<td>
					<p class="name">
						<?php echo $file; ?>
					</p>
				</td>
				<td>
					<span class="type">
						<?php echo $filetype; ?>
					</span>
				</td>
				<td>
					<span class="size">
						<?php echo $filesize; ?>
					</span>
				</td>
					<td>
						<span class="btn btn-success">
							<a href="download.php?file=<?php echo $dirname.'/'.$file; ?>&psw=<?php echo trim($password); ?>&l=<?php echo $lang; ?>&key=<?php echo $key;?>&iv=<?php echo $iv?>&ip=<?php echo $_SERVER['REMOTE_ADDR'];?>&mail=<?php echo htmlspecialchars(trim($_GET["mail"])); ?>" title="<?php echo $file; ?>" style="color:white;text-decoration:none;" id="<?php echo $filetype_explode[0]; ?>_button" class="download">
								<?php echo $vocables["$lang"]["download_1"]; ?>
							<a>
						</span>
					</td>
			</tr>
		<?php
		if (($handle = fopen($conf['name_folder_log']."log_attachment.log", "r")) !== FALSE) {
			while (($data = fgets($handle)) !== FALSE) {
				$explode_data = explode (" | ",$data);
				if($explode_data[4].'/'.$explode_data[5] == $folder.'/'.$file && $explode_data[7] == "Only One : true"):?>
					<script>
						$(document).ready(function(){
							$('#<?php echo $filetype_explode[0]; ?>_button').click(function() {
								$('#<?php echo $filetype_explode[0]; ?>').remove();
								if($('.download').length <= 1) {
									$('#zip').fadeOut();
								}
							});
						});
					</script>
		<?php
				$onlyone = true;
				endif;
			}
		} ?>
			<?php
				endif;
				if(check_only($dirname.'/'.$file,$mail)):
			?>
			<tr class="template-download fade in" id="<?php echo $filetype_explode[0]; ?>">
				<td></td>
				<td>
					<p class="name">
						<?php echo $file; ?>
					</p>
				</td>
				<td>
					<span class="type">
					</span>
				</td>
				<td>
					<span class="size">
					</span>
				</td>
				<td>
					<?php echo $vocables["$lang"]["download_7"]; ?><br/>
					<a target="_blank" href="issue.php?folder=<?php echo $dirname;?>&file=<?php echo $file;?>&mail=<?php echo $mail;?>&psw=<?php echo trim($password); ?>"><i><?php echo $vocables["$lang"]["download_8"]; ?></i></a>
				</td>
			</tr>			
			<?php
				endif;
			}
			closedir($dir);
			?>
		</tbody>
	</table>
	<?php if($onlyone == true) echo "<center>".$vocables["$lang"]["download_9"]."</center>"; ?>
		<!-- The blueimp Gallery widget -->
	<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev"><</a>
		<a class="next">></a>
		<a class="close">X</a>
		<a class="play-pause"></a>
		<ol class="indicator"></ol>
	</div>
	<div id='modal'>
		<div id='popin'>
			<center>
				<img src="img/loading.gif" width="32px" /><br/>
				<h4 style="color:white"><?php echo $vocables["$lang"]["download_2"]; ?>...<br/><?php echo $vocables["$lang"]["download_3"]; ?></h4>
				<span class="btn btn-success" style="display:none;" id="dzip">
					<a href="download.php?file=<?php echo $dirname.'/all_files_list.zip' ?>&mail=<?php echo $mail; ?>&psw=<?php echo trim($password); ?>&l=<?php echo $lang; ?>&ip=<?php echo $_SERVER['REMOTE_ADDR'];?>" style="color:white;text-decoration:none;">
						<?php echo $vocables["$lang"]["download_4"]; ?>
					</a>
				</span>
			</center>
		</div>
	</div>
	<br/>
	<center>
		<span class="btn btn-primary" id="zip">
			<?php echo $vocables["$lang"]["download_5"]; ?>
		</span>
		<!-- penser à modifier la ligne d'affichage et créer une entré dans include_languages -->
		<?php 
			$mail_author = file_get_contents($conf['absolute_path_download']."files/".$folder."/.sender");
			@session_start();
			$log = $mail;
			if( $mail_author != $log ){ 
		?>
				<span class="btn btn-primary" id="upload">
					<a href="<?php echo $conf['url_redirection_upload']; ?>after_auth.php?fm=<?php echo $mail_author; ?>&folder=<?php echo $folder; ?>&log=<?php echo $log;?>" style="color:white;text-decoration:none;"><?php echo $vocables["$lang"]["upload_redirect"]; ?></a>
				</span>
		<?php 
			}
		?>
	</center>
	<script src="js/vendor/jquery.ui.widget.js"></script>
	<script src="js/tmpl.min.js"></script>
	<script src="js/load-image.min.js"></script>
	<script src="js/canvas-to-blob.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.blueimp-gallery.min.js"></script>
	<script src="js/jquery.iframe-transport.js"></script>
	<script src="js/jquery.fileupload.js"></script>
	<script src="js/jquery.fileupload-process.js"></script>
	<!-- The File Upload image preview & resize plugin -->
<!-- <script src="js/jquery.fileupload-image.js"></script> -->
<!-- The File Upload audio preview plugin -->
<!-- <script src="js/jquery.fileupload-audio.js"></script> -->
<!-- The File Upload video preview plugin -->
<!-- <script src="js/jquery.fileupload-video.js"></script> -->
	<script src="js/jquery.fileupload-validate.js"></script>
	<script src="js/jquery.fileupload-ui.js"></script>
	<script src="js/main.js"></script>
	<script>
	jQuery(document).ready(function(){
		if($('.download').length <= 1) {
			$('#zip').fadeOut();
		}
		jQuery('#zip').click(function(){
			var data = 'folder=<?php echo $dirname; ?>&id=<?php echo $mail; ?>&psw=<?php echo trim($password); ?>&l=<?php echo $lang; ?>&iv=<?php echo $iv; ?>&key=<?php echo $key;?>';
			$("#modal").fadeIn();
			$("#popin").fadeIn();
			$("#popin #dzip").fadeOut();
			$("#zip").fadeOut();
			jQuery.ajax({
				type: "POST",
				url: "zip.php",
				data: data,
				success: function(html){
					$("#popin img").fadeOut();
					$("#popin h4").fadeOut();
					$("#dzip").fadeIn();
				}
			});
		});
		jQuery('#dzip').click(function(){
			$("#dzip").fadeOut(function() {
				$("#modal").fadeOut();
				$("#popin").fadeOut();
				$("#zip").fadeIn();
			});
		});
	});
	</script>
<?php else: ?>
	<center>
		<?php echo $vocables["$lang"]["download_6"]; ?>
	</center>
<?php endif; ?>

	<!--[if (gte IE 8)&(lt IE 10)]>
		<script src="js/cors/jquery.xdr-transport.js"></script>
	<![endif]-->
</body>
</html>
