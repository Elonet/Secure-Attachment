var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
var eventer = window[eventMethod];
var messageEvent = eventMethod == 'attachEvent' ? 'onmessage' : 'message';
var old_var = '';
eventer(messageEvent, function (e, old_var) {
    var new_var = e.data;
    var link = e.data.split('|');
    if (link[0] == 'link') {
		parent.postMessage(e.data, "*");
    }
}, false);
var check = location.hash.substring(1);
document.getElementById("child").src = check;

