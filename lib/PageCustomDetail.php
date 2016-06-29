<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageCustomDetail extends Page
{
    public function InitializeContent ()
    {
        $customId = getURLParam ("custom", NULL);
        $custom = CustomColumn::getCustomById ($customId, $this->idGet);
        $this->idPage = $custom->getEntryId ();
        $this->title = $custom->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByCustom ($customId, $this->idGet, $this->n);
    }
}
