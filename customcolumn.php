<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class CustomColumn extends Base {
    /* @var integer */
    public $id;
    /* @var string */
    public $name;
    /* @var CustomColumnType */
    public $customColumnType;

    public function __construct($pid, $pname, $pcustomColumnType) {
        $this->id = $pid;
        $this->name = $pname;
        $this->customColumnType = $pcustomColumnType;
    }

    public function getUri() {
        return $this->customColumnType->getUri($this->id);
    }

    public function getEntryId() {
        return $this->customColumnType->getEntryId($this->id);
    }

    public function getQuery() {
        return $this->customColumnType->getQuery($this->id);
    }

    public static function getCustomById($customId, $id) {
        $columnType = CustomColumnType::getCustomColumnById($customId);

        return $columnType->getCustom($id);
    }
}

abstract class CustomColumnType extends Base {
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

    public $customId;
    public $name;
    public $datatype;

    public function __construct($pcustomId, $pname, $pdatatype) {
        $this->name = $pname;
        $this->customId = $pcustomId;
        $this->datatype = $pdatatype;
    }

    public static function getCustomByLookup($customId) {
        $customID = self::getCustomColumnIDByLookup($customId);
        if (is_null($customID)) return NULL;

        return self::getCustomColumnById($customID);
    }

    public function getUri($id) {
        return "?page=".parent::PAGE_CUSTOM_DETAIL."&custom={$this->customId}&id={$id}";
    }

    public function getEntryId($id) {
        return self::ALL_CUSTOMS_ID.":".$this->customId.":".$id;
    }

    public function getAllCustomsId() {
        return self::ALL_CUSTOMS_ID . ":" . $this->customId;
    }

    public function getUriAllCustoms() {
        return "?page=" . parent::PAGE_ALL_CUSTOMS . "&custom={$this->customId}";
    }

    public function getAllTitle() {
        $result = parent::getDb()->prepare('select name from custom_columns where id = ?');
        $result->execute(array($this->customId));
        $post = $result->fetchObject();
        return $post->name;
    }

    public function getCustomDescriptionByID () {
        $result = parent::getDb()->prepare('select display from custom_columns where id = ?');
        $result->execute(array($this->customId));
        if ($post = $result->fetchObject()) {
            $json = json_decode($post->display);
            return $json->description;
        }
        return NULL;
    }

    public function getCount() {
        $query = 'select count(*) from ' . $this->getTableName ();

        $ptitle = $this->getAllTitle();
        $pid = $this->getAllCustomsId();
        $pcontent = $this->getCustomDescriptionByID();
        if ($pcontent == NULL || empty($pcontent)) $pcontent = str_format(localize("customcolumn.description"), $ptitle);
        $pcontentType = $this->datatype;
        $plinkArray = array(new LinkNavigation($this->getUriAllCustoms()));
        $pclass = "";
        $pcount = parent::executeQuerySingle ($query);

        return new Entry ($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pclass, $pcount);
    }

    public static function getCustomColumnById($customId) {
        $datatype = self::getCustomDatatypeByID($customId);

        switch ($datatype){
            case self::CUSTOM_TYPE_TEXT:
                return new CustomColumnTypeText($customId);
            case self::CUSTOM_TYPE_SERIES:
                return new CustomColumnTypeSeries($customId);
            case self::CUSTOM_TYPE_ENUM:
                return new CustomColumnTypeEnumeration($customId);
            case self::CUSTOM_TYPE_COMMENTS:
                return NULL; // Not supported - Doesn't really make sense
            case self::CUSTOM_TYPE_DATE:
                return new CustomColumnTypeDate($customId);
            case self::CUSTOM_TYPE_FLOAT:
                return new CustomColumnTypeFloat($customId);
            case self::CUSTOM_TYPE_INT:
                return new CustomColumnTypeInteger($customId);
            case self::CUSTOM_TYPE_RATING:
                return new CustomColumnTypeRating($customId);
            case self::CUSTOM_TYPE_BOOL:
                return new CustomColumnTypeBool($customId);
            default:
                return NULL;
        }
    }

    protected static function getCustomDatatypeByID($customId) {
        $result = parent::getDb ()->prepare('select datatype from custom_columns where id = ?');
        $result->execute (array ($customId));
        if ($post = $result->fetchObject ()) {
            return $post->datatype;
        }
        return NULL;
    }

    private static function getCustomColumnIDByLookup($lookup) {
        $result = parent::getDb ()->prepare('select id from custom_columns where label = ?');
        $result->execute (array ($lookup));
        if ($post = $result->fetchObject ()) {
            return $post->id;
        }
        return NULL;
    }

    protected static function getTitle ($customId) {
        $result = parent::getDb ()->prepare('select name from custom_columns where id = ?');
        $result->execute (array ($customId));
        $post = $result->fetchObject ();
        return $post->name;
    }

    abstract public function getTableName ();
    abstract public function getTableLinkName ();
    abstract public function getTableLinkColumn();
    abstract public function getQuery($id);
    abstract public function getCustom($id);
    abstract public function getAllCustomValues();
}

class CustomColumnTypeText extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_TEXT);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return "books_custom_column_{$this->customId}_link";
    }

    public function getTableLinkColumn() {
        return "value";
    }

    public function getQuery($id) {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return array($query, array($id));
    }

    public function getCustom($id) {
        $result = parent::getDb ()->prepare(str_format("select id, value as name from {0} where id = ?", $this->getTableName()));
        $result->execute (array ($id));
        if ($post = $result->fetchObject ()) {
            return new CustomColumn($id, $post->name, $this);
        }
        return NULL;
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select {0}.id as id, {0}.value as name, count(*) as count from {0}, {1} where {0}.id = {1}.{2} group by {0}.id, {0}.value order by {0}.value";
        $query = str_format ($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format (localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry ($post->name, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push ($entryArray, $entry);
        }
        return $entryArray;
    }
}

class CustomColumnTypeSeries extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_SERIES);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return "books_custom_column_{$this->customId}_link";
    }

    public function getTableLinkColumn() {
        return "value";
    }

    public function getQuery($id) {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return array($query, array($id));
    }

    public function getCustom($id) {
        $result = parent::getDb ()->prepare(str_format("select id, value as name from {0} where id = ?", $this->getTableName()));
        $result->execute (array ($id));
        if ($post = $result->fetchObject ()) {
            return new CustomColumn ($id, $post->name, $this);
        }
        return NULL;
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select {0}.id as id, {0}.value as name, count(*) as count from {0}, {1} where {0}.id = {1}.{2} group by {0}.id, {0}.value order by {0}.value";
        $query = str_format ($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format (localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry ($post->name, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push ($entryArray, $entry);
        }
        return $entryArray;
    }
}

class CustomColumnTypeEnumeration extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_ENUM);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return "books_custom_column_{$this->customId}_link";
    }

    public function getTableLinkColumn() {
        return "value";
    }

    public function getQuery($id) {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return array($query, array($id));
    }

    public function getCustom($id) {
        $result = parent::getDb ()->prepare(str_format("select id, value as name from {0} where id = ?", $this->getTableName()));
        $result->execute (array ($id));
        if ($post = $result->fetchObject ()) {
            return new CustomColumn ($id, $post->name, $this);
        }
        return NULL;
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select {0}.id as id, {0}.value as name, count(*) as count from {0}, {1} where {0}.id = {1}.{2} group by {0}.id, {0}.value order by {0}.value";
        $query = str_format ($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format (localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry ($post->name, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push ($entryArray, $entry);
        }
        return $entryArray;
    }
}

class CustomColumnTypeDate extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_DATE);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return NULL;
    }

    public function getTableLinkColumn() {
        return NULL;
    }

    public function getQuery($id) {
        $date = new DateTime($id);
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DATE, "{0}", "{1}", $this->getTableName());
        return array($query, array($date->format("Y-m-d")));
    }

    public function getCustom($id) {
        $date = new DateTime($id);

        return new CustomColumn ($id, $date->format(localize("customcolumn.date.format")), $this);
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select date(value) as datevalue, count(*) as count from custom_column_6 group by datevalue";
        $query = str_format ($queryFormat, $this->getTableName());
        $result = parent::getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $date = new DateTimeImmutable($post->datevalue);
            $id = $date->format("Y-m-d");

            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($id)));

            $entry = new Entry($date->format(localize("customcolumn.date.format")), $this->getEntryId($id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }

        return $entryArray;
    }
}

class CustomColumnTypeRating extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_RATING);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return "books_custom_column_{$this->customId}_link";
    }

    public function getTableLinkColumn() {
        return "value";
    }

    public function getQuery($id) {
        if ($id == 0) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING_NULL, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return array($query, array());
        } else {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return array($query, array($id));
        }
    }

    public function getCustom($id) {
        return new CustomColumn ($id, str_format(localize("customcolumn.stars", $id/2), $id/2), $this);
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select coalesce({0}.value, 0) as value, count(*) as count from books  left join {1} on  books.id = {1}.book left join {0} on {0}.id = {1}.value group by coalesce({0}.value, -1)";
        $query = str_format ($queryFormat, $this->getTableName(), $this->getTableLinkName());
        $result = parent::getDb()->query($query);

        $countArray = array(0=>0, 2=>0, 4=>0, 6=>0, 8=>0, 10=>0);
        while ($row = $result->fetchObject()) {
            $countArray[$row->value] = $row->count;
        }

        $entryArray = array();

        for ($i = 0; $i <= 5; $i++) {
            $count = $countArray[$i*2];
            $name = str_format(localize("customcolumn.stars", $i), $i);
            $entryid = $this->getEntryId($i*2);
            $content = str_format(localize("bookword", $count), $count);
            $linkarray = array(new LinkNavigation ($this->getUri($i*2)));
            $entry = new Entry($name, $entryid, $content, $this->datatype, $linkarray, "", $count);
            array_push($entryArray, $entry);
        }

        return $entryArray;
    }
}

class CustomColumnTypeBool extends CustomColumnType
{
    // PHP pre 5.6 does not support const arrays
    private $BOOLEAN_NAMES = array(
        -1 => "customcolumn.boolean.unknown", // localize("customcolumn.boolean.unknown")
        00 => "customcolumn.boolean.no",      // localize("customcolumn.boolean.no")
        +1 => "customcolumn.boolean.yes",     // localize("customcolumn.boolean.yes")
    );

    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_BOOL);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return "books_custom_column_{$this->customId}_link";
    }

    public function getTableLinkColumn() {
        return NULL;
    }

    public function getQuery($id) {
        if ($id == -1) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_NULL, "{0}", "{1}", $this->getTableName());
            return array($query, array());
        } else if ($id == 0) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_FALSE, "{0}", "{1}", $this->getTableName());
            return array($query, array());
        } else if ($id == 1) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_TRUE, "{0}", "{1}", $this->getTableName());
            return array($query, array());
        } else {
            return NULL;
        }
    }

    public function getCustom($id) {
        return new CustomColumn ($id, localize($this->BOOLEAN_NAMES[$id]), $this);
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select coalesce({0}.value, -1) as id, count(*) as count from books left join {0} on  books.id = {0}.book group by {0}.value order by {0}.value";
        $query = str_format ($queryFormat, $this->getTableName());
        $result = parent::getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry(localize($this->BOOLEAN_NAMES[$post->id]), $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }
}

class CustomColumnTypeInteger extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_INT);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return NULL;
    }

    public function getTableLinkColumn() {
        return NULL;
    }

    public function getQuery($id) {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DIRECT, "{0}", "{1}", $this->getTableName());
        return array($query, array($id));
    }

    public function getCustom($id) {
        return new CustomColumn($id, $id, $this);
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select value as id, count(*) as count from {0} group by value";
        $query = str_format ($queryFormat, $this->getTableName());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation($this->getUri($post->id)));

            $entry = new Entry($post->id, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }
}

class CustomColumnTypeFloat extends CustomColumnType
{
    public function __construct($pcustomId) {
        parent::__construct($pcustomId, self::getTitle($pcustomId), self::CUSTOM_TYPE_FLOAT);
    }

    public function getTableName() {
        return "custom_column_{$this->customId}";
    }

    public function getTableLinkName() {
        return NULL;
    }

    public function getTableLinkColumn() {
        return NULL;
    }

    public function getQuery($id) {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DIRECT, "{0}", "{1}", $this->getTableName());
        return array($query, array($id));
    }

    public function getCustom($id) {
        return new CustomColumn($id, $id, $this);
    }

    public function getAllCustomValues()
    {
        $queryFormat = "select value as id, count(*) as count from {0} group by value";
        $query = str_format ($queryFormat, $this->getTableName());

        $result = parent::getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation($this->getUri($post->id)));

            $entry = new Entry($post->id, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }
}
