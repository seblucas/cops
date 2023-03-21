<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class CustomColumnTypeComment extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_COMMENT);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    public function getQuery($id)
    {
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DIRECT_ID, "{0}", "{1}", $this->getTableName());
        return [$query, [$id]];
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
        return null;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === null || empty($desc)) {
            $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        }
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
        return new CustomColumn(null, localize("customcolumn.float.unknown"), $this);
    }

    public function isSearchable()
    {
        return false;
    }
}
