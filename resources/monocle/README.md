# Monocle

A silky, tactile browser-based ebook reader.

Initial development by Joseph Pearson of Inventive Labs. Released under the
MIT license.

____________________________________________________________________________
THIS PROJECT IS NOW OVER EIGHT YEARS OLD. 
IT HAS NOT BEEN ACTIVELY MAINTAINED SINCE 2015.
You are welcome to explore and learn from the code, but it is no longer the
best approach for modern browsers and devices, and it is not recommended for
new projects or production applications.
____________________________________________________________________________


## Getting Monocle

There's a few different ways to get Monocle. The easiest way to explore
it is from the test site, which is always running the latest `master`:

http://test.monoclejs.com/test

To grab the code for your own use, see:

https://github.com/joseph/Monocle/wiki/Getting-Monocle-running

The scripts and stylesheets are separated into:

* `monocore` - the essential Monocle functionality
* `monoctrl` - the optional basic controls for page numbers, font-sizing, etc


## Integrating Monocle

Here's the simplest thing that could possibly work.

    <head>
      <!-- Include the Monocle library and styles -->
      <script src="scripts/monocore.js"></script>
      <link rel="stylesheet" type="text/css" href="styles/monocore.css" />
      <style>
        #reader { width: 300px; height: 400px; border: 1px solid #000; }
      </style>
    </head>

    <body>
      <!-- The reader element, with all content to paginate inside it -->
      <div id="reader">
        <h1>Hello world.</h1>
      </div>

      <!-- Instantiate the reader when the containing element has loaded -->
      <script>Monocle.Reader('reader');</script>
    </body>


In this example, we initialise the reader with the contents of the div
itself. In theory there's no limit on the size of the contents of that div.

A more advanced scenario involves feeding Monocle a "book data object", from
which it can lazily load the contents of the book as the user requests it.


## Connecting Monocle to your book content

For a non-trivial Monocle implementation, your task is to connect the
Monocle Reader to your book's HTML content and structure. You create
something called "the book data object" to do this.

The book data object is really pretty simple. You'll find the specification
and some examples in the [Monocle Wiki page on the book data object](https://github.com/joseph/Monocle/wiki/Book-data-object).

For more advanced uses and customisations of Monocle, you should definitely
read the [Monocle Wiki](https://github.com/joseph/Monocle/wiki).


## Browser support

At this time, Monocle aims for full support of all browsers with a
W3C-compliant CSS column module implementation. That is Gecko, WebKit and
Opera at this point. Please encourage your browser-maker to work on
implementing these standards in particular:

* CSS Multi-Column Layout
* W3C DOM Level 2 Event Model
* CSS 2D Transforms (better: 3D Transforms, even better: hardware acceleration)

Monocle has a particular focus on mobile devices. Monocle supports:

* iOS 4.2+
* Android 2.2+
* Kindle 3

All these mobile platforms implement columned iframes differently, so support
may be imperfect in places, but we're working on it. Patches that improve or
broaden Monocle's browser support are very welcome (but please provide tests).

Inventive Labs would like to thank Ebooq for providing a device to assist with
Android testing.


## Future directions

Monocle has a small set of big goals:

* Faster, more responsive page flipping
* Wider browser support (and better tests, automated as far as possible)
* Tracking spec developments in EPUB and Zhook, supporting where appropriate

We'd also like to provide more implementation showcases in the tests, and
offer more developer documentation in the wiki. 

If you can help out with any of these things, fork away (or create an issue
on GitHub).


## History

3.2.0 - A new event management subsystem, called Gala, replacing the old
        Monocle.Events. Gala unifies touch and mouse event registration
        a lot better. It also works as a standalone library, if you need that -
        there are no dependencies on other parts of Monocle.

3.1.0 - Numerous stability fixes, plus improvements for Android and Opera,
        including minor API changes to flippers and slow-browser detection.

3.0.1 - Bugfixes for component loading, cancelling magic panel contacts.

3.0.0 - Magic panel, IE10 support, iOS6 support, better Android support,
        selection events, billboard feature, Monocle.Formatting to clean up
        Reader, removing deprecated flippers, Stencil refactor, component
        weights (for more accurate component percentages), and many bug
        fixes. See https://github.com/joseph/Monocle/compare/v2.3.1...v3.0.0

2.3.1 - Fix for serious Firefox 12 bug in paginating content.

2.3.0 - Smoother transitions and animations in more browsers.

2.2.1 - Slider fixes for better iOS performance.

2.2.0 - Speed, compatibility improvements (esp iOS5, Android, Kindle3).

2.1.0 - Source file reorganisation, Sprockets 2, distributables, wiki.

2.0.0 - Complete rewrite to sandbox content in iframes (the Componentry branch).

1.0.1 - Scrolling flipper, more tests, work on sandboxing in iframe (Framer).

1.0.0 - Initial release.
