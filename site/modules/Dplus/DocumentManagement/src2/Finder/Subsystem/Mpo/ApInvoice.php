<?php namespace Dplus\Docm\Finder\Subsystem\Mpo;
// Dplus Model
use PurchaseOrder as PoModel;
use DocumentQuery;
use ApInvoiceQuery, ApInvoice as ApInvoiceModel;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Dplus Docm
use Dplus\Docm\Finder\TagRef1;

/**
 * Finder\Subsystem\Mpo\ApInvoice
 * Decorator for DocumentQuery to find Documents in Database related to AP Invoce Documents
 * 
 * @method  Tag  find($ponbr)   Return Documents related to AP invoice number
 * @method  Tag  count($ponbr)  Return the number Documents related to  AP invoice number
 */
class ApInvoice extends TagRef1 {
	const TAG = ['AP'];

	protected static $instance;

	/** @return self */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents replated to AP Invoice Number
	 * @param  string $invnbr AP Invoice Number
	 * @return Documents[]|ObjectCollection
	 */
	public function find($invnbr) {
		$q = $this->query();
		$this->filterApInvoice($q, $invnbr);
		return $q->find();
	}

	/**
	 * Return the number of Documents related to AP Invoice Number
	 * @uses self::filterInvoice()
	 *
	 * @param  string $invnbr AP Invoice Number
	 * @return int 
	 */
	public function count($invnbr) {
		$q = $this->query();
		$this->filterApInvoice($q, $invnbr);
		return $q->count();
	}

/* =============================================================
	Query Decorator Functions
============================================================= */
	/**
	 * Add Filter Conditions to the DocumentQuery
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
			$conditions[] = $this->addConditionInvoiceRef1($q, $invnbr);
			$conditions[] = $this->addConditionInvoiceRef2($q, $invnbr);
			$conditions[] = $this->addConditionInvoicePonbr($q, $invnbr);
			$q->where($conditions, 'or');
		}
		return $q;
	}

	/**
	 * Add Query Condition for APInvoice for Ref1
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @param  string         $name    Condition Name
	 * @return string
	 */
	private function addConditionInvoiceRef1(DocumentQuery $q, $invnbr, $name = 'cond_invoices2') {
		$q->condition('tag_invoices', "Document.{$this->columns->tag} = ?", self::TAG[0]);
		$q->condition('reference1_invoices', "Document.{$this->columns->reference1} = ?", $invnbr);
		$q->combine(array('tag_invoices', 'reference1_invoices'), 'and', $name);
		return $name;
	}

	/**
	 * Add Query Condition for APInvoice for Ref2
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @param  string         $name    Condition Name
	 * @return string
	 */
	private function addConditionInvoiceRef2(DocumentQuery $q, $invnbr, $name = 'cond_invoices2') {
		$q->condition('tag_invoices', "Document.{$this->columns->tag} = ?", self::TAG[0]);
		$q->condition('reference2_invoices', "Document.{$this->columns->reference2} = ?", $invnbr);
		$q->combine(array('tag_invoices', 'reference2_invoices'), 'and', $name);
		return $name;
	}

	/**
	 * Add Query Condition for APInvoice's PO Nbr
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string         $invnbr  AP Invoice Number
	 * @return string
	 */
	private function addConditionInvoicePonbr(DocumentQuery $q, $invnbr) {
		$apinvoice = $this->getApInvoice($invnbr);
		$docmPo = PurchaseOrder::instance();
		return $docmPo->addConditionPonbr($q, $apinvoice->ponbr);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return APInvoice
	 * @param  string    $invnbr
	 * @return ApInvoiceModel
	 */
	private function getApInvoice($invnbr) {
		return ApInvoiceQuery::create()->findOneByInvnbr($invnbr);
	}
}
