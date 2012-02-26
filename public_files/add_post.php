<?php
// include shared code
require_once '../lib/common.php';
require_once '../lib/db.php';
require_once '../lib/functions.php';
require_once '../lib/User.php';
// include 401 file because user should be logged in to aacess this page
include '401.php';

//retrieve user information
$user = User::getById($_SESSION['userId']);
if (!$user->userId) {
    die(' <p> Sorry, you must be logged in to post. </p> ');
}

// validate incoming values
$forum_id = (isset($_GET['fid'])) ? (int)$_GET['fid'] : 0;
$query = sprintf('SELECT FORUM_ID FROM %sFORUM WHERE FORUM_ID = %d',
    DB_TBL_PREFIX, $forum_id);
$result = mysql_query($query, $GLOBALS['DB']);
if (!mysql_num_rows($result)) {
    mysql_free_result($result);
    mysql_close($GLOBALS['DB']);
    die('<p> Invalid forum id.</p>');
}
mysql_free_result($result);

$msg_id = (isset($_GET['mid'])) ? (int)$_GET['mid'] : 0;
$query = sprintf('SELECT MESSAGE_ID FROM %sFORUM_MESSAGE WHERE ' .
    'MESSAGE_ID = %d', DB_TBL_PREFIX, $msg_id);
$result = mysql_query($query, $GLOBALS['DB']);
if ($msg_id && !mysql_num_rows($result)) {
    mysql_free_result($result);
    mysql_close($GLOBALS['DB']);
    die('<p> Invalid forum id.</p>');
}
mysql_free_result($result);

$msg_subject = (isset($_POST['msg_subject'])) ? trim($_POST['msg_subject']) : '';
$msg_text = (isset($_POST['msg_text'])) ? trim($_POST['msg_text']) : '';

// add entry to the database if the form was submitted and the necessary
// values were supplied in the form
if (isset($_POST['submitted']) && $msg_subject && $msg_text) {
    $query = sprintf('INSERT INTO %sFORUM_MESSAGE (SUBJECT, ' .
	'MESSAGE_TEXT, PARENT_MESSAGE_ID, FORUM_ID, USER_ID) VALUES ' .
	'("%s", "%s", %d, %d, %d)', DB_TBL_PREFIX,
	mysql_real_escape_string($msg_subject, $GLOBALS['DB']),
	mysql_real_escape_string($msg_text, $GLOBALS['DB']),
	$msg_id, $forum_id, $user->userId);
    mysql_query($query, $GLOBALS['DB']);
    //redirect
    header('Location:view.php?fid=' . $forum_id . (($msg_id) ? '&mid=' . $msg_id : ''));
}
// form was submitted but not all the information was correctly filled in
else if (isset($_POST['submitted'])) {
    $message = '<p> Not all information was provided. Please correct ' .
	'and resubmit.</p>';
}
//generate the form
ob_start();
if (isset($message)) {
    echo $message;
}
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) .
'?fid=' . $forum_id . '&mid=' . $msg_id; ?>">
<div>
<label for="msg_subject"> Subject: </label>
<input type="text" id="msg_subject" name="msg_subject"
value="<?php echo htmlspecialchars($msg_subject); ?>" /><br />
<label for="msg_text"> Post: </label>
<textarea id="msg_text"  name="msg_text"><?php echo htmlspecialchars($msg_text) ?>
</textarea>
<br />
<input type="hidden" name="submitted" value="true" />
<input type="submit" value="Create" />
</div>
</form>
<?php
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
//display the page
include '../templates/template-page.php';
?>
