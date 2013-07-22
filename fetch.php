<?php
/**
 * COPS (Calibre OPDS PHP Server) 
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gordon Page <gordon@incero.com> with integration/modification by Sébastien Lucas <sebastien@slucas.fr>
 */
    
    require_once ("config.php");
    require_once ("book.php");
    require_once ("data.php");

function notFound () {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");

    $_SERVER['REDIRECT_STATUS'] = 404;
}
    
    global $config;
    $expires = 60*60*24*14;
    header("Pragma: public");
    header("Cache-Control: maxage=".$expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    $bookId = getURLParam ("id", NULL);
    $type = getURLParam ("type", "jpg");
    $idData = getURLParam ("data", NULL);
    if (is_null ($bookId))
    {
        $book = Book::getBookByDataId($idData);
    }
    else
    {
        $book = Book::getBookById($bookId);
    }
    
    if (!$book) {
        notFound ();
        return;     
    }
    
    if ($book && ($type == "jpg" || empty ($config['calibre_internal_directory']))) {
        if ($type == "jpg") {
            $file = $book->getFilePath ($type);
        } else {
            $file = $book->getFilePath ($type, $idData);
        }
        if (!$file || !file_exists ($file)) {
            notFound ();
            return;
        }
    }
     
    switch ($type)
    {
        case "jpg":
            header("Content-Type: image/jpeg");
            if (isset($_GET["width"]))
            {
                $file = $book->getFilePath ($type);
                // get image size
                if($size = GetImageSize($file)){
                    $w = $size[0];
                    $h = $size[1];
                    //set new size
                    $nw = $_GET["width"];
                    if ($nw > $w) { break; }
                    $nh = ($nw*$h)/$w;
                }
                else{
                    //set new size
                    $nw = "160";
                    $nh = "120";
                }
                //draw the image
                $src_img = imagecreatefromjpeg($file);
                $dst_img = imagecreatetruecolor($nw,$nh);
                imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $nw, $nh, $w, $h);//resizing the image
                imagejpeg($dst_img,null,80);
                imagedestroy($src_img);
                imagedestroy($dst_img);
                return;
            }
            if (isset($_GET["height"]))
            {
                $file = $book->getFilePath ($type);
                // get image size
                if($size = GetImageSize($file)){
                    $w = $size[0];
                    $h = $size[1];
                    //set new size
                    $nh = $_GET["height"];
                    if ($nh > $h) { break; }
                    $nw = ($nh*$w)/$h;
                }
                else{
                    //set new size
                    $nw = "160";
                    $nh = "120";
                }
                //draw the image
                $src_img = imagecreatefromjpeg($file);
                $dst_img = imagecreatetruecolor($nw,$nh);
                imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $nw, $nh, $w, $h);//resizing the image
                imagejpeg($dst_img,null,80);
                imagedestroy($src_img);
                imagedestroy($dst_img);
                return;
            }
            break;
        default:
            header("Content-Type: " . Data::$mimetypes[$type]);
            break;
    }
    $file = $book->getFilePath ($type, $idData, true);
    if ($type == "epub" && $config['cops_update_epub-metadata'])
    {
        $book->getUpdatedEpub ($idData);
        return;
    }
    if ($type == "jpg") {
        header('Content-Disposition: filename="' . basename ($file) . '"');
    } else {
        header('Content-Disposition: attachment; filename="' . basename ($file) . '"');
    }
    
    $dir = $config['calibre_internal_directory'];
    if (empty ($config['calibre_internal_directory'])) {
        $dir = Base::getDbDirectory ();
    }
    
    if (empty ($config['cops_x_accel_redirect'])) {
        $filename = $dir . $file;
        $fp = fopen($filename, 'rb');
        header("Content-Length: " . filesize($filename));
        fpassthru($fp);
    }
    else {
        header ($config['cops_x_accel_redirect'] . ": " . $dir . $file);
    }
