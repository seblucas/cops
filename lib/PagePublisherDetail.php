<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PagePublisherDetail extends Page
{
    public function InitializeContent ()
    {
        $publisher = Publisher::getPublisherById ($this->idGet);
        $this->title = $publisher->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByPublisher ($this->idGet, $this->n);
        $this->idPage = $publisher->getEntryId ();
    }
}
