<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

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
