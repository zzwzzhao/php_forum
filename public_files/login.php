<?php
header("Content-type:text/html;charset=gb2312");
// include shared code
include '../lib/common.php';
include '../lib/db.php';
include '../lib/functions.php';
include '../lib/User.php';

//strat or continue the session
session_start();
header('Cache-control:private');

//perform login logic if login is set
if (isset($_GET['login'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
	// retrieve user record
	$user = (User::validateUsername($_POST['username'])) ?
	    User::getByUsername($_POST['username']) : new User();

	if ($user->userId && $user->password == sha1($_POST['password'])) {
	    //everything checks out so store values in session to track the
	    //user and redirect to main page
	    $_SESSION['access'] = TRUE;
	    $_SESSION['userId'] = $user->userId;
	    $_SESSION['username'] = $user->username;
	    header('Location:view.php');
	}else {
	    // invalid user and/or password
	    $_SESSION['access'] = false;
	    $_SESSION['username'] = null;
	    header('Location:401.php');
	}
    }else {
	// missing credentials
	$_SESSION['access'] = false;
	$_SESSION['username'] = null;
	header('Location:401.php');
    }
    exit();
}

//perform logout logic if logout is set
//(clearing the session data effectively logout the user)
else if (isset($_GET['logout'])) {
    if (isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time() - 42000, '/');
    }
    $_SESSION = array();
    session_unset();
    session_destroy();
}
// generate login form
ob_start();
?>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?login"
method="post">
<table>
  <tr>
    <td><label for="username"> �û��� </label></td>
    <td><input type="text" name="username" id="username"></td>
  </tr><tr>
    <td><label for="password"> ���� </label></td>
    <td><input type="password" name="password" id="password"></td>
  </tr><tr>
    <td></td>
    <td><input type="submit" value="��½"/></td>
  </tr>
</table>
</form>
<?php
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();
$GLOBALS['TEMPLATE']['extra_head'] = '<link rel="stylesheet" type="text/css" ' .
	'href="css/login.css" />';

//display the page
include '../templates/template-page.php';
?>
