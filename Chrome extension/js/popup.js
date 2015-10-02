/* Javascript du pop-up de configuration et d'explication */
var storage;

// Sauvegarde des options en local
function save_options() {
    storage.set({
					"service": service_box.checked,
					"url": url_input.value,
                },
                function (){
                    var status = $("#status");
                    if (chrome.runtime.lastError) {
                        // Mettre à jour le statut d'erreur.
                        status.html("<strong>An error occurred while " +
                            "saving.</strong> Please try again.");
                        status.addClass("alert-error");
                        status.show();
                    } else {
                        // Mettre à jour le statut de laisser l'utilisateur sait paramètres enregistrés.
						var userLang = navigator.language || navigator.userLanguage; 
						if( userLang == "fr" ){
							status.html("Vos changements ont &eacute;t&eacute; enregistr&eacute;s avec succ&egrave;s.");
						} else {
							status.html("Your changes were saved successfully.");
						}
                        status.addClass("alert-success");
                        status.show().delay(1000).fadeOut(800, function() {
                            $(this).removeClass("alert-success");
                        });
                    }

                    $("#save").removeClass("disabled");
                });
}

// Restaure les options sauvegardés en local.
function restore_options() {
	
	service_box.checked = false;
	url_input.value = "https://upload.elonet.fr:8443/attachment/";
	
    storage.get("service", function(items) {
        if (typeof items.service === "undefined") {
            storage.set({ "service": false });
        } else {
            service_box.checked = items.service;
        }
    });
    
    storage.get("url", function(items) {
        if (typeof items.url === "undefined") {
            storage.set({ "url": "https://upload.elonet.fr:8443/attachment/" });
        } else {
            url_input.value = items.url;
        }
    });


    $('.sortable.connected').sortable("destroy");
    $('.sortable.connected').sortable({
        connectWith: '.connected'
    });
}

$(document).ready(function() {
	var userLang = navigator.language || navigator.userLanguage; 
	if( userLang == "fr" ){
		$("#left").append("<p style='text-align:justify;font-size:0.9em;'>Cette application fournit une connexion s&eacute;curis&eacute;e pour h&eacute;berger vos fichiers partag&eacute;s, au sein de votre propre entreprise. Appuyez sur le bouton de verrouillage au bas de votre e-mail, s&eacute;lectionnez les fichiers que vous souhaitez partager, puis cliquez sur \"ins&eacute;rer un lien dans mon email\". Vos contacts devront s&apos;identifier par email chaque fois qu'ils acc&egrave;deront au lien de t&eacute;l&eacute;chargement.</p>");
		$("#URL").append("<i>Adresse du serveur d&apos;&eacute;change :</i>");
	}
	else{
		$("#left").append("<p style='text-align:justify;font-size:0.9em;'>This software provides a secure connection to store your shared files within your own company. Hit the lock button at the bottom of your email, select the files you want to share, and click 'insert a link in my email'. Your contacts will need to identify themselves by email each time they access the download link.</p>");
		$("#URL").append("<i>URL upload server :</i>");

	}
    // Globale variable
    storage = chrome.storage.sync;
    
	service_box = document.getElementById("elonet_service");
	url_input = document.getElementById("elonet_url");
	
	restore_options();
    $('.sortable.connected').sortable({
        connectWith: '.connected'
    });

	$('#elonet_url').focusin(function () {
		$('#save').fadeIn(2000);
	});
	d=document.createElement("a");d.id='close';d.appendChild(document.createTextNode('\u00D7'));d.style.cssFloat="right";d.style.styleFloat="right";d.style.cursor="pointer";d.style.margin='2px -12px 0 0';d.style.fontSize='1.5em';d.style.color='#555555';d.style.textDecoration='none';
	document.getElementById('top').insertBefore(d,document.getElementById('top').firstChild);
	$("#close").click(function () {
		window.close();
	});
	
    // Sauvegarde des options
    $("#save").click(function(e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }
        $(this).addClass("disabled");
        $("#status").stop().hide().removeClass("alert-error");
        save_options();
		$('#save').fadeOut(2000);
        
    });
    
   
});
