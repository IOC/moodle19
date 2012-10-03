YAHOO.util.Event.onDOMReady(function(){
    YAHOO.util.Dom.setStyle(YAHOO.util.Dom.getElementsByClassName('overview-loading'), 'display', 'inline');
    var handleSuccess = function(response) {
        var Y = YAHOO.util.Dom;
        if (response.responseText){
            var node = Y.get('course-list');
            if (node){
                node.innerHTML = response.responseText;
            }
            var nodes = Y.getElementsByClassName('course-overview');
            Y.setStyle(nodes, 'display', 'none');
            nodes = Y.getElementsByClassName('overview-link');
            YAHOO.util.Event.addListener(nodes, 'click', function(e) {
                e.preventDefault();
                node = Y.get(/^(.*)-link/.exec(this.id))[1];
                if (Y.getStyle(node, 'display') !== 'none'){
                    Y.setStyle(node, 'display', 'none');
                }else{
                    var siblings = Y.getChildrenBy(node.parentNode, function(el){return el.className == 'course-overview';});
                    for (i in siblings){
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
    YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success:handleSuccess}, "overview=1&time="+new Date().getTime());
});

var getremotecourses = function(){
    YAHOO.util.Event.onDOMReady(function(){
        setTimeout ( "checkajaxtimeout()", 30000 );
        YAHOO.util.Get.script(M_config['url']+"?hostid="+M_config['hostid']+"&wantsurl="+M_config['wantsurl']+"?callback=myremotecoursescallback");
    });
};

var checkajaxtimeout = function(){
    if (YAHOO.util.Selector.query('#rcourse-list .overview-loading').length > 0){
        var node = '<span class="remote-error">'+M_config['timeouterror']+'</span>';
        YAHOO.util.Dom.getElementsByClassName('rcourses')[0].innerHTML = node;
    }
}

var myremotecoursescallback = function(html){
    var Y = YAHOO.util.Dom;
    if (html){
        var hidden = Y.getElementsByClassName('myhidden');
        if (hidden){
            Y.removeClass(hidden[0], 'myhidden');
        }
        Y.getElementsByClassName('rcourses')[0].innerHTML = html;
        var nodes = Y.getElementsByClassName('rcourse-overview');
        Y.setStyle(nodes, 'display', 'none');
        nodes = Y.getElementsByClassName('roverview-link');
        YAHOO.util.Event.addListener(nodes, 'click', function(e) {
            e.preventDefault();
            var node = Y.get(/^(.*)-link/.exec(this.id))[1];
            if (Y.getStyle(node, 'display') !== 'none'){
                Y.setStyle(node, 'display', 'none');
            }else{
                var siblings = Y.getChildrenBy(node.parentNode, function(el){return el.className == 'rcourse-overview';});
                for (i in siblings){
                    if (siblings[i] !== node){
                        Y.setStyle(siblings[i], 'display', 'none');
                    }
                }
                Y.setStyle(node, 'display', 'block');
            }
        });
    }else{
        var node = '<span class="remote-error">'+M_config['nocourseserror']+'</span>';
        Y.getElementsByClassName('rcourses')[0].innerHTML = node;
    }
};
