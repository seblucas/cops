<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testCheckConfigurationCalibreDirectory () {
        global $config;
        $this->assertTrue(is_string($config["calibre_directory"]));
    }

    public function testCheckConfigurationOPDSTHumbnailHeight () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_opds_thumbnail_height']));
    }

    public function testCheckConfigurationHTMLTHumbnailHeight () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_html_thumbnail_height']));
    }

    public function testCheckConfigurationPreferedFormat () {
        global $config;
        $this->assertTrue(is_array($config["cops_prefered_format"]));
    }

    public function testCheckConfigurationUseUrlRewiting () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_use_url_rewriting']));
    }

    public function testCheckConfigurationGenerateInvalidOPDSStream () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_generate_invalid_opds_stream']));
    }

    public function testCheckConfigurationMaxItemPerPage () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_max_item_per_page']));
    }

    public function testCheckConfigurationAuthorSplitFirstLetter () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_author_split_first_letter']));
    }

    public function estCheckConfigurationTitlesSplitFirstLetter () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_titles_split_first_letter']));
    }

    public function testCheckConfigurationCopsUseFancyapps () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_use_fancyapps']));
    }

    public function testCheckConfigurationCopsBooksFilter () {
        global $config;
        $this->assertTrue(is_array($config["cops_books_filter"]));
    }

    public function testCheckConfigurationCalibreCustomColumn () {
        global $config;
        $this->assertTrue(is_array($config["cops_calibre_custom_column"]));
    }

    public function testCheckConfigurationCalibreCustomColumnList () {
        global $config;
        $this->assertTrue(is_array($config["cops_calibre_custom_column_list"]));
    }

    public function testCheckConfigurationCalibreCustomColumnPreview () {
        global $config;
        $this->assertTrue(is_array($config["cops_calibre_custom_column_preview"]));
    }

    public function testCheckConfigurationProvideKepub () {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_provide_kepub']));
    }

    public function testCheckConfigurationMailConfig () {
        global $config;
        $this->assertTrue(is_array($config["cops_mail_configuration"]));
    }

    public function testCheckConfiguratioHTMLTagFilter () {
        global $config;
        $this->assertTrue(is_int((int)$config["cops_html_tag_filter"]));
    }

    public function testCheckConfigurationIgnoredCategories () {
        global $config;
        $this->assertTrue(is_array($config["cops_ignored_categories"]));
    }
}
