<?php namespace Dplus\DocManagement\Finders\It;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Manangement
use Dplus\DocManagement\Finders\Finder;


/**
 * ITM Image Document Filter
 * Decorator for DocumentQuery to find ITM Image Documents in Database
 */
class Img extends Finder {
	const TAG    = 'IT';
	const FOLDER = 'ITMIMG';

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $itemID Item ID
	 * @return Document[]|ObjectCollection
	 */
	public function getDocuments($itemID) {
		$q = $this->queryTagFolder(self::TAG, self::FOLDER);
		$q->filterByReference1($itemID);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function countDocuments($itemID) {
		$q = $this->queryTagFolder(self::TAG, self::FOLDER);
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
		return boolval($this->countDocuments($itemID));
	}

	/**
	 * Return Filename
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public function getImageFilename($itemID) {
		$q = $this->queryTagFolder(self::TAG, self::FOLDER);
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
		$q = $this->queryTagFolder(self::TAG, self::FOLDER);
		$q->filterByReference1($itemID);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}
}
