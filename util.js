var template, result;

function htmlEscape(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
}

function navigateTo (url) {
    jsonurl = url.replace ("index", "getJSON");
    $.getJSON(jsonurl, function(data) {
        history.pushState(data, "", url);
        updatePage (data);
    });
}

function updatePage (data) {
    result = template (data);
    document.title = data.title;
    $(".container").html (result);
    
    ajaxifyLinks ();
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