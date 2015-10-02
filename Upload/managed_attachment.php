<?php
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('Europe/Berlin'); 
require("/etc/upload.conf");
require("include_languages.php");

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

$frommail = htmlspecialchars($_POST['fm']);
$tmp_mail = htmlspecialchars($_POST['mail']);
$tmp_subject = htmlspecialchars($_POST['subject']);
$tmp_folder = htmlspecialchars($_POST['id']);


$mail = explode(",",$tmp_mail);
//Lecture et vérification du lien
if (($handle = fopen($conf['name_folder_log']."url_attachment.log", "r")) !== FALSE) {
	while (($data = fgets($handle)) !== FALSE) {
		$explode_data = explode (" | ",$data);
		if($explode_data[0]==$_POST['id']){
			$dirname = 'files/'.htmlspecialchars($explode_data[1]);
			$login = htmlspecialchars($explode_data[2]);
			$password = htmlspecialchars($explode_data[3]);
			$folder = htmlspecialchars($explode_data[1]);
		}
	}
	fclose($handle);
}


$mail_arr = array();
$fol = $folder;
	if($fol != "") {
		if (is_dir($conf['absolute_path_download']."files/".$fol."/")){
			if (($handle = fopen($conf['absolute_path_download']."files/".$fol."/mail.json", "w")) !== FALSE) {
				foreach ($mail as $key) {
					if($key != ""){
						$mail_arr[] = $key;
						//Generation du htpasswd dans le répertoire crée
						$rand_motdepasse=rand_str(5);
						$htpasswd=htpasswd($rand_motdepasse);
						$fichier_mot_de_passe = fopen($conf['absolute_path_download']."files/$fol/.htpasswd","a");
						fputs($fichier_mot_de_passe, $key.":".$htpasswd."\n" );
						fclose($fichier_mot_de_passe);
						
					}
				}
				$mail_arr[] = $login;
				$array = array("mail" => $mail_arr, "subject" => $tmp_subject);
				$json = json_encode($array);
				fwrite($handle, $json);
				fclose($handle);
			}
		}
	}

?>
