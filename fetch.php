<?php
/**
 * COPS (Calibre OPDS PHP Server)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

    require_once ("config.php");
    require_once ("book.php");
    require_once ("data.php");

    global $config;

    if ($config ['cops_fetch_protect'] == "1") {
        session_start();
        if (!isset($_SESSION['connected'])) {
            notFound ();
            return;
        }
    }

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
            //by default, we don't cache
            $thumbnailCacheFullpath = null;
            if ( isset($config['cops_thumbnail_cache_directory']) && $config['cops_thumbnail_cache_directory'] !== '' ) {
                $thumbnailCacheFullpath = $config['cops_thumbnail_cache_directory'];
                //if multiple databases, add a subfolder with the database ID
                $thumbnailCacheFullpath .= !is_null (GetUrlParam (DB)) ? 'db-' . GetUrlParam (DB) . DIRECTORY_SEPARATOR : '';
                //when there are lots of thumbnails, it's better to save files in subfolders, so if the book's uuid is
                //"01234567-89ab-cdef-0123-456789abcdef", we will save the thumbnail in .../0/12/34567-89ab-cdef-0123-456789abcdef-...
                $thumbnailCacheFullpath .= substr($book->uuid, 0, 1) . DIRECTORY_SEPARATOR . substr($book->uuid, 1, 2) . DIRECTORY_SEPARATOR;
                //check if cache folder exists or create it
                if ( file_exists($thumbnailCacheFullpath) || mkdir($thumbnailCacheFullpath, 0700, true) ) {
                    //we name the thumbnail from the book's uuid and it's dimensions (width and/or height)
                    $thumbnailCacheName = substr($book->uuid, 3) . '-' . getURLParam ("width") . 'x' . getURLParam ("height") . '.jpg';
                    $thumbnailCacheFullpath = $thumbnailCacheFullpath . $thumbnailCacheName;
                }
                else {
                    //error creating the folder, so we don't cache
                    $thumbnailCacheFullpath = null;
                }
            }

            if ( $thumbnailCacheFullpath !== null && file_exists($thumbnailCacheFullpath) ) {
                //return the already cached thumbnail
                readfile( $thumbnailCacheFullpath );
                return;
            }

            if ($book->getThumbnail (getURLParam ("width"), getURLParam ("height"), $thumbnailCacheFullpath)) {
                //if we don't cache the thumbnail, imagejpeg() in $book->getThumbnail() already return the image data
                if ( $thumbnailCacheFullpath === null ) {
                    // The cover had to be resized
                    return;
                }
                else {
                    //return the just cached thumbnail
                    readfile( $thumbnailCacheFullpath );
                    return;
                }
            }
            break;
        default:
            $data = $book->getDataById ($idData);
            header("Content-Type: " . $data->getMimeType ());
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
