<?php namespace Dplus\DocManagement\Finders;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
use SalesOrderQuery, SalesOrder;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;

/**
 * Quote Document Finder
 *
 * Decorator for DocumentQuery to find Quote Related Documents in Database
 */
class Qt extends Finder {
/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Quote
	 * @param  string $qnbr                  Quote Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocuments($qnbr) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_QUOTE);
		$q->filterByReference1($this->qnbr($qnbr));
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Quote
	 * @param  string $qnbr Quote Number
	 * @return int          Number of Sales Order Documents found
	 */
	public function countDocuments($qnbr) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_QUOTE);
		$q->filterByReference1($this->qnbr($qnbr));
		return $q->count();
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	public function qnbr($qnbr) {
		return substr($qnbr, -4);
	}
}
