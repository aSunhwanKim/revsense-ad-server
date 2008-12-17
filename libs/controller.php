<?
// 
// AdRevenue SuperClass
// controller.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class main
{
	var $db;
	var $http;
	var $user;
	var $input;
	var $default;
	var $section;
	var $tz;
	
	function main()
	{
		global $DEFAULT;
		
		// Set defaults
		$this->default = $DEFAULT;
	
		$this->tz = lib_getmicrotime();
	
		// Setup database lib
		$this->db = new database();
		
		if($this->default[engine] == "pg")
			$this->db->pg_database($this->default[host], $this->default[database], 
										$this->default[user], $this->default[password], 
										$this->default[port]);			
		if($this->default[engine] == "mysql")
			$this->db->mysql_database($this->default[host], $this->default[database],
										$this->default[user], $this->default[password]);

		// Loadup whatever settings we have
		$settings = array();
		/*
		if(file_exists("cache/settings.cache"))
		{
			$settings = @unserialize(@join("", @file("cache/settings.cache")));
			$s = stat("cache/settings.cache");
			if($s[ctime] + 300 < time())
				unlink("cache/settings.cache");
		}
		*/

		// Grab from the DB
		if(@count($settings) == 0)
		{
			$settings = $this->db->getsql("SELECT * FROM adrev_settings");
			#$fp = fopen("cache/settings.cache", "w");
			#fputs($fp, serialize($settings));
			#fclose($fp);
		}
		
		if(@count($settings) > 0)
		{
			foreach($settings as $rec)
			{
				$this->default[adrevenue][stripslashes($rec[name])] = stripslashes($rec[value]); 
			}
			$DEFAULT[adrevenue] = $this->default[adrevenue];
		}										
	
		// Process input
		$this->http = new http;
		$this->input = new input;
		$this->output = new output;
		
		return(0);
	}

}
?>
