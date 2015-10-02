<!DOCTYPE html>
<html>
<head>
<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<![endif]-->
<?php 
require("include_languages.php");
require("/etc/upload.conf");
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
	$frommail = htmlspecialchars($_GET['fm']);
	$folder = @$_GET['folder'];
?>
<meta charset="utf-8">
<meta http-equiv="Pragma" content="no-cache">
<title><?php echo $conf['title']; ?> - File uploading</title>
<meta name="description" content="File Upload widget with multiple file selection, drag&amp;drop support, progress bars, validation and preview images, audio and video for jQuery. Supports cross-domain, chunked and resumable file uploads and client-side image resizing. Works with any server-side platform (PHP, Python, Ruby on Rails, Java, Node.js, Go etc.) that supports standard HTML form file uploads.">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<link rel="icon" href="favicon.ico" type="image/x-icon"/>
<!-- Bootstrap styles -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<!-- Generic page styles -->
<link rel="stylesheet" href="css/style.css">
<!-- blueimp Gallery styles -->
<link rel="stylesheet" href="css/blueimp-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="css/jquery.fileupload.css">
<link rel="stylesheet" href="css/jquery.fileupload-ui.css">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="css/jquery.fileupload-noscript.css"></noscript>
<noscript><link rel="stylesheet" href="css/jquery.fileupload-ui-noscript.css"></noscript>
<style>
#header{display:block;margin-top:10px;margin-bottom:10px;height:100px;}
.left{float:left;display:inline-block;width:50%;margin-bottom:30px;}
.right{float:right;display:inline-block;width:50%;margin-bottom:50px;}
input[type="text"]{width:90%;color: #000000; font-family: Verdana; font-weight: bold; font-size: 14px; background-color: #F8FFF5;}
#message{color: #000000; font-family: Verdana; font-weight: bold; font-size: 14px; background-color: #F8FFF5;}
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
<?php
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
?>
<?php
function createRandomName2() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;
	$name = '';
    while ($i <= 31) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $name = $name.$tmp;
        $i++;
    }
    return $name;
}
?>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
           <!-- <span class="preview"></span> -->
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size"><?php echo $vocables["$lang"]["interne_17"]; ?>...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span><?php echo $vocables["$lang"]["interne_18"]; ?></span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span><?php echo $vocables["$lang"]["interne_19"]; ?></span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview"> 
                {% if (file.thumbnailUrl) { %}
               <!--     <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a> -->
                {% } %} 
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}          
              <div><span class="label label-danger"><?php echo $vocables["$lang"]["interne_20"]; ?></span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span><?php echo $vocables["$lang"]["interne_6"]; ?></span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span><?php echo $vocables["$lang"]["interne_19"]; ?></span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<script src="js/jquery.min.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<link rel=stylesheet href="js/jquery.ui/jquery.ui.all.css" type="text/css">
	<script language="javascript" src="js/jquery-ui.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="js/load-image.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="js/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="js/bootstrap.min.js"></script>
<!-- blueimp Gallery script -->
<script src="js/jquery.blueimp-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<!-- <script src="js/jquery.fileupload-image.js"></script> -->
<!-- The File Upload audio preview plugin -->
<!-- <script src="js/jquery.fileupload-audio.js"></script> -->
<!-- The File Upload video preview plugin -->
<!-- <script src="js/jquery.fileupload-video.js"></script> -->
<!-- The File Upload validation plugin -->
<script src="js/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="js/jquery.fileupload-ui.js"></script>
<!-- The main application script -->
<script src="js/main.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
<script src="js/cors/jquery.xdr-transport.js"></script>
<![endif]-->
<script>
$(document).ready(function(){
	$('#here').click(function() {
		$('#options').fadeToggle();
	});
	$('#check_message').click(function () {
		$('#message_div').fadeToggle();
	});
	$('#submit_info').click(function(){
		var folder = $("#folder").val();
		var file = $("#name").val();
		var notify = $("#uploademail").is(':checked');
		var only = $("#onlydownload").is(':checked');
		var every = $("#everynotify").is(':checked');
		var message = $("#message").val();
		var frommail = '<?php echo $frommail?>';
		var data = 'f='+ folder +'&fm='+ frommail +'&noti='+ notify +'&only='+ only +'&l=<?php echo $lang; ?>'+'&m='+ message + '&every=' + every;
		$.ajax({
			type: "POST",
			url: "send.php",
			data: data,
			success: function(html){
				<?php if($folder != "") { ?>
					$('#fileupload').fadeOut();
					$('#bottom').fadeOut();
					$('#reupload').fadeIn();
				<?php }else{ ?>
					$('#fileupload').fadeOut();
					$('#bottom').fadeOut();
					 $('#result').val(html);
					var insert = $('#result').val();
					$('#end').fadeIn();
					parent.postMessage("link|"+insert, "*");
				<?php } ?>
			}		
		});
	});
	<?php if($folder == "") { ?>
		$('#insert').click(function() {
			var insert = $('#result').val();
			parent.postMessage("link|"+insert, "*");
		});
	<?php } ?>
});
$(window).load(function() {
	$('#lock_container').fadeOut(function(){
		$('.container').fadeIn();
	});
});
</script>
<script>
(function() {
    $('#fileupload').fileupload({
		autoUpload:<?php echo $conf['autoupload']; ?>,
    });
})();
</script>
</head>
<body style="padding-top:30px;">
	<div id="lock_container" style="margin-top:30px;">
		<center>
			<img alt="Lock" width="220" src="img/lock.png" border=0/>
			<H1>Securing Connection</H1>
		</center>
	</div>
<div class="container" style="display:none;">
	<div id="header" style="width:100%;text-align: center;margin-top:0px;">
		<img alt="Elonet" width="220" src="<?php echo $conf['img_for_email']?>" border=0/><br/>
		<font size=4 face="Verdana" style="margin-left:15px;"><?php echo $vocables["$lang"]["attachment_title"]; ?></font>
		<a href='../help.php' target="_blank"><img alt="help" id="help" src="img/inter.jpg" width="32px" border=0 style="cursor:pointer;float:right;margin-top:-40px;"/></a>
	</div>
	<?php if(!isset($_GET['folder'])){ ?>
		<center>
			<i><?php echo $vocables["$lang"]["attachment_1"]; ?></i>
		</center>
	<?php } ?>
	<div class="line"></div>
    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload" method="POST" enctype="multipart/form-data">
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript><input type="hidden" name="redirect" value="http://blueimp.github.io/jQuery-File-Upload/"></noscript>
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span><?php echo $vocables["$lang"]["attachment_2"]; ?>...</span>
                    <input type="file" name="files[]" multiple>
                </span>
<!--               <button type="submit" class="btn btn-primary start">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start upload</span>
                </button>-->
                <button type="reset" id="cancel_button" class="btn btn-warning cancel"  style="display:none;">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span><?php echo $vocables["$lang"]["interne_5"]; ?></span>
                </button>
                <button type="button" id="delete_button" class="btn btn-danger delete" style="display:none;">
                    <i class="glyphicon glyphicon-trash"></i>
                    <span><?php echo $vocables["$lang"]["interne_6"]; ?></span>
                </button>
				<input  type="hidden" name="folder" id="folder" value="<?php if($folder != ""){
																				echo $folder;
																			}
																			else{
																				echo createRandomName(); 
																			}?>"/>
<?php

		/* Create the IV and determine the keysize length, use MCRYPT_RAND
		* on Windows instead */
		$iv = createRandomName2();

		/* Create key from iv*/
		$key = createRandomName2();
?>
			<input  type="hidden" name="iv" id="iv" value="<?php echo $iv; ?>"/>
			<input  type="hidden" name="key" id="key" value="<?php echo $key; ?>"/>
                <input type="checkbox" class="toggle" style="display:none;">
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
    </form>
    <br/>
	<div id="bottom">
		<div class="line"></div>
		<p style="width: 100%;">
			<b><?php echo $vocables["$lang"]["attachment_3"]; ?></b><br/>
			<font size="2" id="here" style="cursor:pointer;"><a href="#"><?php echo $vocables["$lang"]["attachment_4"]; ?></a></font><br/>
		</p>
		<div style="display:none;" id="options">
			<input type="checkbox" name="uploademail" id="uploademail"/> <?php echo $vocables["$lang"]["interne_13"]; ?>
			<br/>
			<input type="checkbox" name="onlydownload" id="onlydownload"/> <?php echo $vocables["$lang"]["interne_14"]; ?>
			<br/>
			<input type="checkbox" name="everynotify" id="everynotify" /> <?php echo $vocables["$lang"]["attachment_option"]; ?>
			<br/>
			<input type="checkbox" name="check_message" id="check_message"/> <?php echo $vocables["$lang"]["interne_12"]; ?><br/>
			<br/>
			<div style="display:none;" id="message_div">
				<textarea name="message" id="message" style="width:100%" rows=4 ></textarea>
			</div>
		</div>
		<div class="line"></div>
		<center>
			<?php if($folder != ""){?>
				<input type="button" class="btn btn-primary" value="<?php echo $vocables["$lang"]["upload_redirect_button"].htmlspecialchars($_GET['fm'])?>" id="submit_info" name="submit_info" style="display:none;"/>
			<?php } else {?>
				<input type="button" class="btn btn-primary" value="<?php echo $vocables["$lang"]["attachment_8"]; ?>" id="submit_info" name="submit_info" style="display:none;"/>
			<?php }?>
		</center>	
	</div>
	<div id="end" style="display:none;">
		<center>
			<p style="margin: 10px 0 10px 0;"><?php echo $vocables["$lang"]["attachment_5"]; ?></p>
			<br/>
			<input type="text" name="result" id="result" style="width:100%;font-size:10px;font-weight:normal;" value=""/>
			<br/>
			<p style="margin: 10px 0 10px 0;"><?php echo $vocables["$lang"]["attachment_6"]; ?><br/>(Ctrl+C & Ctrl+V)<br/><?php echo $vocables["$lang"]["attachment_7"]; ?><br/>(<img src="img/apple.png" width="16px" style="margin-top: -4px;">+C & <img src="img/apple.png" width="16px" style="margin-top: -4px;">+V)</p>
			<br/>
			<input type="button" class="btn btn-primary" value="Insérer automatiquement" id="insert" name="insert">
		</center>
	</div>
	<div id="reupload" style="display:none;">
		<center>
			<p style="margin: 10px 0 10px 0;"><?php echo $vocables["$lang"]["attachment_9"].$frommail.$vocables["$lang"]["attachment_10"]; ?></p>
		</center>
	</div>
</form>
</div>
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


</body> 
</html>
