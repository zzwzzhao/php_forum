<?php
// include shared code
include '../lib/common.php';
include '../lib/db.php';
include '../lib/functions.php';
include '../lib/User.php';

// start or continue session so the CAPTCHA text stored in $_SESSION is accessible
session_start();
header('Cache-control:private');

// prepare the registration form's HTML
ob_start();
?>
<form method="post" action=" <?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> ">
  <table>
    <tr>
      <td><label for="username"> 用户名 </label></td>
      <td><input type="text" name="username" id="username"
           value="<?php if (isset($_POST['username']))
	   echo htmlspecialchars($_POST['username']); ?>"/></td>
    </tr><tr>
      <td><label for="password1"> 密码 </label></td>
      <td><input type="password" name="password1" id="password1" value="" /></td>
    </tr><tr>
      <td><label for="password2"> 再次输入密码 </label></td>
      <td><input type="password" name="password2" id="password2" value="" /></td>
    </tr><tr>
      <td><label for="email"> 邮箱地址 </label></td>
      <td><input type="text" name="email" id="email"
      value="<?php if (isset($_POST['email']))
      echo htmlspecialchars($_POST['email']); ?>"/></td>
    </tr><tr>
      <td><label for="captcha"> 验证码 </label></td>
      <td> 请输入图中文字（不区分大小写） <br />
      <img src="img/captcha.php?nocache=<?php echo time();?>" alt=""/><br />
      <input type="text" name="captcha" id="captcha" /></td>
    </tr><tr>
      <td></td>
      <td><input type="submit" value="注册" /></td>
      <td><input type="hidden" name="submitted" value="1" /></td>
    </tr><tr>
  </table>
</form>
<?php
$form = ob_get_clean();

// show the form if this is the first time the page is viewed
if (!isset($_POST['submitted'])) {
    $GLOBALS['TEMPLATE']['content'] = $form;
}

//otherwise process incoming data
else {
    $password1 = (isset($_POST['password1'])) ? $_POST['password1'] : '';
    $password2 = (isset($_POST['password2'])) ? $_POST['password2'] : '';
    $password = ($password1 && $password1 == $password2) ? sha1($password1) : '';

    //validate CAPTCHA
    $captcha = (isset($_POST['captcha']) && 
	   strtoupper($_POST['captcha']) == $_SESSION['captcha']);

    // add the record if all input validates
    if (User::validateUsername($_POST['username']) && $password &&
/*	User::validateEmailAddr($_POST['email']) &&*/ $captcha) {
	//make sure the user doesn't already exist
        $user = User::getByUsername($_POST['username']);
	if ($user->userId) {
	    $GLOBALS['TEMPLATE']['content'] = '<p><strong> Sorry, that ' .
            'account already exists. </strong></p> <p> Please try a ' .
	    'different username. </p> ';
	$GLOBALS['TEMPLATE']['content'] .= $form;
	}else {
	    //create an inactive user record
	    $user = new User();
	    $user->username = $_POST['username'];
	    $user->password = $password;
	    $user->emailAddr = $_POST['email'];
	    $token = $user->setInactive();

	    $GLOBALS['TEMPLATE']['content'] = '<p><strong> Thank you for ' .
		'registering. </strong></p> <p> Be sure to verify your ' .
		'account by visiting <a href="verify.php?uid=' .
		$user->userId . '&token=' . $token . '"> Verify.php?uid=' .
		$user->userId . '&token=' . $token . '</a></p>';
	}
	}else {
	    // there was invalid data
	    $GLOBALS['TEMPLATE']['content'] = '<p><strong> You provided some ' .
		'invalid data.</strong></p> <p>Please fill in all fields ' .
		'correctly so we can register your user account. </p> ';
	    $GLOBALS['TEMPLATE']['content'] .= $form;
	}
}

// display the page
include '../templates/template-page.php';
?>


