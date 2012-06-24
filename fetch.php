<?php
/**
 * COPS (Calibre OPDS PHP Server) 
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gordon Page <gordon@incero.com> with integration/modification by Sébastien Lucas <sebastien@slucas.fr>
 */
    
    require_once ("config.php");
    require_once ("book.php");
     
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
     
    switch ($type)
    {
        case "jpg":
            header("Content-type: image/jpeg");
            if (isset($_GET["width"]))
            {
                $file = $book->getFilePath ($type);
                // get image size
                if($size = GetImageSize($file)){
                    $w = $size[0];
                    $h = $size[1];
                    //set new size
                    $nw = $_GET["width"];
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
                imagejpeg($dst_img,"",100);
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
                imagejpeg($dst_img,"",100);
                imagedestroy($src_img);
                imagedestroy($dst_img);
                return;
            }
            break;
        default:
            header("Content-type: " . Book::$mimetypes[$type]);
            break;
    }
    $file = $book->getFilePath ($type, $idData, true);
    header('Content-Disposition: attachement; filename="' . basename ($file) . '"');
    header ($config['cops_x_accel_redirect'] . ": " . $config['calibre_internal_directory'] . $file);
?>