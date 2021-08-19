<?php namespace Dplus\DocManagement\Finders;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Management Finders
use Dplus\DocManagement\Finders;

/**
 * Customer Document Finder
 * Decorator for DocumentQuery to find AR Related Documents in Database
 */
class Cu extends Finder {
/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents objects filtered by the tag1, reference1 fields for an AR Invoice
	 * @param  string $custID  Cust ID
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocuments($custID) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_CUSTOMER);
		$q->filterByReference1($custID);
		return $q->find();
	}

	/**
	 * Return the number of Documents found filtered by the tag1, reference1 fields for an AR Invoice
	 * @param  string $custID  Cust ID
	 * @return int
	 */
	public function countDocuments($custID) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_CUSTOMER);
		$q->filterByReference1($custID);
		return $q->count();
	}
}
