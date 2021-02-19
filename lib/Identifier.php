<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     SenorSmartyPants <senorsmartypants@gmail.com>
 */

class Identifier
{
    public $id;
    public $type;
    public $formattedType;
    public $val;

    public function __construct($post)
    {
        $this->id = $post->id;
        $this->type = strtolower($post->type);;
        $this->val = $post->val;
        $this->formatType();
    }

    function formatType()
    {
        if ($this->type == 'amazon') {
            $this->formattedType = "Amazon";
            $this->uri = sprintf("https://amazon.com/dp/%s", $this->val);
        } else if ($this->type == "asin") {
            $this->formattedType = $this->type;
            $this->uri = sprintf("https://amazon.com/dp/%s", $this->val);
        } else if (substr($this->type, 0, 7) == "amazon_") {
            $this->formattedType = sprintf("Amazon.co.%s", substr($this->type, 7));
            $this->uri = sprintf("https://amazon.co.%s/dp/%s", substr($this->type, 7), $this->val);
        } else if ($this->type == "isbn") {
            $this->formattedType = "ISBN";
            $this->uri = sprintf("https://www.worldcat.org/isbn/%s", $this->val);
        } else if ($this->type == "doi") {
            $this->formattedType = "DOI";
            $this->uri = sprintf("https://dx.doi.org/%s", $this->val);
        } else if ($this->type == "douban") {
            $this->formattedType = "Douban";
            $this->uri = sprintf("https://book.douban.com/subject/%s", $this->val);
        } else if ($this->type == "goodreads") {
            $this->formattedType = "Goodreads";
            $this->uri = sprintf("https://www.goodreads.com/book/show/%s", $this->val);
        } else if ($this->type == "google") {
            $this->formattedType = "Google Books";
            $this->uri = sprintf("https://books.google.com/books?id=%s", $this->val);
        } else if ($this->type == "kobo") {
            $this->formattedType = "Kobo";
            $this->uri = sprintf("https://www.kobo.com/ebook/%s", $this->val);
        } else if ($this->type == "litres") {
            $this->formattedType = "ЛитРес";
            $this->uri = sprintf("https://www.litres.ru/%s", $this->val);
        } else if ($this->type == "issn") {
            $this->formattedType = "ISSN";
            $this->uri = sprintf("https://portal.issn.org/resource/ISSN/%s", $this->val);
        } else if ($this->type == "isfdb") {
            $this->formattedType = "ISFDB";
            $this->uri = sprintf("http://www.isfdb.org/cgi-bin/pl.cgi?%s", $this->val);
        } else if ($this->type == "lubimyczytac") {
            $this->formattedType = "Lubimyczytac";
            $this->uri = sprintf("https://lubimyczytac.pl/ksiazka/%s/ksiazka", $this->val);
        } else if ($this->type == "url") {
            $this->formattedType = $this->type;
            $this->uri = $this->val;
        } else {
            $this->formattedType = $this->type;
            $this->uri = '';
        }
    }

    public function getUri()
    {
        return $this->uri;
    }
}
