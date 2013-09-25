<?php
/**
 * Epub loader config
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

$gConfig = array();

/**
 * Cops directory
 *
 * This is the base path of Cops library
 */
$gConfig['cops_directory'] = dirname(dirname(dirname(__DIR__)));

/**
 * Create Calibre databases ?
 *
 * If true: databases are removed and recreated before loading ebooks
 * If false: append ebooks into databases
 */
$gConfig['create_db'] = true;

/**
 * Databases infos
 *
 * For each database:
 *   name: The database name to display
 *   db_path: The path where to create the database
 *   epub_path: The path where to look for the epub files to load
 */
$gConfig['databases'] = array();
$gConfig['databases'][] = array('name' => 'Littérature classique', 'db_path' => '/opt/atoll/opds/demo', 'epub_path' => '/opt/atoll/external/demo');
$gConfig['databases'][] = array('name' => 'Bibliothèque numérique romande', 'db_path' => '/opt/atoll/opds/bnr', 'epub_path' => '/opt/atoll/external/bnr');
$gConfig['databases'][] = array('name' => 'La Bibliothèque d\'Ebooks', 'db_path' => '/opt/atoll/opds/bibebook', 'epub_path' => '/opt/atoll/external/bibebook');
$gConfig['databases'][] = array('name' => 'La Bibliothèque électronique du Québec', 'db_path' => '/opt/atoll/opds/beq', 'epub_path' => '/opt/atoll/external/beq');

/**
 * Available actions
 */
$gConfig['actions'] = array();
$gConfig['actions']['cvs_export'] = 'Cvs export';
$gConfig['actions']['db_load'] = 'Calibre database load';
