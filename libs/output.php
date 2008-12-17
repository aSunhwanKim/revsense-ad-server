<?
// 
// AdRevenue Output controller
// output.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//


class output extends main
{
	var $tpl;
	var $template;
	
	var $title;
	var $heading;
	var $keywords;
	var $description;
	var $crumbs;
	var $meta;
	
	var $content;
	var $leftmenu; 
	
	function output()
	{
		global $DEFAULT;
		if(!$this->template)
			$this->template = $DEFAULT[template];
			
		$this->meta = array();
		
		return(1);
	}
	
	// Actually display the content
	function display()
	{
		global $DEFAULT;
		
		// Manage the page
		$this->tpl = new XTemplate($this->template, $this->meta);
		$this->tpl->assign("TITLE", $this->title);
		$this->tpl->assign("HEADING", $this->heading ? $this->heading : $this->title);
		$this->tpl->assign("META",  implode("\n", $this->meta)); 
		$this->tpl->assign("BODY", $this->content . $home);
		$this->tpl->assign("DATE", str_replace(" ", "&nbsp;", date("M d, Y h:i:s a (T)")));
		$this->tpl->assign("YEAR", date("Y"));

		// Show user sections
		if($_SESSION[user][id])
		{
			if($_SESSION[user][admin] == 3)
				$this->tpl->parse("main.admin");
			
			// Get balance for this user
			$uid = $_SESSION[user][id];
			$db = new database;
			$b = $db->getsql("SELECT balance FROM adrev_users WHERE id='$uid'");
			$_SESSION[user][balance] = $b[0][balance];
				
			if($_SESSION[user][balance] <= $this->default[adrevenue][min_payment] / 2) 
				$this->tpl->assign("BAL", "<font color=red>".number_format($_SESSION[user][balance],2)."</font>");
			else
				$this->tpl->assign("BAL", "<font color=green>".number_format($_SESSION[user][balance],2)."</font>");
				
			if($_SESSION[user][admin] == 2)
				$this->tpl->parse("main.publisher");
			else
				$this->tpl->parse("main.advertiser");
		}
		else
			$this->tpl->parse("main.logged_out");
				
		// Parse and output the page
		$this->tpl->parse("main");
		$this->content = $this->tpl->text("main");
		return(1);
	}

	// Print the page
	function printpage()
	{
		global $DEFAULT;
		
		// Find any helpstrings
		$section = $_REQUEST[section];
		$action = $_REQUEST[action];
		
		$this->content = preg_replace('/##HELPSTR#(.*?)##/ims', "", $this->content); 
		
		echo $this->content;
	}
	
	// Throw an error
	function error($errormsg="", $url="")
	{
		$this->title = "Error!";
		$this->heading = $this->title;
		$this->redirect($errormsg, $url, 5);
		exit;
	}
	
	// Redirect to another page
	function redirect($msg="", $url = "", $timeout=2)
	{
		$this->meta[]   = "<meta http-equiv=\"refresh\" content=\"$timeout;URL=$url\">";
		$this->content  = $msg;
		$this->content .= "<p>";
		$this->content .= "<a href=$url>Click here to continue</a> or wait $timeout seconds.";
		if(!$this->title)
			$this->title = "Notice";
		
		$this->display();
		$this->printpage();
		return(1);
	}

	function admin()
	{
		if($_SESSION[user][admin] <> 3 || !$_SESSION[user][zid])
		{
			$this->redirect("You must be an administrator!", "index.php?section=user&action=login");
			exit;			
		}
		
		return(1);
	}
	
	// Manage security and redirection
	function secure()
	{	
		// We're ok, so just go back
		if($_SESSION[user][zid])
			return(1);
		
		// Save where we were
		$_SESSION[redir] = $_SERVER[REQUEST_URI];
	
		header("Location: index.php?section=user&action=login");
		exit;
	}

	// Go back to where we came based on the redir block
	function goback()
	{
		global $DEFAULT;
	
		if(!$_SESSION[redir])
		{
			header("Location: index.php");
			exit;
		}

		$url = $_SESSION[redir];
		$_SESSION[redir] = "";
	
		header("Location: $url");
		exit;
	}

}
?>
