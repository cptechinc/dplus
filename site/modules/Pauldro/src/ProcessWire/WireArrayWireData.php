<?php namespace Pauldro\ProcessWire;
// ProcessWire
use ProcessWire\WireArray;
use ProcessWire\WireData;

/**
 * Container for WireData Lists
 */
class WireArrayWireData extends WireArray {
	/**
	 * Return Array
	 * @return array
	 */
	public function toArray() {
		$array = [];

		foreach ($this->getAll() as $key => $data) {
			$array[$key] = $data->getArray();
		}
		return $array;
	}

	/**
	 * Get a new/blank item of the type that this WireArray holds
	 * 
	 * #pw-internal
	 *
	 * @throws WireException If class doesn't implement this method. 
	 * @return Wire|null
	 *
	 */
	public function makeBlankItem() {
		return new WireData();
	}

	/**
	 * Return new Instance from array
	 *
	 * @param  array[WireData] $array 
	 * @return WireArrayWireData
	 */
	public static function fromArray($array) {
		$wireArray = new self();

		foreach ($array as $key => $arrayData) {
			$data = new WireData();
			$data->setArray($arrayData);
			$wireArray->set($key, $data);
		}

		return $wireArray;
	}
}