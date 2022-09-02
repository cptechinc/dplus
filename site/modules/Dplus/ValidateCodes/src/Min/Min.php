<?php namespace Dplus\CodeValidators;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use ItemMasterItemQuery, ItemMasterItem;
use InvAssortmentCodeQuery, InvAssortmentCode;
use UnitofMeasureSaleQuery, UnitofMeasureSale;
// ProcessWire
use ProcessWire\WireData;
// Dplus CRUD
use Dplus\Min\Inmain;
use Dplus\Min\Inproc;
// Dplus Configs
use Dplus\Configs;
// Dplus Code Validators
use Dplus\CodeValidators\Map as MapValidator;
use Dplus\Codes;

/**
 * Min
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
		return array_key_exists($type, ItemMasterItem::ITEMTYPE_DESCRIPTIONS);
	}

	/**
	 * Return if Item Is Lotted
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemIsLotted($itemID) {
		return $this->itemIsType($itemID, ItemMasterItem::ITEMTYPE_LOTTED);
	}

	/**
	 * Return if Item Is Serialized
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemIsSerialized($itemID) {
		return $this->itemIsType($itemID, ItemMasterItem::ITEMTYPE_SERIALIZED);
	}

	/**
	 * Return if Item is Lotted or Serialized
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemIsLotSerialized($itemID) {
		return $this->itemIsLotted($itemID) || $this->itemIsSerialized($itemID);
	}

	/**
	 * Return if Item Is Normal
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemIsNormal($itemID) {
		return $this->itemIsType($itemID, ItemMasterItem::ITEMTYPE_NORMAL);
	}

	/**
	 * Return if Item ID is of a Type
	 * @param  string $itemID Item ID
	 * @param  string $type   Item Type (L: lotted | S: serialized | N: normal)
	 * @return bool
	 */
	private function itemIsType($itemID, $type) {
		if ($this->itemtype($type) === false) {
			return false;
		}
		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemID);
		$q->filterByItemtype($type);
		return boolval($q->count());
	}

	/**
	 * Return if Inventory Stock Code is valid
	 * @param  string $code Inventory Stock Code
	 * @return bool
	 */
	public function stockcode($code) {
		return Codes\Min\Stcm::instance()->exists($code);
	}

	/**
	 * Return if Inventory Stock Code is valid
	 * @param  string $code Inventory Stock Code
	 * @return bool
	 */
	public function itemgroup($code) {
		return Codes\Min\Igm::instance()->exists($code);
	}

	/**
	 * Return if Inventory Price Code is valid
	 * @param  string $code Inventory Price Code
	 * @return bool
	 */
	public function pricecode($code) {
		return Codes\Min\Igpm::instance()->exists($code);
	}

	/**
	 * Return if Inventory Commission Group Code is valid
	 * @param  string $code Inventory Commission Group Code
	 * @return bool
	 */
	public function commissiongroup($code) {
		return Codes\Min\Igcm::instance()->exists($code);
	}

	/**
	 * Return if Inventory Special Item Code is valid
	 * @param  string $code Inventory Special Item Group Code
	 * @return bool
	 */
	public function specialitem($code) {
		return Codes\Min\Spit::instance()->exists($code);
	}

	/**
	 * Return if Inventory Assortment Code is valid
	 * @param  string $code Inventory Special Assortment Code
	 * @return bool
	 */
	public function assortmentcode($code) {
		return Codes\Min\Iasm::instance()->exists($code);
	}

	/**
	 * Return if Unit of Measure Sale Code is valid
	 * @param  string $code Unit of Measure Sale Code
	 * @return bool
	 */
	public function unitofm_sale($code) {
		return Codes\Min\Umm::instance()->exists($code);
	}

	/**
	 * Return if Unit of Measure Purchase Code is valid
	 * @param  string $code Unit of Measure Sale Code
	 * @return bool
	 */
	public function unitofm_purchase($code) {
		return Codes\Min\Umm::instance()->exists($code);
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
		return Codes\Mar\Cocom::instance()->exists($code);
	}

	/**
	 * Return if Msds Code is valid
	 * @param  string $code Msds Code
	 * @return bool
	 */
	public function msdscode($code) {
		return Codes\Min\Msdsm::instance()->exists($code);
	}

	/**
	 * Validate Warehouse ID
	 * @param  string $id Warehouse ID
	 * @return bool
	 */
	public function whseid($id) {
		return Codes\Min\Iwhm::instance()->exists($id);
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

		$whse = Codes\Min\Iwhm::instance()->whse($whseID);

		if ($whse->validate_bin($binID) === false) {
			if ($whse->are_binsranged() && $binID == '') {
				return true;
			}

			$config = Configs\In::config();
			if ($binID != $config->default_bin) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Return if Item 2 Item Record Exists
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return bool
	 */
	public function i2i($parentID, $childID) {
		$i2i = Inmain\I2i\I2i::getInstance();
		return $i2i->exists($parentID, $childID);
	}

	/**
	 * Return if Iarn Code Exists
	 * @param  string $id  Inv Adjustment Code
	 * @return bool
	 */
	public function iarn($id) {
		return Codes\Min\Iarn::instance()->exists($id);
	}

	/**
	 * Return if ITM Warehouse Exists
	 * @param  string $itemID Item ID
	 * @param  string $whseID Warehouse ID
	 * @return bool
	 */
	public function itmWhse($itemID, $whseID) {
		$itmw = $this->wire('modules')->get('ItmWarehouse');
		return $itmw->exists($itemID, $whseID);
	}

	/**
	 * Return if Add-on Item Record Exists
	 * @param  string $itemID   Item ID
	 * @param  string $addonID  Add-on Item ID
	 * @return bool
	 */
	public function addm($itemID, $addonID) {
		$addm = Inmain\Addm\Addm::getInstance();
		return $addm->exists($itemID, $addonID);
	}
}
