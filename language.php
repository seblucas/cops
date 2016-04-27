<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class Language extends Base {
    const ALL_LANGUAGES_ID = "cops:languages";

    public $id;
    public $lang_code;

    public function __construct($pid, $plang_code) {
        $this->id = $pid;
        $this->lang_code = $plang_code;
    }

    public function getUri () {
        return "?page=".parent::PAGE_LANGUAGE_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_LANGUAGES_ID.":".$this->id;
    }

    public static function getLanguageString ($code) {
        $string = localize("languages.".$code);
        if (preg_match ("/^languages/", $string)) {
            return $code;
        }
        return $string;
    }

    public static function getCount() {
        // str_format (localize("languages.alphabetical", count(array))
        return parent::getCountGeneric ("languages", self::ALL_LANGUAGES_ID, parent::PAGE_ALL_LANGUAGES);
    }

    public static function getLanguageById ($languageId) {
        $result = parent::getDb ()->prepare('select id, lang_code  from languages where id = ?');
        $result->execute (array ($languageId));
        if ($post = $result->fetchObject ()) {
            return new Language ($post->id, Language::getLanguageString ($post->lang_code));
        }
        return NULL;
    }



    public static function getAllLanguages() {
        $result = parent::getDb ()->query('select languages.id as id, languages.lang_code as lang_code, count(*) as count
from languages, books_languages_link
where languages.id = books_languages_link.lang_code
group by languages.id, books_languages_link.lang_code
order by languages.lang_code');
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $language = new Language ($post->id, $post->lang_code);
            array_push ($entryArray, new Entry (Language::getLanguageString ($language->lang_code), $language->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($language->getUri ())), "", $post->count));
        }
        return $entryArray;
    }
}
