<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class LinkNavigation extends Link
{
    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, Link::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
        if (!is_null (GetUrlParam (DB))) $this->href = addURLParameter ($this->href, DB, GetUrlParam (DB));
        if (!preg_match ("#^\?(.*)#", $this->href) && !empty ($this->href)) $this->href = "?" . $this->href;
        if (preg_match ("/(bookdetail|getJSON).php/", parent::getScriptName())) {
            $this->href = "index.php" . $this->href;
        } else {
            $this->href = parent::getScriptName() . $this->href;
        }
    }
}
