doT-php
=======

PHP rendering engine for [doT.js (The fastest + concise javascript template engine for nodejs and browsers)](https://github.com/olado/doT).


How to use it
-------------

```php
// Load the library
require_once('resources/doT-php/doT.php');

// Load the template
$page = file_get_contents('templates/page.html');

// instanciate the object
$template = new doT();

// Compile your templace in a PHP function ($dot)
$dot = $template->template($page);

// the data is simple PHP array
$data = array('title' => 'My custom title');

// Write the HTML
echo $dot($data);
```


Warning
-------

It's far from complete. I needed it just to provide a server side rendering engine
for another project ([COPS](https://github.com/seblucas/cops)).

So the code provided works perfectly for the templates of COPS and was never tested
elsewhere, doT's unit test were also never tested.

That being said, You can fork, enhance it and send me some pull request, I'll
happily merge them.
