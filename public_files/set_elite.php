<?php
// include shared code
include '../lib/common.php';
include '../lib/db.php';
include '../lib/functions.php';
include '../lib/User.php';

// 401 file included because user should be logged in to access this page
include '401.php';

// user must have appropriate permissions to use this page
$user = User::getById($_SESSION['userId']);
if (!($user->permission & User::MOVE_MESSAGE)) {
    die('<p> Sorry, you do not have sufficient privileges to delete message.</p>');
}

// validate incoming values
$forum_id = (isset($_GET['fid'])) ? (int)$_GET['fid'] : 0;
$msg_id = (isset($_GET['mid'])) ? (int)$_GET['mid'] : 0;

// set the message to elite
if ($forum_id && $msg_id) {
    $query = sprintf('UPDATE %sFORUM_MESSAGE SET ELITE = 1 WHERE MESSAGE_ID = %d',
	DB_TBL_PREFIX, $msg_id);
    mysql_query($query, $GLOBALS['DB']);

     // redirect user to list of forums after new record has been stored
    header('Location:view.php');
}
else {
    header('Location:view.php');
}
