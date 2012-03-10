<?php
// include shared code
require_once '../lib/common.php';
require_once '../lib/db.php';
require_once '../lib/functions.php';
require_once '../lib/User.php';

// 401 file included because user should be logged in to access this page
include '401.php';

// user must have appropriate permissions to use this page
$user = User::getById($_SESSION['userId']);
if (!($user->permission & User::DELETE_USER)) {
    die('<p> Sorry, you do not have sufficient privileges to delete forums.</p>');
}

// validate incoming values
$username = (isset($_POST['username'])) ? trim($_POST['username']) : '';

// delete entry from the database if the form was submitted and the necessary
// values were supplied in the form

if (isset($_POST['submitted']) && $username) {
    $query = sprintf('DELETE FROM %sUSER WHERE USERNAME = "%s"',
	DB_TBL_PREFIX, mysql_real_escape_string($username, $GLOBALS['DB']));
    mysql_query($query, $GLOBALS['DB']);

    // redirect user to list of forums after new record has been stored
    header('Location:view.php');
}

// form was submitted but not all the information was correctly filled in
else if (isset($_POST['submitted'])) {
    $message = ' <p> Not all information was provided. Please correct and resubmit.</p>';
}

//generate the form
ob_start();
if (isset($message)) {
    echo $message;
}
?>
<form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
<div>
  <label for="username"> ÓÃ»§Ãû£º</label>
  <input type="input" id="username" name="username" value="<?php
    echo htmlspecialchars($username); ?>" /><br />
  <input type="hidden" name="submitted" value="true" />
  <input type="submit" value="É¾³ý" />
</div>
</form>
<?php
	$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
// display the page
include '../templates/template-page.php';
?>
