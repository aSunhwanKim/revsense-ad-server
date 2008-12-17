<?
//
// Revsense Ad Management
// doc.php
//
// (C) 2004-2006 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class doc extends main
{
	function _default()
	{
		header("Location: index.php");
		exit;
	}
	
	function terms()
	{
		$this->output->title = "@@Terms and conditions@@";
		$this->output->heading = "@@Advertising Terms & Conditions@@";
		
		$this->output->content = $this->default[adrevenue][terms];
		$this->output->display();
		$this->output->printpage();
		exit;		
	}
	
	function faq()
	{
		$this->output->title = "@@Frequently Asked Questions@@";
		$this->output->heading = "@@Frequently Asked Questions@@";
		
		$this->output->content = $this->default[adrevenue][faq];
		$this->output->display();
		$this->output->printpage();
		exit;			
	}
}
