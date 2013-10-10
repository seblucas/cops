<?php
/**
 * CalibreDbLoader class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

require_once(realpath(dirname(__FILE__)) . '/BookInfos.class.php');

/**
 * Calibre database sql file that comes unmodified from Calibre project:
 *   /calibre/resources/metadata_sqlite.sql
 */
define('CalibreCreateDbSql', realpath(dirname(__FILE__)) . '/metadata_sqlite.sql');

/**
 * CalibreDbLoader class allows to open or create a new Calibre database,
 * and then add BookInfos objects into the database
 */
class CalibreDbLoader
{
	private $mDb = null;

	/**
	 * Open a Calibre database (or create if database does not exist)
	 *
	 * @param string Calibre database file name
	 * @param boolean Force database creation
	 */
	public function __construct($inDbFileName, $inCreate = false)
	{
		if ($inCreate) {
			$this->CreateDatabase($inDbFileName);
		}
		else {
			$this->OpenDatabase($inDbFileName);
		}
	}

	/**
	 * Create an sqlite database
	 *
	 * @param string Database file name
	 * @throws Exception if error
	 *
	 * @return void
	 */
	private function CreateDatabase($inDbFileName)
	{
		// Read the sql file
		$content = file_get_contents(CalibreCreateDbSql);
		if ($content === false) {
			$error = sprintf('Cannot read sql file: %s', CalibreCreateDbSql);
			throw new Exception($error);
		}

		// Remove the database file
		if (file_exists($inDbFileName) && !unlink($inDbFileName)) {
			$error = sprintf('Cannot remove database file: %s', $inDbFileName);
			throw new Exception($error);
		}

		// Create the new database file
		$this->OpenDatabase($inDbFileName);

		// Create the database tables
		try {
			$sqlArray = explode('CREATE ', $content);
			foreach ($sqlArray as $sql) {
				$sql = trim($sql);
				if (empty($sql)) {
					continue;
				}
				$sql = 'CREATE ' . $sql;
				$str = strtolower($sql);
				if (strpos($str, 'create view') !== false) {
					continue;
				}
				if (strpos($str, 'title_sort') !== false) {
					continue;
				}
				$stmt = $this->mDb->prepare($sql);
				$stmt->execute();
			}
		}
		catch (Exception $e) {
			$error = sprintf('Cannot create database: %s', $e->getMessage());
			throw new Exception($error);
		}
	}

	/**
	 * Open an sqlite database
	 *
	 * @param string Database file name
	 * @throws Exception if error
	 *
	 * @return void
	 */
	private function OpenDatabase($inDbFileName)
	{
		try {
			// Init the Data Source Name
			$dsn = 'sqlite:' . $inDbFileName;
			// Open the database
			$this->mDb = new PDO($dsn); // Send an exception if error
			$this->mDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//echo sprintf('Init database ok for: %s%s', $dsn, '<br />');
		}
		catch (Exception $e) {
			$error = sprintf('Cannot open database [%s]: %s', $dsn, $e->getMessage());
			throw new Exception($error);
		}
	}

	/**
	 * Add an epub to the db
	 *
	 * @param string Epub file name
	 * @throws Exception if error
	 *
	 * @return string Empty string or error if any
	 */
	public function AddEpub($inFileName)
	{
		$error = '';

		try {
			// Load the book infos
			$bookInfos = new BookInfos();
			$bookInfos->LoadFromEpub($inFileName);
			// Add the book
			$this->AddBook($bookInfos);
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}

		return $error;

	}

	/**
	 * Add a new book into the db
	 *
	 * @param object BookInfo object
	 * @throws Exception if error
	 *
	 * @return void
	 */
	private function AddBook($inBookInfo)
	{
		// Check if the book uuid does not already exist
		$sql = 'select b.id, b.title, b.path, d.name, d.format from books as b, data as d where d.book = b.id and uuid=:uuid';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':uuid', $inBookInfo->mUuid);
		$stmt->execute();
		while ($post = $stmt->fetchObject()) {
			$error = sprintf('Multiple book id for uuid: %s (already in file "%s/%s.%s" title "%s")', $inBookInfo->mUuid, $post->path, $post->name, $post->format, $post->title);
			throw new Exception($error);
		}
		// Add the book
		$sql = 'insert into books(title, sort, pubdate, last_modified, series_index, uuid, path) values(:title, :sort, :pubdate, :lastmodified, :serieindex, :uuid, :path)';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':title', $inBookInfo->mTitle);
		$stmt->bindParam(':sort', $inBookInfo->mTitle);
		$stmt->bindParam(':pubdate', empty($inBookInfo->mCreationDate) ? null : $inBookInfo->mCreationDate);
		$stmt->bindParam(':lastmodified', empty($inBookInfo->mModificationDate) ? '2000-01-01 00:00:00+00:00' : $inBookInfo->mModificationDate);
		$stmt->bindParam(':serieindex', $inBookInfo->mSerieIndex);
		$stmt->bindParam(':uuid', $inBookInfo->mUuid);
		$stmt->bindParam(':path', $inBookInfo->mPath);
		$stmt->execute();
		// Get the book id
		$sql = 'select id from books where uuid=:uuid';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':uuid', $inBookInfo->mUuid);
		$stmt->execute();
		$idBook = null;
		while ($post = $stmt->fetchObject()) {
			$idBook = $post->id;
			break;
		}
		if (!isset($idBook)) {
			$error = sprintf('Cannot find book id for uuid: %s', $inBookInfo->mUuid);
			throw new Exception($error);
		}
		// Add the book formats
		$sql = 'insert into data(book, format, name, uncompressed_size) values(:idBook, :format, :name, 0)';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
		$stmt->bindParam(':format', $inBookInfo->mFormat);
		$stmt->bindParam(':name', $inBookInfo->mName);
		$stmt->execute();
		// Add the book comments
		$sql = 'insert into comments(book, text) values(:idBook, :text)';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
		$stmt->bindParam(':text', $inBookInfo->mDescription);
		$stmt->execute();
		// Add the book identifiers
		if (!empty($inBookInfo->mUri)) {
			$sql = 'insert into identifiers(book, type, val) values(:idBook, :type, :value)';
			$identifiers = array();
			$identifiers['URI'] = $inBookInfo->mUri;
			$identifiers['ISBN'] = $inBookInfo->mIsbn;
			foreach ($identifiers as $key => $value) {
				if (empty($value)) {
					continue;
				}
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
				$stmt->bindParam(':type', $key);
				$stmt->bindParam(':value', $value);
				$stmt->execute();
			}
		}
		// Add the book serie
		if (!empty($inBookInfo->mSerie)) {
			// Get the serie id
			$sql = 'select id from series where name=:serie';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':serie', $inBookInfo->mSerie);
			$stmt->execute();
			$post = $stmt->fetchObject();
			if ($post) {
				$idSerie = $post->id;
			}
			else {
				// Add a new serie
				$sql = 'insert into series(name, sort) values(:serie, :sort)';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':serie', $inBookInfo->mSerie);
				$stmt->bindParam(':sort', $inBookInfo->mSerie);
				$stmt->execute();
				// Get the serie id
				$sql = 'select id from series where name=:serie';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':serie', $inBookInfo->mSerie);
				$stmt->execute();
				$idSerie = null;
				while ($post = $stmt->fetchObject()) {
					if (!isset($idSerie)) {
						$idSerie = $post->id;
					}
					else {
						$error = sprintf('Multiple series for name: %s', $inBookInfo->mSerie);
						throw new Exception($error);
					}
				}
				if (!isset($idSerie)) {
					$error = sprintf('Cannot find serie id for name: %s', $inBookInfo->mSerie);
					throw new Exception($error);
				}
			}
			// Add the book serie link
			$sql = 'insert into books_series_link(book, series) values(:idBook, :idSerie)';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
			$stmt->bindParam(':idSerie', $idSerie, PDO::PARAM_INT);
			$stmt->execute();
		}
		// Add the book authors
		foreach ($inBookInfo->mAuthors as $authorSort => $author) {
			// Get the author id
			$sql = 'select id from authors where name=:author';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':author', $author);
			$stmt->execute();
			$post = $stmt->fetchObject();
			if ($post) {
				$idAuthor = $post->id;
			}
			else {
				// Add a new author
				$sql = 'insert into authors(name, sort) values(:author, :sort)';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':author', $author);
				$stmt->bindParam(':sort', $authorSort);
				$stmt->execute();
				// Get the author id
				$sql = 'select id from authors where name=:author';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':author', $author);
				$stmt->execute();
				$idAuthor = null;
				while ($post = $stmt->fetchObject()) {
					if (!isset($idAuthor)) {
						$idAuthor = $post->id;
					}
					else {
						$error = sprintf('Multiple authors for name: %s', $author);
						throw new Exception($error);
					}
				}
				if (!isset($idAuthor)) {
					$error = sprintf('Cannot find author id for name: %s', $author);
					throw new Exception($error);
				}
			}
			// Add the book author link
			$sql = 'insert into books_authors_link(book, author) values(:idBook, :idAuthor)';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
			$stmt->bindParam(':idAuthor', $idAuthor, PDO::PARAM_INT);
			$stmt->execute();
		}
		// Add the book language
		{
			// Get the language id
			$sql = 'select id from languages where lang_code=:language';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':language', $inBookInfo->mLanguage);
			$stmt->execute();
			$post = $stmt->fetchObject();
			if ($post) {
				$idLanguage = $post->id;
			}
			else {
				// Add a new language
				$sql = 'insert into languages(lang_code) values(:language)';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':language', $inBookInfo->mLanguage);
				$stmt->execute();
				// Get the language id
				$sql = 'select id from languages where lang_code=:language';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':language', $inBookInfo->mLanguage);
				$stmt->execute();
				$idLanguage = null;
				while ($post = $stmt->fetchObject()) {
					if (!isset($idLanguage)) {
						$idLanguage = $post->id;
					}
					else {
						$error = sprintf('Multiple languages for lang_code: %s', $inBookInfo->mLanguage);
						throw new Exception($error);
					}
				}
				if (!isset($idLanguage)) {
					$error = sprintf('Cannot find language id for lang_code: %s', $inBookInfo->mLanguage);
					throw new Exception($error);
				}
			}
			// Add the book language link
			$itemOder = 0;
			$sql = 'insert into books_languages_link(book, lang_code, item_order) values(:idBook, :idLanguage, :itemOrder)';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
			$stmt->bindParam(':idLanguage', $idLanguage, PDO::PARAM_INT);
			$stmt->bindParam(':itemOrder', $itemOder, PDO::PARAM_INT);
			$stmt->execute();
		}
		// Add the book tags (subjects)
		foreach ($inBookInfo->mSubjects as $subject) {
			// Get the subject id
			$sql = 'select id from tags where name=:subject';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':subject', $subject);
			$stmt->execute();
			$post = $stmt->fetchObject();
			if ($post) {
				$idSubject = $post->id;
			}
			else {
				// Add a new subject
				$sql = 'insert into tags(name) values(:subject)';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':subject', $subject);
				$stmt->execute();
				// Get the subject id
				$sql = 'select id from tags where name=:subject';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':subject', $subject);
				$stmt->execute();
				$idSubject = null;
				while ($post = $stmt->fetchObject()) {
					if (!isset($idSubject)) {
						$idSubject = $post->id;
					}
					else {
						$error = sprintf('Multiple subjects for name: %s', $subject);
						throw new Exception($error);
					}
				}
				if (!isset($idSubject)) {
					$error = sprintf('Cannot find subject id for name: %s', $subject);
					throw new Exception($error);
				}
			}
			// Add the book subject link
			$sql = 'insert into books_tags_link(book, tag) values(:idBook, :idSubject)';
			$stmt = $this->mDb->prepare($sql);
			$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
			$stmt->bindParam(':idSubject', $idSubject, PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	/**
	 * Check database for debug
	 *
	 * @return void
	 */
	private function CheckDatabase()
	{
		// Retrieve some infos for check only
		$sql = 'select id, title, sort from books';
		$stmt = $this->mDb->prepare($sql);
		$stmt->execute();
		while ($post = $stmt->fetchObject()) {
			$id = $post->id;
			$title = $post->title;
			$sort = $post->sort;
		}
	}

}

?>
