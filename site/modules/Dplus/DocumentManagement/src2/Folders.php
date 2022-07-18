<?php namespace Dplus\Docm;
// Dplus Model
use DocumentFolderQuery as Query, DocumentFolder;
// ProcessWire
use ProcessWire\WireData;
// Dplus Docm
use Dplus\Docm\Config;


/**
 * Finder
 * Decorator for DocumentQuery to find Documents in Database
 */
class Folders extends WireData {
	private static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}


/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Folder Query
	 * @return Query
	 */
	public function query() {
		return Query::create();
	}

	/**
	 * Return Query Filtered By Folder Code
	 * @param  string$folder
	 * @return Query
	 */
	public function queryFolder($folder) {
		$config = Config::instance();
		$folder = $config->folderUseLowerCase ? strtolower($folder) : $folder;

		$q = $this->query();
		$q->filterByFolder($folder);
		return $q;
	}

/* =============================================================
	Reads
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