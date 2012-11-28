YAHOO.util.Event.onDOMReady(function(){
    YAHOO.util.Dom.setStyle(YAHOO.util.Dom.getElementsByClassName('overview-loading'), 'display', 'inline');
    var handleSuccess = function(response) {
        var Y = YAHOO.util.Dom;
        var i, node, nodes, siblings;
        if (response.responseText){
            node = Y.get('course-list');
            if (node){
                node.innerHTML = response.responseText;
            }
            nodes = Y.getElementsByClassName('course-overview');
            Y.setStyle(nodes, 'display', 'none');
            nodes = Y.getElementsByClassName('overview-link');
            YAHOO.util.Event.addListener(nodes, 'click', function(e) {
                (e.preventDefault) ? e.preventDefault() : e.returnValue = false;
                node = Y.get(/^(.*)-link/.exec(this.id))[1];
                if (Y.getStyle(node, 'display') !== 'none'){
                    Y.setStyle(node, 'display', 'none');
                }else{
                    siblings = Y.getChildrenBy(node.parentNode, function(el) {
                        return el.className == 'course-overview';
                    });
                    for (i in siblings) {
                        if (siblings[i] !== node){
                            Y.setStyle(siblings[i], 'display', 'none');
                        }
                    }
                    Y.setStyle(node, 'display', 'block');
                }
            });
        }else{
            Y.setStyle(Y.getElementsByClassName('overview-loading'), 'display', 'none');
        }
    };
    YAHOO.util.Connect.asyncRequest(
        'GET', 'index.php?overview=1&t=' + new Date().getTime(),
        { success: handleSuccess });
});

var getremotecourses = function(){
    YAHOO.util.Event.onDOMReady(function(){
        YAHOO.util.Get.script(M_config['url']+"?callback=myremotecoursescallback");
    });
};

var myremotecoursescallback = function(obj){
    var Y = YAHOO.util.Dom;
    var i, node, nodes, siblings, hidden;
    if (obj) {
        node = Y.get('rcourse-list-link');
        if (obj.title) {
            node.innerHTML = obj.title;
        }
        if (obj.url) {
            node.href = obj.url;
        }
    }
    if (obj.html && obj.html != '<!--KO-->'){
        hidden = Y.getElementsByClassName('myhidden');
        if (hidden){
            Y.removeClass(hidden, 'myhidden');
        }
        Y.getElementsByClassName('rcourses')[0].innerHTML = obj.html;
        nodes = Y.getElementsByClassName('rcourse-overview');
        Y.setStyle(nodes, 'display', 'none');
        nodes = Y.getElementsByClassName('roverview-link');
        YAHOO.util.Event.addListener(nodes, 'click', function(e) {
            (e.preventDefault) ? e.preventDefault() : e.returnValue = false;
            node = Y.get(/^(.*)-link/.exec(this.id))[1];
            if (Y.getStyle(node, 'display') !== 'none'){
                Y.setStyle(node, 'display', 'none');
            }else{
                siblings = Y.getChildrenBy(node.parentNode, function(el) {
                    return el.className == 'rcourse-overview';
                });
                for (i in siblings) {
                    if (siblings[i] !== node){
                        Y.setStyle(siblings[i], 'display', 'none');
                    }
                }
                Y.setStyle(node, 'display', 'block');
            }
        });
    }
};
