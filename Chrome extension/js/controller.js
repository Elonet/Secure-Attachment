/* Petite classe de contrôleur pour le plaisir d'avoir le 
 * modèle et la vue sait pas sur l'autre. Ne pas faire grand
 * chose à part le collage des deux éther.
 */

var Controller = function () {
    if (arguments.callee.singleton_instance) {
        return arguments.callee.singleton_instance;
    }

    arguments.callee.singleton_instance = new (function () {
        var controller;
        var viewManager;

        var init = function() {
            var templates = Templates();

            viewManager = new ViewManager();
            controller = this;
            viewManager.addViewObserver(function() {
                processViewEvent.apply(controller, arguments)
            });
        }

        init.call(this);
        return this;
    })();

    return arguments.callee.singleton_instance;
}

