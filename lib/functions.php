<?php
// return a string of random text of a desired length
function random_text($count, $rm_similar = false)
{
	// create list of characters
	$chars = array_flip(array_merge(range(0, 9), range('A', 'Z')));

	// remove similar looking characters that cause confusion
	if ($rm_similar)
	{
		unset($chars[0], $chars[1], $chars[2], $chars[5], $chars[8],
			$chars['B'], $chars['I'], $chars['O'], $chars['Q'],
			$chars['S'], $chars['Z'], $chars['U'], $chars['V']);
	}

	//generate the string of random text
	for ($i = 0, $text = ''; $i < $count; $i++) {
		$text .= array_rand($chars);
	}

	return $text;
}

// convert a list of items (separated by newlines by default) into an array
// omitting blank lines and optionally duplicates
function explode_items($text, $separator = "\n", $preserve = true) {
    $items = array();
    foreach (explode($separator, $text) as $value) {
	$tmp = trim($value);
	if ($preserve) {
	    $items[] = $tmp;
	}else {
	    if(!empty($tmp)) {
		$items[$tmp] = true;
	    }
	}
    }
    if ($preserve) {
	return $items;
    }else {
	return array_keys($items);
    }
}
?>
