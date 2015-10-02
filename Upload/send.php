<?php
date_default_timezone_set('Europe/Berlin');
require("/etc/upload.conf");
require("include_languages.php");
$folder = htmlspecialchars(@$_POST['f']);
$frommail = htmlspecialchars(@$_POST['fm']);

@session_start();
$key = $_SESSION['key'];
$iv = $_SESSION['iv'];


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
//Generation du htaccess dans le répertoire crée
/*$htaccess_content="AuthUserFile \"".$conf['absolute_path']."download/attachment/files/$folder/.htpasswd\"\n";
$htaccess_content.="AuthName \"Authentication required (this information is mentionned in the email which invited you to download the file) \"\nAuthType Basic\nRequire valid-user\n";
$fichier_htaccess = fopen("".$conf['absolute_path']."download/attachment/files/$folder/.htaccess","a"); 
fputs($fichier_htaccess,$htaccess_content); 
fclose($fichier_htaccess);
//chmod du fichier en lecture uniquement - meme propriétaire ( surtout le propriétaire ... vu que c'est apache )
chmod("".$conf['absolute_path']."download/attachment/files/$folder/.htaccess", 0575);*/

//Fonction de génération de chaine aléatoire
function rand_str($len, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'){
	$num_chars = strlen($chars) - 1;
	$ret_dir = '';
	for($nb_char = 0; $nb_char < $len; ++$nb_char){
	      $ret_dir .= $chars[mt_rand(0, $num_chars)];
	}
	return $ret_dir;
}

//Fonction de génération de chaîne aléatoire pour le raccourci
function createRandomName() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;
	$name = '';
    while ($i <= 32) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $name = $name.$tmp;
        $i++;
    }
    return $name;
}

//Fonction de génération de mot de passe Apache - fichier ".htpasswd"
function htpasswd($motdepasse) { 
    $pass = crypt(trim($motdepasse),CRYPT_STD_DES); 
    return $pass; 
}

//Generation du htpasswd dans le répertoire crée
$rand_motdepasse=rand_str(5);
$htpasswd=htpasswd($rand_motdepasse);
$fichier_mot_de_passe = fopen("".$conf['absolute_path']."download/attachment/files/$folder/.htpasswd","a");
fputs($fichier_mot_de_passe, $frommail.":".$htpasswd."\n" );
fclose($fichier_mot_de_passe);

$update = "";
if (($handle = fopen($conf['name_folder_upload']."log_attachment.log", "r")) !== FALSE) {
	while (($data = fgets($handle)) !== FALSE) {
		$explode_data = explode (" | ",$data);
		if($explode_data[3]==$folder){
			$update .= $explode_data[0].' | '.$explode_data[1].' | '.$explode_data[2].' | From : '.$frommail.' | '.$explode_data[3].' | '.$explode_data[4].' | Notify : '.htmlspecialchars(@$_POST["noti"])."/".htmlspecialchars(@$_POST["every"]).' | Only One : '.htmlspecialchars(@$_POST["only"]).' | '.$explode_data[5].' | '.$explode_data[6];
		}
		else {
			$update .= $data;
		}
	}
	fclose($handle);
}
//Enregistrement du message
if(isset($_POST['m'])) {
	$handle2 = fopen($conf['absolute_path']."download/attachment/files/".$folder."/.messenger", "w");
	fwrite($handle2,htmlspecialchars($_POST['m']));
	fclose($handle2);
}
//Mise à jour du fichier
$handle2 = fopen($conf['name_folder_upload']."log_attachment.log", "w+");
fwrite($handle2,$update);
fclose($handle2);
openlog("uploadLog", 0, LOG_LOCAL0);
syslog(LOG_INFO, ";ATT-SENT-ACCESS;".date("d/m/Y-G:i").";".$folder.";".$_SERVER['REMOTE_ADDR'].";".htmlspecialchars($_POST['fm']).";;;;"."Notify : ".htmlspecialchars($_POST["noti"])."/".htmlspecialchars($_POST["every"])."-Only One : ".htmlspecialchars($_POST["only"]));
closelog();
//Raccourcir l'url
$handle3 = fopen($conf['name_folder_upload']."url_attachment.log", "a");
$tmp_var = createRandomName();
$var = $tmp_var." | ".$folder." | ".$frommail." | ".$htpasswd."\n";
fwrite($handle3, $var);
fclose($handle3);

	//Listing des fichiers dans un tableau
	$c_file=0;
	$dir = opendir($conf['absolute_path']."download/attachment/files/$folder/");
	while($file = readdir($dir)) {
		if($file != '.' && $file != '..' && $file != '...' && $file != 'index.php' && !is_dir($conf['absolute_path']."download/attachment/files/$folder/".$file) && $file != 'all_files_list.zip' && $file != '.htaccess' && $file != '.htpasswd' && $file != '.sender' && $file != '.messenger' && $file != 'mail.json') {
			$file_list[$c_file] = $file;
			$c_file++;
		}
	}
	$file_listing = null;
	if($c_file>0) {
		foreach($file_list as $key2 => $value2) {
			$file_listing .= raccourcirChaine($value2,40)." ";
		}
	}
echo $conf['url_redirection_download']."attachment/?folder=".$tmp_var."&key=".$key."&iv=".$iv."|".$file_listing;

//if we have a old sender, we sent mail with attachment
if( file_exists($conf['absolute_path']."download/attachment/files/".$folder."/.sender")){
	$frommail = htmlspecialchars($_POST['fm']);
	$lang = $_POST['l'];
	$sender = file_get_contents($conf['absolute_path']."download/attachment/files/".$folder."/.sender");
	$subject = json_decode(file_get_contents($conf['absolute_path']."download/attachment/files/$folder/mail.json"),true);
	$subject = $subject['subject'];
	// finish by sending email to user with license key in it
	$subject = "Re : ".$subject;
	$entetedate  = "Date: ".date("l j F Y, G:i +0200")."\n";
	$entetemail  = "From: $frommail \n";
	$entetemail .= "Cc: \n";
	$entetemail .= "Bcc: \n";
	$entetemail .= "Reply-To: ".$sender."\n";
	$entetemail .= "X-Mailer: PHP \n" ;
	$entetemail .= "Content-Type: text/html; charset=\"ISO-8859-1\ \n";
	$entetemail .= "Date: $entetedate";
	$body = "<center><img src='".$conf['img_for_email']."'/></center><br/><p>".$vocables["$lang"]["sender_mail_1_part1"].$frommail.$vocables["$lang"]["sender_mail_1_part2"]."</p><a href=\"".$conf['url_redirection_download']."attachment/?id=".$tmp_var."&key=".$key."&iv=".$iv.">".$vocables["$lang"]["sender_mail_2"]."\">Download files</a>";
	mail($sender, utf8_decode($subject), utf8_decode($body), $entetemail);
}

file_put_contents($conf['absolute_path']."download/attachment/files/$folder/.sender",$frommail);
?>
