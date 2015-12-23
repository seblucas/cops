<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Klaus Broelemann <broelemeter@web.de>
 */

require_once('base.php');

/**
 * Read and filter virtual libraries
 */
class VirtualLib {
	const SQL_VL_KEY = "virtual_libraries";  // Key for the virtual library entries.
	
	/**
	 * Checks if the support for virtual libraries is enabled in the settings.
	 * 
	 * @return boolean true, if virtual libraries are enabled.
	 */
	public static function isVLEnabled() {
		global $config;
		return ($config['enable_virtual_libraries'] == 1);
	}
	
	/**
	 * Gets a list of all virtual libraries in a database.
	 * 
	 * @param int $database id of the database
	 * @return array An array of virtual libraries with the names as keys and the filter strings as values.  
	 */
	public static function getVLList($database = NULL) {
		// Load list from Database
		$vLibs = json_decode(Base::getCalibreSetting(self::SQL_VL_KEY, $database), true);
		// Add "All Books" at the beginning
		if (is_null($vLibs))
			return array(localize ("allbooks.title") => "");
		else
			return array_merge(array(localize ("allbooks.title") => ""), $vLibs);
	}
	
	/**
	 * Gets a list of all virtual libraries in a database.
	 *
	 * @param int $database id of the database
	 * @return array An array of virtual libraries with the names as keys and the filter strings as values.
	 */
	public static function getVLNameList($database = NULL) {
		return array_keys(self::getVLList($database));
	}
	
	/**
	 * Combines the database and virtual lib names into a merged name.
	 * 
	 * The resulting name has the form "{$dbName} - {$vlName}". If one of these parameters is empty, the dash will also be removed.
	 * If the support for virtual libraries is not enabled, this function simply returns the database name.
	 * 
	 * @param string $dbName The Database Name
	 * @param string $vlName The Name of the virtual library
	 */
	public static function getDisplayName($dbName, $vlName) {
		if (self::isVLEnabled())
			return trim(str_format('{0} - {1}', $dbName, $vlName), ' -');
		else
			return $dbName;
	}
}