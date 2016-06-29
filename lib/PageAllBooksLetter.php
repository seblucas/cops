<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageAllBooksLetter extends Page
{
    public function InitializeContent ()
    {
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByStartingLetter ($this->idGet, $this->n);
        $this->idPage = Book::getEntryIdByLetter ($this->idGet);

        $count = $this->totalNumber;
        if ($count == -1)
            $count = count ($this->entryArray);

        $this->title = str_format (localize ("splitByLetter.letter"), str_format (localize ("bookword", $count), $count), $this->idGet);
    }
}
