<?php namespace Dplus\DocManagement;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// ProcessWire
use ProcessWire\WireData;
// Dplus Models
use DocumentFolderQuery, DocumentFolder;

/**
 * Document Folders
 * Decorator for DocumentFolderQuery to find Folders in Database
 */
class Folders extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Folder Query
	 * @return DocumentFolderQuery
	 */
	public function query() {
		return DocumentFolderQuery::create();
	}

	public function queryFolder($folder) {
		$q = $this->query();
		$q->filterByFolder($folder);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Folder Exists
	 * @param  string $folder  Folder Code
	 * @return bool
	 */
	public function exists($folder) {
		$q = $this->queryFolder($folder);
		return boolval($q->count());
	}

	/**
	 * Return Folder
	 * @param  string $folder  Folder Code
	 * @return DocumentFolder
	 */
	public function folder($folder) {
		$q = $this->queryFolder($folder);
		return $q->findOne();
	}

	/**
	 * Return Folder Description
	 * @param  string $folder  Folder Code
	 * @return string
	 */
	public function description($folder) {
		$q = $this->queryFolder($folder);
		$q->select(DocumentFolder::aliasproperty('description'));
		return $q->findOne();
	}

	/**
	 * Return Folder Directory
	 * @param  string $folder  Folder Code
	 * @return string
	 */
	public function directory($folder) {
		$q = $this->queryFolder($folder);
		$q->select(DocumentFolder::aliasproperty('directory'));
		return $q->findOne();
	}

}
