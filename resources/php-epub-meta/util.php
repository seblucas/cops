<?php

function to_file($input)
{
    $input = str_replace(' ', '_', $input);
    $input = str_replace('__', '_', $input);
    $input = str_replace(',_', ',', $input);
    $input = str_replace('_,', ',', $input);
    $input = str_replace('-_', '-', $input);
    $input = str_replace('_-', '-', $input);
    $input = str_replace(',', '__', $input);
    return $input;
}

function book_output($input)
{
    $input = str_replace('__', ',', $input);
    $input = str_replace('_', ' ', $input);
    $input = str_replace(',', ', ', $input);
    $input = str_replace('-', ' - ', $input);
    [$author, $title] = explode('-', $input, 2);
    $author = trim($author);
    $title  = trim($title);

    if (!$title) {
        $title  = $author;
        $author = '';
    }

    return '<span class="title">' . htmlspecialchars($title) . '</span>' .
           '<span class="author">' . htmlspecialchars($author) . '</author>';
}
