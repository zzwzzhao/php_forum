<?php
// include shared code
require_once '../lib/common.php';
require_once '../lib/db.php';
require_once '../lib/functions.php';

// must be logged in to access this page
include '401.php';

// processes incoming data if the form has been submitted
if (isset($_POST['submitted'])) {
	// delete existing addresses
	$query = sprintf('TRUNCATE TABLE %sSEARCH_CRAWL', DB_TBL_PREFIX);
	mysql_query($query, $GLOBALS['DB']);

	// add addresses list to database
	$addresses = explode_items($_POST['addresses'], "\n", false);
	if (count($addresses)) {
		$values = array();
		foreach ($addresses as $address) {
			$values[] = mysql_real_escape_string($address, $GLOBALS['DB']);
		}
		$query = sprintf('INSERT INTO %sSEARCH_CRAWL (DOCUMENT_URL) ' .
			'VALUES ("%s")', DB_TBL_PREFIX, implode('"), ("', $values));
		mysql_query($query, $GLOBALS['DB']);
	}

	// delete existing stop words
	$query = sprintf('TRUNCATE TABLE %sSEARCH_STOP_WORD', DB_TBL_PREFIX);
	mysql_query($query, $GLOBALS['DB']);

	// add stop word list to database
	$words = explode_items($_POST['stop_words'], "\n", false);
	if (count($words)) {
		$values = array();
		foreach ($words as $word) {
			$values[] = mysql_real_escape_string($word, $GLOBALS['DB']);
		}
		$query = sprintf('INSERT INTO %sSEARCH_STOP_WORD (TERM_VALUE) ' .
			'VALUES ("%s")', DB_TBL_PREFIX, implode('"), ("', $values));
		mysql_query($query, $GLOBALS['DB']);
	}
}
// generate HTML form
ob_start();
?>
<form method="post"
action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
<table>
  <tr>
    <td style="vertical-align:top; text-align:right">
    <label for="addresses"> Include Addresses </label></td>
    <td> <small> Enter addresses to include in crawling, one address per line.</small> <br />    <textarea name="addresses" id="addresses" rows="5" cols="60"> <?php
//retrieve list of addresses
$query = sprintf('SELECT DOCUMENT_URL FROM %sSEARCH_CRAWL ' .
	'ORDER BY DOCUMENT_URL ASC', DB_TBL_PREFIX);
$result = mysql_query($query, $GLOBALS['DB']);
while ($row = mysql_fetch_array($result)) {
	echo htmlspecialchars($row['DOCUMENT_URL']) . "\n";
}
mysql_free_result($result);
?></textarea>
</td>
</tr><tr>
  <td style="vertical-align:top; text-align:right">
  <label for="stop_words"> Stop Words </label></td>
  <td><small> Enter words to omit from the index, one per line.</small><br />
  <textarea name="stop_words" id="stop_words" rows="5" cols="60"><?php
//retrieve list of stop words
$query = sprintf('SELECT TERM_VALUE FROM %sSEARCH_STOP_WORD ORDER BY ' .
	'TERM_VALUE ASC', DB_TBL_PREFIX);
$result = mysql_query($query, $GLOBALS['DB']);
while ($row = mysql_fetch_array($result)) {
	echo htmlspecialchars($row['TERM_VALUE']) . "\n";
}
mysql_free_result($result);
?></textarea>
</td>
</tr><tr>
<td></td>
<td><input type="submit" value="Submit" /></td>
<td><input type="hidden" name="submitted" value="1" /></td>
</tr><tr>
</table>
</form>
<?php
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
//display the page
include '../templates/template-page.php';
?>
 
