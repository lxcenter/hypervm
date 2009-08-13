<?php
function get_plural($word)
{


	if ($word[strlen($word) - 1] === 'e' || $word[strlen($word) - 1] === 'i' || $word[strlen($word) - 1] === 'ö' || $word[strlen($word) - 1] === 'ü') {
		$ret = "{$word}ler";
		return ucfirst($ret);
	} else if ($word[strlen($word) - 1] === 'a' || $word[strlen($word) - 1] === 'ý' || $word[strlen($word) - 1] === 'o' || $word[strlen($word) - 1] === 'u') {
		$ret = "{$word}lar";
		return ucfirst($ret);
	}

	$ret = "{$word}ler";
	return ucfirst($ret);
}


