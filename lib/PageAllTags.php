<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageAllTags extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("tags.title");
        $this->entryArray = Tag::getAllTags();
        $this->idPage = Tag::ALL_TAGS_ID;
    }
}
