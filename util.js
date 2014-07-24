// util.js
// copyright Sébastien Lucas
// https://github.com/seblucas/cops

/*jshint curly: true, latedef: true, trailing: true, noarg: true, undef: true, browser: true, jquery: true, unused: true, devel: true, loopfunc: true */
/*global LRUCache, doT, Bloodhound, postRefresh */

var templatePage, templateBookDetail, templateMain, templateSuggestion, currentData, before, filterList;

if (typeof LRUCache != 'undefined') {
    var cache = new LRUCache(30);
}

$.ajaxSetup({
    cache: false
});

var copsTypeahead = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    limit: 30,
    remote: {
                url: 'getJSON.php?page=9&search=1&db=%DB&query=%QUERY',
                replace: function (url, query) {
                    if (currentData.multipleDatabase === 1 && currentData.databaseId === "") {
                        return url.replace('%QUERY', query).replace('&db=%DB', "");
                    }
                    return url.replace('%QUERY', query).replace('%DB', currentData.databaseId);
                }
            }
});

copsTypeahead.initialize();

var DEBUG = false;
var isPushStateEnabled = window.history && window.history.pushState && window.history.replaceState &&
  // pushState isn't reliable on iOS until 5.
  !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);

function debug_log(text) {
    if ( DEBUG ) {
        console.log(text);
    }
}

/*exported updateCookie */
function updateCookie (id) {
    if ($(id).prop('pattern') && !$(id).val().match(new RegExp ($(id).prop('pattern')))) {
        return;
    }
    var name = $(id).attr('id');
    var value = $(id).val ();
    $.cookie(name, value, { expires: 365 });
}

/*exported updateCookieFromCheckbox */
function updateCookieFromCheckbox (id) {
    var name = $(id).attr('id');
    if ((/^style/).test (name)) {
        name = "style";
    }
    if ($(id).is(":checked"))
    {
        if ($(id).is(':radio')) {
            $.cookie(name, $(id).val (), { expires: 365 });
        } else {
            $.cookie(name, '1', { expires: 365 });
        }
    }
    else
    {
        $.cookie(name, '0', { expires: 365 });
    }
}

/*exported updateCookieFromCheckboxGroup */
function updateCookieFromCheckboxGroup (id) {
    var name = $(id).attr('name');
    var idBase = name.replace (/\[\]/, "");
    var group = [];
    $(':checkbox[name="' + name + '"]:checked').each (function () {
        var id = $(this).attr("id");
        group.push (id.replace (idBase + "_", ""));
    });
    $.cookie(idBase, group.join (), { expires: 365 });
}


function elapsed () {
    var elapsedTime = new Date () - before;
    return "Elapsed : " + elapsedTime;
}

function retourMail(data) {
    $("#mailButton :first-child").removeClass ("icon-spinner icon-spin").addClass ("icon-envelope");
    alert (data);
}

/*exported sendToMailAddress */
function sendToMailAddress (component, dataid) {
    var email = $.cookie ('email');
    if (!$.cookie ('email')) {
        email = window.prompt (currentData.c.i18n.customizeEmail, "");
        if (email === null)
        {
            return;
        }
        $.cookie ('email', email, { expires: 365 });
    }
    var url = 'sendtomail.php';
    if (currentData.databaseId) {
        url = url + '?db=' + currentData.databaseId;
    }
    $("#mailButton :first-child").removeClass ("icon-envelope").addClass ("icon-spinner icon-spin");
    $.ajax ({'url': url, 'type': 'post', 'data': { 'data':  dataid, 'email': email }, 'success': retourMail});
}

function str_format () {
    var s = arguments[0];
    for (var i = 0; i < arguments.length - 1; i++) {
        var reg = new RegExp("\\{" + i + "\\}", "gm");
        s = s.replace(reg, arguments[i + 1]);
    }
    return s;
}

function isDefined(x) {
    var undefinedVar;
    return x !== undefinedVar;
}

function getCurrentOption (option) {
    if (!$.cookie (option)) {
        if (currentData && currentData.c && currentData.c.config && currentData.c.config [option]) {
            return currentData.c.config [option];
        }
    }
    return $.cookie (option);
}

/*exported htmlspecialchars */
function htmlspecialchars(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
}

/************************************************
 * All functions needed to filter the book list by tags
 ************************************************
 */

function getTagList () {
    var tagList = {};
    $(".se").each (function(){
        if ($(this).parents (".filtered").length > 0) { return; }
        var taglist = $(this).text();

        var tagarray = taglist.split (",");
        for (var i in tagarray) {
            var tag = tagarray [i].replace(/^\s+/g,'').replace(/\s+$/g,'');
            tagList [tag] = 1;
        }
    });
    return tagList;
}

function updateFilters () {
    var tagList = getTagList ();

    // If there is already some filters then let's prepare to update the list
    $("#filter ul li").each (function () {
        var text = $(this).text ();
        if (isDefined (tagList [text]) || $(this).attr ('class')) {
            tagList [text] = 0;
        } else {
            tagList [text] = -1;
        }
    });

    // Update the filter -1 to remove, 1 to add, 0 already there
    for (var tag in tagList) {
        var tagValue = tagList [tag];
        if (tagValue === -1) {
            $("#filter ul li").filter (function () { return $.text([this]) === tag; }).remove();
        }
        if (tagValue === 1) {
            $("#filter ul").append ("<li>" + tag + "</li>");
        }
    }

    $("#filter ul").append ("<li>_CLEAR_</li>");

    // Sort the list alphabetically
    $('#filter ul li').sortElements(function(a, b){
        return $(a).text() > $(b).text() ? 1 : -1;
    });
}

function doFilter () {
    $(".books").removeClass("filtered");
    if (jQuery.isEmptyObject(filterList)) {
        updateFilters ();
        return;
    }

    $(".se").each (function(){
        var taglist = ", " + $(this).text() + ", ";
        var toBeFiltered = false;
        for (var filter in filterList) {
            var onlyThisTag = filterList [filter];
            filter = ', ' + filter + ', ';
            var myreg = new RegExp (filter);
            if (myreg.test (taglist)) {
                if (onlyThisTag === false) {
                    toBeFiltered = true;
                }
            } else {
                if (onlyThisTag === true) {
                    toBeFiltered = true;
                }
            }
        }
        if (toBeFiltered) { $(this).parents (".books").addClass ("filtered"); }
    });

    // Handle the books with no tags
    var atLeastOneTagSelected = false;
    for (var filter in filterList) {
        if (filterList [filter] === true) {
            atLeastOneTagSelected = true;
        }
    }
    if (atLeastOneTagSelected) {
        $(".books").not (":has(span.se)").addClass ("filtered");
    }

    updateFilters ();
}

function handleFilterEvents () {
    $("#filter ul").on ("click", "li", function(){
        var filter = $(this).text ();
        if (filter === "_CLEAR_") {
            filterList = {};
            $("#filter ul li").removeClass ("filter-exclude");
            $("#filter ul li").removeClass ("filter-include");
            doFilter ();
            return;
        }
        switch ($(this).attr("class")) {
            case "filter-include" :
                $(this).attr("class", "filter-exclude");
                filterList [filter] = false;
                break;
            case "filter-exclude" :
                $(this).removeClass ("filter-exclude");
                delete filterList [filter];
                break;
            default :
                $(this).attr("class", "filter-include");
                filterList [filter] = true;
                break;
        }
        doFilter ();
    });
}

/************************************************
 * Functions to handle Ajax navigation
 ************************************************
 */

var updatePage, navigateTo;

updatePage = function (data) {
    var result;
    filterList = {};
    data.c = currentData.c;
    if (false && $("section").length && currentData.isPaginated === 0 &&  data.isPaginated === 0) {
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
    setTimeout( function() { $("input[name=query]").focus(); }, 500 );

    debug_log (elapsed ());

    if ($.cookie('toolbar') === '1') { $("#tool").show (); }
    if (currentData.containsBook === 1) {
        $("#sortForm").show ();
        if (getCurrentOption ("html_tag_filter") === "1") {
            $("#filter ul").empty ();
            updateFilters ();
            handleFilterEvents ();
        }
    } else {
        $("#sortForm").hide ();
    }

    $('input[name=query]').typeahead(
    {
        hint: true,
        minLength : 3
    },
    {
        name: 'search',
        displayKey: 'title',
        templates: {
            suggestion: templateSuggestion
        },
        source: copsTypeahead.ttAdapter()
    });

    $('input[name=query]').bind('typeahead:selected', function(obj, datum) {
        if (isPushStateEnabled) {
            navigateTo (datum.navlink);
        } else {
            window.location = datum.navlink;
        }
    });

    if(typeof postRefresh == 'function')
    { postRefresh(); }
};

navigateTo = function (url) {
    $("h1").append (" <i class='icon-spinner icon-spin'></i>");
    before = new Date ();
    var jsonurl = url.replace ("index", "getJSON");
    var cachedData = cache.get (jsonurl);
    if (cachedData) {
        history.pushState(jsonurl, "", url);
        updatePage (cachedData);
    } else {
        $.getJSON(jsonurl, function(data) {
            history.pushState(jsonurl, "", url);
            cache.put (jsonurl, data);
            updatePage (data);
        });
    }
};

function link_Clicked (event) {
    var currentLink = $(this);
    if (!isPushStateEnabled ||
        currentData.page === "19") {
        return;
    }
    event.preventDefault();
    var url = currentLink.attr('href');

    if ($(".mfp-ready").length)
    {
        $.magnificPopup.close();
    }

    // The bookdetail / about should be displayed in a lightbox
    if (getCurrentOption ("use_fancyapps") === "1" &&
        (currentLink.hasClass ("fancydetail") || currentLink.hasClass ("fancyabout"))) {
        before = new Date ();
        var jsonurl = url.replace ("index", "getJSON");
        $.getJSON(jsonurl, function(data) {
            data.c = currentData.c;
            var detail = "";
            if (data.page === "16") {
                detail = data.fullhtml;
            } else {
                detail = templateBookDetail (data);
            }
            $.magnificPopup.open({
              items: {
                src: detail,
                type: 'inline'
              }
            });
            debug_log (elapsed ());
        });
        return;
    }
    navigateTo (url);
}

function search_Submitted (event) {
    if (!isPushStateEnabled ||
        currentData.page === "19") {
        return;
    }
    event.preventDefault();
    var url = str_format ("index.php?page=9&current={0}&query={1}&db={2}", currentData.page, encodeURIComponent ($("input[name=query]").val ()), currentData.databaseId);
    navigateTo (url);
}

/*exported handleLinks */
function handleLinks () {
    $("body").on ("click", "a[href^='index']", link_Clicked);
    $("body").on ("submit", "#searchForm", search_Submitted);
    $("body").on ("click", "#sort", function(){
        $('.books').sortElements(function(a, b){
            var test = 1;
            if ($("#sortorder").val() === "desc")
            {
                test = -1;
            }
            return $(a).find ("." + $("#sortchoice").val()).text() > $(b).find ("." + $("#sortchoice").val()).text() ? test : -test;
        });
    });

    $("body").on ("click", ".headright", function(){
        if ($("#tool").is(":hidden")) {
            $("#tool").slideDown("slow");
            $("input[name=query]").focus();
            $.cookie('toolbar', '1', { expires: 365 });
        } else {
            $("#tool").slideUp();
            $.removeCookie('toolbar');
        }
    });
    $("body").magnificPopup({
        delegate: '.fancycover', // child items selector, by clicking on it popup will open
        type: 'image',
        gallery:{enabled:true, preload: [0,2]},
        disableOn: function() {
          if( getCurrentOption ("use_fancyapps") === "1" ) {
            return true;
          }
          return false;
        }
    });
}

window.onpopstate = function(event) {
    if (!isDefined (currentData)) {
        return;
    }

    before = new Date ();
    var data = cache.get (event.state);
    updatePage (data);
};

$(document).keydown(function(e){
    if (e.keyCode === 37 && $("#prevLink").length > 0) {
        navigateTo ($("#prevLink").attr('href'));
    }
    if (e.keyCode === 39  && $("#nextLink").length > 0) {
        navigateTo ($("#nextLink").attr('href'));
    }
});

/*exported initiateAjax */
function initiateAjax (url, theme) {
    $.when($.get('templates/' + theme + '/header.html'),
           $.get('templates/' + theme + '/footer.html'),
           $.get('templates/' + theme + '/bookdetail.html'),
           $.get('templates/' + theme + '/main.html'),
           $.get('templates/' + theme + '/page.html'),
           $.get('templates/' + theme + '/suggestion.html'),
           $.getJSON(url)).done(function(header, footer, bookdetail, main, page, suggestion, data){
        templateBookDetail = doT.template (bookdetail [0]);

        var defMain = {
            bookdetail: bookdetail [0]
        };

        templateMain = doT.template (main [0], undefined, defMain);

        var defPage = {
            header: header [0],
            footer: footer [0],
            main  : main [0],
            bookdetail: bookdetail [0]
        };

        templatePage = doT.template (page [0], undefined, defPage);

        templateSuggestion = doT.template (suggestion [0]);

        currentData = data [0];

        updatePage (data [0]);
        cache.put (url, data [0]);
        if (isPushStateEnabled) {
            history.replaceState(url, "", window.location);
        }
        handleLinks ();
    });
}