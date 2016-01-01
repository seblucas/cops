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
		
		$this->filter = Filter::parseFilter($searchStr);
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

/**
 * Abstract classe to store filters internally. It's derived classes represent the different filter types.
 *
 */
abstract class Filter {
	private $isNegated = false;
	
	/**
	 * Converts the calibre search string into afilter object
	 *
	 * @param string $searchStr The calibre string
	 * @return Filter The internal, array-based representation
	 */
	public static function parseFilter($searchStr) {
		// deal with empty input strings
		if (strlen($searchStr) == 0)
			return new EmptyFilter($type);
	
		// Simple search string pattern. It recognizes search string of the form
		//     [attr]:[value]
		// and their negation
		//     not [attr]:[value]
		// where value is either a number, a boolean or a string in double quote.
		// In the latter case, the string starts with an operator (= or ~), followed by the search text.
		// TODO: deal with more complex search terms that can contain "and", "or" and brackets
		$pattern = '#(?P<neg>not)?\s*(?P<attr>\w+):(?P<value>"(?P<op>=|~)(?P<text>.*)"|true|false|\d+)#i';
		preg_match($pattern, $searchStr, $match);
	
		// Create the actual filter object
		$value = $match["value"];
		$filter   = null;
		if (substr($value, 0, 1) == '"') {
			$filter = new ComparingFilter($match["attr"], $match["text"], $match["op"]);
		} elseif (preg_match("#\d+", $value)) {
			$filter = new ComparingFilter($match["attr"], $value, $match["op"]);
		} else {
			$value = (strcasecmp($value, "true") == 0);
			$filter = new ExistenceFilter($match["attr"], $value);
		}
	
		// Negate if a leading "not" is given
		if (strlen($match["neg"]) > 0)
			$filter->negate();
	
		return $filter;
	}
	
	/**
	 * Negates the current filter. A second call will undo it.
	 */
	public function negate() {
		$this->isNegated = !$this->isNegated;
	}
	
	public function isNegated() {
		return $this->isNegated;
	}
}

/**
 * Class that represents an empty filter
 *
 */
class EmptyFilter extends Filter {
	public function __construct() {
		// Do Nothing
	}
}

/**
 * Class that represents a filter, that compares an attribute with a given value, e.g. tags with "Fiction" 
 *
 * This class allows for other comparation operators beside "="
 */
class ComparingFilter extends Filter {
	private $attr = null;   // The attribute that is filtered
	private $value = null;  // The value with which to compare
	private $op = null;     // The operator that is used for comparing
	
	/**
	 * Creates a comparing filter
	 * 
	 * @param string $attr The attribute that is filtered.
	 * @param mixed $value The value with which to compare.
	 * @param string $op The operator that is used for comparing, optional.
	 */
	public function __construct($attr, $value, $op = "=") {
		$this->attr = $attr;
		$this->value = $value;
		$this->op = $op;
	}
}

/**
 * Class that represents a filter, that checks if a given attribute exists for a book.
 */
class ExistenceFilter extends Filter {
	private $attr = null;   // The attribute that is filtered

	/**
	 * Creates an existence filter
	 *
	 * @param string $attr The attribute that is filtered.
	 * @param boolean $value True, if objects with that attribute are accepted by the filter, false if not.
	 */
	public function __construct($attr, $value = true) {
		$this->attr = $attr;
		
		// $value == false is the negation of $value == true 
		if (!$value)
			$this->negate();
	}
}