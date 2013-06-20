var templatePage, templateBookDetail, templateMain, currentData, before;

var DEBUG = true;
var isEink = /Kobo|Kindle|EBRD1101/i.test(navigator.userAgent);
var isPushStateEnabled = window.history && window.history.pushState && window.history.replaceState &&
  // pushState isn't reliable on iOS until 5.
  !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);

function debug_log(text) {
    if ( DEBUG ) {
        console.log(text);
    }
}

function elapsed () {
    var elapsed = new Date () - before; 
    return "Elapsed : " + elapsed;
}

function fancyBoxObject (title, type) {
    var out = { prevEffect      : 'none', nextEffect      : 'none' };
    if (isEink) {
        out ["openEffect"] = 'none';
        out ["closeEffect"] = 'none';
        out ["helper"] = { overlay : null };
    }
    if (title) out ["title"] = title;
    if (type) out ["type"] = type;
    return out;
}

function strformat () {
    var s = arguments[0];
    for (var i = 0; i < arguments.length - 1; i++) {
        var reg = new RegExp("\\{" + i + "\\}", "gm");
        s = s.replace(reg, arguments[i + 1]);
    }
    return s;
}

function isDefined(x) {
    var undefined;
    return x !== undefined;
}

function getCurrentOption (option) {
    if (!$.cookie (option)) {
        if (currentData && currentData.const && currentData.const.config && currentData.const.config [option]) {
            return currentData.const.config [option];
        }
    }
    return $.cookie (option);
}

function htmlEscape(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
}

function navigateTo (url) {
    before = new Date ();
    var jsonurl = url.replace ("index", "getJSON");
    $.getJSON(jsonurl, function(data) {
        history.pushState(data, "", url);
        updatePage (data);
    });
}

function updatePage (data) {
    var result;
    data ["const"] = currentData ["const"];
    if (false && $("section").length && currentData.isPaginated == 0 &&  data.isPaginated == 0) {
        // Partial update (for now disabled)
        debug_log ("Partial update");
        result = templateMain (data);
        $("h1").html (data.title);
        $("section").html (result);
    } else {
        // Full update
        result = templatePage (data);
        $("body").html (result);
    }
    document.title = data.title;
    currentData = data;
    
    debug_log (elapsed ());
    
    if ($.cookie('toolbar') == 1) $("#tool").show ();
    if (currentData.containsBook == 1) {
        $("#sortForm").show ();
    } else {
        $("#sortForm").hide ();
    }
    
    ajaxifyLinks ();
    
    $("#sort").click(function(){
        $('.books').sortElements(function(a, b){
            var test = 1;
            if ($("#sortorder").val() == "desc")
            {
                test = -1;
            }
            return $(a).find ("." + $("#sortchoice").val()).text() > $(b).find ("." + $("#sortchoice").val()).text() ? test : -test;
        });
    });
    
    $(".headright").click(function(){
        if ($("#tool").is(":hidden")) {
            $("#tool").slideDown("slow");
            $.cookie('toolbar', '1');
        } else {
            $("#tool").slideUp();
            $.removeCookie('toolbar');
        }
    });
    
    if (getCurrentOption ("use_fancyapps") == 1) {
        $(".fancydetail").click(function(event){
            event.preventDefault(); 
            before = new Date ();
            var url = $(this).attr("href");
            var jsonurl = url.replace ("index", "getJSON");
            $.getJSON(jsonurl, function(data) {
                data ["const"] = currentData ["const"];
                var detail = templateBookDetail (data);
                var fancyparams = fancyBoxObject (data.title, null);
                fancyparams ["content"] = detail;
                $.fancybox(fancyparams);
                debug_log (elapsed ());
            });
        });
        
        $(".fancycover").fancybox(fancyBoxObject (null, 'image'));
            
        $(".fancyabout").fancybox(fancyBoxObject ('COPS ' + currentData.version, 'ajax'));
    }
}

function ajaxifyLinks () {
    if (isPushStateEnabled) {
        var links = $("a[href^='index']");
        if (getCurrentOption ("use_fancyapps") == 1) links = links.not (".fancydetail");
        links.click (function (event) {
            event.preventDefault(); 

            var url = $(this).attr('href');
            navigateTo (url);
        });
        
        $("#searchForm").submit (function (event) {
            event.preventDefault(); 
            
            var url = strformat ("index.php?page=9&current={0}&query={1}&db={2}", currentData.page, $("input[name=query]").val (), currentData.databaseId);
            navigateTo (url);
        });
    }
}

window.onpopstate = function(event) {
    before = new Date ();
    updatePage (event.state);
};

$(document).keydown(function(e){
    if (e.keyCode == 37 && $("#prevLink").length > 0) {
        navigateTo ($("#prevLink").attr('href'));
    }
    if (e.keyCode == 39  && $("#nextLink").length > 0) {
        navigateTo ($("#nextLink").attr('href'));
    }
});