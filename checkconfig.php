<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
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
    
    header ("Content-Type:text/html; charset=UTF-8");
    
    $err = getURLParam ("err", -1);
    $error = NULL;
    switch ($err) {
        case 1 :
            $error = "Database error";
            break;
    }

?>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>COPS Configuration Check</title>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion(getCurrentCss ()) ?>" media="screen" />
</head>
<body>
<div class="container">
    <header>
        <div class="headcenter">
            <h1>COPS Configuration Check</h1>
        </div>
    </header>
    <div id="content" style="display: none;"></div>
    <section>
        <?php
        if (!is_null ($error)) {
        ?>
        <article class="frontpage">
            <h2>You've been redirected because COPS is not configured properly</h2>
            <h4><?php echo $error ?></h4>
        </article>
        <?php
        }
        ?>
        <article class="frontpage">
            <h2>Check if GD is properly installed and loaded</h2>
            <h4>
            <?php 
            if (extension_loaded('gd') && function_exists('gd_info')) {
                echo "OK";
            } else {
                echo "Please install the php5-gd extension and make sure it's enabled";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Sqlite is properly installed and loaded</h2>
            <h4>
            <?php 
            if (extension_loaded('pdo_sqlite')) {
                echo "OK";
            } else {
                echo "Please install the php5-sqlite extension and make sure it's enabled";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if libxml is properly installed and loaded</h2>
            <h4>
            <?php 
            if (extension_loaded('libxml')) {
                echo "OK";
            } else {
                echo "Please make sure libxml is enabled";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if the rendering will be done on client side or server side</h2>
            <h4>
            <?php 
            if (useServerSideRendering ()) {
                echo "Server side rendering";
            } else {
                echo "Client side rendering";
            }
            ?>
            </h4>
        </article>
<?php 
$i = 0;
foreach (Base::getDbList () as $name => $database) { 
?>
        <article class="frontpage">
            <h2>Check if Calibre database path is not an URL</h2>
            <h4>
            <?php
            if (!preg_match ("#^http#", $database)) {
                echo "OK";
            } else {
                echo "Calibre path has to be local (no URL allowed)";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Calibre database file exists and is readable</h2>
            <?php 
            if (is_readable (Base::getDbFileName ($i))) {
                echo "{$name} OK";
            } else {
                echo "{$name} File " . Base::getDbFileName ($i) . " not found, 
Please check 
<ul>
<li>Value of \$config['calibre_directory'] in config_local.php</li>
<li>Value of <a href='http://php.net/manual/en/ini.core.php#ini.open-basedir'>open_basedir</a> in your php.ini</li>
<li>The access rights of the Calibre Database</li>
<li>Synology users please read <a href='https://github.com/seblucas/cops/wiki/Howto---Synology'>this</a></li>
</ul>";
            }
            ?>
        </article>
        <article class="frontpage">
            <h2>Check if Calibre database file can be opened with PHP</h2>
            <h4>
            <?php 
            try {
                $db = new PDO('sqlite:'. Base::getDbFileName ($i));
                echo "{$name} OK";
            } catch (Exception $e) {
                echo "{$name} If the file is readable, check your php configuration. Exception detail : " . $e;
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Calibre database file contains at least some of the needed tables</h2>
            <h4>
            <?php 
            try {
                $db = new PDO('sqlite:'. Base::getDbFileName ($i));
                $count = $db->query("select count(*) FROM sqlite_master WHERE type='table' AND name in ('books', 'authors', 'tags', 'series')")->fetchColumn();
                if ($count == 4) {
                    echo "{$name} OK";
                } else {
                    echo "{$name} Not all Calibre tables were found. Are you you're using the correct database.";
                }
            } catch (Exception $e) {
                echo "{$name} If the file is readable, check your php configuration. Exception detail : " . $e;
            }
            ?>
            </h4>
        </article>
<?php $i++; } ?>
    </section>
    <footer></footer>
</div>
</body>
</html>