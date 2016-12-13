<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageQueryResult extends Page
{
    const SCOPE_TAG = "tag";
    const SCOPE_RATING = "rating";
    const SCOPE_SERIES = "series";
    const SCOPE_AUTHOR = "author";
    const SCOPE_BOOK = "book";
    const SCOPE_PUBLISHER = "publisher";

    private function useTypeahead () {
        return !is_null (getURLParam ("search"));
    }

    private function searchByScope ($scope, $limit = FALSE) {
        $n = $this->n;
        $numberPerPage = NULL;
        $queryNormedAndUp = trim($this->query);
        if (useNormAndUp ()) {
            $queryNormedAndUp = normAndUp ($this->query);
        }
        if ($limit) {
            $n = 1;
            $numberPerPage = 5;
        }
        switch ($scope) {
            case self::SCOPE_BOOK :
                $array = Book::getBooksByStartingLetter ('%' . $queryNormedAndUp, $n, NULL, $numberPerPage);
                break;
            case self::SCOPE_AUTHOR :
                $array = Author::getAuthorsForSearch ('%' . $queryNormedAndUp);
                break;
            case self::SCOPE_SERIES :
                $array = Serie::getAllSeriesByQuery ($queryNormedAndUp);
                break;
            case self::SCOPE_TAG :
                $array = Tag::getAllTagsByQuery ($queryNormedAndUp, $n, NULL, $numberPerPage);
                break;
            case self::SCOPE_PUBLISHER :
                $array = Publisher::getAllPublishersByQuery ($queryNormedAndUp);
                break;
            default:
                $array = Book::getBooksByQuery (
                    array ("all" => "%" . $queryNormedAndUp . "%"), $n);
        }

        return $array;
    }

    public function doSearchByCategory () {
        $database = GetUrlParam (DB);
        $out = array ();
        $pagequery = Base::PAGE_OPENSEARCH_QUERY;
        $dbArray = array ("");
        $d = $database;
        $query = $this->query;
        // Special case when no databases were chosen, we search on all databases
        if (Base::noDatabaseSelected ()) {
            $dbArray = Base::getDbNameList ();
            $d = 0;
        }
        foreach ($dbArray as $key) {
            if (Base::noDatabaseSelected ()) {
                array_push ($this->entryArray, new Entry ($key, DB . ":query:{$d}",
                                        " ", "text",
                                        array ( new LinkNavigation ("?" . DB . "={$d}")), "tt-header"));
                Base::getDb ($d);
            }
            foreach (array (PageQueryResult::SCOPE_BOOK,
                            PageQueryResult::SCOPE_AUTHOR,
                            PageQueryResult::SCOPE_SERIES,
                            PageQueryResult::SCOPE_TAG,
                            PageQueryResult::SCOPE_PUBLISHER) as $key) {
                if (in_array($key, getCurrentOption ('ignored_categories'))) {
                    continue;
                }
                $array = $this->searchByScope ($key, TRUE);

                $i = 0;
                if (count ($array) == 2 && is_array ($array [0])) {
                    $total = $array [1];
                    $array = $array [0];
                } else {
                    $total = count($array);
                }
                if ($total > 0) {
                    // Comment to help the perl i18n script
                    // str_format (localize("bookword", count($array))
                    // str_format (localize("authorword", count($array))
                    // str_format (localize("seriesword", count($array))
                    // str_format (localize("tagword", count($array))
                    // str_format (localize("publisherword", count($array))
                    array_push ($this->entryArray, new Entry (str_format (localize ("search.result.{$key}"), $this->query), DB . ":query:{$d}:{$key}",
                                        str_format (localize("{$key}word", $total), $total), "text",
                                        array ( new LinkNavigation ("?page={$pagequery}&query={$query}&db={$d}&scope={$key}")),
                                        Base::noDatabaseSelected () ? "" : "tt-header", $total));
                }
                if (!Base::noDatabaseSelected () && $this->useTypeahead ()) {
                    foreach ($array as $entry) {
                        array_push ($this->entryArray, $entry);
                        $i++;
                        if ($i > 4) { break; };
                    }
                }
            }
            $d++;
            if (Base::noDatabaseSelected ()) {
                Base::clearDb ();
            }
        }
        return $out;
    }

    public function InitializeContent ()
    {
        $scope = getURLParam ("scope");
        if (empty ($scope)) {
            $this->title = str_format (localize ("search.result"), $this->query);
        } else {
            // Comment to help the perl i18n script
            // str_format (localize ("search.result.author"), $this->query)
            // str_format (localize ("search.result.tag"), $this->query)
            // str_format (localize ("search.result.series"), $this->query)
            // str_format (localize ("search.result.book"), $this->query)
            // str_format (localize ("search.result.publisher"), $this->query)
            $this->title = str_format (localize ("search.result.{$scope}"), $this->query);
        }

        $crit = "%" . $this->query . "%";

        // Special case when we are doing a search and no database is selected
        if (Base::noDatabaseSelected () && !$this->useTypeahead ()) {
            $i = 0;
            foreach (Base::getDbNameList () as $key) {
                Base::clearDb ();
                list ($array, $totalNumber) = Book::getBooksByQuery (array ("all" => $crit), 1, $i, 1);
                array_push ($this->entryArray, new Entry ($key, DB . ":query:{$i}",
                                        str_format (localize ("bookword", $totalNumber), $totalNumber), "text",
                                        array ( new LinkNavigation ("?" . DB . "={$i}&page=9&query=" . $this->query)), "", $totalNumber));
                $i++;
            }
            return;
        }
        if (empty ($scope)) {
            $this->doSearchByCategory ();
            return;
        }

        $array = $this->searchByScope ($scope);
        if (count ($array) == 2 && is_array ($array [0])) {
            list ($this->entryArray, $this->totalNumber) = $array;
        } else {
            $this->entryArray = $array;
        }
    }
}
