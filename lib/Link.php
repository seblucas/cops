<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class Link
{
    public const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    public const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";
    public const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
    public const OPDS_NAVIGATION_TYPE = "application/atom+xml;profile=opds-catalog;kind=navigation";
    public const OPDS_PAGING_TYPE = "application/atom+xml;profile=opds-catalog;kind=acquisition";

    public $href;
    public $type;
    public $rel;
    public $title;
    public $facetGroup;
    public $activeFacet;

    public function __construct($phref, $ptype, $prel = null, $ptitle = null, $pfacetGroup = null, $pactiveFacet = false)
    {
        $this->href = $phref;
        $this->type = $ptype;
        $this->rel = $prel;
        $this->title = $ptitle;
        $this->facetGroup = $pfacetGroup;
        $this->activeFacet = $pactiveFacet;
    }

    public function hrefXhtml()
    {
        return $this->href;
    }

    public function getScriptName()
    {
        $parts = explode('/', $_SERVER["SCRIPT_NAME"]);
        return $parts[count($parts) - 1];
    }
}
