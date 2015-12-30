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
	
	const FILTER_TYPE_TEXT = 0;   // Filter by search text
	const FILTER_TYPE_NUM  = 1;   // Filter by number
	const FILTER_TYPE_BOOL = 2;   // Filter by existence
	
	private $db_id = null;   // Database Index for the virtual lib
	private $vl_id = null;   // Library Index
	private $filter = null;  // structured representation of the current filter 
	
	private static $currentVL = null;  // Singleton: current virtual lib
	
	/**
	 * The constructor parses the calibre search string and creates a array-based representation out of it.
	 * 
	 * @param int $database The database id of the new object.
	 * @param int $virtualLib The virtual library id of the new object.
	 */
	private function __construct($database = null, $virtualLib = null) {
		$this->db_id  = $database;
		$this->vl_id  = $virtualLib;
		
		// Get the current search string
		$vlList = self::getVLList($database);
		$vlList = array_values($vlList);
		$searchStr = $vlList[$virtualLib];
		
		$this->filter = self::parseFilter($searchStr);
	}
	
	/**
	 * Get the current VirtualLib object. 
	 *  
	 * @param int $database The current database id.
	 * @param int $virtualLib The current virtual library id.
	 * @return VirtualLib The corresponding VirtualLib object.
	 */
	public static function getVL($database = null, $virtualLib = null) {
		if ( is_null(self::$currentVL) || self::$currentVL->db_id != $database || self::$currentVL->vl_id != $virtualLib ) {
			self::$currentVL = new VirtualLib($database, $virtualLib);
		}
		return self::$currentVL;
	}
	
	/**
	 * Converts the calibre search string into an internal format
	 * 
	 * @param string $searchStr The calibre string
	 * @return string The internal, array-based representation
	 */
	private static function parseFilter($searchStr) {
		// deal with empty strings
		if (strlen($searchStr) == 0)
			return null;
		
		// Simple search string pattern. It recognizes search string of the form
		//     [name]:[value]
		// and their negation
		//     not [name]:[value]
		// where value is either a number, a boolean or a string in double quote.
		// In the latter case, the string starts with an operator (= or ~), followed by the search text.
		// TODO: deal with more complex search terms that can contain "and", "or" and brackets
		$pattern = '#(?P<neg>not)?\s*(?P<name>\w+):(?P<value>"(?P<op>=|~)(?P<text>.*)"|true|false|\d+)#i';
		preg_match($pattern, $searchStr, $match);
		
		// Extract the actual value, operator and type
		$value    = $match["value"];
		$operator = "=";
		if (substr($value, 0, 1) == '"') {
			$value = $match["text"];
			$operator = $match["op"];
			$type = self::FILTER_TYPE_TEXT;
		} elseif (preg_match("#\d+", $value)) {
			$value = intval($value);
			$type = self::FILTER_TYPE_NUM;
		} else {
			$value = (strcasecmp($value, "true") == 0);
			$type = self::FILTER_TYPE_BOOL;
		}
		
		// Put together filter data
		$filter = array(
				"name"  => $match["name"],
				"neg"   => (strlen($match["neg"]) > 0)?true:false,
				"op"    => $operator,
				"value" => $value,
				"type"  => $type
		);
		
		return $filter;
	}
	
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