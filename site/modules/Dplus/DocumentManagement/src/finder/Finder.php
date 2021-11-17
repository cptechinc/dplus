<?php namespace Dplus\DocManagement\Finders;
// Purl URI manipulation library
use Purl\Url;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// ProcessWire
use ProcessWire\WireData;
// Dplus Models
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
// Dplus Document Management
use Dplus\DocManagement\Mover as FileMover;
use Dplus\DocManagement\Viewer;
use Dplus\DocManagement\Folders;

/**
 * Document Finder
 * Decorator for DocumentQuery to find Documents in Database
 */
class Finder extends WireData {
	const FOLDER = '';
	const TAG_ARINVOICE  = 'AR';
	const TAG_SALESORDER = 'SO';
	const TAG_QUOTE      = 'QT';
	const TAG_ITEM       = 'IT';
	const TAG_VENDORPO   = 'PO';
	const TAG_APINVOICE  = 'AP';
	const TAG_CUSTOMER   = 'CU';
	const TAG_WIP        = 'WP';
	const TAG_LOT        = 'LT';

	const TAG_AR_CHECKS = 'RC';

	const FOLDER_ARINVOICE = 'ARINVC';
	const FOLDER_ARINVOICE_ALT = 'ARINV';

	const EXTENSIONS_IMAGES = ['jpg', 'gif', 'png'];

	private static $filemover;


	protected function initColumns() {
		$this->columns = $this->getColumnData();
	}

	static protected function getFileMover() {
		if (empty(self::$filemover)) {
			self::$filemover = new FileMover();
		}
		return self::$filemover;
	}

	/**
	 * Return Tag Code for tag name
	 * @uses self::TAG_*
	 *
	 * @param  string $tagname Tag name
	 * @return string          Tag Code
	 */
	public function getTag($tagname) {
		$tag = strtoupper($tagname);
		return constant("self::TAG_$tag");
	}

	/**
	 * Return Folder Code
	 * @return string
	 */
	public function getFolder() {
		return static::FOLDER;
	}

	/**
	 * Return Document Query
	 * @return DocumentQuery
	 */
	public function docQuery() {
		return DocumentQuery::create();
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return if File Exists in Database
	 * @param  string $folder   Folder Code
	 * @param  string $filename File Name
	 * @return bool
	 */
	public function exists($folder, $filename) {
		$q = $this->docQuery();
		$q->filterByFolder($folder);
		$q->filterByFilename($filename);
		return boolval($q->count());
	}

	/**
	 * Return Document
	 * @param  string $folder   Folder Code
	 * @param  string $filename File Name
	 * @return Document
	 */
	public function getDocumentByFilename($folder, $filename) {
		$q = $this->docQuery();
		$q->filterByFolder($folder);
		$q->filterByFilename($filename);
		return $q->findOne();
	}

	/**
	 * Return filepath for Document
	 * @param  string $folder   Document Folder
	 * @param  string $filename File Name
	 * @return string
	 */
	public function documentFilepath($folder, $filename) {
		if ($this->exists($folder, $filename) === false) {
			return '';
		}
		$folder = $this->getFolders()->folder($folder);
		return "$folder->directory/$filename";
	}

	/**
	 * Returns if file is already in the web access directory
	 * @param  string $filename File Name
	 * @return bool             Is the file in the web access directory?
	 */
	public function isInWebDirectory($filename) {
		$mover = self::getFileMover();
		return $mover->isInWebDirectory($filename);
	}

/* =============================================================
	File Retrieval Functions
============================================================= */
	/**
	 * Finds a Document from the Documents table and creates a copy
	 * @uses self::move_file()
	 *
	 * @param  string $folder      Which Folder to Filter the document to
	 * @param  string $filename    File Name
	 * @param  string $destination Directory to move the file to
	 * @return bool
	 */
	public function moveDocument($folder, $filename, $destination = '') {
		if (empty($destination) === false && file_exists($destination) === false) {
			return false;
		}

		if ($this->exists($folder, $filename) === false) {
			return false;
		}

		$folder = $this->getFolders()->folder($folder);
		$mover = self::getFileMover();
		return $mover->copyFile($folder->directory, $filename, $destination);
	}


/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return columns
	 * @return WireData
	 */
	public function getColumnData() {
		$columns = new WireData();
		$columns->tag = Document::aliasproperty('tag');
		$columns->reference1 = Document::aliasproperty('reference1');
		$columns->reference2 = Document::aliasproperty('reference2');
		return $columns;
	}

	/**
	 * Return Doc Viewer
	 * @return Viewer
	 */
	public function getViewer() {
		return Viewer::getInstance();
	}

	/**
	 * Return Folders
	 * @return Folders
	 */
	public function getFolders() {
		return Folders::getInstance();
	}
}
