<?php
require("/etc/upload.conf");
require("/opt/elonet/alert_module.php");
require("".$conf['absolute_path']."/upload/attachment/include_languages.php");

function get_mails($folder){
    require("/etc/attachment.conf");  
	$mails = array();

	//mettre ici une fonction pour verifier $folder ( != "*.*")

        $htpwd = file_get_contents($conf['absolute_path']."download_attachment/files/".$folder."/.htpasswd");
        $htpwd_lines = explode("\n", $htpwd );
        foreach( $htpwd_lines as $line ){
                $mail = explode(":", $line );
                $mails[] = $mail[0];
		}
		return $mails;
}
                
      
$email = filter_input(INPUT_GET, 'mail', FILTER_VALIDATE_EMAIL);

if(!$email){
        alert_module("1", $_SERVER[REMOTE_ADDR]." : email input not valid (".$_GET['mail'].")","");
echo "email invalid";

        exit;
}

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

if( $_GET['mail'] ){
	$address = $_GET['mail'];	
	$dirname = $_GET['folder'];
	
	if (($handle = fopen($conf['name_folder_upload']."url_attachment.log", "r")) !== FALSE) {
		while (($data = fgets($handle)) !== FALSE) {
			$explode_data = explode (" | ",$data);
			if($explode_data[0]==$dirname){
				$tmp_folder = htmlspecialchars($explode_data[1]);
				if (($handle = fopen($conf['absolute_path']."download_attachment/files/".$tmp_folder."/.htpasswd", "r")) !== FALSE) {
					while (($data = fgets($handle)) !== FALSE) {
						$tmp_data = explode(":",$data);
						if($tmp_data[0] == $address) {
							$password = htmlspecialchars($tmp_data[1]);
						}
					}
				}				
			}
		}
		fclose($handle);
	}

	$mails = get_mails($tmp_folder);
	$i = 0 ;
	while( $i<count($mails) and $mails[$i] != $address ){
		$i = $i+1;
	}
	
	//Listing des fichiers dans un tableau
	$c_file=0;
	$dir = opendir($conf['absolute_path']."download/attachment/files/$folder/");
	while($file = readdir($dir)) {
		if($file != '.' && $file != '..' && $file != '...' && $file != 'index.php' && !is_dir($conf['absolute_path']."download/attachment/files/$folder/".$file) && $file != 'all_files_list.zip' && $file != '.htaccess' && $file != '.htpasswd' && $file != '.sender' && $file != '.messenger' && $file != 'mail.json') {
			$file_list[$c_file] = $file;
			$c_file++;
		}
	}
	$lang = $_GET['lang'];
	//Fonction de reduction de chaine
	function raccourcirChaine($chaine, $tailleMax) {
		$positionDernierEspace = 0;
		if( strlen($chaine) >= $tailleMax ) {
		/*	$chaine = substr($chaine,0,$tailleMax);
			$positionDernierEspace = strrpos($chaine,' ');
			$chaine = substr($chaine,0,$positionDernierEspace).'...'; Gestion des blancs */
			$chaine = substr($chaine,0,$tailleMax);
			$chaine .= '...';
		}
		return $chaine;
	}
	
	$from_email  = $conf['from_email'];
	$subject = $vocables["$lang"]["subject_download"];
	$entetedate  = "Date: ".date("l j F Y, G:i +0200")."\n";
	$entetemail  = "From: $from_email \n";
	$entetemail .= "Cc: \n";
	$entetemail .= "Bcc: \n";
	$entetemail .= "Reply-To: ".$conf['from_email']."\n";
	$entetemail .= "X-Mailer: PHP \n" ;
	$entetemail .= "Content-Type: text/html; charset=\"ISO-8859-1\ \n";
	$entetemail .= "Date: $entetedate";
	$body = "<center><img src='".$conf['img_for_email']."'/></center><br/><a href=\"".$conf['url_redirection_download']."attachment/after_auth.php?id=".$_GET['id']."&psw=".trim($password)."&folder=".$folder."&iv=".$_GET['iv']."&key=".$_GET['key']."&mail=".$address."\">".$vocables["$lang"]['attachment_mail']."</a>";
	$body .= "<br/><table cellspacing='0' cellpadding='0'><tr>";
	$body .= "<td align='left'  width='100%'>( ".$vocables["$lang"]["attachment_available"]." ";
	foreach($file_list as $key2 => $value2) {
		$body .= raccourcirChaine($value2,40)." ";
	}
	$body .= ")</td>";
	if( $i < count($mails)){
		mail($address, utf8_decode($subject), utf8_decode($body), $entetemail);
		echo "0";
		exit();
	}
	else{
		echo "1";
		exit();
	}
	echo "1";
}

?>

