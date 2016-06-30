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

    /**
     * EntryBook constructor.
     * @param string $ptitle
     * @param integer $pid
     * @param string $pcontent
     * @param string $pcontentType
     * @param array $plinkArray
     * @param Book $pbook
     */
    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pbook) {
        parent::__construct ($ptitle, $pid, $pcontent, $pcontentType, $plinkArray);
        $this->book = $pbook;
        $this->localUpdated = $pbook->timestamp;
    }

    public function getCoverThumbnail () {
        foreach ($this->linkArray as $link) {
            /* @var $link LinkNavigation */

            if ($link->rel == Link::OPDS_THUMBNAIL_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }

    public function getCover () {
        foreach ($this->linkArray as $link) {
            /* @var $link LinkNavigation */

            if ($link->rel == Link::OPDS_IMAGE_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }
}
