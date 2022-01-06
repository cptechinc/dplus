<?php namespace Dplus\Urls\ItmImages\Sources;

/**
 * Client
 * Interface for Getting Urls from External Source
 */
interface Client {
	/**
	 * Return URL to Item Image
	 * @param  string $id Item Identifier
	 * @return string
	 */
	public function imageUrl($id);
}
