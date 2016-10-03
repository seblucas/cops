<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

/**
 * A CustomColumn with an value
 */
class CustomColumn extends Base
{
    /* @var string|integer the ID of the value */
    public $valueID;
    /* @var string the (string) representation of the value */
    public $value;
    /* @var CustomColumnType the custom column that contains the value */
    public $customColumnType;
    /* @var string the value encoded for HTML displaying */
    public $htmlvalue;

    /**
     * CustomColumn constructor.
     *
     * @param integer $pid id of the chosen value
     * @param string $pvalue string representation of the value
     * @param CustomColumnType $pcustomColumnType the CustomColumn this value lives in
     */
    public function __construct($pid, $pvalue, $pcustomColumnType)
    {
        $this->valueID = $pid;
        $this->value = $pvalue;
        $this->customColumnType = $pcustomColumnType;
        $this->htmlvalue = $this->customColumnType->encodeHTMLValue($this->value);
    }

    /**
     * Get the URI to show all books with this value
     *
     * @return string
     */
    public function getUri()
    {
        return $this->customColumnType->getUri($this->valueID);
    }

    /**
     * Get the EntryID to show all books with this value
     *
     * @return string
     */
    public function getEntryId()
    {
        return $this->customColumnType->getEntryId($this->valueID);
    }

    /**
     * Get the query to find all books with this value
     * the returning array has two values:
     *  - first the query (string)
     *  - second an array of all PreparedStatement parameters
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->customColumnType->getQuery($this->valueID);
    }

    /**
     * Return the value of this column as an HTML snippet
     *
     * @return string
     */
    public function getHTMLEncodedValue()
    {
        return $this->htmlvalue;
    }

    /**
     * Craete an CustomColumn by CustomColumnID and ValueID
     *
     * @param integer $customId the id of the customColumn
     * @param integer $id the id of the chosen value
     * @return CustomColumn|null
     */
    public static function createCustom($customId, $id)
    {
        $columnType = CustomColumnType::createByCustomID($customId);

        return $columnType->getCustom($id);
    }

    /**
     * Return this object as an array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'valueID' => $this->valueID,
            'value' => $this->value,
            'customColumnType' => (array) $this->customColumnType,
            'htmlvalue' => $this->htmlvalue);
    }
}

/**
 * A single calibre custom column
 */
abstract class CustomColumnType extends Base
{
    const ALL_CUSTOMS_ID       = "cops:custom";

    const CUSTOM_TYPE_TEXT      = "text";        // type 1 + 2
    const CUSTOM_TYPE_COMMENT   = "comments";    // type 3
    const CUSTOM_TYPE_SERIES    = "series";      // type 4
    const CUSTOM_TYPE_ENUM      = "enumeration"; // type 5
    const CUSTOM_TYPE_DATE      = "datetime";    // type 6
    const CUSTOM_TYPE_FLOAT     = "float";       // type 7
    const CUSTOM_TYPE_INT       = "int";         // type 8
    const CUSTOM_TYPE_RATING    = "rating";      // type 9
    const CUSTOM_TYPE_BOOL      = "bool";        // type 10
    const CUSTOM_TYPE_COMPOSITE = "composite";   // type 11 + 12

    /** @var array[integer]CustomColumnType  */
    private static $customColumnCacheID = array();

    /** @var array[string]CustomColumnType  */
    private static $customColumnCacheLookup = array();

    /** @var integer the id of this column */
    public $customId;
    /** @var string name/title of this column */
    public $columnTitle;
    /** @var string the datatype of this column (one of the CUSTOM_TYPE_* constant values) */
    public $datatype;
    /** @var null|Entry[] */
    private $customValues = NULL;

    protected function __construct($pcustomId, $pdatatype)
    {
        $this->columnTitle = self::getTitleByCustomID($pcustomId);
        $this->customId = $pcustomId;
        $this->datatype = $pdatatype;
        $this->customValues = NULL;
    }

    /**
     * The URI to show all book swith a specific value in this column
     *
     * @param string|integer $id the id of the value to show
     * @return string
     */
    public function getUri($id)
    {
        return "?page=" . parent::PAGE_CUSTOM_DETAIL . "&custom={$this->customId}&id={$id}";
    }

    /**
     * The URI to show all the values of this column
     *
     * @return string
     */
    public function getUriAllCustoms()
    {
        return "?page=" . parent::PAGE_ALL_CUSTOMS . "&custom={$this->customId}";
    }

    /**
     * The EntryID to show all book swith a specific value in this column
     *
     * @param string|integer $id the id of the value to show
     * @return string
     */
    public function getEntryId($id)
    {
        return self::ALL_CUSTOMS_ID . ":" . $this->customId . ":" . $id;
    }

    /**
     * The EntryID to show all the values of this column
     *
     * @return string
     */
    public function getAllCustomsId()
    {
        return self::ALL_CUSTOMS_ID . ":" . $this->customId;
    }

    /**
     * The title of this column
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->columnTitle;
    }

    /**
     * The description of this column as it is definied in the database
     *
     * @return string|null
     */
    public function getDatabaseDescription()
    {
        $result = $this->getDb()->prepare('SELECT display FROM custom_columns WHERE id = ?');
        $result->execute(array($this->customId));
        if ($post = $result->fetchObject()) {
            $json = json_decode($post->display);
            return (isset($json->description) && !empty($json->description)) ? $json->description : NULL;
        }
        return NULL;
    }

    /**
     * Get the Entry for this column
     * This is used in the initializeContent method to display e.g. the index page
     *
     * @return Entry
     */
    public function getCount()
    {
        $ptitle = $this->getTitle();
        $pid = $this->getAllCustomsId();
        $pcontent = $this->getDescription();
        $pcontentType = $this->datatype;
        $plinkArray = array(new LinkNavigation($this->getUriAllCustoms()));
        $pclass = "";
        $pcount = $this->getDistinctValueCount();

        return new Entry($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pclass, $pcount);
    }

    /**
     * Get the amount of distinct values for this column
     *
     * @return int
     */
    protected function getDistinctValueCount()
    {
        return count($this->getAllCustomValues());
    }

    /**
     * Encode a value of this column ready to be displayed in an HTML document
     *
     * @param integer|string $value
     * @return string
     */
    public function encodeHTMLValue($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * Get the datatype of a CustomColumn by its customID
     *
     * @param integer $customId
     * @return string|null
     */
    private static function getDatatypeByCustomID($customId)
    {
        $result = parent::getDb()->prepare('SELECT datatype FROM custom_columns WHERE id = ?');
        $result->execute(array($customId));
        if ($post = $result->fetchObject()) {
            return $post->datatype;
        }
        return NULL;
    }

    /**
     * Create a CustomColumnType by CustomID
     *
     * @param integer $customId the id of the custom column
     * @return CustomColumnType|null
     * @throws Exception If the $customId is not found or the datatype is unknown
     */
    public static function createByCustomID($customId)
    {
        // Reuse already created CustomColumns for performance
        if (array_key_exists($customId, self::$customColumnCacheID))
            return self::$customColumnCacheID[$customId];

        $datatype = self::getDatatypeByCustomID($customId);

        switch ($datatype) {
            case self::CUSTOM_TYPE_TEXT:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeText($customId);
            case self::CUSTOM_TYPE_SERIES:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeSeries($customId);
            case self::CUSTOM_TYPE_ENUM:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeEnumeration($customId);
            case self::CUSTOM_TYPE_COMMENT:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeComment($customId);
            case self::CUSTOM_TYPE_DATE:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeDate($customId);
            case self::CUSTOM_TYPE_FLOAT:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeFloat($customId);
            case self::CUSTOM_TYPE_INT:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeInteger($customId);
            case self::CUSTOM_TYPE_RATING:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeRating($customId);
            case self::CUSTOM_TYPE_BOOL:
                return self::$customColumnCacheID[$customId] = new CustomColumnTypeBool($customId);
            case self::CUSTOM_TYPE_COMPOSITE:
                return NULL; //TODO Currently not supported
            default:
                throw new Exception("Unkown column type: " . $datatype);
        }
    }

    /**
     * Create a CustomColumnType by its lookup name
     *
     * @param string $lookup the lookup-name of the custom column
     * @return CustomColumnType|null
     */
    public static function createByLookup($lookup)
    {
        // Reuse already created CustomColumns for performance
        if (array_key_exists($lookup, self::$customColumnCacheLookup))
            return self::$customColumnCacheLookup[$lookup];

        $result = parent::getDb()->prepare('SELECT id FROM custom_columns WHERE label = ?');
        $result->execute(array($lookup));
        if ($post = $result->fetchObject()) {
            return self::$customColumnCacheLookup[$lookup] = self::createByCustomID($post->id);
        }
        return self::$customColumnCacheLookup[$lookup] = NULL;
    }

    /**
     * Return an entry array for all possible (in the DB used) values of this column
     * These are the values used in the getUriAllCustoms() page
     *
     * @return Entry[]
     */
    public function getAllCustomValues()
    {
        // lazy loading
        if ($this->customValues == NULL)
            $this->customValues = $this->getAllCustomValuesFromDatabase();

        return $this->customValues;
    }

    /**
     * Get the title of a CustomColumn by its customID
     *
     * @param integer $customId
     * @return string
     */
    protected static function getTitleByCustomID($customId)
    {
        $result = parent::getDb()->prepare('SELECT name FROM custom_columns WHERE id = ?');
        $result->execute(array($customId));
        if ($post = $result->fetchObject()) {
            return $post->name;
        }
        return "";
    }

    /**
     * Get the query to find all books with a specific value of this column
     * the returning array has two values:
     *  - first the query (string)
     *  - second an array of all PreparedStatement parameters
     *
     * @param string|integer $id the id of the searched value
     * @return array
     */
    abstract public function getQuery($id);

    /**
     * Get a CustomColumn for a specified (by ID) value
     *
     * @param string|integer $id the id of the searched value
     * @return CustomColumn
     */
    abstract public function getCustom($id);

    /**
     * Return an entry array for all possible (in the DB used) values of this column by querying the database
     *
     * @return Entry[]
     */
    abstract protected function getAllCustomValuesFromDatabase();

    /**
     * The description used in the index page
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Find the value of this column for a specific book
     *
     * @param Book $book
     * @return CustomColumn
     */
    public abstract function getCustomByBook($book);

    /**
     * Is this column searchable by value
     * only searchable columns can be displayed on the index page
     *
     * @return bool
     */
    public abstract function isSearchable();
}

class CustomColumnTypeText extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_TEXT);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string|null
     */
    private function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string|null
     */
    private function getTableLinkColumn()
    {
        return "value";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return array($query, array($id));
    }

    public function getCustom($id)
    {
        $result = $this->getDb()->prepare(str_format("SELECT id, value AS name FROM {0} WHERE id = ?", $this->getTableName()));
        $result->execute(array($id));
        if ($post = $result->fetchObject()) {
            return new CustomColumn($id, $post->name, $this);
        }
        return NULL;
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.value AS name, count(*) AS count FROM {0}, {1} WHERE {0}.id = {1}.{2} GROUP BY {0}.id, {0}.value ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = $this->getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry($post->name, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === NULL || empty($desc)) $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        return $desc;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = {3} ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->id, $post->name, $this);
        }
        return new CustomColumn(NULL, "", $this);
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeSeries extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_SERIES);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string|null
     */
    private function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string|null
     */
    private function getTableLinkColumn()
    {
        return "value";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return array($query, array($id));
    }

    public function getCustom($id)
    {
        $result = $this->getDb()->prepare(str_format("SELECT id, value AS name FROM {0} WHERE id = ?", $this->getTableName()));
        $result->execute(array($id));
        if ($post = $result->fetchObject()) {
            return new CustomColumn($id, $post->name, $this);
        }
        return NULL;
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.value AS name, count(*) AS count FROM {0}, {1} WHERE {0}.id = {1}.{2} GROUP BY {0}.id, {0}.value ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = $this->getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation($this->getUri($post->id)));

            $entry = new Entry($post->name, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        return str_format(localize("customcolumn.description.series", $this->getDistinctValueCount()), $this->getDistinctValueCount());
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.id AS id, {1}.{2} AS name, {1}.extra AS extra FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = {3}";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->id, $post->name . " [" . $post->extra . "]", $this);
        }
        return new CustomColumn(NULL, "", $this);
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeEnumeration extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_ENUM);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string|null
     */
    private function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string|null
     */
    private function getTableLinkColumn()
    {
        return "value";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return array($query, array($id));
    }

    public function getCustom($id)
    {
        $result = $this->getDb()->prepare(str_format("SELECT id, value AS name FROM {0} WHERE id = ?", $this->getTableName()));
        $result->execute(array($id));
        if ($post = $result->fetchObject()) {
            return new CustomColumn ($id, $post->name, $this);
        }
        return NULL;
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.value AS name, count(*) AS count FROM {0}, {1} WHERE {0}.id = {1}.{2} GROUP BY {0}.id, {0}.value ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = $this->getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry ($post->name, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        return str_format(localize("customcolumn.description.enum", $this->getDistinctValueCount()), $this->getDistinctValueCount());
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = {3}";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->id, $post->name, $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.enum.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeDate extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_DATE);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    public function getQuery($id)
    {
        $date = new DateTime($id);
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DATE, "{0}", "{1}", $this->getTableName());
        return array($query, array($date->format("Y-m-d")));
    }

    public function getCustom($id)
    {
        $date = new DateTime($id);

        return new CustomColumn($id, $date->format(localize("customcolumn.date.format")), $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT date(value) AS datevalue, count(*) AS count FROM {0} GROUP BY datevalue";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);
            $id = $date->format("Y-m-d");

            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($id)));

            $entry = new Entry($date->format(localize("customcolumn.date.format")), $this->getEntryId($id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }

        return $entryArray;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === NULL || empty($desc)) $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        return $desc;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT date({0}.value) AS datevalue FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);

            return new CustomColumn($date->format("Y-m-d"), $date->format(localize("customcolumn.date.format")), $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.date.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeRating extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_RATING);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string|null
     */
    private function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string|null
     */
    private function getTableLinkColumn()
    {
        return "value";
    }

    public function getQuery($id)
    {
        if ($id == 0) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING_NULL, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return array($query, array());
        } else {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return array($query, array($id));
        }
    }

    public function getCustom($id)
    {
        return new CustomColumn ($id, str_format(localize("customcolumn.stars", $id / 2), $id / 2), $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT coalesce({0}.value, 0) AS value, count(*) AS count FROM books  LEFT JOIN {1} ON  books.id = {1}.book LEFT JOIN {0} ON {0}.id = {1}.value GROUP BY coalesce({0}.value, -1)";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName());
        $result = $this->getDb()->query($query);

        $countArray = array(0 => 0, 2 => 0, 4 => 0, 6 => 0, 8 => 0, 10 => 0);
        while ($row = $result->fetchObject()) {
            $countArray[$row->value] = $row->count;
        }

        $entryArray = array();

        for ($i = 0; $i <= 5; $i++) {
            $count = $countArray[$i * 2];
            $name = str_format(localize("customcolumn.stars", $i), $i);
            $entryid = $this->getEntryId($i * 2);
            $content = str_format(localize("bookword", $count), $count);
            $linkarray = array(new LinkNavigation($this->getUri($i * 2)));
            $entry = new Entry($name, $entryid, $content, $this->datatype, $linkarray, "", $count);
            array_push($entryArray, $entry);
        }

        return $entryArray;
    }

    public function getDescription()
    {
        return localize("customcolumn.description.rating");
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = {3}";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, str_format(localize("customcolumn.stars", $post->value / 2), $post->value / 2), $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.rating.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
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

    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_BOOL);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    public function getQuery($id)
    {
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

    public function getCustom($id)
    {
        return new CustomColumn($id, localize($this->BOOLEAN_NAMES[$id]), $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT coalesce({0}.value, -1) AS id, count(*) AS count FROM books LEFT JOIN {0} ON  books.id = {0}.book GROUP BY {0}.value ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry(localize($this->BOOLEAN_NAMES[$post->id]), $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        return localize("customcolumn.description.bool");
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS boolvalue FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->boolvalue, localize($this->BOOLEAN_NAMES[$post->boolvalue]), $this);
        } else {
            return new CustomColumn(-1, localize($this->BOOLEAN_NAMES[-1]), $this);
        }
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeInteger extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_INT);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DIRECT, "{0}", "{1}", $this->getTableName());
        return array($query, array($id));
    }

    public function getCustom($id)
    {
        return new CustomColumn($id, $id, $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} GROUP BY value";
        $query = str_format($queryFormat, $this->getTableName());

        $result = $this->getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation($this->getUri($post->id)));

            $entry = new Entry($post->id, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === NULL || empty($desc)) $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        return $desc;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, $post->value, $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.int.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeFloat extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_FLOAT);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DIRECT, "{0}", "{1}", $this->getTableName());
        return array($query, array($id));
    }

    public function getCustom($id)
    {
        return new CustomColumn($id, $id, $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} GROUP BY value";
        $query = str_format($queryFormat, $this->getTableName());

        $result = $this->getDb()->query($query);
        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation($this->getUri($post->id)));

            $entry = new Entry($post->id, $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === NULL || empty($desc)) $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        return $desc;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, $post->value, $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.float.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}

class CustomColumnTypeComment extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_COMMENT);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DIRECT_ID, "{0}", "{1}", $this->getTableName());
        return array($query, array($id));
    }

    public function getCustom($id)
    {
        return new CustomColumn($id, $id, $this);
    }

    public function encodeHTMLValue($value)
    {
        return "<div>" . $value . "</div>"; // no htmlspecialchars, this is already HTML
    }

    protected function getAllCustomValuesFromDatabase()
    {
        return NULL;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === NULL || empty($desc)) $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        return $desc;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.value AS value FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->id, $post->value, $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.float.unknown"), $this);
    }

    public function isSearchable()
    {
        return false;
    }
}
