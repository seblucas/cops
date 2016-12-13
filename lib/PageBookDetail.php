<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageBookDetail extends Page
{
    public function InitializeContent ()
    {
        $this->book = Book::getBookById ($this->idGet);
        $this->title = $this->book->title;
    }
}
