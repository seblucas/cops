<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageSerieDetail extends Page
{
    public function InitializeContent ()
    {
        $serie = Serie::getSerieById ($this->idGet);
        $this->title = $serie->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksBySeries ($this->idGet, $this->n);
        $this->idPage = $serie->getEntryId ();
    }
}
