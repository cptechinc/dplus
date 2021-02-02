<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use Dplus\CodeValidators\Map as MapValidator;

use ItemMasterItemQuery, ItemMasterItem;
use InvAssortmentCodeQuery, InvAssortmentCode;
use UnitofMeasureSaleQuery, UnitofMeasureSale;

/**
 * In
 *
 * Class for Validating Inventory (IN) table codes, IDs
 */
class Min extends WireData {
	/**
	 * Return if Item ID is Valid
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemid($itemID) {
		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}

	/**
	 * Return if Item Type is Valid
	 * @param  string $type Item Type
	 * @return bool
	 */
	public function itemtype($type) {
		return in_array($type, array_keys(ItemMasterItem::ITEMTYPE_DESCRIPTIONS));
	}

	/**
	 * Return if Inventory Stock Code is valid
	 * @param  string $code Inventory Stock Code
	 * @return bool
	 */
	public function stockcode($code) {
		$stcm = $this->modules->get('CodeTablesStcm');
		return $stcm->code_exists($code);
	}

	/**
	 * Return if Inventory Stock Code is valid
	 * @param  string $code Inventory Stock Code
	 * @return bool
	 */
	public function itemgroup($code) {
		$igm = $this->modules->get('CodeTablesIgm');
		return $igm->code_exists($code);
	}

	/**
	 * Return if Inventory Price Code is valid
	 * @param  string $code Inventory Price Code
	 * @return bool
	 */
	public function pricecode($code) {
		$igpm = $this->modules->get('CodeTablesIgpm');
		return $igpm->code_exists($code);
	}

	/**
	 * Return if Inventory Commission Group Code is valid
	 * @param  string $code Inventory Commission Group Code
	 * @return bool
	 */
	public function commissiongroup($code) {
		$igpm = $this->modules->get('CodeTablesIgcm');
		return $igpm->code_exists($code);
	}

	/**
	 * Return if Inventory Special Item Code is valid
	 * @param  string $code Inventory Special Item Group Code
	 * @return bool
	 */
	public function specialitem($code) {
		$spit = $this->modules->get('CodeTablesSpit');
		return $spit->code_exists($code);
	}

	/**
	 * Return if Inventory Assortment Code is valid
	 * @param  string $code Inventory Special Assortment Code
	 * @return bool
	 */
	public function assortmentcode($code) {
		$iasm = $this->modules->get('CodeTablesIasm');
		return $iasm->code_exists($code);
	}

	/**
	 * Return if Unit of Measure Sale Code is valid
	 * @param  string $code Unit of Measure Sale Code
	 * @return bool
	 */
	public function unitofm_sale($code) {
		$umm = $this->modules->get('CodeTablesUmm');
		return $umm->code_exists($code);
	}

	/**
	 * Return if Unit of Measure Purchase Code is valid
	 * @param  string $code Unit of Measure Sale Code
	 * @return bool
	 */
	public function unitofm_purchase($code) {
		$umm = $this->modules->get('CodeTablesUmm');
		return $umm->code_exists($code);
	}

	/**
	 * Return if Inventory Tarriff Code is valid
	 * @param  string $code Inventory Tarriff Code
	 * @return bool
	 */
	public function tariffcode($code) {
		$stcm = $this->modules->get('CodeTablesTarm');
		return $stcm->code_exists($code);
	}

	/**
	 * Return if Country Code is valid
	 * @param  string $code Country Code
	 * @return bool
	 */
	public function countrycode($code) {
		$validate = new MapValidator();
		return $validate->countrycode($code);
	}

	/**
	 * Return if Msds Code is valid
	 * @param  string $code Msds Code
	 * @return bool
	 */
	public function msdscode($code) {
		$msdsm = $this->modules->get('CodeTablesMsdsm');
		return $msdsm->code_exists($code);
	}

	/**
	 * Validate Warehouse ID
	 * @param  string $id Warehouse ID
	 * @return bool
	 */
	public function whseid($id) {
		return $this->modules->get('CodeTablesIwhm')->code_exists($id);
	}

	/**
	 * Validate Warehouse ID
	 * @param  string $id Warehouse ID
	 * @return bool
	 */
	public function whsebin($whseID, $binID) {
		if ($this->whseid($whseID) === false) {
			return false;
		}

		$whse = $this->modules->get('CodeTablesIwhm')->get_code($whseID);

		if ($whse->validate_bin($binID) === false) {
			$config = $this->modules->get('ConfigureIn')->config();

			if ($binID != $config->default_bin) {
				return false;
			}
		}
		return true;
	}
}
