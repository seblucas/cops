<?php
/**
 * BookInfos class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

$ePubMetaPath = realpath(dirname(dirname(dirname(__FILE__)))) . '/vendor/seblucas/php-epub-meta';
require_once $ePubMetaPath . '/lib/EPub.php';
require_once $ePubMetaPath . '/lib/EPubDOMElement.php';
require_once $ePubMetaPath . '/lib/EPubDOMXPath.php';

class BookEPub extends EPub
{
	protected $epubVersion = 0;

	/**
	 * Get the ePub version
	 *
	 * @return int The number of the ePub version (2 or 3 for now) or 0 if not found
	 */
	public function getEpubVersion()
	{
		if ($this->epubVersion) {
			return $this->epubVersion;
		}

		$this->epubVersion = 0;
		$nodes = $this->xpath->query('//opf:package[@unique-identifier="BookId"]');
		if ($nodes->length) {
			$this->epubVersion = (int)$nodes->item(0)->attr('version');
		}
		else {
			$nodes = $this->xpath->query('//opf:package');
			if ($nodes->length) {
				$this->epubVersion = (int)$nodes->item(0)->attr('version');
			}
		}

		return $this->epubVersion;
	}

	/**
	 * meta file getter
	 */
	public function meta()
	{
		return $this->meta;
	}

	/**
	 * Get or set the book author(s)
	 *
	 * Authors should be given with a "file-as" and a real name. The file as
	 * is used for sorting in e-readers.
	 *
	 * Example:
	 *
	 * array(
	 * 'Pratchett, Terry' => 'Terry Pratchett',
	 * 'Simpson, Jacqueline' => 'Jacqueline Simpson',
	 * )
	 *
	 * @params array $authors
	 */
	public function Authors($authors = false)
	{
		// set new data
		if ($authors !== false) {
			// Author where given as a comma separated list
			if (is_string($authors)) {
				if ($authors == '') {
					$authors = array();
				}
				else {
					$authors = explode(',', $authors);
					$authors = array_map('trim', $authors);
				}
			}

			// delete existing nodes
			$nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
			foreach ($nodes as $node)
				$node->delete();

				// add new nodes
				$parent = $this->xpath->query('//opf:metadata')->item(0);
				foreach ($authors as $as => $name) {
					if (is_int($as))
						$as = $name; //numeric array given
						$node = $parent->newChild('dc:creator', $name);
						$node->attr('opf:role', 'aut');
						$node->attr('opf:file-as', $as);
				}

				$this->reparse();
		}

		// read current data
		$rolefix = false;
		$authors = array();
		$version = $this->getEpubVersion();
		if ($version == 3) {
			$rolefix = true;
			// <dc:creator id="create1">Marie d'Agoult</dc:creator>
			$nodes = $this->xpath->query('//opf:metadata/dc:creator');
		}
		else {
			// <dc:creator opf:file-as="Bouvier, Alexis" opf:role="aut">Alexis Bouvier</dc:creator>
			$nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
		}
		foreach ($nodes as $node) {
			$as = '';
			$name = $node->nodeValue;
			if ($version == 3) {
				$property = '';
				$id = $node->attr('id');
				// Check if role is aut
				// <meta refines="#create1" scheme="marc:relators" property="role">aut</meta>
				$metaNodes = $this->xpath->query('//opf:metadata/opf:meta[@refines="#' . $id . '"]');
				foreach ($metaNodes as $metaNode) {
					$metaProperty = $metaNode->attr('property');
					switch ($metaProperty) {
						case 'role':
							$property = $metaNode->nodeValue;
							break;
						case 'file-as':
							$as = $metaNode->nodeValue;
							break;
					}
				}
				if ($property != 'aut') {
					continue;
				}
			}
			else {
				$as = $node->attr('opf:file-as');
			}
			if (!$as) {
				$as = $name;
				$node->attr('opf:file-as', $as);
			}
			if ($rolefix) {
				$node->attr('opf:role', 'aut');
			}
			$authors[$as] = $name;
		}

		if (count($authors) > 1) {
			ksort($authors);
		}

		return $authors;
	}

	/**
	 * Set or get the book's creation date
	 *
	 * @param string Date eg: 2012-05-19T12:54:25Z
	 */
	public function CreationDate($date = false)
	{
		// <dc:date opf:event="creation">2014-08-03T16:01:40Z</dc:date>
		$res = $this->getset('dc:date', $date, 'opf:event', 'creation');

		// <meta property="dcterms:created">2014-06-08T14:22:53Z</meta>
		if (empty($res)) {
			$version = $this->getEpubVersion();
			if ($version == 3) {
				$res = $this->getset('opf:meta', $date, 'property', 'dcterms:created');
			}
		}

		// <meta content="2014-08-03T18:01:35" name="amanuensis:xhtml-creation-date" />
		if (empty($res)) {
			$res = $this->getset('opf:meta', $date, 'name', 'amanuensis:xhtml-creation-date', 'content');
		}

		return $res;
	}

	/**
	 * Set or get the book's modification date
	 *
	 * @param string Date eg: 2012-05-19T12:54:25Z
	 */
	public function ModificationDate($date = false)
	{
		// <dc:date opf:event="modification">2014-08-03T16:01:40Z</dc:date>
		$res = $this->getset('dc:date', $date, 'opf:event', 'modification');

		// <meta property="dcterms:modified">2018-12-20T13:59:10Z</meta>
		if (empty($res)) {
			$version = $this->getEpubVersion();
			if ($version == 3) {
				$res = $this->getset('opf:meta', $date, 'property', 'dcterms:modified');
			}
		}

		return $res;
	}

	// public function Cover($path = false, $mime = false)
	// $zip->close();
}
