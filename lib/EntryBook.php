<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class EntryBook extends Entry
{
    public $book;

    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pbook) {
        parent::__construct ($ptitle, $pid, $pcontent, $pcontentType, $plinkArray);
        $this->book = $pbook;
        $this->localUpdated = $pbook->timestamp;
    }

    public function getCoverThumbnail () {
        foreach ($this->linkArray as $link) {
            if ($link->rel == Link::OPDS_THUMBNAIL_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }

    public function getCover () {
        foreach ($this->linkArray as $link) {
            if ($link->rel == Link::OPDS_IMAGE_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }
}
