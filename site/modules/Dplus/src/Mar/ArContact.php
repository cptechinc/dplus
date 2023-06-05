<?php namespace Dplus\Mar;
// Dplus Models
use ArContactQuery as Query, ArContact as Record;
// Dplus
use Dplus\Abstracts\AbstractQueryWrapper;

class ArContact extends AbstractQueryWrapper {
	const MODEL              = 'ArContact';
	const MODEL_KEY          = 'custid,shiptoid,contactid';
	const MODEL_TABLE        = 'ar_cont_mast';
	const DESCRIPTION        = 'Customer Contact';
	const PRIMARY_BUYER_CODE = 'P';
	const YN_TRUE            = 'Y';

	protected static $instance;

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered By Cust ID
	 * @param  string $custID
	 * @return Query
	 */
	public function queryCustid($custID) {
		return $this->query()->filterByCustid($custID);
	}

	/**
	 * Return Query Filtered By Cust ID, Ship-to ID
	 * @param  string $custID 
	 * @param  string $shiptoID 
	 * @return Query
	 */
	public function queryCustidShiptoid($custID, $shiptoID) {
		return $this->queryCustid($custID)->filterByShiptoid($shiptoID);
	}

	/**
	 * Return Query Filtered By Cust ID, Ship-to ID, Primary Buyer
	 * @param  string $custID 
	 * @return Query
	 */
	public function queryCustidBuyer($custID) {
		return $this->queryCustid($custID)->filterByBuyer(self::YN_TRUE);
	}

	/**
	 * Return Query Filtered By Cust ID, Ship-to ID, Primary Buyer
	 * @param  string $custID 
	 * @return Query
	 */
	public function queryCustidPrimaryBuyer($custID) {
		return $this->queryCustid($custID)->filterByBuyer(self::PRIMARY_BUYER_CODE);
	}

/* =============================================================
	Read Functions
============================================================= */
	public function hasPrimaryBuyer($custID) {
		return boolval($this->queryCustidPrimaryBuyer($custID)->count());
	}

	public function primaryBuyer($custID) {
		return $this->queryCustidPrimaryBuyer($custID)->findOne();
	}

	public function buyer($custID) {
		return $this->queryCustidBuyer($custID)->findOne();
	}
}