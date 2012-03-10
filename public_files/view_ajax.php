<?php
header("Content-type:text/html;charset=gb2312");
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
$user = User::getById($_SESSION['userId']);

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
	echo '<p><a href="view.php?fid=' . $forum_id . '"> 回到帖子列表 ' .
	    '</a></p>';
    }else {
	// link back to forum list
	echo '<p><a href="view.php"> 回到板块列表 </a></p>';

	// display option to add new post if user is logged in
	if (isset($_SESSION['access'])) {
	    echo '<p><a href="add_post.php?fid=' . $forum_id . '"> 发布新帖 ' .
		'</a></p>';
	}
    }
}else {
    echo ' <h1> 板块列表 </h1> ';
    if (isset($_SESSION['userId'])) {
	// display link to create new forum if user has permission to do so
	$user = User::getById($_SESSION['userId']);
//	if ($user->permission & User::CREATE_FORUM) {
//	    echo '<p><a href="add_forum.php"> 创建新板块 </a>' . 
//		'<a href="delete_forum.php"> 删除版块 </a></p>';	    
	//	}
	$add_forum = ($user->permission & User::CREATE_FORUM) ?  '<a href="add_forum.php">创建新版块</a>' : '';
	$delete_forum = ($user->permission & User::DELETE_FORUM) ?  '<a href="delete_forum.php">删除板块</a>' : '';
	$delete_user = ($user->permission & User::DELETE_FORUM) ?  '<a href="delete_user.php">删除用户</a>' : '';
	echo '<p>' .$add_forum  . $delete_forum . $delete_user . '</p>';
    }
}
// generate message view
if ($forum_id && $msg_id) {
    $display = 10;
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
    if ($total = mysql_num_rows($result)) {
	// accept the display offset
	$start = (isset($_GET['start']) && ctype_digit($_GET['start']) &&
	    $_GET['start'] <= $total) ? $_GET['start'] : 0;

	// move the data pointer to the appropriate starting record
	mysql_data_seek($result,$start);
	

	echo '<table class="message-table"> ';
	$count = 0;
	$i = 0;
	while (($row = mysql_fetch_assoc($result)) && ($count++ < $display)) {
	    $i++;
	echo '<tr>';
	echo '<td class="userinformation" >';
	if (file_exists('avatars/' . $row['USERNAME'] . 'jpg')) {
	    echo '<img src="avatars/' . $row['USERNAME'] . 'jpg" />';
	}else {
	    echo '<img src="img/default_avatar.jpg" />';
	}
	echo '<br /><strong> ' . $row['USERNAME'] . ' </strong><br />';
	echo date('m/d/Y <\b\r/> H:i:s', $row['MESSAGE_DATE']) . '</td>';
	echo '<td class="message" >';
	echo '<div><strong> ' . htmlspecialchars($row['SUBJECT']) .
	    '</strong></div>';
	echo '<div> ' . htmlspecialchars($row['MESSAGE_TEXT']) . '</div>';
	echo '<div style="text-align: right;">';
	echo '<a href="add_post.php?fid=' . $row['FORUM_ID'] . '&mid=' .
	    (($row['PARENT_MESSAGE_ID'] != 0) ? $row['PARENT_MESSAGE_ID'] :
	    $row['MESSAGE_ID']) . '"> 回复 </a><br />' . ($start+$i) . '楼</div></td>';
	echo '</tr>';
    }
	echo '</table>';
	//Generate the paginiation menu
	echo '<p class="fenye">';
	if ($start > 0) {
	    echo '<a href="view.php?fid=' . $forum_id . '&mid=' . $msg_id .'&start=0"> ' .
		'首页</a>';
	    echo '<a href="view.php?fid=' . $forum_id . '&mid=' . $msg_id . '&start=' .
		($start - $display) . '"> &lt;上一页 </a> ';
	}
	$page = ceil($total / $display);
	$current_page =ceil($start / $display) + 1 ;
	$min = ($current_page - 2 > 0) ? ($current_page - 2) : 1;
	$mix = ($current_page + 2 <= $page) ? ($current_page + 2) : $page;
	
	for ($i = $min; $i <= $mix; $i++) {
	    if ($i == $current_page){
		echo $i;
	    }else {
	    echo '<a href="view.php?fid=' . $forum_id . '&mid=' . $msg_id . '&start=' .
		(($i - 1) * $display) . '"> '. $i . '</a>';
	    }
	} 
	if ($total > ($start + $display)) {
	    echo '<a href="view.php?fid=' . $forum_id . '&mid=' . $msg_id . '&start=' .
		($start + $display) . '"> 下一页 &gt; </a>';
	    echo '<a href="view.php?fid=' . $forum_id . '&mid=' . $msg_id . '&start=' .
		($total - $display) . '"> 尾页 </a> ';
	}
       echo '</p>';	
	mysql_free_result($result);
    }
}
else if ($forum_id) {
    $display = 10; 
    $query = sprintf('SELECT MESSAGE_ID, SUBJECT, USERNAME, ' .
	'UNIX_TIMESTAMP(MESSAGE_DATE) AS MESSAGE_DATE FROM  %sFORUM_MESSAGE M JOIN %sUSER U ON M.USER_ID = U.USER_ID '.
	'WHERE PARENT_MESSAGE_ID = 0 AND FORUM_ID = %d ORDER BY ' .
	'MESSAGE_DATE DESC', DB_TBL_PREFIX, DB_TBL_PREFIX, $forum_id);
    $result = mysql_query($query, $GLOBALS['DB']);

    if ($total = mysql_num_rows($result)) {
	// accept the display offet
	$start = (isset($_GET['start']) && ctype_digit($_GET['start']) &&
	    $_GET['start'] <= $total) ? $_GET['start'] : 0;

	// move the data pointer to the appropriate starting record
	mysql_data_seek($result,$start);

	// display entries
	echo '<table>';
	echo '<thead><tr><th class="subject">主题</th><th class="time">发帖时间</th><th class="user">发帖人</th>';
	if($user->permission & User::DELETE_MESSAGE) {
	    echo '<th class="delete">删除帖子</th>';
	}
	    echo '</tr></thead><tbody>';
	$count = 0;
	while (($row = mysql_fetch_assoc($result)) && ($count++ < $display)) {
	    echo '<tr><td class="subject"><a href="view.php?fid=' . $forum_id . '&mid=' .
		$row['MESSAGE_ID'] . '">' . htmlspecialchars($row['SUBJECT']) . '</td>';
	    echo '<td class="time">' . date('m/d/Y H:i:s', $row['MESSAGE_DATE']) . ':</td> ';
	    echo '<td class="user">' . htmlspecialchars($row['USERNAME']) . '</td>';
	    if ($user->permission & User::DELETE_MESSAGE) {
		echo '<td class="delete"><a href="delete_message.php?fid=' . $forum_id . '&mid=' .
		    $row['MESSAGE_ID'] . '">删除</td>';
	    }
	    echo '</tr>';
	}
	echo '</tbody></table>';
	//Generate the paginiation menu
	echo '<p class="fenye">';
	if ($start > 0) {
	    echo '<a href="view.php?fid=' . $forum_id . '&start=0"> ' .
		'首页</a>';
	    echo '<a href="view.php?fid=' . $forum_id . '&start=' .
		($start - $display) . '"> &lt;上一页 </a> ';
	}
	$page = ceil($total / $display);
	$current_page =ceil($start / $display) + 1 ;
	$min = ($current_page - 2 > 0) ? ($current_page - 2) : 1;
	$mix = ($current_page + 2 <= $page) ? ($current_page + 2) : $page;
	
	for ($i = $min; $i <= $mix; $i++) {
	    if ($i == $current_page){
		echo $i;
	    }else {
	    echo '<a href="view.php?fid=' . $forum_id . '&start=' .
		(($i - 1) * $display) . '"> '. $i . '</a>';
	    }
	} 
	if ($total > ($start + $display)) {
	    echo '<a href="view.php?fid=' . $forum_id . '&start=' .
		($start + $display) . '"> 下一页 &gt; </a>';
	    echo '<a href="view.php?fid=' . $forum_id . '&start=' .
		($total - $display) . '"> 尾页 </a> ';
	}
       echo '</p>';	
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

    echo '<ul id="board-list">';
    while ($row = mysql_fetch_assoc($result)) {
	echo '<li><a href="' . htmlspecialchars($_SERVER['PHP_SELF']);
	echo '?fid=' . $row['FORUM_ID'] . '">';
	echo htmlspecialchars($row['FORUM_NAME']) . ': ';
	echo htmlspecialchars($row['DESCRIPTION']) . '</a></li>';
    }
    echo '</ul>';
    mysql_free_result($result);
}
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
$GLOBALS['TEMPLATE']['extra_head'] = '<link rel="stylesheet" type="text/css" href="css/view.css" />' .
    '<script src="js/jquery-1.7.js" type="text/javascript"></script>' .
    '<script src="js/fenye.js" type="text/javascript"></script>';
ob_start();
?>
    <ul><li><a href="userinformation.php">我的资料</a></li>
        <li><a href="register.php">用户注册</a></li>
        <li><a href="login.php">登陆</a></li>
<?php
if (isset($_SESSION['username'])) {
    echo '<li><a href="login.php?logout">退出</a></li>';
}
?>
    </ul>
<?php
$GLOBALS['TEMPLATE']['nav']= ob_get_clean();
// display the page
echo $GLOBALS['TEMPLATE']['content'];
?>
