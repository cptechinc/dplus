<?php namespace Dplus\CodeValidators;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use PrinterQuery, Printer as Model;
// ProcessWire
use ProcessWire\WireData;

/**
 * Printer
 * Class for Validating Printer
 */
class Printer extends WireData {
	/**
	 * Return if Printer ID exists
	 * @param  string $id Printer ID
	 * @return bool
	 */
	public function id($id) {
		return boolval(PrinterQuery::create()->filterById($id)->count());
	}

	/**
	 * Return if Printer Exists
	 * @param  string $id Printer ID / [Printer ID] + [PITCH]
	 * @return bool
	 */
	public function printer($id) {
		if ($this->id($id)) {
			return true;
		}

		foreach (Model::PITCHES as $pitch) {
			$regex = "/\w($pitch)/";

			if (preg_match($regex, $id)) {
				$printerID = str_replace($pitch, '', $id);

				if ($this->id($printerID) === false) {
					return false;
				}
				$alias = "pitch$pitch";
				$col = Model::aliasproperty($alias);
				$filter = "filterBy" . ucFirst($col);
				$q = PrinterQuery::create();
				$q->filterById($printerID);
				$q->$filter(Model::PITCH_TRUE);
				return boolval($q->count());
			}
		}
		return false;
	}
}
