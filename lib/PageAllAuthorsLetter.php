<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageAllAuthorsLetter extends Page
{
    public function InitializeContent ()
    {
        $this->idPage = Author::getEntryIdByLetter ($this->idGet);
        $this->entryArray = Author::getAuthorsByStartingLetter ($this->idGet);
        $this->title = str_format (localize ("splitByLetter.letter"), str_format (localize ("authorword", count ($this->entryArray)), count ($this->entryArray)), $this->idGet);
    }
}
