<?php
date_default_timezone_set('Europe/Berlin'); 
require("/etc/upload.conf");
require($conf['absolute_path_upload']."include_languages.php");
$file = htmlspecialchars($_GET['file']);
$password=htmlspecialchars($_GET['psw']);
$lang=htmlspecialchars($_GET['l']);
$ip=htmlspecialchars($_GET['ip']);
$mail=htmlspecialchars($_GET['mail']);
$folder_tmp=explode ("/",$file);
if (($handle = fopen($conf['absolute_path_download']."files/".$folder_tmp[1]."/.htpasswd", "r")) !== FALSE) {
	while (($data = fgets($handle)) !== FALSE) {
		if($data == $mail.':'.$password."\n" && !check_only($file,$mail))
			download_file($file,$folder_tmp,$password,$ip,$mail);
	}
}
function check_only($file,$login){
	$login_found = 0;
	if(!file_exists($file.".lock")){
		return false;
	}
	else{
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
function download_file($fullPath,$folder_tmp,$password,$ip,$mail){
	require("/etc/upload.conf");
	require($conf['absolute_path_upload']."include_languages.php");
	$lang=htmlspecialchars($_GET['l']);
	$key=htmlspecialchars($_GET['key']);
	$iv=htmlspecialchars($_GET['iv']);
	if(headers_sent())
		die('Headers Sent');

	if(ini_get('zlib.output_compression'))
		ini_set('zlib.output_compression', 'Off');
		
	if(file_exists($fullPath) ){
		$fsize = filesize($fullPath);
		$path_parts = pathinfo($fullPath);
		$ext = strtolower($path_parts["extension"]);
		if( $path_parts['filename'] != "all_files_list" ){
			echo "<p>on va déchiffrer</p>";
			$file_handler = fopen($fullPath,"r");
			if( !$file_handler ){
				echo "<p>Erreur d'ouverture du fichier à déchiffrer.</p>";
			}
			else{
				$file_content = fread($file_handler,filesize($fullPath));
				fclose($file_handler);
				$file_handler = fopen($fullPath,"w");
				//déchiffrement du fichier
				// Open the cipher 
				$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
				
				// Initialize encryption module for decryption 
				mcrypt_generic_init($td, $key, $iv);
				
					
				// Decrypt encrypted string 
				$decrypted = mdecrypt_generic($td, $file_content);
				
				$decrypted = base64_decode($decrypted);

				fwrite($file_handler,$decrypted);
				
				// Terminate decryption handle and close module 
				mcrypt_generic_deinit($td);
				mcrypt_module_close($td);
				fclose($file_handler);
			}
		}
		switch ($ext) {
			case "pdf": $ctype="application/pdf"; break;
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "doc": $ctype="application/msword"; break;
			case "xls": $ctype="application/vnd.ms-excel"; break;
			case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "jpeg":
			case "jpg": $ctype="image/jpg"; break;
			default: $ctype="application/force-download";
		}
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: $ctype");
		header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$fsize);
		ob_clean();
		flush();
		readfile( $fullPath );
		//Ecriture des logs
		$log = fopen($conf['name_folder_log'] ."log_download.log", "a");
		fwrite($log, "DOWNLOAD | ".$_SERVER['REMOTE_ADDR']." | ".date("l j F Y, G:i")." | ".$mail." | ".$fullPath." | "."\n");
		fclose($log);
		$date = date("Y-m-d H:i:s");
		//Gestion du Notify first download + only one download
		$update = "";
		if (($handle = fopen($conf['name_folder_log'] ."log_attachment.log", "r")) !== FALSE) {
			while (($data = fgets($handle)) !== FALSE) {
				$explode_data = explode (" | ",$data);
				//Récupération de l'email FROM dans les logs UPLOAD
				$explode_email = explode (" : ",$explode_data[3]);
				$explode_notify = explode ("/",$explode_data[6]);
				//Envoi du mail pour les fichiers normaux + gestion files.zip or not
				if($explode_data[4].'/'.$explode_data[5] == $folder_tmp[1].'/'.$folder_tmp[2] && ($explode_notify[0] == 'Notify : true' || $explode_notify[1] == 'true')){
					if($folder_tmp[2]!=='all_files_list.zip') {
						$subject=$vocables["$lang"]["download_mail_1"];
						$body="<center><img src='".$conf['img_for_email']."'/></center><br/><br/>".$vocables["$lang"]["invite_receiver_mail_body_1"]."<br/><br/>"
						.$vocables["$lang"]["download_mail_2"].$folder_tmp[2].$vocables["$lang"]["download_mail_3"]." ".$mail." via ".$ip." le ".$date."<br/><br/>"
						.$vocables["$lang"]["download_mail_4"].$conf['name_folder_log']."<br/><br/>"
						.$vocables["$lang"]["invite_author_mail_body_7"];
						$from_email  = $conf['from_email'];
						$entetedate  = "Date: ".date("l j F Y, G:i +0200")."\n";
						$entetemail  = "From: $from_email \n";
						$entetemail .= "Cc: \n";
						$entetemail .= "Bcc: \n";
						$entetemail .= "Reply-To: ".$conf['from_email']."\n";
						$entetemail .= "X-Mailer: PHP \n" ;
						$entetemail .= "Content-Type: text/html; charset=\"ISO-8859-1\ \n";
						$entetemail .= "Date: $entetedate";
						mail($explode_email[1], utf8_decode($subject), utf8_decode($body), $entetemail);
					} else {
						$subject="Your file has just been downloaded";
						$body="<center><img src='".$conf['img_for_email']."'/></center><br/><br/>".$vocables["$lang"]["invite_receiver_mail_body_1"]."<br/><br/>"
						.$vocables["$lang"]["download_mail_6"]."<br/><br/>"
						.$vocables["$lang"]["download_mail_4"].$conf['name_folder_log'].$vocables["$lang"]["download_mail_5"]."<br/><br/>"
						.$vocables["$lang"]["invite_author_mail_body_7"];
						$from_email  = $conf['from_email'];
						$entetedate  = "Date: ".date("l j F Y, G:i +0200")."\n";
						$entetemail  = "From: $from_email \n";
						$entetemail .= "Cc: \n";
						$entetemail .= "Bcc: \n";
						$entetemail .= "Reply-To: ".$conf['from_email']."\n";
						$entetemail .= "X-Mailer: PHP \n" ;
						$entetemail .= "Content-Type: text/html; charset=\"ISO-8859-1\ \n";
						$entetemail .= "Date: $entetedate";
						mail($explode_email[1], utf8_decode($subject), utf8_decode($body), $entetemail);
					}
					//Passage du notify à "done"
				if($explode_notify[0] == 'Notify : true'){
					$explode_notify[0] = 'Notify : done';
				}
				$explode_data[6] = implode("/",$explode_notify);
					$update .= implode(" | ",$explode_data);
				}
				else {
					$update .= $data;
				}
				//Gestion only one download
				if($explode_data[4].'/'.$explode_data[5] == $folder_tmp[1].'/'.$folder_tmp[2] && $explode_data[7] == "Only One : true"){
					$lock = fopen("files/".$explode_data[4].'/'.$explode_data[5].".lock", "a");
					fwrite($lock,$mail."\n");
					fclose($lock);
				}
					if($explode_data[4].'/'.$explode_data[5] == $folder_tmp[1].'/'.$folder_tmp[2]){
					openlog("uploadLog", 0, LOG_LOCAL0);
					syslog(LOG_INFO, ";DOWNLOAD;".date("d/m/Y-G:i").";".$explode_data[4].";".$_SERVER['REMOTE_ADDR'].";".$mail.";".$explode_data[5].";".$explode_data[8].";".trim($explode_data[9]).";".$explode_data[6]."-".$explode_data[7]);
					closelog();
				}
			}
			fclose($handle);
		}
			$handle2 = fopen($conf['name_folder_log'] ."log_attachment.log", "w+");
			fwrite($handle2,$update);
			fclose($handle2);
			
		if( $path_parts['filename'] != "all_files_list" ){
			$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
			$ks = mcrypt_enc_get_key_size($td);
			mcrypt_generic_init($td, $key, $iv);
			$file_handler=fopen($fullPath, "r");
			$file_content = fread($file_handler,filesize($fullPath));
			fclose($file_handler);
			$file_handler=fopen($fullPath, "w");
			$file_content = base64_encode($file_content);
			$encrypted_content = mcrypt_generic($td, $file_content);
			fwrite($file_handler,$encrypted_content);
			fclose($file_handler);
		}
	} 
	else {
		die('File Not Found');
	}
}
?>
