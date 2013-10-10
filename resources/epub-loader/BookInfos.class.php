<?php
/**
 * BookInfos class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

require_once(realpath(dirname(__FILE__)) . '/ZipFile.class.php');
require_once(realpath(dirname(dirname(__FILE__))) . '/php-epub-meta/epub.php');

/**
 * BookInfos class contains informations about a book,
 * and methods to load this informations from multiple sources (eg epub file)
 */
class BookInfos
{
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
	 * @param string Epub full file name
	 * @throws Exception if error
	 *
	 * @return void
	 */
	public function LoadFromEpub($inFileName)
	{
		// Load the epub file
		$ePub = new EPub($inFileName, 'ZipFile');

		// Get the epub infos
		$this->mFormat = 'epub';
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
		$this->mCover = ($cover['found'] !== false) ? $cover['found'] : '';
		$this->mIsbn = $ePub->ISBN();
		$this->mRights = $ePub->Copyright();
		$this->mPublisher = $ePub->Publisher();
		$this->mSerie = $ePub->Serie();
		$this->mSerieIndex = $ePub->SerieIndex();
		$this->mCreationDate = $ePub->CreationDate();
		$this->mModificationDate = $ePub->ModificationDate();
	}

}

?>