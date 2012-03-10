<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312" />
    <title>
<?php
if (!empty($GLOBALS['TEMPLATE']['title'])) {
	echo $GLOBALS['TEMPLATE']['title'];
}
?>
    </title>
    <link rel="stylesheet" type="text/css" href="css/harmonise.css" />
<?php
if (!empty($GLOBALS['TEMPLATE']['extra_head'])) {
	echo $GLOBALS['TEMPLATE']['extra_head'];
}
?>
</head>
<body>
<div id="wrap">
  <div id="header">
<?php
if (!empty($GLOBALS['TEMPLATE']['title'])) {
	echo $GLOBALS['TEMPLATE']['title'];
}
?>
  </div>
  <div id="nav">
<?php
if (!empty($GLOBALS['TEMPLATE']['nav'])) {
    echo $GLOBALS['TEMPLATE']['nav'];
}
?>
  </div>
  <div id="content">
<?php
if (!empty($GLOBALS['TEMPLATE']['content'])) {
	echo $GLOBALS['TEMPLATE']['content'];
}
?>
  </div>
  <div id="footer"><p> Copyright &copy; <?php echo date('Y'); ?></P></div>
</div>
</body>
</html>



