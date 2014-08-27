<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class CustomColumn extends Base {
    const ALL_CUSTOMS_ID = "cops:custom";

    public $id;
    public $name;
    public $customId;

    public function __construct($pid, $pname, $pcustomId) {
        $this->id = $pid;
        $this->name = $pname;
        $this->customId = $pcustomId;
    }

    public function getUri () {
        return "?page=".parent::PAGE_CUSTOM_DETAIL."&custom={$this->customId}&id={$this->id}";
    }

    public function getEntryId () {
        return self::ALL_CUSTOMS_ID.":".$this->customId.":".$this->id;
    }

    public static function getTableName ($customId) {
        return "custom_column_{$customId}";
    }

    public static function getTableLinkName ($customId) {
        return "books_custom_column_{$customId}_link";
    }

    public static function getTableLinkColumn ($customId) {
        return "value";
    }

    public static function getAllCustomsId ($customId) {
        return self::ALL_CUSTOMS_ID . ":" . $customId;
    }

    public static function getUriAllCustoms ($customId) {
        return "?page=" . parent::PAGE_ALL_CUSTOMS . "&custom={$customId}";
    }

    public static function getAllTitle ($customId) {
        $result = parent::getDb ()->prepare('select name from custom_columns where id = ?');
        $result->execute (array ($customId));
        $post = $result->fetchObject ();
        return $post->name;
    }

    public static function getCustomId ($lookup) {
        $result = parent::getDb ()->prepare('select id from custom_columns where label = ?');
        $result->execute (array ($lookup));
        if ($post = $result->fetchObject ()) {
            return $post->id;
        }
        return NULL;
    }

    public static function getCount($customId) {
        $nCustoms = parent::executeQuerySingle ('select count(*) from ' . self::getTableName ($customId));
        $entry = new Entry (self::getAllTitle ($customId), self::getAllCustomsId ($customId),
            str_format (localize("tags.alphabetical", $nCustoms), $nCustoms), "text",
            array ( new LinkNavigation (self::getUriAllCustoms ($customId))), "", $nCustoms);
        return $entry;
    }

    public static function getCustomById ($customId, $id) {
        $result = parent::getDb ()->prepare('select id, value as name from ' . self::getTableName ($customId) . ' where id = ?');
        $result->execute (array ($id));
        if ($post = $result->fetchObject ()) {
            return new CustomColumn ($post->id, $post->name, $customId);
        }
        return NULL;
    }

    public static function getAllCustoms($customId) {
        $result = parent::getDb ()->query(str_format ('select {0}.id as id, {0}.value as name, count(*) as count
from {0}, {1}
where {0}.id = {1}.{2}
group by {0}.id, {0}.value
order by {0}.value', self::getTableName ($customId), self::getTableLinkName ($customId), self::getTableLinkColumn ($customId)));
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $customColumn = new CustomColumn ($post->id, $post->name, $customId);
            array_push ($entryArray, new Entry ($customColumn->name, $customColumn->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($customColumn->getUri ())), "", $post->count));
        }
        return $entryArray;
    }
}
