<?php namespace Dplus\DocManagement\Finders;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
use SalesOrderQuery, SalesOrder as SoModel;
use PurchaseOrderQuery, PurchaseOrder as PoModel;
// ProcessWire
use ProcessWire\WireData;
// Alias Doc Finders
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Item Information Document Finder
 *
 * Decorator for DocumentQuery to find II Related Documents in Database
 */
class Ii extends Finder {
	const PREFIX_REF_SO = 'so no.';
	const ACTIVITY_TYPES_SO = array(
		'sale',
		'ds sale',
		'k use',
		'rga'
	);

	const PREFIX_REF_PO = 'po no.';
	const ACTIVITY_TYPES_PO = array(
		'receipt',
		'ds rcpt',
		'fabrcpt',
		'fab use',
		'fabship'
	);

	const ACTIVITY_TYPES_WIP = array(
		'fin item',
		'prd fin',
		'fabrcpt',
		'prd use'
	);

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for an Item ID
	 * @param  string $itemID                      Item ID
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocuments($itemID) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_ITEM);
		$q->filterByReference1($itemID);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for an Item ID
	 * @param  string $itemID                      Item ID
	 * @return int
	 */
	public function countDocuments($itemID) {
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_ITEM);
		$q->filterByReference1($itemID);
		return $q->count();
	}

	/**
	 * Returns Documents Query
	 * filtered for Item Images
	 * @param  string $itemID Item ID
	 * @return DocumentQuery
	 */
	public function getQueryImage($itemID) {
		$wildcards = array();
		$like = array();

		foreach (self::EXTENSIONS_IMAGES as $ext) {
			$like[] = 'Document.Docifilename LIKE ?';
			$wildcards[] = "%.$ext";

		}
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_ITEM);
		$q->filterByReference1($itemID);
		$q->where(implode(' OR ', $like), $wildcards);
		return $q;
	}

	/**
	 * Return if there is an image associated with an Item
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function hasImages($itemID) {
		$q = $this->getQueryImage($itemID);
		return $q->count();
	}

	/**
	 * Return Item Image Name
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function getImageName($itemID) {
		$q = $this->getQueryImage($itemID);
		$q->select('Docifilename');
		return $q->findOne();
	}

	/**
	 * Returns if Item Image Exists in the directory or if tehre's one listed
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function imageExists($itemID) {
		if ($this->hasImages($itemID)) {
			$img = $this->getImageName($itemID);
			$file = $this->wire('config')->directory_images.$img;
			return file_exists($file);
		}
		return false;
	}

	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for an Item Activity
	 * @param  string $type                  Activity Type
	 * @param  string $reference             Activity Reference (e.g. Po No. 1072)
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocumentsActivity($type, $reference) {
		$q = $this->docQuery();
		$this->filterActivity($q, $type, $reference);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for an Item Activity
	 * @param  string $type                  Activity Type (e.g. receipt)
	 * @param  string $reference             Activity Reference (e.g. Po No. 1072)
	 * @return string
	 */
	public function countDocumentsActivity($type, $reference) {
		$q = $this->docQuery();
		$this->filterActivity($q, $type, $reference);
		return $q->count();
	}

/* =============================================================
	Query Filtering (Decorations) Functions
============================================================= */
	/**
	 * Add Filter Conditions for Item Activity
	 * @param  DocumentQuery $q Query to apply filters to
	 * @param  string         $type             Activity Type (e.g. receipt)
	 * @param  string         $reference        Activity Reference (e.g. Po No. 1072)
	 * @return void
	 */
	protected function filterActivity(DocumentQuery $q, $type, $reference) {
		$type = strtolower($type);

		if (in_array($type, self::ACTIVITY_TYPES_SO)) {
			$ref = $this->determine_reference($reference, self::PREFIX_REF_SO);
			$ref = SoModel::get_paddedordernumber($ref);
			$finderSo = new DocFinders\SalesOrder();
			$finderSo->filterSales($q, $ref);
		} elseif (in_array($type, self::ACTIVITY_TYPES_PO)) {
			$ref = $this->determine_reference($reference, self::PREFIX_REF_PO);
			$ref = PoModel::get_paddedponumber($ref);
			$finderPo = new DocFinders\PurchaseOrder();
			$finderPo->filterInvoice($q, $ref);
		} elseif (in_array($type, self::ACTIVITY_TYPES_WIP)) {
			$q->filterByTag(self::TAG_WIP);
			$q->filterByReference1($reference);
		} else {
			$q->filterByTag($type);
		}
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Returns Reference with Prefix Removed
	 * @param  string $reference Activity Reference (e.g. Po No. 1072)
	 * @param  string $strip     Prefix to Remove (e.g. Po No.)
	 * @return string
	 */
	protected function determine_reference($reference, $strip) {
		$ref = str_replace($strip, '', strtolower($reference));
		return trim($ref);
	}
}
