Gmailr.debug = true; // Tourne message d'erreur en verbeux
/* Retrieve data passed by content script.
*/

/*var getBootstrapData = function(id) {
    return document.getElementById(id + "_gmailr_data").getAttribute('data-val');
}
var service_enabled = document.getBootstrapData("elonet_service");*/


var service_enabled = document.getElementById("elonet_service_gmailr_data").getAttribute('data-val');

Gmailr.init(function(G) {
	
    G.insertCss(getData('css_path'));
    var head= document.getElementsByTagName('head')[0];
	//ici pour l'option enabled/disabled

   //if (service_enabled == "true") {

		var controller = new Controller();
	//}
	//else {}
});
