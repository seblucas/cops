<?php
/**
 * PHP EPub Meta library
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sébastien Lucas <sebastien@slucas.fr>
 */

class EPubDOMXPath extends DOMXPath
{
    public function __construct(DOMDocument $doc)
    {
        parent::__construct($doc);

        if (is_a($doc->documentElement, 'EPubDOMElement')) {
            foreach ($doc->documentElement->namespaces as $ns => $url) {
                $this->registerNamespace($ns, $url);
            }
        }
    }
}
