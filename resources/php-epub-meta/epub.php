<?php
/**
 * PHP EPub Meta library
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(realpath( dirname( __FILE__ ) ) . '/tbszip.php');

define ("METADATA_FILE", "META-INF/container.xml");

class EPub {
    public $xml; //FIXME change to protected, later
    public $toc;
    protected $xpath;
    protected $toc_xpath;
    protected $file;
    protected $meta;
    protected $zip;
    protected $coverpath='';
    protected $namespaces;
    protected $imagetoadd='';

    /**
     * Constructor
     *
     * @param string $file path to epub file to work on
     * @param string $zipClass class to handle zip
     * @throws Exception if metadata could not be loaded
     */
    public function __construct($file, $zipClass = 'clsTbsZip'){
        // open file
        $this->file = $file;
        $this->zip = new $zipClass();
        if(!$this->zip->Open($this->file)){
            throw new Exception('Failed to read epub file');
        }

        // read container data
        if (!$this->zip->FileExists(METADATA_FILE)) {
            throw new Exception ("Unable to find metadata.xml");
        }

        $data = $this->zip->FileRead(METADATA_FILE);
        if($data == false){
            throw new Exception('Failed to access epub container data');
        }
        $xml = new DOMDocument();
        $xml->registerNodeClass('DOMElement','EPubDOMElement');
        $xml->loadXML($data);
        $xpath = new EPubDOMXPath($xml);
        $nodes = $xpath->query('//n:rootfiles/n:rootfile[@media-type="application/oebps-package+xml"]');
        $this->meta = $nodes->item(0)->attr('full-path');

        // load metadata
        if (!$this->zip->FileExists($this->meta)) {
            throw new Exception ("Unable to find " . $this->meta);
        }

        $data = $this->zip->FileRead($this->meta);
        if(!$data){
            throw new Exception('Failed to access epub metadata');
        }
        $this->xml =  new DOMDocument();
        $this->xml->registerNodeClass('DOMElement','EPubDOMElement');
        $this->xml->loadXML($data);
        $this->xml->formatOutput = true;
        $this->xpath = new EPubDOMXPath($this->xml);
    }

    public function initSpineComponent ()
    {
        $spine = $this->xpath->query('//opf:spine')->item(0);
        $tocid = $spine->getAttribute('toc');
        $tochref = $this->xpath->query("//opf:manifest/opf:item[@id='$tocid']")->item(0)->attr('href');
        $tocpath = $this->getFullPath ($tochref);
        // read epub toc
        if (!$this->zip->FileExists($tocpath)) {
            throw new Exception ("Unable to find " . $tocpath);
        }

        $data = $this->zip->FileRead($tocpath);
        $this->toc =  new DOMDocument();
        $this->toc->registerNodeClass('DOMElement','EPubDOMElement');
        $this->toc->loadXML($data);
        $this->toc_xpath = new EPubDOMXPath($this->toc);
        $rootNamespace = $this->toc->lookupNamespaceUri($this->toc->namespaceURI);
        $this->toc_xpath->registerNamespace('x', $rootNamespace);
    }

    /**
     * file name getter
     */
    public function file(){
        return $this->file;
    }

    /**
     * Close the epub file
     */
    public function close (){
        $this->zip->FileCancelModif($this->meta);
        // TODO : Add cancelation of cover image
        $this->zip->Close ();
    }

    /**
     * Remove iTunes files
     */
    public function cleanITunesCrap () {
        if ($this->zip->FileExists("iTunesMetadata.plist")) {
            $this->zip->FileReplace ("iTunesMetadata.plist", false);
        }
        if ($this->zip->FileExists("iTunesArtwork")) {
            $this->zip->FileReplace ("iTunesArtwork", false);
        }
    }

    /**
     * Writes back all meta data changes
     */
    public function save(){
        $this->download ();
        $this->zip->close();
    }

    /**
     * Get the updated epub
     */
    public function download($file=false){
        $this->zip->FileReplace($this->meta,$this->xml->saveXML());
        // add the cover image
        if($this->imagetoadd){
            $this->zip->FileReplace($this->coverpath,file_get_contents($this->imagetoadd));
            $this->imagetoadd='';
        }
        if ($file) $this->zip->Flush(TBSZIP_DOWNLOAD, $file);
    }

    /**
     * Get the components list as an array
     */
    public function components(){
        $spine = array();
        $nodes = $this->xpath->query('//opf:spine/opf:itemref');
        foreach($nodes as $node){
            $idref =  $node->getAttribute('idref');
            $spine[] = $this->encodeComponentName ($this->xpath->query("//opf:manifest/opf:item[@id='$idref']")->item(0)->getAttribute('href'));
        }
        return $spine;
    }

    /**
     * Get the component content
     */
    public function component($comp) {
        $path = $this->decodeComponentName ($comp);
        $path = $this->getFullPath ($path);
        if (!$this->zip->FileExists($path)) {
            throw new Exception ("Unable to find {$path} <{$comp}>");
        }

        $data = $this->zip->FileRead($path);
        return $data;
    }

    public function getComponentName ($comp, $elementPath) {
        $path = $this->decodeComponentName ($comp);
        $path = $this->getFullPath ($path, $elementPath);
        if (!$this->zip->FileExists($path)) {
            error_log ("Unable to find " . $path);
            return false;
        }
        $ref = dirname('/'.$this->meta);
        $ref = ltrim($ref,'\\');
        $ref = ltrim($ref,'/');
        if (strlen ($ref) > 0) {
            $path = str_replace ($ref . "/", "", $path);
        }
        return $this->encodeComponentName ($path);
    }

    /**
     * Encode the component name (to replace / and -)
     */
    private function encodeComponentName ($src) {
        return str_replace (array ("/", "-"),
                            array ("~SLASH~", "~DASH~"),
                            $src);
    }

    /**
     * Decode the component name (to replace / and -)
     */
    private function decodeComponentName ($src) {
        return str_replace (array ("~SLASH~", "~DASH~"),
                            array ("/", "-"),
                            $src);
    }


    /**
     * Get the component content type
     */
    public function componentContentType($comp) {
        $comp = $this->decodeComponentName ($comp);
        $item = $this->xpath->query("//opf:manifest/opf:item[@href='$comp']")->item(0);
        if ($item) return $item->getAttribute('media-type');

        // I had at least one book containing %20 instead of spaces in the opf file
        $comp = str_replace (" ", "%20", $comp);
        $item = $this->xpath->query("//opf:manifest/opf:item[@href='$comp']")->item(0);
        if ($item) return $item->getAttribute('media-type');
        return "application/octet-stream";
    }

    private function getNavPointDetail ($node) {
        $title = $this->toc_xpath->query('x:navLabel/x:text', $node)->item(0)->nodeValue;
        $src = $this->toc_xpath->query('x:content', $node)->item(0)->attr('src');
        $src = $this->encodeComponentName ($src);
        return array("title" => $title, "src" => $src);
    }

    /**
     * Get the Epub content (TOC) as an array
     *
     * For each chapter there is a "title" and a "src"
     */
    public function contents(){
        $contents = array();
        $nodes = $this->toc_xpath->query('//x:ncx/x:navMap/x:navPoint');
        foreach($nodes as $node){
            $contents[] = $this->getNavPointDetail ($node);

            $insidenodes = $this->toc_xpath->query('x:navPoint', $node);
            foreach($insidenodes as $insidenode){
                $contents[] = $this->getNavPointDetail ($insidenode);
            }
        }
        return $contents;
    }

    /**
     * Get or set the book author(s)
     *
     * Authors should be given with a "file-as" and a real name. The file as
     * is used for sorting in e-readers.
     *
     * Example:
     *
     * array(
     *      'Pratchett, Terry'   => 'Terry Pratchett',
     *      'Simpson, Jacqueline' => 'Jacqueline Simpson',
     * )
     *
     * @params array $authors
     */
    public function Authors($authors=false){
        // set new data
        if($authors !== false){
            // Author where given as a comma separated list
            if(is_string($authors)){
                if($authors == ''){
                    $authors = array();
                }else{
                    $authors = explode(',',$authors);
                    $authors = array_map('trim',$authors);
                }
            }

            // delete existing nodes
            $nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
            foreach($nodes as $node) $node->delete();

            // add new nodes
            $parent = $this->xpath->query('//opf:metadata')->item(0);
            foreach($authors as $as => $name){
                if(is_int($as)) $as = $name; //numeric array given
                $node = $parent->newChild('dc:creator',$name);
                $node->attr('opf:role', 'aut');
                $node->attr('opf:file-as', $as);
            }

            $this->reparse();
        }

        // read current data
        $rolefix = false;
        $authors = array();
        $nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
        if($nodes->length == 0){
            // no nodes where found, let's try again without role
            $nodes = $this->xpath->query('//opf:metadata/dc:creator');
            $rolefix = true;
        }
        foreach($nodes as $node){
            $name = $node->nodeValue;
            $as   = $node->attr('opf:file-as');
            if(!$as){
                $as = $name;
                $node->attr('opf:file-as',$as);
            }
            if($rolefix){
                $node->attr('opf:role','aut');
            }
            $authors[$as] = $name;
        }
        return $authors;
    }

    /**
     * Set or get the book title
     *
     * @param string $title
     */
    public function Title($title=false){
        return $this->getset('dc:title',$title);
    }

    /**
     * Set or get the book's language
     *
     * @param string $lang
     */
    public function Language($lang=false){
        return $this->getset('dc:language',$lang);
    }

    /**
     * Set or get the book' publisher info
     *
     * @param string $publisher
     */
    public function Publisher($publisher=false){
        return $this->getset('dc:publisher',$publisher);
    }

    /**
     * Set or get the book's copyright info
     *
     * @param string $rights
     */
    public function Copyright($rights=false){
        return $this->getset('dc:rights',$rights);
    }

    /**
     * Set or get the book's description
     *
     * @param string $description
     */
    public function Description($description=false){
        return $this->getset('dc:description',$description);
    }

    /**
     * Set or get the book's Unique Identifier
     *
     * @param string Unique identifier
     */
    public function Uuid($uuid = false)
    {
        $nodes = $this->xpath->query('/opf:package');
        if ($nodes->length !== 1) {
            $error = sprintf('Cannot find ebook identifier');
            throw new Exception($error);
        }
        $identifier = $nodes->item(0)->attr('unique-identifier');

        $res = $this->getset('dc:identifier', $uuid, 'id', $identifier);

        return $res;
    }

    /**
     * Set or get the book's creation date
     *
     * @param string Date eg: 2012-05-19T12:54:25Z
     */
    public function CreationDate($date = false)
    {
        $res = $this->getset('dc:date', $date, 'opf:event', 'creation');

        return $res;
    }

    /**
     * Set or get the book's modification date
     *
     * @param string Date eg: 2012-05-19T12:54:25Z
     */
    public function ModificationDate($date = false)
    {
        $res = $this->getset('dc:date', $date, 'opf:event', 'modification');

        return $res;
    }

    /**
     * Set or get the book's URI
     *
     * @param string URI
     */
    public function Uri($uri = false)
    {
        $res = $this->getset('dc:identifier', $uri, 'opf:scheme', 'URI');

        return $res;
    }

    /**
     * Set or get the book's ISBN number
     *
     * @param string $isbn
     */
    public function ISBN($isbn=false){
        return $this->getset('dc:identifier',$isbn,'opf:scheme','ISBN');
    }

    /**
     * Set or get the Google Books ID
     *
     * @param string $google
     */
    public function Google($google=false){
        return $this->getset('dc:identifier',$google,'opf:scheme','GOOGLE');
    }

    /**
     * Set or get the Amazon ID of the book
     *
     * @param string $amazon
     */
    public function Amazon($amazon=false){
        return $this->getset('dc:identifier',$amazon,'opf:scheme','AMAZON');
    }

    /**
     * Set or get the Calibre UUID of the book
     *
     * @param string $uuid
     */
    public function Calibre($uuid=false){
        return $this->getset('dc:identifier',$uuid,'opf:scheme','calibre');
    }

    /**
     * Set or get the Serie of the book
     *
     * @param string $serie
     */
    public function Serie($serie=false){
        return $this->getset('opf:meta',$serie,'name','calibre:series','content');
    }

    /**
     * Set or get the Serie Index of the book
     *
     * @param string $serieIndex
     */
    public function SerieIndex($serieIndex=false){
        return $this->getset('opf:meta',$serieIndex,'name','calibre:series_index','content');
    }

    /**
     * Set or get the book's subjects (aka. tags)
     *
     * Subject should be given as array, but a comma separated string will also
     * be accepted.
     *
     * @param array $subjects
     */
    public function Subjects($subjects=false){
        // setter
        if($subjects !== false){
            if(is_string($subjects)){
                if($subjects === ''){
                    $subjects = array();
                }else{
                    $subjects = explode(',',$subjects);
                    $subjects = array_map('trim',$subjects);
                }
            }

            // delete previous
            $nodes = $this->xpath->query('//opf:metadata/dc:subject');
            foreach($nodes as $node){
                $node->delete();
            }
            // add new ones
            $parent = $this->xpath->query('//opf:metadata')->item(0);
            foreach($subjects as $subj){
                $node = $this->xml->createElement('dc:subject',htmlspecialchars($subj));
                $node = $parent->appendChild($node);
            }

            $this->reparse();
        }

        //getter
        $subjects = array();
        $nodes = $this->xpath->query('//opf:metadata/dc:subject');
        foreach($nodes as $node){
            $subjects[] =  $node->nodeValue;
        }
        return $subjects;
    }

    /**
     * Read the cover data
     *
     * Returns an associative array with the following keys:
     *
     *   mime  - filetype (usually image/jpeg)
     *   data  - the binary image data
     *   found - the internal path, or false if no image is set in epub
     *
     * When no image is set in the epub file, the binary data for a transparent
     * GIF pixel is returned.
     *
     * When adding a new image this function return no or old data because the
     * image contents are not in the epub file, yet. The image will be added when
     * the save() method is called.
     *
     * @param  string $path local filesystem path to a new cover image
     * @param  string $mime mime type of the given file
     * @return array
     */
    public function Cover($path=false, $mime=false){
        // set cover
        if($path !== false){
            // remove current pointer
            $nodes = $this->xpath->query('//opf:metadata/opf:meta[@name="cover"]');
            foreach($nodes as $node) $node->delete();
            // remove previous manifest entries if they where made by us
            $nodes = $this->xpath->query('//opf:manifest/opf:item[@id="php-epub-meta-cover"]');
            foreach($nodes as $node) $node->delete();

            if($path){
                // add pointer
                $parent = $this->xpath->query('//opf:metadata')->item(0);
                $node = $parent->newChild('opf:meta');
                $node->attr('opf:name','cover');
                $node->attr('opf:content','php-epub-meta-cover');

                // add manifest
                $parent = $this->xpath->query('//opf:manifest')->item(0);
                $node = $parent->newChild('opf:item');
                $node->attr('id','php-epub-meta-cover');
                $node->attr('opf:href','php-epub-meta-cover.img');
                $node->attr('opf:media-type',$mime);

                // remember path for save action
                $this->imagetoadd = $path;
            }

            $this->reparse();
        }

        // load cover
        $nodes = $this->xpath->query('//opf:metadata/opf:meta[@name="cover"]');
        if(!$nodes->length) return $this->no_cover();
        $coverid = (String) $nodes->item(0)->attr('opf:content');
        if(!$coverid) return $this->no_cover();

        $nodes = $this->xpath->query('//opf:manifest/opf:item[@id="'.$coverid.'"]');
        if(!$nodes->length) return $this->no_cover();
        $mime = $nodes->item(0)->attr('opf:media-type');
        $path = $nodes->item(0)->attr('opf:href');
        $path = dirname('/'.$this->meta).'/'.$path; // image path is relative to meta file
        $path = ltrim($path,'/');

        $zip = new ZipArchive();
        if(!@$zip->open($this->file)){
            throw new Exception('Failed to read epub file');
        }
        $data = $zip->getFromName($path);

        return array(
            'mime'  => $mime,
            'data'  => $data,
            'found' => $path
        );
    }

    public function getCoverItem () {
        $nodes = $this->xpath->query('//opf:metadata/opf:meta[@name="cover"]');
        if(!$nodes->length) return NULL;

        $coverid = (String) $nodes->item(0)->attr('opf:content');
        if(!$coverid) return NULL;

        $nodes = $this->xpath->query('//opf:manifest/opf:item[@id="'.$coverid.'"]');
        if(!$nodes->length) return NULL;

        return $nodes->item(0);
    }

    public function Combine($a, $b)
    {
        $isAbsolute = false;
        if ($a[0] == "/")
            $isAbsolute = true;

        if ($b[0] == "/")
            throw new InvalidArgumentException("Second path part must not start with " . $m_Separator);

        $splittedA = preg_split("#/#", $a);
        $splittedB = preg_split("#/#", $b);

        $pathParts = array();
        $mergedPath = array_merge($splittedA, $splittedB);

        foreach($mergedPath as $item)
        {
            if ($item == null || $item == "" || $item == ".")
                continue;

            if ($item == "..")
            {
                array_pop($pathParts);
                continue;
            }

            array_push($pathParts, $item);
        }

        $path = implode("/", $pathParts);
        if ($isAbsolute)
            return("/" . $path);
        else
            return($path);
    }

    private function getFullPath ($file, $context = NULL) {
        $path = dirname('/'.$this->meta).'/'.$file;
        $path = ltrim($path,'\\');
        $path = ltrim($path,'/');
        if (!empty ($context)) {
            $path = $this->combine (dirname ($path), $context);
        }
        //error_log ("FullPath : $path ($file / $context)");
        return $path;
    }

    public function updateForKepub () {
        $item = $this->getCoverItem ();
        if (!is_null ($item)) {
            $item->attr('opf:properties', 'cover-image');
        }
    }

    public function Cover2($path=false, $mime=false){
        $hascover = true;
        $item = $this->getCoverItem ();
        if (is_null ($item)) {
            $hascover = false;
        } else {
            $mime = $item->attr('opf:media-type');
            $this->coverpath = $item->attr('opf:href');
            $this->coverpath = dirname('/'.$this->meta).'/'.$this->coverpath; // image path is relative to meta file
            $this->coverpath = ltrim($this->coverpath,'\\');
            $this->coverpath = ltrim($this->coverpath,'/');
        }

        // set cover
        if($path !== false){
            if (!$hascover) return; // TODO For now only update

            if($path){
                $item->attr('opf:media-type',$mime);

                // remember path for save action
                $this->imagetoadd = $path;
            }

            $this->reparse();
        }

        if (!$hascover) return $this->no_cover();
    }

    /**
     * A simple getter/setter for simple meta attributes
     *
     * It should only be used for attributes that are expected to be unique
     *
     * @param string $item   XML node to set/get
     * @param string $value  New node value
     * @param string $att    Attribute name
     * @param string $aval   Attribute value
     * @param string $datt   Destination attribute
     */
    protected function getset($item,$value=false,$att=false,$aval=false,$datt=false){
        // construct xpath
        $xpath = '//opf:metadata/'.$item;
        if($att){
            $xpath .= "[@$att=\"$aval\"]";
        }

        // set value
        if($value !== false){
            $value = htmlspecialchars($value);
            $nodes = $this->xpath->query($xpath);
            if($nodes->length == 1 ){
                if($value === ''){
                    // the user want's to empty this value -> delete the node
                    $nodes->item(0)->delete();
                }else{
                    // replace value
                    if ($datt){
                        $nodes->item(0)->attr ($datt, $value);
                    }else{
                        $nodes->item(0)->nodeValue = $value;
                    }
                }
            }else{
                // if there are multiple matching nodes for some reason delete
                // them. we'll replace them all with our own single one
                foreach($nodes as $n) $n->delete();
                // readd them
                if($value){
                    $parent = $this->xpath->query('//opf:metadata')->item(0);

                    $node = $parent->newChild ($item);
                    if($att) $node->attr($att,$aval);
                    if ($datt){
                        $node->attr ($datt, $value);
                    }else{
                        $node->nodeValue = $value;
                    }
                }
            }

            $this->reparse();
        }

        // get value
        $nodes = $this->xpath->query($xpath);
        if($nodes->length){
            if ($datt){
                return $nodes->item(0)->attr ($datt);
            }else{
                return $nodes->item(0)->nodeValue;
            }
        }else{
            return '';
        }
    }

    /**
     * Return a not found response for Cover()
     */
    protected function no_cover(){
        return array(
            'data'  => base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7'),
            'mime'  => 'image/gif',
            'found' => false
        );
    }

    /**
     * Reparse the DOM tree
     *
     * I had to rely on this because otherwise xpath failed to find the newly
     * added nodes
     */
    protected function reparse() {
        $this->xml->loadXML($this->xml->saveXML());
        $this->xpath = new EPubDOMXPath($this->xml);
    }
}

class EPubDOMXPath extends DOMXPath {
    public function __construct(DOMDocument $doc){
        parent::__construct($doc);

        if(is_a($doc->documentElement, 'EPubDOMElement')){
            foreach($doc->documentElement->namespaces as $ns => $url){
                $this->registerNamespace($ns,$url);
            }
        }
    }
}

class EPubDOMElement extends DOMElement {
    public $namespaces = array(
        'n'   => 'urn:oasis:names:tc:opendocument:xmlns:container',
        'opf' => 'http://www.idpf.org/2007/opf',
        'dc'  => 'http://purl.org/dc/elements/1.1/'
    );


    public function __construct($name, $value='', $namespaceURI=''){
        list($ns,$name) = $this->splitns($name);
        $value = htmlspecialchars($value);
        if(!$namespaceURI && $ns){
            $namespaceURI = $this->namespaces[$ns];
        }
        parent::__construct($name, $value, $namespaceURI);
    }


    /**
     * Create and append a new child
     *
     * Works with our epub namespaces and omits default namespaces
     */
    public function newChild($name, $value=''){
        list($ns,$local) = $this->splitns($name);
        if($ns){
            $nsuri = $this->namespaces[$ns];
            if($this->isDefaultNamespace($nsuri)){
                $name  = $local;
                $nsuri = '';
            }
        }

        // this doesn't call the construcor: $node = $this->ownerDocument->createElement($name,$value);
        $node = new EPubDOMElement($name,$value,$nsuri);
        return $this->appendChild($node);
    }

    /**
     * Split given name in namespace prefix and local part
     *
     * @param  string $name
     * @return array  (namespace, name)
     */
    public function splitns($name){
        $list = explode(':',$name,2);
        if(count($list) < 2) array_unshift($list,'');
        return $list;
    }

    /**
     * Simple EPub namespace aware attribute accessor
     */
    public function attr($attr,$value=null){
        list($ns,$attr) = $this->splitns($attr);

        $nsuri = '';
        if($ns){
            $nsuri = $this->namespaces[$ns];
            if(!$this->namespaceURI){
                if($this->isDefaultNamespace($nsuri)){
                    $nsuri = '';
                }
            }elseif($this->namespaceURI == $nsuri){
                 $nsuri = '';
            }
        }

        if(!is_null($value)){
            if($value === false){
                // delete if false was given
                if($nsuri){
                    $this->removeAttributeNS($nsuri,$attr);
                }else{
                    $this->removeAttribute($attr);
                }
            }else{
                // modify if value was given
                if($nsuri){
                    $this->setAttributeNS($nsuri,$attr,$value);
                }else{
                    $this->setAttribute($attr,$value);
                }
            }
        }else{
            // return value if none was given
            if($nsuri){
                return $this->getAttributeNS($nsuri,$attr);
            }else{
                return $this->getAttribute($attr);
            }
        }
    }

    /**
     * Remove this node from the DOM
     */
    public function delete(){
        $this->parentNode->removeChild($this);
    }

}


