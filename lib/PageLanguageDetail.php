<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageLanguageDetail extends Page
{
    public function InitializeContent ()
    {
        $language = Language::getLanguageById ($this->idGet);
        $this->idPage = $language->getEntryId ();
        $this->title = $language->lang_code;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByLanguage ($this->idGet, $this->n);
    }
}
