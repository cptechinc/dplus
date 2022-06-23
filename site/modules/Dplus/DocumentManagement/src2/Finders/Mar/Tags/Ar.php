<?php namespace Dplus\Docm\Finders\Mar\Tags;

use Dplus\Docm\Finder\TagRef1;

/**
 * Ar
 * Decorator for DocumentQuery to find Documents in Database related to AR Documents
 * 
 * @method  Tag  find($invnbr   Return Documents related to AR Invoice #
 * @method  Tag  count($invnbr  Return the number Documents related to AR Invoice #
 */
class Ar extends TagRef1 {
	const TAG = ['AR'];
}