/* Cette page gère l'ajout dans la page Gmail, et l'envoi d'événements appropriés au 
 * contrôleur lorsque l'utilisateur ou Gmail fait quelque chose qui nous intéresse.
 * Il y a deux vue: regularView et updatedVoir. 
 * regularView prend soin d'interposer sur le vieux Gmail Compose, 
 * alors que updatedView interpose la nouvelle Compose qui est sorti en Novembre 2012.
 */
var UpdatedView = function () {
	if (arguments.callee.singleton_instance) {
		// return existing instance
		return arguments.callee.singleton_instance;
	}

	// create singleton instance
	arguments.callee.singleton_instance = new (function () {

/* PROPERTIES */

		// reference to the view ("this")
		var cloudy_view;

		// enabled/disabled boolean
		var view_enabled;

		// a temporary <input type="file"> element. Used when simulating local
		// attachments (when the users has turned Cloudy off)
		var tmpinputelem;

		// JQuery callbacks, through which the view talks to its observers
		var view_callbacks;

		// URL to Cloudy icons, on and off
		var cloudimgurl;
		var cloudimgoffurl;

		// URLs to status images -- success and error
		var dwnldcompleteimgurl;
		var errorimgurl;

		// in the new Compose, we can have many compose windows open. 
		// This is a map: ID -> compose window. The ID we use is the 
		// DOM id of the "attach" button of the message.
		var composeMessages = {};

/* PUBLIC METHODS */

		/* Registers a callback function. Used by objects to subscribe to 
		 * events that this View is interposing on.
		 */
		this.addObserver = function(fn) {
			view_callbacks.add(fn);
		}

		/* Callers use this function to pass a file to Gmail to be attached. 
		 * With the new-style compose, we need to know which message the file
		 * is being attached to, so we pass the messageID.
		 */
		this.attachFiles = function (filesArray, messageId) {
			// This should never happen. If we don't have a handle to the input 
			// element added by Gmail, we cannot signal to Gmail that files 
			// were added.
			var inputElem = composeMessages[messageId].inputElem;
			if (!inputElem) {
				alert("General error in Cloudy extension. " + 
					"Disabling and reverting to regular " +
					"attachment mechanism.");
				view_enabled = false;
				return;
			} 
			inputElem.__defineGetter__("files", function(){
				return filesArray;
			});

			if (!inputElem.parentNode) {
				// Gmail removes the <input type="file"> element upon detecting 
				// that the user chose a file. If already removed because of 
				// another attachment, we insert the element back into the DOM, 
				// so that we don't get a nullPointer error when Gmail calls 
				// removeChild() on its parent.
				inputElem.style.display = "none";
				$jQcl(inputElem).prependTo($jQcl(document.body));
			}
			if ("fireEvent" in inputElem)
				inputElem.fireEvent("onchange");
			else {
				var evt = top.document.createEvent("HTMLEvents");
				evt.initEvent("change", false, true);
				inputElem.dispatchEvent(evt);
			}
		}

/* PRIVATE METHODS */

		/* Retrieve data passed by content script.
		 */
		var getBootstrapData = function(id) {
			return document.getElementById(id + "_gmailr_data")
				.getAttribute('data-val');
		}
		
		var setBootstrapData = function(id,value){
			document.getElementById(id + "_gmailr_data")
				.setAttribute('data-val',value);
		}

		/* Called every second by a timer. Checks if the user has a new-style 
		 * Compose window open in Gmail. This is done by checking the page 
		 * for textareas named "subject". For each uninitialized such textarea,
		 * we have to remember the new compose message in our composeMessages
		 * dictionary, and override the default attachment icon with Cloudy's
		 * icon.
		 */
		var checkCompose = function() {
			// #selector
			var tofields = $jQcl(document.getElementsByName('subject'));
			var foundUninitialized = false;
			for (var i = 0; i < tofields.length; i++) {
				if (!tofields.eq(i).data("cloudy_initialized")) {
					foundUninitialized = true;
					break;
				}
			}
			if (foundUninitialized) {
				console.log("setting Cloudy icons");
				// #selector
				var attachmentIcons = $jQcl(".a8X.gU > div");
				attachmentIcons.each(function () {
					if (!$jQcl(this).data("cloudy_initialized")) {
					var composeicon = '<div class="wG J-Z-I" command="Elonet" onclick="pop();" tabindex="1" id=":sz" role="button" aria-pressed="false" aria-haspopup="true" aria-expanded="false" style="-webkit-user-select: none;"><div class="J-J5-Ji J-Z-I-Kv-H" style="-webkit-user-select: none;"><div class="J-J5-Ji J-Z-I-J6-H" style="-webkit-user-select: none;"><div id=":sv" class="e5 aaA aMZ" style="-webkit-user-select: none;"><div class="a3I" style="-webkit-user-select: none;">&nbsp;</div></div></div></div></div>';
					$jQcl(this).append(composeicon);
					var attachmentSubIcons = $jQcl("[command='Elonet']").children().children().children();
					//$jQcl(this).css("background-image", "url(" + 
						//	  getData("cloudicon_newcompose_thick_path") + ")");
						attachmentSubIcons.css("cssText", "background: " + 
							"url(" + getData("cloudicon_newcompose_thick_path")+
							") no-repeat 0px 0px / 21px 18px!important");
						attachmentSubIcons.addClass("cloudy_icon_updatedview");
						$jQcl(this).data("cloudy_initialized", true);
					}
				});
				tofields.data("cloudy_initialized", true);
			}
		}

		/* Toggle enabled/disabled state of the view (i.e. of the application)
		 * Update GUI to reflect this change. 
		 * Note: with the new-style Compose, there is no easy way to disable 
		 * Cloudy. The only way it will happen is if Cloudy encounters an error
		 * and disables itself.
		 */ 
		var toggleEnabled = function() {
			view_enabled = !view_enabled;
			/*TODO for (var i=0; i<rows.length; i++) {
				updateCloudyIcon(rows[i], false);
			}*/
		}

		var init = function() {
			// get templates from DOM
			var templates = Templates();

			// get URL to cloud icon -- to add next to "attach a file" link
			cloudimgurl = getData("cloudiconon_path");
			cloudimgoffurl = getData("cloudiconoff_path");
			errorimgurl = getData("erroricon_path");

			// erase data divs passed in DOM
			$jQcl("#filepicker_customrow_template").remove();
			
			// add tmpparent div, used to add elements temporarily to the DOM
			var tmpParentHtml = 
				$jQcl("<div id='tmpparent' style='display: none;'></div>");
			tmpParentHtml.prependTo($jQcl(document.body));

			// initialize callbacks object, so the Controller can bind callbacks 
			// to the View.
			view_callbacks = $jQcl.Callbacks();

			// Check for "Compose" mode every second.
			setInterval(function() {checkCompose()}, 500);

			// set View as enabled. Marking this as false effectively disables 
			// the entire extension, as it will no longer receive any inputs.
			view_enabled = true;

			// set the reference to the view
			cloudy_view = this;
		}

		init.call(this);
		return this;
	})();

	return arguments.callee.singleton_instance;
}
var pop = function() {
	var currentEmail = $jQcl(document.activeElement).children().children().children().eq(0)[0];
	$jQcl(currentEmail).parents(".I5").find("div.editable").addClass("current_elonet");
	var fm=GLOBALS[10];
	var url = getBootstrapData("elonet_url");
	var iframe = getBootstrapData("elonet_iframe");
	//integration div background
	a=document.createElement("div");a.id=a.name="elonet_background";a.style.position='fixed';a.style.top=0;a.style.bottom=0;a.style.right=0;a.style.left=0;a.style.opacity='0.5';a.style.zIndex=10000;a.style.backgroundColor='#000000';a.style.filter='alpha(opacity=50)';a.onclick=function(){document.body.removeChild(a);document.body.removeChild(b);};
	document.body.appendChild(a);
	//integration div englobe
	b=document.createElement("div");b.id='elonet_pop';b.style.position='fixed';b.style.padding="10px";b.style.background='#ffffff';b.style.top='50%';b.style.left='50%';b.style.height='600px';b.style.width='800px';b.style.overflow='hidden';b.style.webkitOverflowScrolling='touch';b.style.border='1px solid #999';b.style.webkitBorderRadius='3px';b.style.borderRadius='3px';b.style.margin='-300px 0 0 -400px';b.style.webkitBoxShadow='0 3px 7px rgba(0, 0, 0, 0.3)';b.style.boxShadow='0 3px 7px rgba(0, 0, 0, 0.3)';b.style.zIndex=10001;b.style.boxSizing="content-box";b.style.webkitBoxSizing="content-box";b.style.mozBoxSizing="content-box";
	document.body.appendChild(b);
	//integration de la croix de fermeture
	d=document.createElement("a");d.id='close';d.appendChild(document.createTextNode('\u00D7'));d.style.cssFloat="right";d.style.styleFloat="right";d.style.cursor="pointer";d.style.padding='0 5px 0 0px';d.style.fontSize='1.5em';d.style.color='#555555';d.style.textDecoration='none';d.onclick=function(){document.body.removeChild(a);document.body.removeChild(b);};
	document.getElementById('elonet_pop').appendChild(d);
	//integration de l'intérieur de la div
	c=document.createElement("iframe");c.id=c.name="elonet_iframe";c.src=iframe+"#"+url+"?fm="+fm;c.style.width='100%';c.style.height='95%';c.style.border="none";c.style.position="relative";c.setAttribute('border',0);c.setAttribute('frameborder',0);c.setAttribute('frameBorder',0);c.setAttribute('marginwidth',0);c.setAttribute('marginheight',0);
	document.getElementById('elonet_pop').appendChild(c);
	//integration javascript
	f=document.createElement("script");
	var s = document.getElementsByTagName('script')[0];
	f.text = "var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';\
	var eventer = window[eventMethod];\
	var messageEvent = eventMethod == 'attachEvent' ? 'onmessage' : 'message';\
	var old_var = '';\
	eventer(messageEvent, function (e, old_var) {\
		var new_var = e.data;\
		var link = e.data.split('|');\
		if (link[0] == 'link') {\
			if (new_var !== old_var) {\
				old_var = new_var;\
				$jQcl('#elonet_background').remove();\
				$jQcl('#elonet_pop').remove();\
				var userLang = navigator.language || navigator.userLanguage; \
				if( userLang == 'fr' ){\
					$jQcl('<div />').html('<br/><a class=\"link\" href=\"' + link[1] + '\">'+ 'Veuillez cliquer ici pour télécharger les fichiers associés à cet email</a><br/><br/>( '+link[2]+')').prependTo('.current_elonet');\
					$jQcl('.current_elonet').removeClass('current_elonet');\
				}\
				else{\
					$jQcl('<div />').html('<br/><a class=\"link\" href=\"' + link[1] + '\">'+ 'Please click on this link to download the files associated to this email</a><br/><br/>( '+link[2]+')').prependTo('.current_elonet');\
					$jQcl('.current_elonet').removeClass('current_elonet');\
				}\
			}\
		}\
	}, false);";
	s.parentNode.insertBefore(f, s);
	document.getElementById('elonet_pop').appendChild(f);
	var options = {};
}
/* Retrieve data passed by content script.
*/
var getBootstrapData = function(id) {
	return document.getElementById(id + "_gmailr_data").getAttribute('data-val');
}

var setBootstrapData = function(id,value){
	document.getElementById(id + "_gmailr_data").setAttribute('data-val',value);
}