<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
 
    require_once ("config.php");
    require_once ("base.php");
    require_once ("author.php");
    require_once ("serie.php");
    require_once ("tag.php");
    require_once ("language.php");
    require_once ("customcolumn.php");
    require_once ("book.php");
    
    header ("Content-Type:application/json;charset=utf-8");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
    $n = getURLParam ("n", "1");
    $database = GetUrlParam (DB);
    
    $currentPage = Page::getPage ($page, $qid, $query, $n);
    $currentPage->InitializeContent ();
    
    $out = array ( "title" => $currentPage->title, "version" => VERSION);
    $entries = array ();
    foreach ($currentPage->entryArray as $entry) {
        array_push ($entries, $entry->getContentArray ());
    }
    if (!is_null ($currentPage->book)) {
        $out ["book"] = $currentPage->book->getFullContentArray ();
    }
    $out ["databaseId"] = GetUrlParam (DB, "");
    $out ["databaseName"] = Base::getDbName ();
    $out ["page"] = $page;
    $out ["entries"] = $entries;
    $out ["isPaginated"] = 0;
    if ($currentPage->isPaginated ()) {
        $prevLink = $currentPage->getPrevLink ();
        $nextLink = $currentPage->getNextLink ();
        $out ["isPaginated"] = 1;
        $out ["prevLink"] = "";
        if (!is_null ($prevLink)) {
            $out ["prevLink"] = $prevLink->hrefXhtml ();
        }
        $out ["nextLink"] = "";
        if (!is_null ($nextLink)) {
            $out ["nextLink"] = $nextLink->hrefXhtml ();
        }
        $out ["maxPage"] = $currentPage->getMaxPage ();
        $out ["currentPage"] = $currentPage->n;
    }
    $out ["i18n"] = array ("coverAlt" => localize("i18n.coversection"),
                   "authorsTitle" => localize("authors.title"),
                   "tagsTitle" => localize("tags.title"),
                   "seriesTitle" => localize("series.title"),
                   "customizeTitle" => localize ("customize.title"),
                   "aboutTitle" => localize ("about.title"),
                   "previousAlt" => localize ("paging.previous.alternate"),
                   "nextAlt" => localize ("paging.next.alternate"),
                   "searchAlt" => localize ("search.alternate"),
                   "homeAlt" => localize ("home.alternate"),
                   "permalinkAlt" => localize ("permalink.alternate"),
                   "pubdateTitle" => localize("pubdate.title"),
                   "languagesTitle" => localize("language.title"),
                   "contentTitle" => localize("content.summary"));

    $out ["containsBook"] = 0;
    if ($currentPage->containsBook ()) {
        $out ["containsBook"] = 1;
    }
    $out["abouturl"] = "about.xml";
    if (getCurrentOption ('use_fancyapps') == 0) {
        $out["abouturl"] = "index.php" . str_replace ("&", "&amp;", addURLParameter ("?page=16", DB, $database));
    }
    
    $out ["homeurl"] = "index.php";
    if ($page != Base::PAGE_INDEX && !is_null ($database)) $out ["homeurl"] = $out ["homeurl"] .  "?" . addURLParameter ("", DB, $database);

    
    echo json_encode ($out);

?>