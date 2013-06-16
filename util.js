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
    data ["i18n"] = currentData ["i18n"];
    currentData = data;
    var result = templatePage (data);
    document.title = data.title;
    $(".container").html (result);
    
    if ($.cookie('toolbar') == 1) $("#tool").show ();
    
    ajaxifyLinks ();
    
    $(".headright").click(function(){
        if ($("#tool").is(":hidden")) {
            $("#tool").slideDown("slow");
            $.cookie('toolbar', '1');
        } else {
            $("#tool").slideUp();
            $.removeCookie('toolbar');
        }
    });
    
    $(".fancydetail").click(function(event){
        event.preventDefault(); 
        var url = $(this).attr("href");
        var jsonurl = url.replace ("bookdetail", "getJSON");
        $.getJSON(jsonurl, function(data) {
            var detail = templateBookDetail (data);
            $.fancybox( {
                content: detail,
                autoSize: true
            });
        });
    });

}

function ajaxifyLinks () {
    if (history.pushState) {
        $("a[href^='index']").click (function (event) {
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