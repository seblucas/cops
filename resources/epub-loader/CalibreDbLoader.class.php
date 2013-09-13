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
		if ($inCreate || !file_exists($inDbFileName)) {
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
			$error = sprintf('Cannot read sql file: %s', $inDbFileName);
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
	 * @return void
	 */
	public function AddEpub($inFileName)
	{
		// Load the book infos
		$bookInfos = new BookInfos();
		$bookInfos->LoadFromEpub($inFileName);
		// Add the book
		$this->AddBook($bookInfos);
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
		$sql = 'insert into books(title, sort, uuid, path) values(:title, :sort, :uuid, :path)';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':title', $inBookInfo->mTitle);
		$stmt->bindParam(':sort', $inBookInfo->mTitle);
		$stmt->bindParam(':uuid', $inBookInfo->mUuid);
		$stmt->bindParam(':path', $inBookInfo->mPath);
		$stmt->execute();
		// Get the book id
		$sql = 'select id, title from books where uuid=:uuid';
		$stmt = $this->mDb->prepare($sql);
		$stmt->bindParam(':uuid', $inBookInfo->mUuid);
		$stmt->execute();
		$idBook = null;
		while ($post = $stmt->fetchObject()) {
			if (!isset($idBook)) {
				$idBook = $post->id;
			}
			else {
				$error = sprintf('Multiple book id for uuid: %s (already in title "%s")', $inBookInfo->mUuid, $post->title);
				throw new Exception($error);
			}
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
		// Add the book identifiers
		if (!empty($inBookInfo->mUri)) {
			$sql = 'insert into identifiers(book, type, val) values(:idBook, :type, :value)';
			$stmt = $this->mDb->prepare($sql);
			$type = 'URI';
			$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
			$stmt->bindParam(':type', $type);
			$stmt->bindParam(':value', $inBookInfo->mUri);
			$stmt->execute();
		}
		// Add the authors in the db
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
				// Add the book author link
				$sql = 'insert into books_authors_link(book, author) values(:idBook, :idAuthor)';
				$stmt = $this->mDb->prepare($sql);
				$stmt->bindParam(':idBook', $idBook, PDO::PARAM_INT);
				$stmt->bindParam(':idAuthor', $idAuthor, PDO::PARAM_INT);
				$stmt->execute();
			}
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
