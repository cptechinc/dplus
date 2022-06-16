<?php namespace Dplus\Docm\Finder\Tags;

/**
 * Finder\Qt
 * Decorator for DocumentQuery to find Documents in Database related to QT Documents
 * 
 * @method  Tag  find($qnbr)   Return Documents related to quote number
 * @method  Tag  count($qnbr)  Return the number Documents related to quote number
 */
class Tag extends TagRef1 {
	const TAG = ['QT'];
}