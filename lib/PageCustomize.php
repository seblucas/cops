<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageCustomize extends Page
{
    private function isChecked($key, $testedValue = 1)
    {
        $value = getCurrentOption($key);
        if (is_array($value)) {
            if (in_array($testedValue, $value)) {
                return "checked='checked'";
            }
        } else {
            if ($value == $testedValue) {
                return "checked='checked'";
            }
        }
        return "";
    }

    private function isSelected($key, $value)
    {
        if (getCurrentOption($key) == $value) {
            return "selected='selected'";
        }
        return "";
    }

    private function getTemplateList()
    {
        $result = [];
        foreach (glob("templates/*") as $filename) {
            if (preg_match('/templates\/(.*)/', $filename, $m)) {
                array_push($result, $m [1]);
            }
        }
        return $result;
    }

    private function getStyleList()
    {
        $result = [];
        foreach (glob("templates/" . getCurrentTemplate() . "/styles/style-*.css") as $filename) {
            if (preg_match('/styles\/style-(.*?)\.css/', $filename, $m)) {
                array_push($result, $m [1]);
            }
        }
        return $result;
    }

    public function InitializeContent()
    {
        $this->title = localize("customize.title");
        $this->entryArray = [];

        $ignoredBaseArray = [PageQueryResult::SCOPE_AUTHOR,
                                   PageQueryResult::SCOPE_TAG,
                                   PageQueryResult::SCOPE_SERIES,
                                   PageQueryResult::SCOPE_PUBLISHER,
                                   PageQueryResult::SCOPE_RATING,
                                   "language"];

        $content = "";
        if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
            $content .= "<select id='template' onchange='updateCookie (this); window.location=$(\".headleft\").attr(\"href\");'>";

            foreach ($this-> getTemplateList() as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected("template", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getTemplateList() as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this); window.location=$(\".headleft\").attr(\"href\");' id='template' name='template' value='{$filename}' " . $this->isChecked("template", $filename) . " /><label for='template-{$filename}'> {$filename} </label>";
            }
        }
        array_push($this->entryArray, new Entry(
            "Template",
            "",
            $content,
            "text",
            []
        ));

        $content = "";
        if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
            $content .= '<select id="style" onchange="updateCookie (this);">';
            foreach ($this-> getStyleList() as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected("style", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getStyleList() as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this);' id='style-{$filename}' name='style' value='{$filename}' " . $this->isChecked("style", $filename) . " /><label for='style-{$filename}'> {$filename} </label>";
            }
        }
        array_push($this->entryArray, new Entry(
            localize("customize.style"),
            "",
            $content,
            "text",
            []
        ));
        if (!useServerSideRendering()) {
            $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="use_fancyapps" ' . $this->isChecked("use_fancyapps") . ' />';
            array_push($this->entryArray, new Entry(
                localize("customize.fancybox"),
                "",
                $content,
                "text",
                []
            ));
        }
        $content = '<input type="number" onchange="updateCookie (this);" id="max_item_per_page" value="' . getCurrentOption("max_item_per_page") . '" min="-1" max="1200" pattern="^[-+]?[0-9]+$" />';
        array_push($this->entryArray, new Entry(
            localize("customize.paging"),
            "",
            $content,
            "text",
            []
        ));
        $content = '<input type="text" onchange="updateCookie (this);" id="email" value="' . getCurrentOption("email") . '" />';
        array_push($this->entryArray, new Entry(
            localize("customize.email"),
            "",
            $content,
            "text",
            []
        ));
        $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="html_tag_filter" ' . $this->isChecked("html_tag_filter") . ' />';
        array_push($this->entryArray, new Entry(
            localize("customize.filter"),
            "",
            $content,
            "text",
            []
        ));
        $content = "";
        foreach ($ignoredBaseArray as $key) {
            $keyPlural = preg_replace('/(ss)$/', 's', $key . "s");
            $content .=  '<input type="checkbox" name="ignored_categories[]" onchange="updateCookieFromCheckboxGroup (this);" id="ignored_categories_' . $key . '" ' . $this->isChecked("ignored_categories", $key) . ' > ' . localize("{$keyPlural}.title") . '</input> ';
        }

        array_push($this->entryArray, new Entry(
            localize("customize.ignored"),
            "",
            $content,
            "text",
            []
        ));
    }
}
