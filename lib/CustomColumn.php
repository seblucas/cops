<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

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
     * Create an CustomColumn by CustomColumnID and ValueID
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
            'valueID'          => $this->valueID,
            'value'            => $this->value,
            'customColumnType' => (array)$this->customColumnType,
            'htmlvalue'        => $this->htmlvalue,
        );
    }
}
