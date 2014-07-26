<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Pfitzner
 */

require_once('base.php');

class Rating extends Base {
    const ALL_RATING_ID = "cops:rating";

    const RATING_COLUMNS = "ratings.id as id, ratings.rating as rating, count(*) as count";
    const SQL_ALL_RATINGS ="select {0} from ratings, books_ratings_link where books_ratings_link.rating = ratings.id group by ratings.id order by ratings.rating";
    public $id;
    public $name;

    public function __construct($pid, $pname) {
        $this->id = $pid;
        $this->name = $pname;
    }

    public function getUri () {
        return "?page=".parent::PAGE_RATING_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_RATING_ID.":".$this->id;
    }

    public static function getCount() {
        // str_format (localize("ratings", count(array))
        return parent::getCountGeneric ("ratings", self::ALL_RATING_ID, parent::PAGE_ALL_RATINGS, "ratings");
    }

    public static function getAllRatings() {
        return self::getEntryArray (self::SQL_ALL_RATINGS, array ());
    }

    public static function getEntryArray ($query, $params) {
        list (, $result) = parent::executeQuery ($query, self::RATING_COLUMNS, "", $params, -1);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $ratingObj = new Rating ($post->id, $post->rating);
            $rating=$post->rating/2;
            $rating = str_format (localize("ratingword", $rating), $rating);
            array_push ($entryArray, new Entry ($rating, $ratingObj->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($ratingObj->getUri ())), "", $post->count));
        }
        return $entryArray;
    }

    public static function getRatingById ($ratingId) {
        $result = parent::getDb ()->prepare('select rating from ratings where id = ?');
        $result->execute (array ($ratingId));
        return new Rating ($ratingId, $result->fetchColumn ());
    }
}
