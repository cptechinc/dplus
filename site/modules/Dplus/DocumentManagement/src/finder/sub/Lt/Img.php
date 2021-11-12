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
	const FOLDER = 'LOTIMG';

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $lotnbr Lot Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocuments($lotnbr) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_QUOTE);
		$q->filterByFolder(self::FOLDER);
		$q->filterByReference1($lotnbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Lot
	 * @param  string $lotnbr Lot Number
	 * @return int
	 */
	public function countDocuments($lotnbr) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_QUOTE);
		$q->filterByFolder(self::FOLDER);
		$q->filterByReference1($lotnbr);
		return $q->count();
	}
}
