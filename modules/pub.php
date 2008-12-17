<?
// 
// AdRevenue Ad Management
// ads.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class pub extends main
{
	function _default()
	{
		
	}
	
	// Show ad code for retrieval
	function ads()
	{
		$this->output->secure();
		$f = $this->input->f;

		// Grab the zones in question
		//$data = $this->db->getsql("SELECT * FROM adrev_zones WHERE aff_percent > '0' AND status='1' ORDER BY name");
		$data = $this->db->getsql("SELECT a.*,b.name as adtypename FROM adrev_zones a, adrev_ad_types b
										WHERE a.style=b.id AND a.aff_percent > '0' AND a.status='1' ORDER BY name");
		$gen = new formgen();
		$gen->startrow("#FFFFFF");
			$gen->column(lib_lang("Click on one of the links below to fetch its ad code."));
		$gen->endrow();
		$tabletop = $gen->gentable("100%", 0, 1, 3, "#FFFFFF");
		
		if(count($data) > 0)
		{
			$gen = new formgen();
			$gen->startrow("#CCCCCC");
			$gen->column("<b>@@Zone@@</b>");
			$gen->column("<b>@@Type@@</b>");
			$gen->column("<b>@@Rate@@</b>","","","","right");
			$gen->column("<b>@@Commission@@</b>","","","","right");
			$gen->column("<b>@@Layout@@</b>");
			$gen->endrow();
			
			foreach($data as $rec)
			{
				$rec[name] = stripslashes($rec[name]);
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";
				$gen->startrow($bgcolor);
				$gen->column("<a href=?section=pub&action=code&f[id]=$rec[id] title=\"Get ad code for $rec[name]\">$rec[name]</a>");	
				$gen->column($rec[rate_type]);
				$gen->column("@@$@@ " . number_format($rec[rate],2),"","","","right");
				$gen->column(number_format($rec[aff_percent],2)."@@%@@","","","","right");
				$gen->column(lib_lang($rec[adtypename]));
				$gen->endrow();
			}
		}
		
		$this->output->title = lib_lang("Get Ad Code");
		$this->output->content = $tabletop . "<p>" . $gen->gentable("500", 0, 1, 3, "#FFFFFF");
		$this->output->display();
		$this->output->printpage();		
		exit;		
	}
	
	function code()
	{
		// Show ad code
		$this->output->secure();
		$f = $this->input->f;

		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id='$f[id]'");
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("Error locating this code!"), "index.php?section=pub&action=ads", 3);
			exit;
		}

		// Show the form		
		$tpl = new XTemplate("templates/zone_pub_code.html");
	
		$domain = $this->default[adrevenue][hostname];
		$url = "$domain$path"."index.php";
		$uid = $_SESSION[user][id];
	
		if(!$z[0][keywords_enable])
		{
			$tpl->assign("URL", "$url?section=serve&id=$f[id]&affid=$uid");
		}
		else
		{
			$tpl->assign("AFFID", $_SESSION[user][id]);
			$tpl->assign("ZONE", $f[id]);
			$tpl->assign("DOMAIN", $domain);			
			$tpl->parse("main.keywords");
			$tpl->assign("URL", "$url?section=serve&id=$f[id]&affid=$uid&keyword=");			
		}
		
		$tpl->assign("ID", $f[id]);
		$tpl->assign("TZ", date("T"));
		
		$tpl->parse("main");
		$this->output->title = lib_lang("Get Ad Code") . ": " . stripslashes($z[0][name]);	
		$this->output->content = $tpl->text("main");
		$this->output->display();
		$this->output->printpage();			
		exit;				
	}
	
	// Show stats
	function stats()
	{
		$this->output->secure();
		$f = $this->input->f;
		$uid = $_SESSION[user][id];
		
		$data = $this->db->getsql("SELECT count(a.id) as num, sum(a.amount) as total, a.adtype, b.zone
									FROM adrev_aff_traffic a, adrev_ads b
									WHERE a.affid='$uid' AND a.adid=b.id
									GROUP BY b.zone,a.affid,a.adtype
									ORDER BY b.zone");

		$table = "There are no stats now";
		$records = array();
		if(count($data) > 0)
		{
			foreach($data as $rec)
			{
				$records[$rec[zone]] = array($rec[adtype], $rec[num], $rec[total]);
			}
			
			if(count($records) > 0)
			{
				$gen = new formgen();
				$gen->startrow("#CCCCCC");
				$gen->column("<b>".lib_lang("Zone")."</b>");
				$gen->column("<b>".lib_lang("Type")."</b>");
				$gen->column("<b>".lib_lang("Amount")."</b>","","","","right");
				$gen->column("<b>".lib_lang("Earned")."</b>","","","","right");
				$gen->column();
				$gen->endrow();
				
				reset($records);
				$earned = 0;
				while(list($zone,$rec) = each($records))
				{
					if($rec[0] == "CPC")
						$type = "Clicks";
					elseif($rec[0] == "CPM")
						$type = "Impressions";
					else
						$type = "Days";
					
					// Get zone name
					$z = $this->db->getsql("SELECT name FROM adrev_zones WHERE id='$zone'");
					$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";				
					$gen->startrow($bgcolor);
					$gen->column(stripslashes($z[0][name]));
					$gen->column($type);
					$gen->column($rec[1],"","","","right");
					$gen->column($rec[2],"","","","right");
					$gen->column("<a href=\"?section=pub&action=download&f[id]=$zone\" title=\"Download to CSV\">@@Download Stats to CSV@@</a>");
					$gen->endrow();	
					
					$earned += $rec[2];
				}
				
				$gen->startrow("#FFFFFF");
				$gen->column();
				$gen->column();
				$gen->column();
				$gen->column("@@$@@ <b>".number_format($earned,2) . "</b>","#CCCCCC","","","right");
				$table = $gen->gentable("100%", 0, 1, 3, "#FFFFFF");
			}
		}
		
		$this->output->title = lib_lang("Transaction History");
		$this->output->content = $table;
		$this->output->display();
		$this->output->printpage();		
		exit;		
	}
	
	// Show History
	function history()
	{
		$this->output->secure();
		$f = $this->input->f;
		$uid = $_SESSION[user][id];
		
		$gen = new formgen();
		$gen->startrow("#CCCCCC");
		$gen->column("<b>".lib_lang("Date")."</b>");
		$gen->column("<b>".lib_lang("Description")."</b>");
		$gen->column("<b>".lib_lang("Amount")."</b>","","","","right");			
		$gen->endrow();
		
		// Get the data
		$data = $this->db->getsql("SELECT * FROM adrev_payments WHERE userid='$uid' ORDER BY date");
		$total = 0;
		if(count($data) > 0)
		{
			foreach($data as $rec)
			{
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";				
				$gen->startrow($bgcolor);
				$gen->column(date("M d Y", $rec[date]));
				$gen->column($rec[description]);
				$gen->column(number_format($rec[amount],2),"","","","right");			
				$gen->endrow();
				$total += $rec[amount];				
			}
			
			$gen->startrow("#FFFFFF");
			$gen->column();
			$gen->column();
			$gen->column(number_format($total,2),"#CCCCCC","","","right");
			$gen->endrow();
			
		}
		
		$this->output->title = lib_lang("Transaction History");
		$this->output->content = $gen->gentable("400", 0, 1, 3, "#FFFFFF");
		$this->output->display();
		$this->output->printpage();		
		exit;				
	}
	
	// Download stats for a zone
	function download()
	{
		$this->output->secure();
		$f = $this->input->f;
		$uid = $_SESSION[user][id];	
		$inlist = "";
		$in = array();
		$ads = $this->db->getsql("SELECT id FROM adrev_ads WHERE zone='$f[id]'");
		if(count($ads) > 0)
		{
			foreach($ads as $rec)
				$in[] = $rec[id];
			$inlist = implode(",", $in);
			$this->db->getcsv("SELECT date,adtype,ip,referer,amount FROM adrev_aff_traffic WHERE affid='$uid' AND adid IN ($inlist) ORDER BY date");
			exit;
		}
		
		$this->output->redirect("@@There are no stats to download@@", "index.php?section=pub&action=stats", 3);
		exit;
	}
}
