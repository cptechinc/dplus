<?php namespace Dplus\Docm;
// Dplus Model
use DocumentQuery, Document;
// ProcessWire
use ProcessWire\WireData;
// Dplus Docm
use Dplus\Docm\Config;


/**
 * Documents
 * Decorator for DocumentQuery to find Documents in Database
 */
class Documents extends WireData {
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
	Queries
============================================================= */
	/**
	 * Return Document Query
	 * @return DocumentQuery
	 */
	public function query() {
		return DocumentQuery::create();
	}

	/**
	 * Return Query filtered By Tag, Folder
	 * @param  string $tag
	 * @param  string $folder
	 * @return DocumentQuery
	 */
	public function queryTagFolder($tag, $folder) {
		$config = Config::instance();
		$folder = $config->folderUseLowerCase ? strtolower($folder) : $folder;

		$q = $this->query();
		$q->filterByTag($tag);
		$q->filterByFolder($folder);
		return $q;
	}

	/**
	 * Return Query filtered By Tag
	 * @param  string $tag
	 * @return DocumentQuery
	 */
	public function queryTag($tag) {
		$q = $this->query();
		$q->filterByTag($tag);
		return $q;
	}

/* =============================================================
	Reads
============================================================= */
	/**
	 * Return if File Exists in Database
	 * @param  string $folder   Folder Code
	 * @param  string $filename File Name
	 * @return bool
	 */
	public function exists($folder, $filename) {
		$config = Config::instance();
		$folder = $config->folderUseLowerCase? strtolower($folder) : $folder;

		$q = $this->query();
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
		$config = Config::instance();
		$folder = $config->folderUseLowerCase? strtolower($folder) : $folder;

		$q = $this->query();
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
		$config = Config::instance();
		$folder = $config->folderUseLowerCase? strtolower($folder) : $folder;

		if ($this->exists($folder, $filename) === false) {
			return '';
		}
		$dirPath = Folders::instance()->directory($folder);
		return "$dirPath/$filename";
	}
}