<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageRatingDetail extends Page
{
    public function InitializeContent ()
    {
        $rating = Rating::getRatingById ($this->idGet);
        $this->idPage = $rating->getEntryId ();
        $this->title =str_format (localize ("ratingword", $rating->name/2), $rating->name/2);
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByRating ($this->idGet, $this->n);
    }
}
