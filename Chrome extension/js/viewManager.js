/* Le ViewManager détecte si l'utilisateur utilise l'ancienne ou le nouveau Gmail
 * Une fois qu'il découvert, il instance 
 * La vue appropriée (regularView ou updatedVoir). 
 */
var ViewManager = function () {
    if (arguments.callee.singleton_instance) {
        return arguments.callee.singleton_instance;
    }
	var service_enabled = 'true';
	
    arguments.callee.singleton_instance = new (function () {
        var observerCallbacks = [];
        var cloudy_view;
        var mytimer;
        var enabled;

        this.addViewObserver = function (fn) {
            observerCallbacks.push(fn);
        }

        this.getView = function(){
            return cloudy_view;
        }

		 /* Recherchez un élément nommé «sujet». S'il est trouvé, alors une fenêtre de composition d'e-mail 
          * doit être ouverte. Si un élément DOM avec le nom "from" existe, et il est de 
          * type "input", alors l'utilisateur est sur le nouveau Gmail. 
          * Sinon, il est sur l'ancien l'ancienne. 
          */
        var checkCompose = function() {
            // #selector
            if (enabled && document.getElementsByName("subject").length && service_enabled === "true") {
                if (!document.getElementsByClassName("I5").length) {
                    // Gmail's old interface
                    cloudy_view = new RegularView();
                } else {
                    // Gmail's new interface
                    cloudy_view = new UpdatedView();
                }
                for (var i = 0; i < observerCallbacks.length; i++) {
                    cloudy_view.addObserver(observerCallbacks[i]);
                }
                clearInterval(mytimer);
                enabled = false;
            }
        }

        var init = function() {
            // check for promo bubble, display it if loaded
            var notification_bubble = $jQcl("#cloudy_bubble");
            if (notification_bubble && notification_bubble.length > 0) {
                console.log("Showing notification");

                /* why does this not work? */
                //notification_bubble.show(); 
                notification_bubble.css("display", "block");
                notification_bubble.delay(1500).fadeTo(1000, 1, 
                        function() {
                    var cloudy_events = 
                        document.getElementById("cloudy_events");
                    if (cloudy_events) {
                        var e = document.createEvent("Events");
                        e.initEvent("cloudy_notificationDisplayed", 
                            false, true);
                        cloudy_events.dispatchEvent(e);
                    }
                });
                $jQcl("#cloudy_bubble_close").click(function(){
                    notification_bubble.fadeTo(600, 0, function(){
                        notification_bubble.hide();
                        //notification_bubble.parentNode.removeChild(
                        //    notification_bubble);
                    });
                });
            }

            // start timer
            mytimer = setInterval(checkCompose, 500);
            enabled = true;
        }

        init.call(this);
        return this;
    })();

    return arguments.callee.singleton_instance;
}
