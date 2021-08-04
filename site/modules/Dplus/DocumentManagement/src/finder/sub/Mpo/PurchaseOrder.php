<?php namespace Dplus\DocManagement\Finders;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
use PurchaseOrderQuery, PurchaseOrder as PoModel;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;

/**
 * Purchase Order Document Finder
 * Decorator for DocumentQuery to find Purchase Order Related Documents in Database
 */
class PurchaseOrder extends Finder {
/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Purchase Order Number
	 * @param  string $ponbr                 Purchase Order Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocumentsPo($ponbr) {
		$ponbr = PoModel::get_paddedponumber($ponbr);
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_VENDORPO);
		$q->filterByReference1($ponbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Purchase Order
	 * @param  string $ponbr Purchase Order Number
	 * @return int           Number of Purchase Order Documents found
	 */
	public function countDocumentsPo($ponbr) {
		$ponbr = PoModel::get_paddedponumber($ponbr);
		$q = $this->docQuery();
		$q->filterByTag(self::TAG_VENDORPO);
		$q->filterByReference1($ponbr);
		return $q->count();
	}

	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for an AP Invoice #
	 * @param  string $invnbr Purchase Order Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocumentsInvoice($invnbr) {
		$q = $this->docQuery();
		$this->filterInvoice($q, $invnbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a AP Invoice Number
	 * @uses self::filterInvoice()
	 *
	 * @param  string $invnbr AP Invoice Number
	 * @return int            Number of Documents found associated with AP Invoice Number
	 */
	public function countDocumentsInvoice($invnbr) {
		$q = $this->docQuery();
		$this->filterInvoice($q, $invnbr);
		return $q->count();
	}

/* =============================================================
	Query Filtering (Decorations) Functions
============================================================= */
	/**
	 * Adds Filter Conditions to the Document Query
	 * to find Documents associated with an AP Invoice Number
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @return DocumentQuery
	 */
	public function filterInvoice(DocumentQuery $q, $invnbr) {
		$this->initColumns();
		$invnbr = PoModel::get_paddedponumber($invnbr);
		$validate = new MpoValidator();
		$conditions = array();

		if ($validate->invoice($invnbr) === false) {
			$this->addConditionPo($q, $invnbr);
		}

		if ($validate->invoice($invnbr)) {
			$conditions[] = $this->addConditionInvoiceReference1($q, $invnbr);
			$conditions[] = $this->addConditionInvoiceReference2($q, $invnbr);
			$conditions[] = $this->addConditionInvoicePo($q, $invnbr);
			$q->where($conditions, 'or');
		}


		return $q;
	}

	/**
	 * Add AP Invoice Condition to Document Query
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @return string
	 */
	private function addConditionInvoiceReference1(DocumentQuery $q, $invnbr) {
		$name = 'cond_invoices';
		$q->condition('tag_invoices', "Document.{$this->columns->tag} = ?", self::TAG_APINVOICE);
		$q->condition('reference1_invoices', "Document.{$this->columns->reference1} = ?", $invnbr);
		$q->combine(array('tag_invoices', 'reference1_invoices'), 'and', $name);
		return $name;
	}

	/**
	 * Add AP Invoice Condition to Document Query
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @return string
	 */
	private function addConditionInvoiceReference2(DocumentQuery $q, $invnbr) {
		$name = 'cond_invoices2';
		$q->condition('tag_invoices', "Document.{$this->columns->tag} = ?", self::TAG_APINVOICE);
		$q->condition('reference2_invoices', "Document.{$this->columns->reference2} = ?", $invnbr);
		$q->combine(array('tag_invoices', 'reference2_invoices'), 'and', $name);
		return $name;
	}

	/**
	 * Add Vendor PO condition from Invoice to Document Query
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @return string
	 */
	private function addConditionInvoicePo(DocumentQuery $q, $invnbr) {
		$apinvoice = $this->getInvoice($invnbr);
		return $this->addConditionPo($q, $apinvoice->ponbr);
	}

	/**
	 * Add Vendor PO Condition to Document Query
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string|array   $invnbr  AP Invoice Number
	 * @return string
	 */
	public function addConditionPo(DocumentQuery $q, $ponbr) {
		$name = 'cond_vendorpo';
		$list = $this->wire('sanitizer')->array($ponbr);
		$q->condition('tag_vendorpo', "Document.{$this->columns->tag} = ?", self::TAG_VENDORPO);
		$q->condition('reference1_vendorpo', "Document.{$this->columns->reference1} IN ?", $list);
		$q->combine(array('tag_vendorpo', 'reference1_vendorpo'), 'and', $name);
		return $name;
	}
}
