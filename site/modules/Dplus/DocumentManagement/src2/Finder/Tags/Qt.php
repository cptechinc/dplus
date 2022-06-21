<?php namespace Dplus\Docm\Finder\Tags;

use Dplus\Docm\Finder\TagRef1;

/**
 * Finder\Tags\Qt
 * Decorator for DocumentQuery to find Documents in Database related to QT Documents
 * 
 * @method  Tag  find($qnbr)   Return Documents related to quote number
 * @method  Tag  count($qnbr)  Return the number Documents related to quote number
 */
class Tag extends TagRef1 {
	const TAG = ['QT'];
}