<?php
/**
 * BookInfos class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

require_once(realpath(dirname(__FILE__)) . '/ZipFile.class.php');
require_once(realpath(dirname(dirname(__FILE__))) . '/php-epub-meta/epub.php');

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
		$ePub = new EPub($fullFileName, 'ZipFile');

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
		$this->mCreationDate = $ePub->CreationDate();
		$this->mModificationDate = $ePub->ModificationDate();
	}

}

?>