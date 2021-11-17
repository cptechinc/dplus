<?php namespace Dplus\DocManagement\Finders\Lt;
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
 * Lot Image Document Filter
 * Decorator for DocumentQuery to find Lot Image Documents in Database
 */
class Img extends Finder {
	const TAG    = 'LT';
	const FOLDER = 'LOTIMG';

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $lotserial Lot Number
	 * @return Document[]|ObjectCollection
	 */
	public function getDocuments($lotserial) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG);
		$q->filterByFolder(self::FOLDER);
		$q->filterByReference1($lotserial);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $lotserial Lot Number
	 * @return int
	 */
	public function countDocuments($lotserial) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG);
		$q->filterByFolder(self::FOLDER);
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
		return boolval($this->countDocuments($lotserial));
	}

	/**
	 * Return Filename
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $lotserial Lot Number
	 * @return string
	 */
	public function getImageFilename($lotserial) {
		$q = $this->docQuery();
		$q->select(Document::aliasproperty('filename'));
		$q->filterByTag(self::TAG);
		$q->filterByFolder(self::FOLDER);
		$q->filterByReference1($lotserial);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}

	/**
	 * Return Document
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $lotserial Lot Number
	 * @return Document
	 */
	public function getImage($lotserial) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG);
		$q->filterByFolder(self::FOLDER);
		$q->filterByReference1($lotserial);
		$q->orderBy(Document::aliasproperty('date'), 'DESC');
		$q->orderBy(Document::aliasproperty('time'), 'DESC');
		return $q->findOne();
	}
}
