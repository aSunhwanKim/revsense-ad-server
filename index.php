<?
// 
// AdRevenue Ad Management System
// index.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Include our main lib
include_once("libs/startup.php");

// Get section and action
$section = $_REQUEST[section];
$action  = $_REQUEST[action];

// Determine if we need to install
if(!$DEFAULT[database])
{
	header("Location: install.php");
	exit;
}

// Go to the home page if we have no section
if(!$section)
	$section = "home";
	
// Call whatever modules we now have
$exc = "modules/" . $section . ".php";
if(file_exists($exc))
{
	// Loadup the section
	include_once($exc);
	$s = new $section;
	
	// Run the action if this is not an install
	if($action != "install")
		$s->main();

	if($action)
		$s->$action();
	else
		$s->_default();
}
else
{
	print "<h2>AdRevenue Error: [ $section ] not found</h2>";
	print "That module does not exist. Several problems could cause this:<br>";
	print "<ol>";
	print "<li> You entered an invalid request (URL)";
	print "<li> You did not upload files correctly";
	print "<li> You do not have read permissions on the <b>modules</b> directory";
	print "<li> The module you are requesting is an add-on to AdRevenue";
	print "</ol>";
	print "For help, please <a href=http://www.w3matter.com/support>contact us</a> for support and ";
	print "refer to this screen.";
}

exit;
?>
