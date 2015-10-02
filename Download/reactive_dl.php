<?php
require("/etc/secure_attachment.conf");
require($conf['absolute_path_upload']."include_languages.php");

$fichier = htmlspecialchars($_GET['f']);
$dossier = htmlspecialchars($_GET['d']);
$lang = htmlspecialchars($_GET['l']);
$login = htmlspecialchars($_GET['lo']);
$psw = htmlspecialchars($_GET['pwd']);

$subject = $vocables[$lang]["issue_16"];
$from_email  = $conf['from_email'];
$entetedate  = "Date: ".date("l j F Y, G:i +0200")."\n"; 
$entetemail  = "From: ".$conf['from_email']." \n";
$entetemail .= "Cc: \n";
$entetemail .= "Bcc: \n";
$entetemail .= "X-Mailer: PHP \n" ;
$entetemail .= "Content-Type: text/html; charset=\"ISO-8859-1\ \n";
$body = "<center><img src='".$conf['img_for_email']."?folder=".$dossier."'/></center>".
"<br/><br/><p>".$vocables[$lang]['issue_15']." : ".$fichier."</p>".
"<br/><a href='".$conf['url_redirection_download']."?folder=".$dossier."&id=".$login."&psw=".$psw."' style='color: #0d00ff; font-size:14px; font-family: Helvetica, Arial, sans-serif; text-decoration: none; line-height:24px; width:100%; display:inline-block'>".$vocables[$lang]['download']."</a>";
if( !file_exists($conf['absolute_path_download']."files/".$dossier) ){
	echo "Le dossier ".$dossier." n'existe pas.\n";
}
elseif( !file_exists($conf['absolute_path_download']."files/".$dossier."/".$fichier.".lock") ){
	echo "Le fichier ".$fichier." n'existe pas.\n";
}
else{
	$file_mail = fopen($conf['absolute_path_download']."files/".$dossier."/".$fichier.".lock","r+");
	if( !$file_mail ) {
		echo "Erreur de création ou d'ouverture du fichier ".$fichier.".lock.\n";
		exit(1);
	} else {
		echo $file_mail;
		$e_mail = fread($file_mail,filesize($conf['absolute_path_download']."files/".$dossier."/".$fichier.".lock"));
	}
	fclose($file_mail);
}
if( !mail($e_mail, utf8_decode($subject), utf8_decode($body), $entetemail) ){
	echo "Problème lors de l'envoi du mail vers l'adresse ".$e_mail.".\n";
}

?>
