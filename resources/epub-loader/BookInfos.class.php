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
		$epub = new EPub($inFileName, 'ZipFile');

		// Get the epub infos
		$this->mFormat = 'epub';
		$this->mPath = pathinfo($inFileName, PATHINFO_DIRNAME);
		$this->mName = pathinfo($inFileName, PATHINFO_FILENAME);
		$this->mUuid = $epub->Uuid();
		$this->mUri = $epub->Uri();
		$this->mTitle = $epub->Title();
		$this->mAuthors = $epub->Authors();
		$this->mLanguage = $epub->Language();
		$this->mDescription = $epub->Description();
		$this->mSubjects = $epub->Subjects();
		$this->mCover = $epub->getCoverItem();
		$this->mIsbn = $epub->ISBN();
		$this->mRights = $epub->Copyright();
		$this->mPublisher = $epub->Publisher();
	}

}

?>