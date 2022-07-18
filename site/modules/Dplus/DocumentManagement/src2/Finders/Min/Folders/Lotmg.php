<?php namespace Dplus\Docm\Finders\Min\Folders;
// Dplus Model
use Document;
// Dplus Document Manangement
use Dplus\Docm\Finders\Finder\FolderRef1;


/**
 * Finders\Min\Folders\Itmimg
 * Decorator for DocumentQuery to find Documents in Database related to LOTIMG Documents
 */
class Itmimg extends FolderRef1 {
	const TAG    = 'LT';
	const FOLDER = 'LOTIMG';

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * @param  string $lotserial Lot / Serial #
	 * @return ObjectCollection|Document[]
	 */
	public function find($lotserial) {
		$q = $this->query();
		$q->filterByReference1($lotserial);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * @param  string $lotserial Lot / Serial #
	 * @return int
	 */
	public function count($lotserial) {
		$q = $this->query();
		$q->filterByReference1($lotserial);
		return $q->count();
	}

/* =============================================================
	Image Functions
============================================================= */
	/**
	 * Return if Image Exists for Lot / Serial #
	 * @param  string $lotserial Lot / Serial #
	 * @return bool
	 */
	public function hasImage($lotserial) {
		return boolval($this->count($lotserial));
	}

	/**
	 * Return Filename
	 * @param  string $lotserial Lot / Serial #
	 * @return string
	 */
	public function getImageFilename($lotserial) {
		$q = $this->query();
		$q->select(Document::aliasproperty('filename'));
		$q->filterByReference1($lotserial);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}

	/**
	 * Return Document
	 * @param  string $lotserial Lot / Serial #
	 * @return Document
	 */
	public function getImage($lotserial) {
		$q = $this->query();
		$q->filterByReference1($lotserial);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}
}
