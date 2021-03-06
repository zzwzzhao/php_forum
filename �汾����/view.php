<?php
// include shared code
require_once '../lib/common.php';
require_once '../lib/db.php';
require_once '../lib/functions.php';
require_once '../lib/User.php';

//start or continue session
session_start();

// validate incoming values
$forum_id = (isset($_GET['fid'])) ? (int)$_GET['fid'] : 0;
$msg_id = (isset($_GET['mid'])) ? (int)$_GET['mid'] : 0;

ob_start();
if ($forum_id) {
    //display forum name as header
    $query = sprintf('SELECT FORUM_NAME FROM %sFORUM WHERE FORUM_ID = %d',
	DB_TBL_PREFIX, $forum_id);
    $result = mysql_query($query, $GLOBALS['DB']);
    if (!mysql_num_rows($result)) {
	die('<p> Invalid forum id. </p>');
    }
    $row = mysql_fetch_assoc($result);
    echo '<h1>' . htmlspecialchars($row['FORUM_NAME']) . '</h1>';
    mysql_free_result($result);

    if ($msg_id) {
	$query = sprintf('SELECT MESSAGE_ID FROM %sFORUM_MESSAGE ' .
	    'WHERE MESSAGE_ID = %d', DB_TBL_PREFIX, $msg_id);
	$result = mysql_query($query, $GLOBALS['DB']);

	if (!mysql_num_rows($result)) {
	    mysql_free_result($result);
	    die('<p> Invalid forum id. </p>');
	}
	mysql_free_result($result);

	//link back to thread view
	echo '<p><a href="view.php?fid=' . $forum_id . '"> Back to forum ' .
	    'threads.</a></p>';
    }else {
	// link back to forum list
	echo '<p><a href="view.php"> Back to forum list. </a></p>';

	// display option to add new post if user is logged in
	if (isset($_SESSION['access'])) {
	    echo '<p><a href="add_post.php?fid=' . $forum_id . '"> Post new ' .
		'message.</a></p>';
	}
    }
}else {
    echo ' <h1> Forums </h1> ';
    if (isset($_SESSION['userId'])) {
	// display link to create new forum if user has permission to do so
	$user = User::getById($_SESSION['userId']);
	if ($user->permission & User::CREATE_FORUM) {
	    echo '<p><a href="add_forum.php"> Create new forum. </a></p>';
	}
    }
}
// generate message view
if ($forum_id && $msg_id) {
    $query = sprintf('
	SELECT
	 USERNAME, FORUM_ID, MESSAGE_ID, PARENT_MESSAGE_ID,
	 SUBJECT, MESSAGE_TEXT, UNIX_TIMESTAMP(MESSAGE_DATE) AS MESSAGE_DATE
	  FROM
	   %sFORUM_MESSAGE M JOIN %sUSER U ON M.USER_ID = U.USER_ID
	    WHERE
	     MESSAGE_ID = %d OR PARENT_MESSAGE_ID = %d
	      ORDER BY MESSAGE_DATE ASC',
	      DB_TBL_PREFIX, DB_TBL_PREFIX, $msg_id, $msg_id);
    $result = mysql_query($query, $GLOBALS['DB']);

    echo '<table border=1> ';
    while ($row = mysql_fetch_assoc($result)) {
	echo '<tr>';
	echo '<td style="text-align:center; vertical-align:top;
	width:150px;">';
	if (file_exists('avatars/' . $row['USERNAME'] . 'jpg')) {
	    echo '<img src="avatars/' . $row['USERNAME'] . 'jpg" />';
	}else {
	    echo '<img src="img/default_avatar.jpg" />';
	}
	echo '<br /><strong> ' . $row['USERNAME'] . ' </strong><br />';
	echo date('m/d/Y <\b\r/> H:i:s', $row['MESSAGE_DATE']) . '</td>';
	echo '<td style="vertical_align:top;">';
	echo '<div><strong> ' . htmlspecialchars($row['SUBJECT']) .
	    '</strong></div>';
	echo '<div> ' . htmlspecialchars($row['MESSAGE_TEXT']) . '</div>';
	echo '<div style="text-align: right;">';
	echo '<a href="add_post.php?fid=' . $row['FORUM_ID'] . '&mid=' .
	    (($row['PARENT_MESSAGE_ID'] != 0) ? $row['PARENT_MESSAGE_ID'] :
	    $row['MESSAGE_ID']) . '"> Reply </a></div></td>';
	echo '</tr>';
    }
    echo '</table>';
    mysql_free_result($result);
}
else if ($forum_id) {
    $query = sprintf('SELECT MESSAGE_ID, SUBJECT, ' .
	'UNIX_TIMESTAMP(MESSAGE_DATE) AS MESSAGE_DATE FROM %sFORUM_MESSAGE '.
	'WHERE PARENT_MESSAGE_ID = 0 AND FORUM_ID = %d ORDER BY ' .
	'MESSAGE_DATE DESC', DB_TBL_PREFIX, $forum_id);
    $result = mysql_query($query, $GLOBALS['DB']);

    if (mysql_num_rows($result)) {
	echo '<ul>';
	while ($row = mysql_fetch_assoc($result)) {
	    echo '<li><a href="view.php?fid=' . $forum_id . '&mid=' .
		$row['MESSAGE_ID'] . '">';
	    echo date('m/d/Y', $row['MESSAGE_DATE']) . ': ';
	    echo htmlspecialchars($row['SUBJECT']) . '</li> ';
	}
	echo '</ul>';
    }else {
	echo '<p> This forum contains no messages. </p>';
    }
    mysql_free_result($result);
}
// generate forums view
else {
    $query = sprintf('SELECT FORUM_ID, FORUM_NAME, DESCRIPTION FROM %sFORUM '.
	'ORDER BY FORUM_NAME ASC, FORUM_ID ASC', DB_TBL_PREFIX);
    $result = mysql_query($query, $GLOBALS['DB']);

    echo '<ul>';
    while ($row = mysql_fetch_assoc($result)) {
	echo '<li><a href="' . htmlspecialchars($_SERVER['PHP_SELF']);
	echo '?fid=' . $row['FORUM_ID'] . '">';
	echo htmlspecialchars($row['FORUM_NAME']) . ': ';
	echo htmlspecialchars($row['DESCRIPTION']) . '</li>';
    }
    echo '</ul>';
    mysql_free_result($result);
}
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
// display the page
include '../templates/template-page.php';
?>



