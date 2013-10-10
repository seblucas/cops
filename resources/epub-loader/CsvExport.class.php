<?php
/**
 * CsvExport class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

require_once(realpath(dirname(__FILE__)) . '/BaseExport.class.php');

class CsvExport extends BaseExport
{
	private $mLines = null;

	const CsvSeparator = "\t";

	/**
	 * Open an export file (or create if file does not exist)
	 *
	 * @param string Export file name
	 * @param boolean Force file creation
	 */
	public function __construct($inFileName, $inCreate = false)
	{
		$this->mSearch = array("\r", "\n", self::CsvSeparator);
		$this->mReplace = array('', '<br />', '');

		// Init container
		$this->mLines = array();

		parent::__construct($inFileName, $inCreate);
	}

	/**
	 * Add the current properties into the export content
	 * and reset the properties
	 */
	public function AddContent()
	{
		$text = '';
		foreach ($this->mProperties as $key => $value) {
			$info = '';
			if (is_array($value)) {
				foreach ($value as $value1) {
					// Escape quotes
					if (strpos($value1, '\'') !== false) {
						$value1 = '\'' . str_replace('\'', '\'\'', $value1) . '\'';
					}
					$text .= $value1 . self::CsvSeparator;
				}
				continue;
			}
			else {
				// Escape quotes
				if (strpos($value, '\'') !== false) {
					$value = '\'' . str_replace('\'', '\'\'', $value) . '\'';
				}
				$info = $value;
			}
			$text .= $info . self::CsvSeparator;
		}

		$this->mLines[] = $text;

		$this->ClearProperties();
	}

	protected function GetContent()
	{
		$text = implode("\n", $this->mLines) . "\n";

		return $text;
	}

}

?>
