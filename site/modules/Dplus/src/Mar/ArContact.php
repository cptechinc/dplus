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
	public function queryCustidShiptoidBuyer($custID, $shiptoID = '') {
		return $this->queryCustidShiptoid($custID, $shiptoID)->filterByBuyer(self::YN_TRUE);
	}

	/**
	 * Return Query Filtered By Cust ID, Ship-to ID, Primary Buyer
	 * @param  string $custID 
	 * @return Query
	 */
	public function queryCustidPrimaryBuyer($custID, $shiptoID = '') {
		return $this->queryCustidShiptoid($custID, $shiptoID)->filterByBuyer(self::PRIMARY_BUYER_CODE);
	}

/* =============================================================
	Read Functions
============================================================= */
	public function hasPrimaryBuyer($custID, $shiptoID = '') {
		return boolval($this->queryCustidPrimaryBuyer($custID, $shiptoID)->count());
	}

	public function primaryBuyer($custID, $shiptoID = '') {
		return $this->queryCustidPrimaryBuyer($custID, $shiptoID)->findOne();
	}

	public function buyer($custID, $shiptoID = '') {
		return $this->queryCustidShiptoidBuyer($custID, $shiptoID)->findOne();
	}
}