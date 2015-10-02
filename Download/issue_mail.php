<?php
date_default_timezone_set('Europe/Berlin');
require("/etc/secure_attachment.conf");
require($conf['absolute_path_upload']."include_languages.php");
$phone = htmlspecialchars($_POST['p']);
$message = htmlspecialchars($_POST['m']);
$recup = htmlspecialchars($_POST['r']);
$lang = htmlspecialchars($_POST['l']);
$folder = htmlspecialchars($_POST['fo']);
$file = htmlspecialchars($_POST['fi']);
$login = htmlspecialchars($_POST['lo']);
$psw = htmlspecialchars($_POST['psw']);

//Envoi d'un mail à l'auteur
$subject = $vocables[$lang]['issue_6'];
$body = "<center><img src='".$conf['img_for_email']."?id=".$folder."'/></center>".
"<br/><br/>".$vocables[$lang]['issue_7'].
"<br/><br/>".$vocables[$lang]['issue_8'].$login.
"<br/>".$vocables[$lang]['issue_9'].$psw.
"<br/>".$vocables[$lang]['issue_10'].$phone.
"<br/>".$vocables[$lang]['issue_11'].$folder.
"<br/>".$vocables[$lang]['issue_12'].$file;


if($recup == "true"){
	$body .= "<br/>".$vocables[$lang]['issue_13'].
	"<br/>"."<a href=\"".$conf['url_redirection_download']."reactive_dl.php?f=".$file."&d=".$folder."&l=".$lang."&lo=".$login."&pwd=".$psw."\" >".$vocables[$lang]['issue_14']." : ".$file."</a>";
	
}		
$body .= "<br/><br/>".$vocables[$lang]['invite_author_mail_body_7'];

// cet email regroupe michael decamps et leo leroy
$from_email  = $conf['from_email'];
$entetedate  = "Date: ".date("l j F Y, G:i +0200")."\n"; 
$entetemail  = "From: ".$from_email." \n";
$entetemail .= "Cc: \n";
$entetemail .= "Bcc: \n";
//le reply to est l'email de la personne qui envoi le fichier
$entetemail .= "Reply-To: ".$from_email."\n"; 
$entetemail .= "X-Mailer: PHP \n" ;
$entetemail .= "Content-Type: text/html; charset=\"ISO-8859-1\ \n";
$entetemail .= "Date: $entetedate";
mail($conf['support'], utf8_decode($subject), utf8_decode($body), $entetemail);
?>
