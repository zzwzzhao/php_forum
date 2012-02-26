<!DOCTYPE html>
<html lang="en">
<head>
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
  <div id="content">
<?php
if (!empty($GLOBALS['TEMPLATE']['content'])) {
	echo $GLOBALS['TEMPLATE']['content'];
}
?>
  </div>
  <div id="footer"> Copyright &copy; <?php echo date('Y'); ?></div>
</div>
</body>
</html>



