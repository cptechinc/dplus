<?php namespace Controllers\Ajax\Json;
// Dplus Lookups
use Dplus\Lookup\Item\Lookups as Lookups;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class ItemLookup extends AbstractController {
	static public function test() {
		return 'test';
	}

	static public function lookup($data) {
		$fields = ['entry|text', 'ponbr|ponbr'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->entry == 'po' || $data->ponbr) {
			return self::lookupPo($data);
		}
		return self::lookupAr($data);
	}

	static public function lookupPo($data) {
		$lookup = new Lookups\ApEntry();
		$lookup->setInputDataFromWireInput(self::pw('input'));
		$lookup->initInputData();
		$lookup->find();
		return $lookup->getResultsData();
	}

	static public function lookupAr($data) {
		$lookup = new Lookups\ArEntry();
		$lookup->setInputDataFromWireInput(self::pw('input'));
		$lookup->initInputData();
		$lookup->find();
		return $lookup->getResultsData();
	}
}
