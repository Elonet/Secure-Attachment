<?php
	require("/etc/upload.conf");
	$dirname=$_REQUEST['folder'];
	$login=$_REQUEST['id'];
	$password=$_REQUEST['psw'];
	$iv=$_REQUEST['iv'];
	$key=$_REQUEST['key'];
	$folder=explode ('/', $dirname);
	if (($handle = fopen($conf['absolute_path_download']."files/".$folder[1]."/.htpasswd", "r")) !== FALSE) {
		while (($data = fgets($handle)) !== FALSE) {

			if(trim($data) == trim($login.':'.$password))
				$logpas = true;
		}
	}
	
	
	if($logpas == true) {
		if(!file_exists("$dirname/all_files_list.zip")){
			$dir = opendir($dirname);
			$zip = new ZipArchive();
			if($zip->open("$dirname/all_files_list.zip", ZipArchive::CREATE) == TRUE) {
				echo '&quot;all_files_list.zip&quot; ouvert<br/>';
				while($file = readdir($dir)) {
					
					$filetype_explode = explode(".", $file);
					$fileext = $filetype_explode[count($filetype_explode)-1];
					if($file != '.' && $file != '..' && $file != 'index.php' && !is_dir($dirname.'/'.$file) && $file != 'mail.json' && $file != '.htaccess' && $file != '.sender' && $file != '.htpasswd' && !check_only($dirname.'/'.$file,$login) && $fileext != "lock") {
						
						//ici on intègre le déchiffrement
						
						$file_handler = file_get_contents($conf['absolute_path_download'].$dirname."/".$file);
						if( !$file_handler ){
							echo "<p>Erreur d'ouverture du fichier à déchiffrer.</p>";
							
						}
						else{
							
							//déchiffrement du fichier
							// Open the cipher 
							$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
			
							// Initialize encryption module for decryption 
							mcrypt_generic_init($td, $key, $iv);

							// Decrypt encrypted string 
							$decrypted = mdecrypt_generic($td, $file_handler);
							$decrypted = base64_decode($decrypted);
							$zip->addFromString($dirname.'/'.$file,$decrypted);
			
							// Terminate decryption handle and close module 
							mcrypt_generic_deinit($td);
							mcrypt_module_close($td);
							fclose($file_handler);
						}		
						
					}
				}
				$zip->close();
			}
			else {
				echo 'Impossible d&#039;ouvrir &quot;all_files_list.zip&quot;';
			}
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
?>
