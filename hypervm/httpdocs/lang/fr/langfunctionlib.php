<?php 
function get_plural($word)
{
	return $word ;
}

// This is an alternate get_plural, which has the all the plurals are defined in a file.
function get_plural_alternate($word)
{
	include_once "lang/fr/lang_plural.inc";

	if (isset($__plural_desc[$word])) {
		return $__plural_desc[$word];
	}

	//return "{$word}s";
	return "{$word}";
}


