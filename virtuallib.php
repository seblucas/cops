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
	 * Returns a SQL query that finds the IDs of all books accepted by the filter.
	 *
	 * The sql statement return only one column with the name 'id'.
	 * This statement can be included into other sql statements in order to apply the filter, e.g. by using inner joins
	 * like "select books.* from books inner join ({0}) as filter on books.id = filter.id"
	 * @see Filter
	 * 
	 * @return string an sql query
	 */
	public function getFilterQuery() {
		return $this->filter->toSQLQuery();
	}
	
	/**
	 * Get the current VirtualLib object. 
	 *  
	 * @param int $database The current database id.
	 * @param int $virtualLib The current virtual library id.
	 * @return VirtualLib The corresponding VirtualLib object.
	 */
	public static function getVL($database = null, $virtualLib = null) {
		if ( is_null(self::$currentVL) || self::$currentVL->db_id != $database || (self::$currentVL->vl_id != $virtualLib && !is_null($virtualLib))) {
			if (is_null($virtualLib))
				$virtualLib = GetUrlParam (VL, 0);
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
	 * If virtual libraries are disabled, only an empty entry is returned.
	 * 
	 * @param int $database id of the database
	 * @return array An array of virtual libraries with the names as keys and the filter strings as values.  
	 */
	public static function getVLList($database = NULL) {
		// Standard return if virtual libraries are not enabled
		if (!self::isVLEnabled())
			return array("" => "");
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
	// Special settings for known attributes
	private static $KNOWN_ATTRIBUTES = array(
		"authors"    => array(),
		"series"     => array("link_join_on" => "series"),
		"publishers" => array(),
		"tags"       => array(),
		"ratings"    => array("filterColumn" => "rating"),
		"languages"  => array("filterColumn" => "lang_code", "link_join_on" => "lang_code")
	);
	
	private $isNegated = false;
	
	/**
	 * Creates the attribute settings
	 * @param string $attr the name of the attribute, e.g. "tags"
	 * @return array an assotiative array with the keys "table", "filterColumn", "link_table", "link_join_on", "bookID".
	 */
	public static function getAttributeSettings($attr) {
		if (!array_key_exists($attr, self::$KNOWN_ATTRIBUTES))
			return null;
		return self::$KNOWN_ATTRIBUTES[$attr] + array(
			"table"        => $attr,
			"filterColumn" => "name",
			"link_table"   => "books_" . $attr . "_link",
			"link_join_on" => substr($attr, 0, strlen($attr) - 1),
			"bookID"       => "book"
		);
	}
	
	/**
	 * Gets the from - part of a table, its link-table and a placeholder for the filter
	 * 
	 * @param string $table a table, e.g. "authors"
	 * @return string a from string with a placeholder for the filter query
	 */
	public static function getLinkedTable($table) {
		foreach (array_keys(self::$KNOWN_ATTRIBUTES) as $attr) {
			$tabInfo = self::getAttributeSettings($attr);
			if ($tabInfo["table"] == $table) {
				return str_format_n(
						"{table} inner join {link_table} as link on {table}.id = link.{link_join_on} 
							inner join ({placeholder}) as filter on filter.id = link.{bookID}", $tabInfo + array("placeholder" => "{0}"));
			}
		}
		return $table;
	}
	/**
	 * Converts the calibre search string into afilter object
	 *
	 * @param string $searchStr The calibre string
	 * @return Filter The internal, array-based representation
	 */
	public static function parseFilter($searchStr) {
		// deal with empty input strings
		if (strlen($searchStr) == 0)
			return new EmptyFilter();
	
		// Simple search string pattern. It recognizes search string of the form
		//     [attr]:[value]
		// and their negation
		//     not [attr]:[value]
		// where value is either a number, a boolean or a string in double quote.
		// In the latter case, the string starts with an operator (= or ~), followed by the search text.
		// TODO: deal with more complex search terms that can contain "and", "or" and brackets
		$pattern = '#(?P<neg>not)?\s*(?P<attr>\w+):(?P<value>"(?P<op>=|~)(?P<text>.*)"|true|false|\d+)#i';
		if (!preg_match($pattern, $searchStr, $match)) {
			trigger_error("Virtual Library Filter is not supported.", E_USER_WARNING);
			return new EmptyFilter();
		}
	
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
	 * Returns a SQL query that finds the IDs of all books accepted by the filter. The single columns name is id.
	 */
	public abstract function toSQLQuery();
	
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
	
	// Return all books (or no book if the filter is negated)
	public function toSQLQuery() {
		if ($this->isNegated())
			return "select id from books where 1 = 0";
		return "select id from books";
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
		$this->attr = strtolower($attr);
		$this->value = $value;
		$this->op = $op;
	}
	
	public function toSQLQuery() {
		$queryParams = self::getAttributeSettings($this->attr);
		// Do not filter if attribute is not valid
		if (is_null($queryParams))
			return "select id from books";
		
		// Include parameters into the sql query 
		$queryParams["value"] = $this->value;
		$sql = str_format_n(
				"select distinct {link_table}.{bookID} as id ".
				"from {table} inner join {link_table} on {table}.id = {link_table}.{link_join_on} ".
				"where {table}.{filterColumn} = '{value}'",
				$queryParams);
		// TODO: support different operators
		return $sql;
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
	
	public function toSQLQuery() {
		$queryParams = self::getAttributeSettings($this->attr);
		// Do not filter if attribute is not valid
		if (is_null($queryParams))
			return "select id from books";
	
		// Include parameters into the sql query
		$sql = str_format_n(
				"select distinct {link_table}.{bookID} as id".
				"from {table} inner join {link_table} on {table}.id = {link_table}.{link_join_on} ",
				$queryParams);
		return $sql;
	}
}