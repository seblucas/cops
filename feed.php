<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

    require_once ("config.php");
    require_once ("base.php");
    require_once ("author.php");
    require_once ("serie.php");
    require_once ("book.php");
    header ("Content-Type:application/xml");
    $page = Base::PAGE_INDEX;
    global $config;
    if (!empty ($_GET) && isset($_GET["page"])) {
        $page = $_GET["page"];
    }
    switch ($page) {
        case Base::PAGE_ALL_AUTHORS :
            $title = "All authors";
            break;
        case Base::PAGE_AUTHOR_DETAIL :
            $title = Author::getAuthorName ($_GET["id"]);
            break;
        case Base::PAGE_ALL_SERIES :
            $title = "All series";
            break;
        case Base::PAGE_ALL_BOOKS :
            $title = "All books by starting letter";
            break;
        case Base::PAGE_ALL_BOOKS_LETTER:
            $title = "All books starting by " . $_GET["id"];
            break;
        case Base::PAGE_ALL_RECENT_BOOKS :
            $title = "Most recent books";
            break;
        case Base::PAGE_SERIE_DETAIL : 
            $title = "Series : " . Serie::getSerieById ($_GET["id"])->name;
            break;
        case Base::PAGE_OPENSEARCH :
            echo Base::getOpenSearch ();
            return;
        case Base::PAGE_OPENSEARCH_QUERY :
            $title = "Search result for query <" . $_GET["query"] . ">";
            break;
        default:
            $title = $config['cops_title_default']; 
            break;
    }
    Base::startXmlDocument ($title);
    switch ($page) {
        case Base::PAGE_ALL_AUTHORS :
            Author::getAllAuthors();
            break;
        case Base::PAGE_AUTHOR_DETAIL :
            Book::getBooksByAuthor ($_GET["id"]);
            break;
        case Base::PAGE_ALL_SERIES :
            Serie::getAllSeries();
            break;
        case Base::PAGE_ALL_BOOKS :
            Book::getAllBooks ();
            break;
        case Base::PAGE_ALL_BOOKS_LETTER:
            Book::getBooksByStartingLetter ($_GET["id"]);
            break;
        case Base::PAGE_ALL_RECENT_BOOKS :
            Book::getAllRecentBooks ();
            break;
        case Base::PAGE_SERIE_DETAIL : 
            Book::getBooksBySeries ($_GET["id"]);
            break;
        case Base::PAGE_OPENSEARCH_QUERY :
            Book::getBooksByQuery ($_GET["query"]);
            break;
        default:
            Author::getCount();
            Serie::getCount();
            Book::getCount();
            break;
    }
    echo Base::endXmlDocument ();
?>
