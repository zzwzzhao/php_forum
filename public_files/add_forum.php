<?php
//include shared code
require_once('../lib/common.php');
require_once('../lib/db.php');
require_once('../lib/functions.php');
require_once('../lib/User.php');

// 401 file included because user should be logged in to access this page
require_once('401.php');

//user must have appropriate permission to use the page
$user = User::getById($_SESSION['userId']);
if (~$user->permission & User::CREATE_FORUM) {
    die('<p> Sorry, you do not have sufficient privileges to create new ' .
	'forums.</p>');
}
// validate incoming values
$forum_name = (isset($_POST['forum_name'])) ? trim($_POST['forum_name']) : '';
$forum_desc = (isset($_POST['forum_desc'])) ? trim($_POST['forum_desc']) : '';

// add entry to the database if the form was submitted and the necessary
// values were supplied in the form
if (isset($_POST['submitted']) && $forum_name && $forum_desc) {
    $query = sprintf('INSERT INTO %sFORUM (FORUM_NAME, DESCRIPTION) ' .
	'VALUES ("%s", "%s")', DB_TBL_PREFIX,
	    mysql_real_escape_string($forum_name, $GLOBALS['DB']),
	    mysql_real_escape_string($forum_desc, $GLOBALS['DB']));
    mysql_query($query, $GLOBALS['DB']);

    // redirect user to list of forums after new record has been stored
    header('Location:view.php');
}
// form was submitted but not all the information was correctly filled in
else if (isset($_POST['submitted'])) {
    $message = '<p> Not all information was provided. Please correct ' .
	'and resubmit. </p>';
}
// generate the form
ob_start();
if (isset($message)) {
    echo $message;
}
?>
<form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>"
method="post">
<div>
<label for="forum_name"> Forum Name: </label>
<input type="text" name="forum_name" id="forum_name"
value="<?php echo htmlspecialchars($forum_name); ?>" /><br />
<label for="forum_desc"> Description: </label>
<input type="input" name="forum_desc" id="forum_desc"
value="<?php echo htmlspecialchars($forum_desc); ?>" /><br />
<input type="hidden" name="submitted" value="true" />
<input type="submit" value="Create" />
</div>
</form>
<?php
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
// display the page
include '../templates/template-page.php';
?>
