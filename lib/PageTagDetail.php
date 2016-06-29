<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageTagDetail extends Page
{
    public function InitializeContent ()
    {
        $tag = Tag::getTagById ($this->idGet);
        $this->idPage = $tag->getEntryId ();
        $this->title = $tag->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByTag ($this->idGet, $this->n);
    }
}
