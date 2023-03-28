<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class Author extends Base
{
    public const ALL_AUTHORS_ID = "cops:authors";

    public const AUTHOR_COLUMNS = "authors.id as id, authors.name as name, authors.sort as sort, count(*) as count";
    public const SQL_AUTHORS_BY_FIRST_LETTER = "select {0} from authors, books_authors_link where author = authors.id and upper (authors.sort) like ? group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_AUTHORS_FOR_SEARCH = "select {0} from authors, books_authors_link where author = authors.id and (upper (authors.sort) like ? or upper (authors.name) like ?) group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_ALL_AUTHORS = "select {0} from authors, books_authors_link where author = authors.id group by authors.id, authors.name, authors.sort order by sort";

    public $id;
    public $name;
    public $sort;

    public function __construct($post)
    {
        $this->id = $post->id;
        $this->name = str_replace("|", ",", $post->name);
        $this->sort = $post->sort;
    }

    public function getUri()
    {
        return "?page=".parent::PAGE_AUTHOR_DETAIL."&id=$this->id";
    }

    public function getEntryId()
    {
        return self::ALL_AUTHORS_ID.":".$this->id;
    }

    public static function getEntryIdByLetter($startingLetter)
    {
        return self::ALL_AUTHORS_ID.":letter:".$startingLetter;
    }

    public static function getCount()
    {
        // str_format (localize("authors.alphabetical", count(array))
        return parent::getCountGeneric("authors", self::ALL_AUTHORS_ID, parent::PAGE_ALL_AUTHORS);
    }

    public static function getAllAuthorsByFirstLetter()
    {
        [, $result] = parent::executeQuery("select {0}
from authors
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)", "substr (upper (sort), 1, 1) as title, count(*) as count", "", [], -1);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->title,
                Author::getEntryIdByLetter($post->title),
                str_format(localize("authorword", $post->count), $post->count),
                "text",
                [ new LinkNavigation("?page=".parent::PAGE_AUTHORS_FIRST_LETTER."&id=". rawurlencode($post->title))],
                "",
                $post->count
            ));
        }
        return $entryArray;
    }

    public static function getAuthorsByStartingLetter($letter)
    {
        return self::getEntryArray(self::SQL_AUTHORS_BY_FIRST_LETTER, [$letter . "%"]);
    }

    public static function getAuthorsForSearch($query)
    {
        return self::getEntryArray(self::SQL_AUTHORS_FOR_SEARCH, [$query . "%", $query . "%"]);
    }

    public static function getAllAuthors()
    {
        return self::getEntryArray(self::SQL_ALL_AUTHORS, []);
    }

    public static function getEntryArray($query, $params)
    {
        return Base::getEntryArrayWithBookNumber($query, self::AUTHOR_COLUMNS, $params, "Author");
    }

    public static function getAuthorById($authorId)
    {
        $result = parent::getDb()->prepare('select ' . self::AUTHOR_COLUMNS . ' from authors where id = ?');
        $result->execute([$authorId]);
        $post = $result->fetchObject();
        return new Author($post);
    }

    public static function getAuthorByBookId($bookId)
    {
        $result = parent::getDb()->prepare('select authors.id as id, authors.name as name, authors.sort as sort from authors, books_authors_link
where author = authors.id
and book = ? order by books_authors_link.id');
        $result->execute([$bookId]);
        $authorArray = [];
        while ($post = $result->fetchObject()) {
            array_push($authorArray, new Author($post));
        }
        return $authorArray;
    }
}
