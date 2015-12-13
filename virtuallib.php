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
		$vl_json = Base::getCalibreSetting(self::SQL_VL_KEY, $database);
		return json_decode($vl_json, true);
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
}