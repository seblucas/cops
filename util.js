var templatePage, templateBookDetail, templateMain, currentData;

var isEink = /Kobo|Kindle|EBRD1101/i.test(navigator.userAgent);

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

function getCurrentOption (option) {
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
    var jsonurl = url.replace ("index", "getJSON");
    $.getJSON(jsonurl, function(data) {
        history.pushState(data, "", url);
        updatePage (data);
    });
}

function updatePage (data) {
    data ["const"] = currentData ["const"];
    currentData = data;
    var result = templatePage (data);
    document.title = data.title;
    $("body").html (result);
    
    if ($.cookie('toolbar') == 1) $("#tool").show ();
    
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
            var url = $(this).attr("href");
            var jsonurl = url.replace ("index", "getJSON");
            $.getJSON(jsonurl, function(data) {
                data ["const"] = currentData ["const"];
                var detail = templateBookDetail (data);
                var fancyparams = fancyBoxObject (data.title, null);
                fancyparams ["content"] = detail;
                $.fancybox(fancyparams);
            });
        });
        
        $(".fancycover").fancybox(fancyBoxObject (null, 'image'));
            
        $(".fancyabout").fancybox(fancyBoxObject ('COPS ' + currentData.version, 'ajax'));
    }
    
    $("#searchForm").submit (function (event) {
        event.preventDefault(); 
        
        var url = strformat ("index.php?page=9&current={0}&query={1}&db={2}", currentData.page, $("input[name=query]").val (), currentData.databaseId);
        navigateTo (url);
    });

}

function ajaxifyLinks () {
    if (history.pushState) {
        var links = $("a[href^='index']");
        if (getCurrentOption ("use_fancyapps") == 1) links = links.not (".fancydetail");
        links.click (function (event) {
            event.preventDefault(); 

            var url = $(this).attr('href');
            navigateTo (url);
        });
    }
}

window.onpopstate = function(event) {
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