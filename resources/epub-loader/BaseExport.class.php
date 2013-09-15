<?php
/**
 * BaseExport class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

class BaseExport
{
	protected $mProperties = null;
	protected $mFileName = '';
	protected $mSearch = null;
	protected $mReplace = null;

	public $mFormatProperty = true;

	/**
	 * Open an export file (or create if file does not exist)
	 *
	 * @param string Export file name
	 * @param boolean Force file creation
	 */
	public function __construct($inFileName, $inCreate = false)
	{
		if ($inCreate && file_exists($inFileName)) {
			if (!unlink($inFileName)) {
				$error = sprintf('Cannot remove file: %s', $inFileName);
				throw new Exception($error);
			}
		}

		$this->mFileName = $inFileName;

		$this->mProperties = array();
	}

	public function ClearProperties()
	{
		$this->mProperties = array();
	}

	public function SetProperty($inKey, $inValue)
	{
		// Don't store empty keys
		if (empty($inKey)) {
			return;
		}

		if ($this->mFormatProperty) {
			$inValue = $this->FormatProperty($inValue);
		}

		$this->mProperties[$inKey] = $inValue;
	}

	/**
	 * Format a property
	 *
	 * @param string or array of strings to format
	 * @return string or array of strings formated
	 */
	protected function FormatProperty($inValue)
	{
		if (!isset($inValue)) {
			return '';
		}
		if (is_numeric($inValue)) {
			return (string)$inValue;
		}
		if (is_array($inValue)) {
			// Recursive call for arrays
			foreach ($inValue as $key => $value) {
				$inValue[$key] = $this->FormatProperty($value);
			}
			return $inValue;
		}
		if (!is_string($inValue) || empty($inValue)) {
			return '';
		}

		// Replace html entities with normal characters
		$str = html_entity_decode($inValue, ENT_COMPAT, 'UTF-8');
		// Replace characters
		if (isset($this->mSearch)) {
			$str = str_replace($this->mSearch, $this->mReplace, $str);
		}

		// Strip double spaces
		while (strpos($str, '  ') !== false) {
			$str = str_replace('  ', ' ', $str);
		}

		// Trim
		$str = trim($str);

		return $str;
	}

	/**
	 * Save data to file
	 *
	 * @throws Exception if error
	 */
	public function SaveToFile()
	{
		// Write the file
		$content = $this->GetContent();
		if (!file_put_contents($this->mFileName, $content)) {
			$error = sprintf('Cannot save export to file: %s', $this->mFileName);
			throw new Exception($error);
		}
	}

	/**
	 * Send download http headers
	 *
	 * @param string $inFileName Download file name to display in the browser
	 * @param int $inFileSize Download file size
	 * @param string $inCodeSet Charset
	 * @throws exception if http headers have been already sent
	 *
	 * @return void
	 */
	private function SendDownloadHeaders($inFileName, $inFileSize = null, $inCodeSet = 'utf-8')
	{
		// Throws excemtion if http headers have been already sent
		$filename = '';
		$linenum = 0;
		if (headers_sent($filename, $linenum)) {
			$error = sprintf('Http headers already sent by file: %s ligne %d', $filename, $linenum);
			throw new Exception($error);
		}

		$inFileName = str_replace(' ', '', basename($inFileName)); // Cleanup file name
		$ext = strtolower(substr(strrchr($inFileName, '.'), 1));

		switch ($ext) {
		case 'pdf':
			$contentType = 'application/pdf';
			break;
		case 'zip':
			$contentType = 'application/zip';
			break;
		case 'xml':
			$contentType = 'text/xml';
			if (!empty($inCodeSet)) {
				$contentType .= '; charset=' . $inCodeSet . '"';
			}
			break;
		case 'txt':
			$contentType = 'text/plain';
			if (!empty($inCodeSet)) {
				$contentType .= '; charset=' . $inCodeSet . '"';
			}
			break;
		case 'csv':
			$contentType = 'text/csv';
			if (!empty($inCodeSet)) {
				$contentType .= '; charset=' . $inCodeSet . '"';
			}
			break;
		case 'html':
			$contentType = 'text/html';
			if (!empty($inCodeSet)) {
				$contentType .= '; charset=' . $inCodeSet . '"';
			}
			break;
		default:
			$contentType = 'application/force-download';
			break;
		}

		// Send http headers for download
		header('Content-disposition: attachment; filename="' . $inFileName . '"');
		Header('Content-Type: ' . $contentType);
		//header('Content-Transfer-Encoding: binary');
		if (isset($inFileSize)) {
			header('Content-Length: ' . $inFileSize);
		}

		// Send http headers to remove the browser cache
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	}

	/**
	 * Download export and stop further script execution
	 */
	public function Download()
	{
		$content = $this->GetContent();

		// Send http download headers
		$size = strlen($content);
		$this->SendDownloadHeaders($this->mFileName, $size);

		// Send file content to download
		echo $content;

		exit;
	}

}

?>
