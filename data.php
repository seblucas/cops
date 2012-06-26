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
        'epub'   => 'application/epub+zip',
        'mobi'   => 'application/x-mobipocket-ebook',
        'pdf'    => 'application/pdf'
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
        return self::$mimetypes [$this->extension];
    }
    
    public function getFilename () {
        $this->name . "." . strtolower ($this->format);
    }
    
    public function getDataLink ($rel, $title = NULL) {
        return self::getLink ($this->book, $this->extension, $this->getMimeType (), $rel, $this->getFilename (), $this->id, $title);
    }
    
    public static function getLink ($book, $type, $mime, $rel, $filename, $idData, $title = NULL)
    {
        global $config;
        
        $textData = "";
        if (!is_null ($idData))
        {
            $textData = "&data=" . $idData;
        }
        
        if (preg_match ('/^\//', $config['calibre_directory']))
        {
            return new Link ("fetch.php?id=$book->id" . $textData . "&type=" . $type, $mime, $rel, $title);
        }
        else
        {
            return new Link (str_replace('%2F','/',rawurlencode ($book->path."/".$filename)), $mime, $rel, $title);
        }
    }
}
?>