<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class Data extends Base {
    public $id;
    public $name;
    public $format;
    public $realFormat;
    public $extension;
    public $book;
    
    public static $mimetypes = array(
        'azw'   => 'application/x-mobipocket-ebook',
        'azw3'  => 'application/x-mobipocket-ebook',
        'cbz'   => 'application/x-cbz',
        'cbr'   => 'application/x-cbr',
        'doc'   => 'application/msword',
        'epub'  => 'application/epub+zip',
        'fb2'   => 'text/fb2+xml',
        'kobo'  => 'application/x-koboreader-ebook',
        'mobi'  => 'application/x-mobipocket-ebook',
        'lit'   => 'application/x-ms-reader',
        'lrs'   => 'text/x-sony-bbeb+xml',
        'lrf'   => 'application/x-sony-bbeb',
        'lrx'   => 'application/x-sony-bbeb',
        'ncx'   => 'application/x-dtbncx+xml',
        'opf'   => 'application/oebps-package+xml',
        'otf'   => 'application/x-font-opentype',
        'pdb'   => 'application/vnd.palm',
        'pdf'   => 'application/pdf',
        'prc'   => 'application/x-mobipocket-ebook',
        'rtf'   => 'application/rtf',
        'svg'   => 'image/svg+xml',
        'ttf'   => 'application/x-font-truetype',
        'wmf'   => 'image/wmf',
        'xhtml' => 'application/xhtml+xml',
        'xpgt'  => 'application/adobe-page-template+xml',
        'zip'   => 'application/zip'
    );
    
    public function __construct($post, $book = null) {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->format = $post->format;
        $this->realFormat = str_replace ("ORIGINAL_", "", $post->format);
        $this->extension = strtolower ($this->realFormat);
        $this->book = $book;
    }
    
    public function isKnownType () {
        return array_key_exists ($this->extension, self::$mimetypes);
    }
    
    public function getMimeType () {
        if ($this->isKnownType ()) {
            return self::$mimetypes [$this->extension];
        } else {
            return "application/octet-stream";
        }
    }
    
    public function getFilename () {
        return $this->name . "." . strtolower ($this->format);
    }
    
    public function getDataLink ($rel, $title = NULL) {
        return self::getLink ($this->book, $this->extension, $this->getMimeType (), $rel, $this->getFilename (), $this->id, $title);
    }
    
    public function getLocalPath () {
        return $this->book->path . "/" . $this->getFilename ();
    }
    
    public function getHtmlLink () {
        global $config;
        
        if ($config['cops_use_url_rewriting'] == "1")
        {
            return "download/" . $this->id . "/" . urlencode ($this->getFilename ());
        }
        else
        {
            return str_replace ("&", "&amp;", self::getLink ($this->book, $this->extension, $this->getMimeType (), NULL, $this->getFilename (), $this->id, NULL)->href);
        }
    }
    
    public static function getLink ($book, $type, $mime, $rel, $filename, $idData, $title = NULL)
    {
        global $config;
        
        $textData = "";
        if (!is_null ($idData))
        {
            $textData = "&data=" . $idData;
        }
        
        if (preg_match ('/^\//', $config['calibre_directory']) || // Linux /
            preg_match ('/^\w\:/', $config['calibre_directory']) || // Windows X:
            ($type == "epub" && $config['cops_update_epub-metadata']))
        {
            if ($type != "jpg") $textData .= "&type=" . $type;
            return new Link ("fetch.php?id=$book->id" . $textData, $mime, $rel, $title);
        }
        else
        {
            return new Link (str_replace('%2F','/',rawurlencode ($book->path."/".$filename)), $mime, $rel, $title);
        }
    }
}
?>