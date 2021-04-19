<?php namespace Dplus\DocManagement\Finders;
// Purl
use Purl\Url;
// Propel
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use DocumentFolderQuery, DocumentFolder;
use DocumentQuery, Document;
use SalesOrderQuery, SalesOrder as SoModel;
use SalesOrderDetailQuery, SalesOrderDetail;
use SalesHistoryQuery, SalesHistory;
use SalesHistoryDetailQuery, SalesHistoryDetail;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Alias Document Finders Namespace
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Sales Order Document Finder
 *
 * Decorator for DocumentQuery to find Sales Order Related Documents in Database
 */
class SalesOrder extends Finder {
/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents objects
	 * filtered by the tag1, reference1 fields for a Sales Order
	 * @param  string $ordn                      Sales Order Number
	 * @return Documents[]|ObjectCollection
	 */
	public function getDocuments($ordn) {
		$q = $this->docQuery();
		$this->filterSales($q, $ordn);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Sales Order
	 * @param  string $ordn Sales Order Number
	 * @return int          Number of Sales Order Documents found
	 */
	public function countDocuments($ordn) {
		$q = $this->docQuery();
		$this->filterSales($q, $ordn);
		return $q->count();
	}

/* =============================================================
	Query Filtering (Decorations) Functions
============================================================= */
	/**
	 * Adds Filter Conditions to the Documents Query
	 * to find Documents associated with a Sales Order
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @return DocumentQuery
	 */
	public function filterSales(DocumentQuery $q, $ordn) {
		$this->initColumns();
		$ordn = SoModel::get_paddedordernumber($ordn);

		$conditions = [
			// Filter Sales Orders
			$this->filterSalesOrders($q, $ordn),
			// Filter Invoices
			$this->filterSalesInvoices($q, $ordn)
		];


		if ($this->doesOrderHavePos($ordn)) {
			$cond = $this->filterSalesVendorpo($q, $ordn);

			if ($cond) {
				$conditions[] = $cond;
			}
		}
		$q->where($conditions, 'or');
		return $q;
	}

	/**
	 * Filter the Query for Sales Order Documents
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @return string
	 */
	private function filterSalesOrders(DocumentQuery $q, $ordn) {
		$name = 'cond_so';
		$q->condition('tag_so', "Document.{$this->columns->tag} = ?", self::TAG_SALESORDER);
		$q->condition('reference1_so', "Document.{$this->columns->reference1} = ?", $ordn);
		$q->combine(array('tag_so', 'reference1_so'), 'and', $name) ;
		return $name;
	}

	/**
	 * Filter the Query for Invoice Documents
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @return string
	 */
	private function filterSalesInvoices(DocumentQuery $q, $ordn) {
		$name = 'cond_invoices';
		$q->condition('tag_invoices', "Document.{$this->columns->tag} = ?", self::TAG_ARINVOICE);
		$q->condition('reference1_invoices', "Document.{$this->columns->reference1} = ?", $ordn);
		$q->combine(array('tag_invoices', 'reference1_invoices'), 'and', $name);
		return $name;
	}

	/**
	 * Filter the Query for Vendor Purchase Order Documents
	 * @param  DocumentQuery $q     Query
	 * @param  string        $ordn  Sales Order #
	 * @return mixed
	 */
	private function filterSalesVendorpo(DocumentQuery $q, $ordn) {
		$name = 'cond_vendorpo';
		$validate = new MsoValidator();

		if ($validate->invoice($ordn) === false && $validate->order($ordn) === false) {
			return false;
		}

		if ($this->doesOrderHavePos($ordn) === false) {
			return false;
		}
		$q_detail = $this->getSoDetailVendorPoQuery($ordn);
		$ponbrs = $q_detail->find()->toArray();
		$finderPo = new DocFinders\PurchaseOrder();
		$finderPo->initColumns();
		return $finderPo->addConditionPo($q, $ponbrs);
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Returns if Sales Order has Purchase Orders related to it
	 * @param  string $ordn Sales Order #
	 * @return bool
	 */
	private function doesOrderHavePos($ordn) {
		$q = $this->getSoDetailVendorPoQuery($ordn);
		return boolval($q->count());
	}


	/**
	 * Return Detail Query
	 * @param  string $ordn Sales Order #
	 * @return SalesOrderDetailQuery|SalesHistoryDetailQuery
	 */
	private function getSoDetailVendorPoQuery($ordn) {
		$validate = new MsoValidator();
		$q = SalesOrderDetailQuery::create();
		$q->select(SalesOrderDetail::aliasproperty('vendorpo'));

		if ($validate->invoice($ordn)) {
			$q = SalesHistoryDetailQuery::create();
			$q->select(SalesHistoryDetail::aliasproperty('vendorpo'));
		}
		$q->filterByOrdernumber($ordn);
		return $q;
	}
}
