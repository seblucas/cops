<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCheckConfigurationCalibreDirectory()
    {
        global $config;
        $this->assertTrue(is_string($config["calibre_directory"]));
    }

    public function testCheckConfigurationOPDSTHumbnailHeight()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_opds_thumbnail_height']));
    }

    public function testCheckConfigurationHTMLTHumbnailHeight()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_html_thumbnail_height']));
    }

    public function testCheckConfigurationPreferedFormat()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_prefered_format"]));
    }

    public function testCheckConfigurationUseUrlRewiting()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_use_url_rewriting']));
    }

    public function testCheckConfigurationGenerateInvalidOPDSStream()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_generate_invalid_opds_stream']));
    }

    public function testCheckConfigurationMaxItemPerPage()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_max_item_per_page']));
    }

    public function testCheckConfigurationAuthorSplitFirstLetter()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_author_split_first_letter']));
    }

    public function estCheckConfigurationTitlesSplitFirstLetter()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_titles_split_first_letter']));
    }

    public function testCheckConfigurationCopsUseFancyapps()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_use_fancyapps']));
    }

    public function testCheckConfigurationCopsBooksFilter()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_books_filter"]));
    }

    public function testCheckConfigurationCalibreCustomColumn()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_calibre_custom_column"]));
    }

    public function testCheckConfigurationCalibreCustomColumnList()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_calibre_custom_column_list"]));
    }

    public function testCheckConfigurationCalibreCustomColumnPreview()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_calibre_custom_column_preview"]));
    }

    public function testCheckConfigurationProvideKepub()
    {
        global $config;
        $this->assertTrue(is_int((int)$config['cops_provide_kepub']));
    }

    public function testCheckConfigurationMailConfig()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_mail_configuration"]));
    }

    public function testCheckConfiguratioHTMLTagFilter()
    {
        global $config;
        $this->assertTrue(is_int((int)$config["cops_html_tag_filter"]));
    }

    public function testCheckConfigurationIgnoredCategories()
    {
        global $config;
        $this->assertTrue(is_array($config["cops_ignored_categories"]));
    }

    public function testCheckConfigurationTemplate()
    {
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        global $config;
        $style = 'bootstrap';

        $config["cops_template"] = $style;

        $headcontent = file_get_contents(dirname(__FILE__) . '/../templates/' . $config["cops_template"] . '/file.html');
        $template = new doT();
        $tpl = $template->template($headcontent, null);
        $data = ["title"                 => $config['cops_title_default'],
            "version"               => VERSION,
            "opds_url"              => $config['cops_full_url'] . "feed.php",
            "customHeader"          => "",
            "template"              => $config["cops_template"],
            "server_side_rendering" => useServerSideRendering(),
            "current_css"           => getCurrentCss(),
            "favico"                => $config['cops_icon'],
            "getjson_url"           => "getJSON.php?" . addURLParameter(getQueryString(), "complete", 1)];

        $head = $tpl($data);

        $this->assertStringContainsString($style.".min.css", $head);
        $this->assertStringContainsString($style.".min.js", $head);
    }
}
