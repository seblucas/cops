<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageAllCustoms extends Page
{
    public function InitializeContent ()
    {
        $customId = getURLParam ("custom", NULL);
        $this->title = CustomColumn::getAllTitle ($customId);
        $this->entryArray = CustomColumn::getAllCustoms($customId);
        $this->idPage = CustomColumn::getAllCustomsId ($customId);
    }
}
