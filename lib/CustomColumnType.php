<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

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
