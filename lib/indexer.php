#! C:\PHP
<?php
// include shared code
require_once 'common.php';
require_once 'db.php';

// clear index tables
$query = sprintf('TRUNCATE TABLE %sSEARCH_INDEX', DB_TBL_PREFIX);
mysql_query($query, $GLOBALS['DB']);

$query = sprintf('TRUNCATE TABLE %sSEARCH_TERM', DB_TBL_PREFIX);
mysql_query($query, $GLOBALS['DB']);

$query = sprintf('TRUNCATE TABLE %sSEARCH_DOCUMENT', DB_TBL_PREFIX);
mysql_query($query, $GLOBALS['DB']);

// retrieve the list of stop words
$query = sprintf('SELECT TERM_VALUE FROM %sSEARCH_STOP_WORD',DB_TBL_PREFIX);
$result = mysql_query($query, $GLOBALS['DB']);
$stop_words = array();
while ($row = mysql_fetch_array($result)) {
    // since this list will be checked for each word, use term as the array
    // key-- isset($stop_words[<term>]) is more efficient than using
    // in_array(<term>, $stop_words)
    $stop_words[$row['TERM_VALUE']] = true;    
}
mysql_free_result($result);

// open CURL handle for downloading
$ch = curl_init();

// set curl options
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Search Engine Indexer');

// fetch list of documents to index
$query = sprintf('SELECT DOCUMENT_URL FROM %sSEARCH_CRAWL', DB_TBL_PREFIX);
$result = mysql_query($query, $GLOBALS['DB']);
while ($row = mysql_fetch_array($result)) {
    echo 'Processing: ' . $row['DOCUMENT_URL'] . "...\n";

    //retrieve the document's content
    curl_setopt($ch, CURLOPT_URL, $row['DOCUMENT_URL']);
    $file = curl_exec($ch);
   // $file = tidy_repair_string($file);
    $html = @simplexml_load_string($file);

    // or:$html = simplexml_load_string($file);
    // extract the title
    if ($html->head->title) {
	$title = $html->head->title;
    } else {
	// use the filename if a title is not found
	$title = basename($row['DOCUMENT_URL']);
    }
    //extract the description
    $description = 'No description provided.';
    foreach ($html->head->meta as $meta) {
	if (isset($meta['name']) && $meta['name'] == 'description') {
	    $description = $meta['content'];
	    break;
	}
    }

    // add the document to the index
    $query = sprintf('INSERT INTO %sSEARCH_DOCUMENT (DOCUMENT_URL, ' .
	'DOCUMENT_TITLE, DESCRIPTION) VALUES ("%s", "%s", "%s")',
	    DB_TBL_PREFIX,
	    mysql_real_escape_string($row['DOCUMENT_URL'], $GLOBALS['DB']),
	    mysql_real_escape_string($title, $GLOBALS['DB']),
	    mysql_real_escape_string($description, $GLOBALS['DB']));
    mysql_query($query, $GLOBALS['DB']);

    // retrieve the document's id
    $doc_id = mysql_insert_id($GLOBALS['DB']);

    // strip HTML tags out from the content
    $file = strip_tags($file);

    // break content into individual words
    foreach (str_word_count($file, 1) as $index => $word) {
	// word should be stored as lowercase for comparisons
	$word = strtolower($word);

	// skip word if it appears in the stop word list
	if (isset($stop_words[$word])) continue;

	// determine if the word already exists in the database
	$query = sprintf('SELECT TERM_ID FROM %sSEARCH_TERM WHERE ' .
	    'TERM_VALUE = "%s"', DB_TBL_PREFIX,
	    mysql_real_escape_string($word, $GLOBALS['DB']));
	$result2 = mysql_query($query, $GLOBALS['DB']);
	if (mysql_num_rows($result2)) {
	    // word exists so retrieve its id
	    list($word_id) = mysql_fetch_row($result2);
	} else {
	    // add word to the database
	    $query = sprintf('INSERT INTO %sSEARCH_TERM (TERM_VALUE) ' .
		'VALUES ("%s")', DB_TBL_PREFIX,
		    mysql_real_escape_string($word, $GLOBALS['DB']));
	    mysql_query($query, $GLOBALS['DB']);

	    // determine the word's id
	    $word_id = mysql_insert_id($GLOBALS['DB']);
	}
	mysql_free_result($result2);

	// add the index record
	$query = sprintf('INSERT INTO %sSEARCH_INDEX (DOCUMENT_ID, ' .
	    'TERM_ID, OFFSET) VALUE (%d, %d, %d)',
		DB_TBL_PREFIX, $doc_id, $word_id, $index);
	mysql_query($query, $GLOBALS['DB']);
    }
}
mysql_free_result($result);
curl_close($ch);
echo 'Indexing complete.' . "\n";
?>
