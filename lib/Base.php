<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

abstract class Base
{
    const PAGE_INDEX = "index";
    const PAGE_ALL_AUTHORS = "1";
    const PAGE_AUTHORS_FIRST_LETTER = "2";
    const PAGE_AUTHOR_DETAIL = "3";
    const PAGE_ALL_BOOKS = "4";
    const PAGE_ALL_BOOKS_LETTER = "5";
    const PAGE_ALL_SERIES = "6";
    const PAGE_SERIE_DETAIL = "7";
    const PAGE_OPENSEARCH = "8";
    const PAGE_OPENSEARCH_QUERY = "9";
    const PAGE_ALL_RECENT_BOOKS = "10";
    const PAGE_ALL_TAGS = "11";
    const PAGE_TAG_DETAIL = "12";
    const PAGE_BOOK_DETAIL = "13";
    const PAGE_ALL_CUSTOMS = "14";
    const PAGE_CUSTOM_DETAIL = "15";
    const PAGE_ABOUT = "16";
    const PAGE_ALL_LANGUAGES = "17";
    const PAGE_LANGUAGE_DETAIL = "18";
    const PAGE_CUSTOMIZE = "19";
    const PAGE_ALL_PUBLISHERS = "20";
    const PAGE_PUBLISHER_DETAIL = "21";
    const PAGE_ALL_RATINGS = "22";
    const PAGE_RATING_DETAIL = "23";

    const COMPATIBILITY_XML_ALDIKO = "aldiko";

    private static $db = NULL;

    public static function isMultipleDatabaseEnabled () {
        global $config;
        return is_array ($config['calibre_directory']);
    }

    public static function useAbsolutePath () {
        global $config;
        $path = self::getDbDirectory();
        return preg_match ('/^\//', $path) || // Linux /
               preg_match ('/^\w\:/', $path); // Windows X:
    }

    public static function noDatabaseSelected () {
        return self::isMultipleDatabaseEnabled () && is_null (GetUrlParam (DB));
    }

    public static function getDbList () {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            return $config['calibre_directory'];
        } else {
            return array ("" => $config['calibre_directory']);
        }
    }

    public static function getDbNameList () {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            return array_keys ($config['calibre_directory']);
        } else {
            return array ("");
        }
    }

    public static function getDbName ($database = NULL) {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            if (is_null ($database)) $database = GetUrlParam (DB, 0);
            if (!is_null($database) && !preg_match('/^\d+$/', $database)) {
                self::error ($database);
            }
            $array = array_keys ($config['calibre_directory']);
            return  $array[$database];
        }
        return "";
    }

    public static function getDbDirectory ($database = NULL) {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            if (is_null ($database)) $database = GetUrlParam (DB, 0);
            if (!is_null($database) && !preg_match('/^\d+$/', $database)) {
                self::error ($database);
            }
            $array = array_values ($config['calibre_directory']);
            return  $array[$database];
        }
        return $config['calibre_directory'];
    }


    public static function getDbFileName ($database = NULL) {
        return self::getDbDirectory ($database) .'metadata.db';
    }

    private static function error ($database) {
        if (php_sapi_name() != "cli") {
            header("location: checkconfig.php?err=1");
        }
        throw new Exception("Database <{$database}> not found.");
    }

    public static function getDb ($database = NULL) {
        if (is_null (self::$db)) {
            try {
                if (is_readable (self::getDbFileName ($database))) {
                    self::$db = new PDO('sqlite:'. self::getDbFileName ($database));
                    if (useNormAndUp ()) {
                        self::$db->sqliteCreateFunction ('normAndUp', 'normAndUp', 1);
                    }
                } else {
                    self::error ($database);
                }
            } catch (Exception $e) {
                self::error ($database);
            }
        }
        return self::$db;
    }

    public static function checkDatabaseAvailability () {
        if (self::noDatabaseSelected ()) {
            for ($i = 0; $i < count (self::getDbList ()); $i++) {
                self::getDb ($i);
                self::clearDb ();
            }
        } else {
            self::getDb ();
        }
        return true;
    }

    public static function clearDb () {
        self::$db = NULL;
    }

    public static function executeQuerySingle ($query, $database = NULL) {
        return self::getDb ($database)->query($query)->fetchColumn();
    }

    public static function getCountGeneric($table, $id, $pageId, $numberOfString = NULL) {
        if (!$numberOfString) {
            $numberOfString = $table . ".alphabetical";
        }
        $count = self::executeQuerySingle ('select count(*) from ' . $table);
        if ($count == 0) return NULL;
        $entry = new Entry (localize($table . ".title"), $id,
            str_format (localize($numberOfString, $count), $count), "text",
            array ( new LinkNavigation ("?page=".$pageId)), "", $count);
        return $entry;
    }

    public static function getEntryArrayWithBookNumber ($query, $columns, $params, $category) {
        /* @var $result PDOStatement */

        list (, $result) = self::executeQuery ($query, $columns, "", $params, -1);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            /* @var $instance Author|Tag|Serie|Publisher */

            $instance = new $category ($post);
            if (property_exists($post, "sort")) {
                $title = $post->sort;
            } else {
                $title = $post->name;
            }
            array_push ($entryArray, new Entry ($title, $instance->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($instance->getUri ())), "", $post->count));
        }
        return $entryArray;
    }

    public static function executeQuery($query, $columns, $filter, $params, $n, $database = NULL, $numberPerPage = NULL) {
        $totalResult = -1;

        if (useNormAndUp ()) {
            $query = preg_replace("/upper/", "normAndUp", $query);
            $columns = preg_replace("/upper/", "normAndUp", $columns);
        }

        if (is_null ($numberPerPage)) {
            $numberPerPage = getCurrentOption ("max_item_per_page");
        }

        if ($numberPerPage != -1 && $n != -1)
        {
            // First check total number of results
            $result = self::getDb ($database)->prepare (str_format ($query, "count(*)", $filter));
            $result->execute ($params);
            $totalResult = $result->fetchColumn ();

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push ($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $result = self::getDb ($database)->prepare(str_format ($query, $columns, $filter));
        $result->execute ($params);
        return array ($totalResult, $result);
    }

}
