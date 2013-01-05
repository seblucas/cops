<?php
/**
 * COPS (Calibre OPDS PHP Server) Configuration check
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
 
    require_once ("config.php");
    require_once ("base.php");
    
    header ("Content-Type:application/xhtml+xml");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <title>COPS Configuration Check</title>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("style.css") ?>" media="screen" />
</head>
<body>
<div class="container">
    <div class="head">
        <div class="headcenter">
            <p>COPS Configuration Check</p>
        </div>
    </div>
    <div class="clearer" />
    <div id="content" style="display: none;"></div>
    <div class="entries">
        <div class="entry">
            <div class="entryTitle">Check if GD is properly installed and loaded</div>
            <div class="entryContent">
            <?php 
            if (extension_loaded('gd') && function_exists('gd_info')) {
                echo "OK";
            } else {
                echo "Please install the php5-gd extension and make sure it's enabled";
            }
            ?>
            </div>
        </div>
        <div class="entry">
            <div class="entryTitle">Check if Sqlite is properly installed and loaded</div>
            <div class="entryContent">
            <?php 
            if (extension_loaded('pdo_sqlite')) {
                echo "OK";
            } else {
                echo "Please install the php5-sqlite extension and make sure it's enabled";
            }
            ?>
            </div>
        </div>

        <div class="entry">
            <div class="entryTitle">Check if Calibre database file exists and is readable</div>
            <div class="entryContent">
            <?php 
            if (is_readable (Base::getDbFileName ())) {
                echo "OK";
            } else {
                echo "File " . Base::getDbFileName () . " not found, check open_dir or the access rights";
            }
            ?>
            </div>
        </div>
        <div class="entry">
            <div class="entryTitle">Check if Calibre database file can be opened with PHP</div>
            <div class="entryContent">
            <?php 
            try {
                $db = new PDO('sqlite:'. Base::getDbFileName ());
                echo "OK";
            } catch (Exception $e) {
                echo "If the file is readable, check your php configuration. Exception detail : " . $e;
            }
            ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>

