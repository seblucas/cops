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
    
    $err = getURLParam ("err", -1);
    $error = NULL;
    switch ($err) {
        case 1 :
            $error = "Database error";
            break;
    }

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
        <?php
        if (!is_null ($error)) {
        ?>
        <div class="entry">
            <div class="entryTitle">You've been redirected because COPS is not configured properly</div>
            <div class="entryContent"><?php echo $error ?></div>
        </div>
        <?php
        }
        ?>
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
                echo "File " . Base::getDbFileName () . " not found, 
Please check 
<ul>
<li>Value of \$config['calibre_directory'] in config_local.php</li>
<li>Value of <a href='http://php.net/manual/en/ini.core.php#ini.open-basedir'>open_basedir</a> in your php.ini</li>
<li>The access rights of the Calibre Database</li>
<li>Synology users please read <a href='https://github.com/seblucas/cops/wiki/Howto---Synology'>this</a></li>
</ul>";
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

