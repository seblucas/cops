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

    $err = getURLParam ("err", -1);
    $full = getURLParam ("full");
    $error = NULL;
    switch ($err) {
        case 1 :
            $error = "Database error";
            break;
    }

?>
<head>
    <meta charset="utf-8">
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
            <h2>Check if PHP version is correct</h2>
            <h4>
            <?php
            if (defined('PHP_VERSION_ID')) {
                if (PHP_VERSION_ID >= 50300) {
                    echo "OK (" . PHP_VERSION . ")";
                } else {
                    echo "Please install PHP >= 5.3 (" . PHP_VERSION . ")";
                }
            } else {
                echo "Please install PHP >= 5.3";
            }
            ?>
            </h4>
        </article>
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
            <h2>Check if Json is properly installed and loaded</h2>
            <h4>
            <?php
            if (extension_loaded('json')) {
                echo "OK";
            } else {
                echo "Please install the php5-json extension and make sure it's enabled";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if mbstring is properly installed and loaded</h2>
            <h4>
            <?php
            if (extension_loaded('mbstring')) {
                echo "OK";
            } else {
                echo "Please install the php5-mbstring extension and make sure it's enabled";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if intl is properly installed and loaded</h2>
            <h4>
            <?php
            if (extension_loaded('intl')) {
                echo "OK";
            } else {
                echo "Please install the php5-intl extension and make sure it's enabled";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Normalizer class is properly installed and loaded</h2>
            <h4>
            <?php
            if (class_exists("Normalizer", $autoload = false)) {
                echo "OK";
            } else {
                echo "Please make sure intl is enabled in your php.ini";
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
                echo "{$name} OK";
            } else {
                echo "{$name} Calibre path has to be local (no URL allowed)";
            }
            ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Calibre database file exists and is readable</h2>
            <h4>
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
            </h4>
        </article>
    <?php if (is_readable (Base::getDbFileName ($i))) { ?>
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
                    echo "{$name} Not all Calibre tables were found. Are you sure you're using the correct database.";
                }
            } catch (Exception $e) {
                echo "{$name} If the file is readable, check your php configuration. Exception detail : " . $e;
            }
            ?>
            </h4>
        </article>
        <?php if ($full) { ?>
        <article class="frontpage">
            <h2>Check if all Calibre books are found</h2>
            <h4>
            <?php
            try {
                $db = new PDO('sqlite:'. Base::getDbFileName ($i));
                $result = $db->prepare("select books.path || '/' || data.name || '.' || lower (format) as fullpath from data join books on data.book = books.id");
                $result->execute ();
                while ($post = $result->fetchObject ())
                {
                    if (!is_file (Base::getDbDirectory ($i) . $post->fullpath)) {
                        echo "<p>" . Base::getDbDirectory ($i) . $post->fullpath . "</p>";
                    }
                }
            } catch (Exception $e) {
                echo "{$name} If the file is readable, check your php configuration. Exception detail : " . $e;
            }
            ?>
            </h4>
        </article>
        <?php } ?>
    <?php } ?>
<?php $i++; } ?>
    </section>
    <footer></footer>
</div>
</body>
</html>
