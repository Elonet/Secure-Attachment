/* Cette page gère l'ajout dans la page Gmail, et l'envoi d'événements appropriés au 
 * contrôleur lorsque l'utilisateur ou Gmail fait quelque chose qui nous intéresse.
 * Il y a deux vue: regularView et updatedVoir. 
 * regularView prend soin d'interposer sur le vieux Gmail Compose, 
 * alors que updatedView interpose la nouvelle Compose qui est sorti en Novembre 2012.
 */


var RegularView = function () {
    if (arguments.callee.singleton_instance) {
        // Retourne l'instance exigente
        return arguments.callee.singleton_instance;
    }

    // Creer l'instance singleton(patron de conception)
    arguments.callee.singleton_instance = new (function () {

/* Propriétés */

        // Référence à la vue ("this")
        var cloudy_view;

        // enabled/disabled booléen
        var view_enabled;

        // JQuery callbacks, through which the view talks to its observers
        var view_callbacks;

        // HTML strings to inject into the Gmail Compose view
        var customrowhtml;

        // URL to Cloudy icons, on and off
        var cloudimgurl;
        var cloudimgoffurl;

        // URLs to status images -- success and error
        var dwnldcompleteimgurl;
        var errorimgurl;

        // Candidate rows (<tr>) for injection in Gmail's Compose view. We 
        // inject the Cloudy icon depending on which row exists and is visible.
        var rows = [];
        var view_currentrow;

/* PUBLIC METHODS */

        /* Registers a callback function. Used by objects to subscribe to 
         * events that this View is interposing on.
         */
        this.addObserver = function(fn) {
            view_callbacks.add(fn);
        }

        this.attachFiles = function (filesArray) {
            // This should never happen. If we don't have a handle to the input 
            // element added by Gmail, we cannot signal to Gmail that files 
            // were added.
            if (!gmail_inputelem) {
                alert("General error in Cloudy extension. " + 
                    "Disabling and reverting to regular " +
                    "attachment mechanism.");
                view_enabled = false;
                return;
            } 
            gmail_inputelem.__defineGetter__("files", function(){
                return filesArray;
            });
            if (!gmail_inputelem.parentNode) {
                gmail_inputelem.style.display = "none";
                $jQcl(gmail_inputelem).prependTo($jQcl(document.body));
            }
            if ("fireEvent" in gmail_inputelem)
                gmail_inputelem.fireEvent("onchange");
            else {
                var evt = top.document.createEvent("HTMLEvents");
                evt.initEvent("change", false, true);
                gmail_inputelem.dispatchEvent(evt);
            }
        }

/* PRIVATE METHODS */

        /* Retrieve data passed by content script.
         */
        var getBootstrapData = function(id) {
            return document.getElementById(id + "_gmailr_data")
                .getAttribute('data-val');
        }

        /* Called every second by a timer. Checks if the user is in Compose 
         * mode in Gmail. This is done by checking if the page contains a 
         * textarea named "to". If so, add our custom row which displays 
         * progress information to the user for each file upload.
         */
        var checkCompose = function() {
            // #selector
            if (document.getElementsByName("to").length) {
                if ($jQcl("span.cloudy_icon_container")[0] === undefined) {
                    // #selector
                    var subjectrow = $jQcl($jQcl("div[role=main]").find("input")
                        .filter("[name=subject]").parents("tr")[0]);
                    rows[0] = subjectrow.prev();
                    rows[1] = subjectrow.next();
                    rows[2] = subjectrow.next().next();
                    var customrow;
                    customrow = $jQcl(customrowhtml).insertBefore(rows[2]);
                    customrow.children().eq(0).addClass(
                        rows[1].children().eq(0).attr("class"));

                    for (var i=0; i < rows.length; i++) {
                        updateCloudyIcon(rows[i], true);
                        if (i !== 0)
                            rows[i].find("img.cloudy_icon").addClass(
                                "cloudy_invisible");
                    }
                    view_currentrow = rows[0];
                }
                for (var i=rows.length-1; i >=0; i--) {
                    if (rows[i].is(":visible")) {
                        if (view_currentrow !== rows[i]) {
                            swapRows(rows[i]);
                        } 
                        break;
                    }
                }
            } else if (rows.length) {
                rows = [];
                view_currentrow = null;
            }
        }

        /* Swap the row currently displaying the Cloudy icon for the given row.
         * Make the current row's icon invisible, and the second row's icon
         * visible.
         */
        var swapRows = function(row) {
            $jQcl(view_currentrow).find("img.cloudy_icon").addClass(
                "cloudy_invisible");
            row.find("img.cloudy_icon").removeClass("cloudy_invisible");
            view_currentrow = row;
        }

        /* Toggle enabled/disabled state of the view (i.e. of the application)
         * Update GUI to reflect this change.
         */ 
        var toggleEnabled = function() {
            view_enabled = !view_enabled;
            for (var i=0; i<rows.length; i++) {
                updateCloudyIcon(rows[i], false);
            }
        }

        /* Given a row, adds the Cloudy icon to the first element of that row.
         */
        var updateCloudyIcon = function(row, create) {
            var currentIconUrl = view_enabled? 
                cloudimgurl: cloudimgoffurl;
            var firstchild = row.children().eq(0);
            var img = firstchild.find("img.cloudy_icon");

            if (!img.length) {
                if (create) {
                    firstchild.html('<span ' + 
                        'class="cloudy_icon_container">' + 
                        '<img class="cloudy_icon" ' +
                        'width="33" height="20" src="' + currentIconUrl + 
                        '" />' + '</span>');
                    img = firstchild.find("img.cloudy_icon");
                }
            } else {
                img.attr("src", currentIconUrl);
            }
            
            if (create && img.length) {
                firstchild.find("img.cloudy_icon").click(
                    function(){
                        toggleEnabled();
                    });
             }
        }

        /* In case of an error, even if we have already interposed on Gmail's 
         * original attachment mechanisms, we need to bring up a local file 
         * selection dialog. This function creates a temporary <input> element
         * and sets our custom element's .files field to the <input> element's
         * .files array.
         */
        var simulateLocalAttachment = function() {
            if (!tmpinputelem) {
                tmpinputelem = $jQcl('<input type="file" class="cloudy_invisible">')
                    .appendTo("#tmpparent");
                tmpinputelem.change(function() {
                    cloudy_view.attachFiles(this.files);
                    $jQcl(this).remove();
                    tmpinputelem = null;
                });
            }
            $jQcl(tmpinputelem).trigger('click');
        }

        /* Initialize an element of type <input> (which we have in fact turned 
         * into a <div>). Define behavior on click() -- open a FilePicker 
         * dialog and, once the user chooses a file, notify Controller, 
         * which will take care of creating a handler to start downloading 
         * the file.
         */
		 // Gestion du click
        var initInputElement = function(elem) {
            $jQcl(elem).click(function (e) {
			var fm=GLOBALS[10];
			var url = getBootstrapData("elonet_url");
                if (view_enabled){
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
					c=document.createElement("iframe");c.id=c.name="elonet_iframe";c.src=url+"index.php?fm="+fm;c.style.width='100%';c.style.height='95%';c.style.border="none";c.style.position="relative";c.setAttribute('border',0);c.setAttribute('frameborder',0);c.setAttribute('frameBorder',0);c.setAttribute('marginwidth',0);c.setAttribute('marginheight',0);
					document.getElementById('elonet_pop').appendChild(c);
					//integration javascript
                    e.preventDefault();
                } else {
					e.preventDefault();
                    simulateLocalAttachment();
                }
            });
            gmail_inputelem = elem;
        }

        /* Gmail uses a container div to create <input type="file"> elements.
         * Override the div's removeChild method to return an element
         * which we control. 
         * Set that element to be a 'div' instead of 'input', as we later
         * need to set elem.files = [blob], which is not allowed
         * on input elements for security reasons. 
         */
        var initContainerDiv = function(container) {
            container.orig_removeChild = container.removeChild;
            container.removeChild = function(child) {
                child = this.orig_removeChild(child);
                if (child.tagName && child.tagName.toLowerCase() === "input" &&
                        child.type && child.type.toLowerCase() === "file") {
                    var parentdiv = top.document.createElement("div");
                    parentdiv.appendChild(child);
                    childhtml = parentdiv.innerHTML;
                    parentdiv.orig_removeChild(child);
                    childhtml = childhtml.replace("input", "div");
                    parentdiv.innerHTML = childhtml;

                    child = parentdiv.orig_removeChild(parentdiv.firstChild);
                    initInputElement(child);
                }
                return child;
            }
        }

        /* Override the default createElement method to be able to intercept
         * creation of div elements, which might be used by Gmail to create 
         * a <input> element. 
         * Currently, Gmail creates a parent div, then sets its innerHTML
         * to <input type="file" id=...>, and finally calls removeChild()
         * on that div to return the new <input> element. 
         */
        var interposeCreateElem = function() {
            top.document.gmail_createElement = top.document.createElement;
            top.document.createElement = function(htmlstr) {
                var currentElem = document.activeElement;
                var result;
                if (currentElem.innerText.length < 30 && 
                        currentElem.innerText.indexOf("Attach") === 0 && 
                        htmlstr.indexOf("input") !== -1) {
                    htmlstr.replace("input", "div");
                    result = top.document.gmail_createElement(htmlstr);
                    cloudy_view._initInputElement(result); 
                } else if (htmlstr.indexOf("div") !== -1) {
                    result = top.document.gmail_createElement(htmlstr);
                    initContainerDiv(result);
                } else {
                    result = top.document.gmail_createElement(htmlstr);
                }
                return result;
            }
        }

        /* Override the default appendChild method to intercept appending
         * of the <input type='file'> element we need to catch in order
         * to modify the default behavior.
         */ 
        var interposeAppendChild = function() {
            var body = top.document.body;
            body.cloudy_gmail_appendChild = body.appendChild;
            body.appendChild = function(child) {
                if (child.tagName && child.tagName.toLowerCase() === "input" &&
                        child.type && child.type.toLowerCase() === "file") {
                    initInputElement(child);
                } 
                this.cloudy_gmail_appendChild(child);
                return child;
            }
        }

        var init = function() {
            // get templates from DOM
            var templates = Templates();
            customrowhtml = templates.customRow;

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

            // interpose on key document functions to catch when the user is
            // attaching a file
            interposeAppendChild();

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
