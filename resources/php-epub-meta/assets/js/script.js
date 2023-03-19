
var bookapi = {
    $dialog: null,

    resulttpl:
        '<div class="result">' +
        '    <img src="" />'+
        '    <div>' +
        '    <div class="buttons">' +
        '    <button class="btn-repl">replace</button><br />' +
        '    <button class="btn-fill">fill in</button>' +
        '    </div>' +
        '    <h1 class="title"></h1>' +
        '    <p class="authors"></p>' +
        '    <p class="description"></p>' +
        '    <p class="more">' +
        '        <span class="lang"></span>' +
        '        <span class="publisher"></span>' +
        '        <span class="subjects"></span>' +
        '    </p>' +
        '    </div>' +
        '</div>',

    init: function(){
        $('body').append('<div id="bookapi"></div>');
        bookapi.$dialog = $('#bookapi');
        bookapi.$dialog.dialog(
            {
                autoOpen: false,
                title: 'Lookup Book Data',
                width: 800,
                height: 500
            }
        );
        bookapi.$dialog.append('<div class="head">Lookup: <input type="text" id="bookapi-q" /></div>')
                       .append('<div id="bookapi-out"></div>');
        bookapi.$out = $('#bookapi-out');

        $('#bookpanel').append('<a href="#" id="bookapi-s">Lookup Book Data</a>');
        $('#bookapi-s').attr('title','Search this book at Google Books');
        $('#bookapi-s').click(bookapi.open);

        $('#bookapi-q').keypress(
            function(event){
                if(event.which == 13){
                    event.preventDefault();
                    bookapi.search();
                }
            });

    },

    open: function(){
        bookapi.$dialog.dialog('open');

        var query = $('#bookpanel input[name=title]').val();
        $('#bookapi-q').val(query);

        bookapi.search();
    },

    search: function(){
        bookapi.$out.html('please wait...');
        $.ajax({
            type: 'GET',
            data: {'api':$('#bookapi-q').val()},
            success: bookapi.searchdone,
            dataType: 'json'
        });
    },

    searchdone: function(data){
        if(data.totalItems == 0){
            bookapi.$out.html('Found no results.<br />Try adjusting the query and retry.');
            return;
        }

        bookapi.$out.html('');
        for(i=0; i<data.items.length; i++){
            $res = $(bookapi.resulttpl);
            if(data.items[i].volumeInfo.title)
                $res.find('.title').html(data.items[i].volumeInfo.title);
            if(data.items[i].volumeInfo.authors)
                $res.find('.authors').html(data.items[i].volumeInfo.authors.join(', '));
            if(data.items[i].volumeInfo.description)
                $res.find('.description').html(data.items[i].volumeInfo.description);
            if(data.items[i].volumeInfo.language)
                $res.find('.lang').html('['+data.items[i].volumeInfo.language+']');
            if(data.items[i].volumeInfo.publisher)
                $res.find('.publisher').html(data.items[i].volumeInfo.publisher);
            if(data.items[i].volumeInfo.categories)
                $res.find('.subjects').html(data.items[i].volumeInfo.categories.join(', '));
            if(data.items[i].volumeInfo.imageLinks)
                if(data.items[i].volumeInfo.imageLinks.thumbnail)
                    $res.find('img').attr('src',data.items[i].volumeInfo.imageLinks.thumbnail);

            $res.find('.btn-repl').click(data.items[i].volumeInfo,bookapi.replace);
            $res.find('.btn-fill').click(data.items[i].volumeInfo,bookapi.fillin);

            bookapi.$out.append($res);
        }
    },

    replace: function(event){
        item = event.data;
        if(item.title)
            $('#bookpanel input[name=title]').val(item.title);
        if(item.description)
            $('#bookpanel textarea[name=description]').val(item.description);
            $wysiwyg[0].updateFrame();
        if(item.language)
            $('#bookpanel input[name=language]').val(item.language);
        if(item.publisher)
            $('#bookpanel input[name=publisher]').val(item.publisher);
        if(item.categories)
            $('#bookpanel input[name=subjects]').val(item.categories.join(', '));
        if(item.imageLinks){
            $('#bookpanel input[name=coverurl]').val(item.imageLinks.thumbnail);
            $('#cover').attr('src',item.imageLinks.thumbnail);
        }
        bookapi.$dialog.dialog('close');
    },

    fillin: function(event){
        item = event.data;

        if(item.title && $('#bookpanel input[name=title]').val() == '')
            $('#bookpanel input[name=title]').val(item.title);
        if(item.description && $('#bookpanel textarea[name=description]').val() == '')
            $('#bookpanel textarea[name=description]').val(item.description);
            $wysiwyg[0].updateFrame();
        if(item.language && $('#bookpanel input[name=language]').val() == '')
            $('#bookpanel input[name=language]').val(item.language);
        if(item.publisher && $('#bookpanel input[name=publisher]').val() == '')
            $('#bookpanel input[name=publisher]').val(item.publisher);
        if(item.categories && $('#bookpanel input[name=subjects]').val() == '')
            $('#bookpanel input[name=subjects]').val(item.categories.join(', '));
        if(item.imageLinks && $('#cover').hasClass('noimg')){
            $('#bookpanel input[name=coverurl]').val(item.imageLinks.thumbnail);
            $('#cover').attr('src',item.imageLinks.thumbnail);
        }
        bookapi.$dialog.dialog('close');
    }

};

var author = {
    init: function(){
        $button = $(document.createElement('a'));
        $button.text('+').attr('href','#');
        $button.attr('title','add another author line');
        $button.click(author.add);
        $button.addClass('addauthor');

        $td = $('#authors');
        $td.append($button);
    },

    add: function(){
        $td  = $('#authors');

        $ps  = $td.find('p');
        $new = $ps.first().clone();
        $new.find('input').first().attr('name','authorname['+$ps.length+']').val('');
        $new.find('input').last().attr('name','authoras['+$ps.length+']').val('');

        $ps.last().after($new);
    }
};

var $wysiwg = null;
$(function(){
    bookapi.init();
    author.init();

    // scroll to currently selected book
    $current = $('#booklist li.active');
    if($current.length){
         $current[0].scrollIntoView();
    }

    // initialize the WYSIWYG editor
    $wysiwyg = $('textarea').cleditor({
        width: 450,
        controls:     // controls to add to the toolbar
                "bold italic underline strikethrough | " +
                "style removeformat | bullets numbering | " +
                "alignleft center alignright justify | undo redo | " +
                "link unlink | source",
        styles:       // styles in the style popup
                [["Paragraph", "<p>"], ["Header 1", "<h1>"], ["Header 2", "<h2>"],
                ["Header 3", "<h3>"],  ["Header 4","<h4>"],  ["Header 5","<h5>"]]
    });
});
