<?php namespace Dplus\Docm\Finder\Folders;
// Dplus Model
use Document;
// Dplus Document Manangement
use Dplus\Docm\Finder\FolderRef1;


/**
 * Finder\Folders\Itmimg
 * Decorator for DocumentQuery to find Documents in Database related to ITMIMG Documents
 */
class Itmimg extends FolderRef1 {
	const TAG    = 'IT';
	const FOLDER = 'ITMIMG';

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * @param  string $itemID Item ID
	 * @return ObjectCollection|Document[]
	 */
	public function find($itemID) {
		$q = $this->query();
		$q->filterByReference1($itemID);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function count($itemID) {
		$q = $this->query();
		$q->filterByReference1($itemID);
		return $q->count();
	}

/* =============================================================
	Image Functions
============================================================= */
	/**
	 * Return if Image Exists for Lot / Serial #
	 * @param  string $itemID Lot / Serial #
	 * @return bool
	 */
	public function hasImage($itemID) {
		return boolval($this->count($itemID));
	}

	/**
	 * Return Filename
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public function getImageFilename($itemID) {
		$q = $this->query();
		$q->select(Document::aliasproperty('filename'));
		$q->filterByReference1($itemID);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}

	/**
	 * Return Document
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $itemID Item ID
	 * @return Document
	 */
	public function getImage($itemID) {
		$q = $this->query();
		$q->filterByReference1($itemID);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}
}
