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

    const CUSTOM_TYPE_TEXT     = "text";        // type 1 + 2
    const CUSTOM_TYPE_COMMENTS = "comments";    // type 3
    const CUSTOM_TYPE_SERIES   = "series";      // type 4
    const CUSTOM_TYPE_ENUM     = "enumeration"; // type 5
    const CUSTOM_TYPE_DATE     = "datetime";    // type 6
    const CUSTOM_TYPE_FLOAT    = "float";       // type 7
    const CUSTOM_TYPE_INT      = "int";         // type 8
    const CUSTOM_TYPE_RATING   = "rating";      // type 9
    const CUSTOM_TYPE_BOOL     = "bool";        // type 10

    const BOOLEAN_NAMES = array(
        -1 => "customcolumn.boolean.unknown", // localize("customcolumn.boolean.unknown")
        00 => "customcolumn.boolean.no",      // localize("customcolumn.boolean.no")
        +1 => "customcolumn.boolean.yes",     // localize("customcolumn.boolean.yes")
    );

    public $id;
    public $name;
    public $customId;
    public $datatype;

    public function __construct($pid, $pname, $pcustomId, $pdatatype) {
        $this->id = $pid;
        $this->name = $pname;
        $this->customId = $pcustomId;
        $this->datatype = $pdatatype;
    }

    public function getUri () {
        return "?page=".parent::PAGE_CUSTOM_DETAIL."&custom={$this->customId}&id={$this->id}";
    }

    public function getEntryId () {
        return self::ALL_CUSTOMS_ID.":".$this->customId.":".$this->id;
    }

    public function getQuery($id) {
        switch ($this->datatype){
            case self::CUSTOM_TYPE_TEXT:
            case self::CUSTOM_TYPE_SERIES:
            case self::CUSTOM_TYPE_ENUM:
                $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", CustomColumn::getTableLinkName($this->customId), CustomColumn::getTableLinkColumn());
                return array($query, array($id));
            case self::CUSTOM_TYPE_COMMENTS:
                return NULL;
            case self::CUSTOM_TYPE_DATE:
                return NULL;
            case self::CUSTOM_TYPE_FLOAT:
                return NULL;
            case self::CUSTOM_TYPE_INT:
                return NULL;
            case self::CUSTOM_TYPE_RATING:
                if ($id == 0) {
                    $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING_NULL, "{0}", "{1}", CustomColumn::getTableLinkName($this->customId), CustomColumn::getTableName($this->customId), CustomColumn::getTableLinkColumn());
                    return array($query, array());
                } else {
                    $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING, "{0}", "{1}", CustomColumn::getTableLinkName($this->customId), CustomColumn::getTableName($this->customId), CustomColumn::getTableLinkColumn());
                    return array($query, array($id));
                }
            case self::CUSTOM_TYPE_BOOL:
                if ($id == -1) {
                    $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_NULL, "{0}", "{1}", CustomColumn::getTableName($this->customId));
                    return array($query, array());
                } else if ($id == 0) {
                    $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_FALSE, "{0}", "{1}", CustomColumn::getTableName($this->customId), CustomColumn::getTableLinkColumn());
                    return array($query, array());
                } else if ($id == 1) {
                    $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_TRUE, "{0}", "{1}", CustomColumn::getTableName($this->customId), CustomColumn::getTableLinkColumn());
                    return array($query, array());
                } else {
                    return NULL;
                }
            default:
                return NULL;
        }
    }

    public static function getTableName ($customId) {
        return "custom_column_{$customId}";
    }

    public static function getTableLinkName ($customId) {
        return "books_custom_column_{$customId}_link";
    }

    public static function getTableLinkColumn () {
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

    public static function getCustomDatatypeByID ($customId) {
        $result = parent::getDb ()->prepare('select datatype from custom_columns where id = ?');
        $result->execute (array ($customId));
        if ($post = $result->fetchObject ()) {
            return $post->datatype;
        }
        return NULL;
    }

    public static function getCustomDescriptionByID ($customId) {
        $result = parent::getDb ()->prepare('select display from custom_columns where id = ?');
        $result->execute (array ($customId));
        if ($post = $result->fetchObject ()) {
            $json = json_decode($post->display);
            return $json->description;
        }
        return NULL;
    }

    public static function getCount($customId) {
        $query = 'select count(*) from ' . self::getTableName ($customId);

        $ptitle = self::getAllTitle ($customId);
        $pid = self::getAllCustomsId ($customId);
        $pcontent = self::getCustomDescriptionByID($customId);
        if ($pcontent == NULL || empty($pcontent)) $pcontent = str_format(localize("customcolumn.description"), $ptitle);
        $pcontentType = self::getCustomDatatypeByID($customId);
        $plinkArray = array ( new LinkNavigation (self::getUriAllCustoms ($customId)));
        $pclass = "";
        $pcount = parent::executeQuerySingle ($query);

        return new Entry ($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pclass, $pcount);
    }

    /**
     * @param $customId integer
     * @param $id integer
     * @return CustomColumn
     */
    public static function getCustomById($customId, $id) {
        $datatype = self::getCustomDatatypeByID($customId);

        switch ($datatype){
            case self::CUSTOM_TYPE_TEXT:
            case self::CUSTOM_TYPE_SERIES:
            case self::CUSTOM_TYPE_ENUM:
                return self::getCustomById_Simple($customId, $id, $datatype);
            case self::CUSTOM_TYPE_COMMENTS:
                return NULL;
            case self::CUSTOM_TYPE_DATE:
                return NULL;
            case self::CUSTOM_TYPE_FLOAT:
                return NULL;
            case self::CUSTOM_TYPE_INT:
                return NULL;
            case self::CUSTOM_TYPE_RATING:
                return self::getCustomById_Rating($customId, $id, $datatype);
            case self::CUSTOM_TYPE_BOOL:
                return self::getCustomById_Boolean($customId, $id, $datatype);
            default:
                return NULL;
        }
    }

    /**
     * @param $customId integer
     * @param $id integer
     * @param $datatype integer
     * @return CustomColumn
     */
    public static function getCustomById_Simple($customId, $id, $datatype) {
        // works for text, series, enum

        $result = parent::getDb ()->prepare(str_format("select id, value as name from {0} where id = ?", self::getTableName($customId)));
        $result->execute (array ($id));
        if ($post = $result->fetchObject ()) {
            return new CustomColumn ($id, $post->name, $customId, $datatype);
        }
        return NULL;
    }

    /**
     * @param $customId integer
     * @param $id integer
     * @param $datatype integer
     * @return CustomColumn
     */
    public static function getCustomById_Boolean($customId, $id, $datatype) {
        return new CustomColumn ($id, localize(self::BOOLEAN_NAMES[$id]), $customId, $datatype);
    }

    /**
     * @param $customId integer
     * @param $id integer
     * @param $datatype integer
     * @return CustomColumn
     */
    public static function getCustomById_Rating($customId, $id, $datatype) {
        return new CustomColumn ($id, str_format(localize("customcolumn.stars", $id/2), $id/2), $customId, $datatype);
    }

    public static function getAllCustoms($customId) {
        $datatype = self::getCustomDatatypeByID($customId);

        switch ($datatype){
            case self::CUSTOM_TYPE_TEXT:
                return self::getAllCustoms_Text($customId, $datatype);
            case self::CUSTOM_TYPE_COMMENTS:
                return NULL;
            case self::CUSTOM_TYPE_SERIES:
                return self::getAllCustoms_Series($customId, $datatype);
            case self::CUSTOM_TYPE_ENUM:
                return self::getAllCustoms_Enumeration($customId, $datatype);
            case self::CUSTOM_TYPE_DATE:
                return NULL;
            case self::CUSTOM_TYPE_FLOAT:
                return NULL;
            case self::CUSTOM_TYPE_INT:
                return NULL;
            case self::CUSTOM_TYPE_RATING:
                return self::getAllCustoms_Rating($customId, $datatype);
            case self::CUSTOM_TYPE_BOOL:
                return self::getAllCustoms_Boolean($customId, $datatype);
            default:
                return NULL;
        }
    }

    private static function getAllCustoms_Text($customId, $customdatatype)
    {
        $queryFormat = "select {0}.id as id, {0}.value as name, count(*) as count from {0}, {1} where {0}.id = {1}.{2} group by {0}.id, {0}.value order by {0}.value";
        $query = str_format ($queryFormat, self::getTableName ($customId), self::getTableLinkName ($customId), self::getTableLinkColumn ());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $customColumn = new CustomColumn ($post->id, $post->name, $customId, $customdatatype);

            $entryPContent = str_format (localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($customColumn->getUri()));

            $entry = new Entry ($customColumn->name, $customColumn->getEntryId(), $entryPContent, $customdatatype, $entryPLinkArray, "", $post->count);

            array_push ($entryArray, $entry);
        }
        return $entryArray;
    }

    private static function getAllCustoms_Series($customId, $customdatatype)
    {
        $queryFormat = "select {0}.id as id, {0}.value as name, count(*) as count from {0}, {1} where {0}.id = {1}.{2} group by {0}.id, {0}.value order by {0}.value";
        $query = str_format($queryFormat, self::getTableName($customId), self::getTableLinkName($customId), self::getTableLinkColumn());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $customColumn = new CustomColumn($post->id, $post->name, $customId, $customdatatype);

            $entryPContent = str_format (localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($customColumn->getUri()));

            $entry = new Entry($customColumn->name, $customColumn->getEntryId(), $entryPContent, $customdatatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    private static function getAllCustoms_Enumeration($customId, $customdatatype)
    {
        $queryFormat = "select {0}.id as id, {0}.value as name, count(*) as count from {0}, {1} where {0}.id = {1}.{2} group by {0}.id, {0}.value order by {0}.value";
        $query = str_format ($queryFormat, self::getTableName($customId), self::getTableLinkName($customId), self::getTableLinkColumn());
        $result = parent::getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $customColumn = new CustomColumn($post->id, $post->name, $customId, $customdatatype);

            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($customColumn->getUri()));

            $entry = new Entry($customColumn->name, $customColumn->getEntryId(), $entryPContent, $customdatatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    private static function getAllCustoms_Boolean($customId, $customdatatype)
    {
        $queryFormat = "select coalesce({0}.value, -1) as id, count(*) as count from books left join {0} on  books.id = {0}.book group by {0}.value order by {0}.value";
        $query = str_format ($queryFormat, self::getTableName($customId));
        $result = parent::getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $customColumn = new CustomColumn($post->id, localize(self::BOOLEAN_NAMES[$post->id]), $customId, $customdatatype);

            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($customColumn->getUri()));

            $entry = new Entry($customColumn->name, $customColumn->getEntryId(), $entryPContent, $customdatatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    private static function getAllCustoms_Rating($customId, $customdatatype)
    {
        $queryFormat = "select coalesce({0}.value, 0) as value, count(*) as count from books  left join {1} on  books.id = {1}.book left join {0} on {0}.id = {1}.value group by coalesce({0}.value, -1)";
        $query = str_format ($queryFormat, self::getTableName($customId), self::getTableLinkName($customId));
        $result = parent::getDb()->query($query);

        $countArray = array(0=>0, 2=>0, 4=>0, 6=>0, 8=>0, 10=>0);
        while ($row = $result->fetchObject()) {
            $countArray[$row->value] = $row->count;
        }

        $entryArray = array();

        for ($i = 0; $i <= 5; $i++) {
            $col = new CustomColumn($i*2, str_format(localize("customcolumn.stars", $i), $i), $customId, $customdatatype);
            $count = $countArray[$col->id];
            $entry = new Entry($col->name, $col->getEntryId(), str_format(localize("bookword", $count), $count), $customdatatype, array(new LinkNavigation ($col->getUri())), "", $count);
            array_push($entryArray, $entry);
        }

        return $entryArray;
    }
}
