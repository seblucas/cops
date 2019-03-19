<?php
/**
 * BookInfos class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

require_once (realpath(dirname(__FILE__)) . '/BookEPub.class.php');
require_once (realpath(dirname(__FILE__)) . '/ZipFile.class.php');
/*
$fileName = realpath(dirname(dirname(__FILE__))) . '/php-epub-meta/epub.php';
if (file_exists($fileName)) {
	require_once ($fileName);
}
else {
	$fileName = realpath(dirname(dirname(dirname(__FILE__)))) . '/vendor/seblucas/php-epub-meta/lib/EPub.php';
	require_once ($fileName);
}
*/

/**
 * BookInfos class contains informations about a book,
 * and methods to load this informations from multiple sources (eg epub file)
 */
class BookInfos
{

	public $mBasePath = '';

	public $mPath = '';

	public $mName = '';

	public $mFormat = '';

	public $mUuid = '';

	public $mUri = '';

	public $mTitle = '';

	public $mAuthors = null;

	public $mLanguage = '';

	public $mDescription = '';

	public $mSubjects = null;

	public $mCover = '';

	public $mIsbn = '';

	public $mRights = '';

	public $mPublisher = '';

	public $mSerie = '';

	public $mSerieIndex = '';

	public $mCreationDate = '';

	public $mModificationDate = '';

	public $mTimeStamp = 0;

	/**
	 * Loads book infos from an epub file
	 *
	 * @param string Epub base directory
	 * @param string Epub file name (from base directory)
	 * @throws Exception if error
	 *
	 * @return void
	 */
	public function LoadFromEpub($inBasePath, $inFileName)
	{
		$fullFileName = sprintf('%s%s%s', $inBasePath, DIRECTORY_SEPARATOR, $inFileName);
		// Check file access
		if (!is_readable($fullFileName)) {
			throw new Exception('Cannot read file');
		}

		// Load the epub file
		$ePub = new BookEPub($fullFileName, 'ZipFile');

		// Check epub version
		$version = $ePub->getEpubVersion();
		switch ($version) {
		case 2:
		case 3:
			break;
		default:
			$error = sprintf('Incorrect ebook epub version=%d', $version);
			throw new Exception($error);
		}

		// Get the epub infos
		$this->mFormat = 'epub';
		$this->mBasePath = $inBasePath;
		$this->mPath = pathinfo($inFileName, PATHINFO_DIRNAME);
		$this->mName = pathinfo($inFileName, PATHINFO_FILENAME);
		$this->mUuid = $ePub->Uuid();
		$this->mUri = $ePub->Uri();
		$this->mTitle = $ePub->Title();
		$this->mAuthors = $ePub->Authors();
		$this->mLanguage = $ePub->Language();
		$this->mDescription = $ePub->Description();
		$this->mSubjects = $ePub->Subjects();
		$cover = $ePub->Cover();
		$cover = $cover['found'];
		if (($cover !== false)) {
			// Remove meta base path
			$meta = $ePub->meta();
			$len = strlen($meta) - strlen(pathinfo($meta, PATHINFO_BASENAME));
			$this->mCover = substr($cover, $len);
		}
		$this->mIsbn = $ePub->ISBN();
		$this->mRights = $ePub->Copyright();
		$this->mPublisher = $ePub->Publisher();
		// Tag sample in opf file:
		//   <meta content="Histoire de la Monarchie de Juillet" name="calibre:series"/>
		$this->mSerie = $ePub->Serie();
		// Tag sample in opf file:
		//   <meta content="7" name="calibre:series_index"/>
		$this->mSerieIndex = $ePub->SerieIndex();
		$this->mCreationDate = $this->GetSqlDate($ePub->CreationDate());
		$this->mModificationDate = $this->GetSqlDate($ePub->ModificationDate());
		// Timestamp is used to get latest ebooks
		$this->mTimeStamp = $this->mCreationDate;
	}

	/**
	 * Format an date from a date
	 *
	 * @param string $inDate
	 *
	 * @return string Sql formated date
	 */
	private function GetSqlDate($inDate)
	{
		if (empty($inDate)) {
			return null;
		}

		$date = new \DateTime($inDate);
		$res = $date->format('Y-m-d H:i:s');

		return $res;
	}

	/**
	 * Format a timestamp from a date
	 *
	 * @param string $inDate
	 *
	 * @return int Timestamp
	 */
	public static function GetTimeStamp($inDate)
	{
		if (empty($inDate)) {
			return null;
		}

		$date = new \DateTime($inDate);
		$res = $date->getTimestamp();

		return $res;
	}

	/**
	 * Create a new unique id (same as shell uuidgen)
	 *
	 * @return void
	 */
	public function CreateUuid()
	{
		$data = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		$this->mUuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}

?>
