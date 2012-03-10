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
if (!($user->permission & User::DELETE_MESSAGE)) {
    die('<p> Sorry, you do not have sufficient privileges to delete message.</p>');
}

// validate incoming values
$forum_id = (isset($_GET['fid'])) ? (int)$_GET['fid'] : 0;
$msg_id = (isset($_GET['mid'])) ? (int)$_GET['mid'] : 0;

// delete message
if ($forum_id && $msg_id) {
    $query = sprintf('DELETE FROM %sFORUM_MESSAGE WHERE MESSAGE_ID = %d OR PARENT_MESSAGE_ID = %d',
	DB_TBL_PREFIX, $msg_id, $msg_id);
    mysql_query($query, $GLOBALS['DB']);

    // redirect user to list of forums after new record has been stored
    header('Location:view.php');
}else{
    header('Location:view.php');
}
?>
