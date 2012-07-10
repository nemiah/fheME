<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mKalenderGUI extends mKalender implements iGUIHTML2 {
	function  __construct() {
		parent::__construct();
		
		$this->customize();
	}
	public function getHTML($id){

		$bps = $this->getMyBPSData();
		$_SESSION["BPS"]->unregisterClass(get_class($this));
		
		$ansicht = mUserdata::getUDValueS("KalenderAnsicht", "monat");
		#$ansicht = $ansicht->getUDValue("KalenderAnsicht");
		#if($ansicht == null) $ansicht = "monat";
		
		$display = mUserdata::getUDValueS("KalenderDisplay".ucfirst($ansicht), "0");
		#$display = $display->getUDValue("KalenderDisplay".ucfirst($ansicht));
		#if($display == null) $display = "0";
		
		switch($ansicht){
			case "monat":
				$Date = new Datum(Datum::parseGerDate("1.".date("m.Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addMonth();
					else $Date->subMonth();

			break;
			
			case "woche":
				$Date = new Datum(Datum::parseGerDate(date("d.m.Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addWeek();
					else $Date->subWeek();
			break;
			
			case "tag":
				$Date = new Datum(Datum::parseGerDate(date("d.m.Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addDay();
					else $Date->subDay();
			break;
		}

		$currentMonth = clone $Date;

		if($ansicht != "tag"){
			while(date("w",$Date->time()) > 1)
				$Date->subDay();

			if(date("w",$Date->time()) == 0)
				$Date->addDay();
		}
		$firstDay = $Date->time();
		
		
		$D = clone $Date;
		$rows = 5;
		if($ansicht == "woche")
			$rows = 1;
		
		$cols = 7;
		if($ansicht == "tag") {
			$cols = 1;
			$rows = 1;
		}
		
		for($i = 0; $i < $rows; $i++){
			if($i > 0 AND date("m.Y",$D->time()) != date("m.Y",$currentMonth->time())) break;
			
			for($j = 0; $j < 7; $j++)
				$D->addDay();
		}
		$D->subDay();
		$lastDay = $D->time();
		

		$K = $this->getData($firstDay, $lastDay);
		
		$D = clone $Date;
		
		// <editor-fold defaultstate="collapsed" desc="styles">
		$html = "
		<script type=\"text/javascript\">";
			
			if($ansicht != "monat") $html .= "
			\$j('#monatButton').animate({opacity: 0.5});";
			
			if($ansicht != "woche") $html .= "
			\$j('#wocheButton').animate({opacity: 0.5});";
			
			if($ansicht != "tag") $html .= "
			\$j('#tagButton').animate({opacity: 0.5});";

			$html .= "
			
						
			if(\$j('#tagDiv').length) {
				\$j('#tagDiv').animate({scrollTop: 7*40}, 0);
				var pos = \$j('#tagDiv').offset();
				pos.position = 'absolute';

				\$j('#tagDiv').css(pos)
			}
		</script>
		
		<style type=\"text/css\">
		.Day {
			-moz-user-select:none;
		}
		
		/*.Day:hover {
			border-style:solid;
			border-width:1px;
			padding:2px;
		}*/

		.dayOptions {
			display:none;
		}

		.Day:hover .dayOptions {
			display:inline;
		}

		.Termin {
			position:relative;
			left:44px;
			cursor:pointer;
			width:150px;
			float:left;
			border-style:solid;
			border-width:1px;
			margin-right:3px;
		}
		
		.KalenderButton {
			opacity:0.5;
		}
		
		.KalenderButton:hover {
			opacity:1;
		}
		
		.cellHeight {
			height:100px;
		}
		
		#calendar1stMonth .ui-datepicker-prev, #calendar1stMonth .ui-datepicker-next/*,
		#calendar2ndMonth .ui-datepicker-prev, #calendar2ndMonth .ui-datepicker-next */{
			display:none;
		}
		
		#calendar1stMonth .ui-widget-content,
		#calendar2ndMonth .ui-widget-content, 
		#calendar2ndMonth .ui-widget-content .ui-state-default,
		#calendar1stMonth .ui-widget-content .ui-state-default {
			border:0px;
		}
		
		#calendar1stMonth .ui-datepicker-header,
		#calendar2ndMonth .ui-datepicker-header {
			border:0px;
		}
		
		@media only screen and (max-height: 820px) {
			.cellHeight {
				height:55px;
			}
		}
		</style>";
		// </editor-fold>

		$BLeft = new Button("Zurück","back", "icon");
		$BLeft->rmePCR("mKalender", "", "setDisplay", $display - 1, "contentManager.loadFrame('contentLeft','mKalender');");
		$BLeft->style("margin-right:10px;");

		$BRight = new Button("Weiter","navigation", "icon");
		$BRight->rmePCR("mKalender", "", "setDisplay", $display + 1, "contentManager.loadFrame('contentLeft','mKalender');");
		$BRight->style("margin-right:10px;");

		$BToday = new Button("Aktuelles Datum","down", "icon");
		$BToday->rmePCR("mKalender", "", "setToday", '', "contentManager.loadFrame('contentLeft','mKalender');");
		$BToday->style("margin-right:10px;");

		$BMonat = new Button("Monat","./ubiquitous/Kalender/month.png", "icon");
		$BMonat->rmePCR("mKalender", "", "setView", "monat", "contentManager.loadFrame('contentLeft','mKalender');");
		$BMonat->style("float:right;margin-right:100px;");
		$BMonat->id("monatButton");

		$BWoche = new Button("Woche","./ubiquitous/Kalender/workweek.png", "icon");
		$BWoche->rmePCR("mKalender", "", "setView", "woche", "contentManager.loadFrame('contentLeft','mKalender');");
		$BWoche->style("float:right;margin-right:10px;");
		$BWoche->id("wocheButton");

		$BTag = new Button("Tag","./ubiquitous/Kalender/day.png", "icon");
		$BTag->rmePCR("mKalender", "", "setView", "tag", "contentManager.loadFrame('contentLeft','mKalender');");
		$BTag->style("float:right;margin-right:10px;");
		$BTag->id("tagButton");



		$ST = new HTMLSideTable("right");
		$ST->setTableStyle("width:40px;margin:0px;margin-right:-215px;float:right;/*margin-right:-50px;margin-top:95px;*/");
		
		$newWindow = new Button("Kalender in neuem Fenster öffnen", "newWindow", "icon");
		$newWindow->style("margin-right:10px;");
		#$newWindow->onclick("contentManager.newSession('Mail', 'mMail');");
		$newWindow->newSession("Mail", Applications::activeApplication(), "mKalender");
		if(Session::physion())
			$newWindow = "";
		
		$ST->addRow("<div id=\"calendar1stMonth\"></div>");
		$ST->addRow("<div id=\"calendar2ndMonth\"></div>");
		
		$pCalButton = "";
		if(Session::isPluginLoaded("mpCal")){
			$pCalButton = pCal::getTBButton();
			$pCalButton->type("icon");
			$pCalButton->style("margin-right:10px;");
			#$ST->addRow($pCalButton);
		}
		
		$GoogleButton = "";
		$GoogleDLButton = "";
		if(Session::isPluginLoaded("mGoogle")){
			$GoogleButton = LoginData::getButtonU("GoogleAccountUserPass", "Google-Daten bearbeiten", "./ubiquitous/Google/google.png");
			$GoogleButton->type("icon");
			$GoogleButton->style("margin-right:10px;");
			

			$GoogleDLButton = new Button("Daten herunterladen", "./ubiquitous/Google/googleDL.png", "icon");
			$GoogleDLButton->popup("", "Daten herunterladen", "Google", "-1", "syncByDateRange", array("'".date("Y-m-d", $firstDay)."'", "'".date("Y-m-d", $lastDay)."'"));
			$GoogleDLButton->style("margin-right:10px;");
		}
		

		$BShare = new Button("Kalender teilen", "./ubiquitous/Kalender/share.png", "icon");
		$BShare->popup("", "Kalender teilen", "mKalender", "-1", "share");
		$BShare->style("margin-right:10px;");
			
		$ST->addRow($newWindow.$GoogleButton.$GoogleDLButton.$pCalButton.$BShare);
		
		
		$DBrowser = clone $currentMonth;	
		#$BrowserColNum = 13;
		#if($ansicht == "monat")
			$BrowserColNum = 10;
		
		$TBrowser = new HTMLTable($BrowserColNum);
		$TBrowser->setTableStyle("width:964px;margin-left:10px;border-collapse:collapse;");
		
		if($ansicht == "tag"){
			$DBrowser->subDay();
			$DBrowser->subDay();
		}
		
		if($ansicht == "woche"){
			$DBrowser->subWeek();
			$DBrowser->subWeek();
		}
		
		if($ansicht == "monat"){
			$DBrowser->subMonth();
			$DBrowser->subMonth();
		}
		
		$CBrowser = 0;
		
		$BrowserCols = array();
		for($i = 0; $i < $BrowserColNum; $i++){
			if($ansicht == "tag")
				$BrowserCols[] = (date("w", $DBrowser->time()) == 1 ? "<span style=\"float:left;margin-left:15px;font-weight:bold;\">".date("W", $DBrowser->time())."</span>" : "")."<small style=\"color:#444;\">".date("d.m.", $DBrowser->time())."</small> ".substr(Util::CLWeekdayName(date("w",$DBrowser->time())), 0, 2);
			
			if($ansicht == "woche")
				$BrowserCols[] = "<small style=\"color:#444;\">KW</small> ".date("W", $DBrowser->time());
			
			if($ansicht == "monat")
				$BrowserCols[] = Util::CLMonthName(date("m", $DBrowser->time()))."<small style=\"color:grey;\">".((date("Y", $DBrowser->time()) != date("Y", time()))  ? " ".date("y", $DBrowser->time()) : "")."</small>";
			
			
			if($DBrowser->time() == $currentMonth->time())
				$CBrowser = $i + 1;
			
			
			if($ansicht == "tag")
				$DBrowser->addDay();
			
			if($ansicht == "woche")
				$DBrowser->addWeek();
			
			if($ansicht == "monat")
				$DBrowser->addMonth();
		}	
		
		$TBrowser->addRow($BrowserCols);
		$TBrowser->addRowClass("backgroundColor0");
		if($ansicht == "tag" OR $ansicht == "woche")
			$TBrowser->addRowStyle("text-align:right;");
		
		$TBrowser->addRowStyle("cursor:pointer;");
		$TBrowser->addCellStyle($CBrowser, "background-color:#CCC;");
		for($i = 0; $i < $BrowserColNum; $i++){
			#$BLeft->rmePCR("mKalender", "", "setDisplay", $display - 1, "contentManager.loadFrame('contentLeft','mKalender');");
			$TBrowser->addCellEvent($i + 1, "click", OnEvent::rme($this, "setDisplay", $display + $i - 2, OnEvent::reload("Left")));
			$TBrowser->setColWidth($i, (100/$BrowserColNum)."%");
			$TBrowser->addCellStyle($i + 1, "padding-top:10px;padding-bottom:5px;");
		}
		
		$colWidth = "137px";
		
		$html .= "
		<div style=\"width:984px;\">
			$TBrowser
			$ST
		</div>
		<div class=\"Tab backgroundColor1\" style=\"width:964px;margin-top:0px;\">
			<span style=\"float:right;\">
				$BLeft$BToday$BRight
			</span>
			$BMonat$BWoche$BTag
			<p><b>Kalender für ".($ansicht == "monat" ? "den Monat ".Util::CLMonthName(date("m",$currentMonth->time())).date(" Y",$currentMonth->time()) : ($ansicht == "woche" ? "die ".date("W",$currentMonth->time()).". Woche ".date("Y",$currentMonth->time() + 6 * 24 * 3600) : Util::CLDateParserL($currentMonth->time(), "load")))."</b></p>
		</div>
		
		<table style=\"margin-left:10px;\">
			<colgroup>";
		for($j = 0; $j < $cols -2; $j++)#
			$html .= "
				<col class=\"backgroundColor".($j % 2 + 2)."\" style=\"width:$colWidth;\" />";
			
			$html .= "
				<col style=\"background-color:#EBEBEB;\" />
				<col style=\"background-color:#DDD;\" />
			</colgroup>";

		if($ansicht != "tag"){
			$html .= "
				<tr style=\"height:40px;\">";

			$D2 = clone $Date;
				for($j = 0; $j < $cols; $j++) {
					$html .= "
						<th style=\"border-bottom-width:1px;border-bottom-style:solid;\" class=\"borderColor1 backgroundColor0\">".Util::CLWeekdayName(date("w",$D2->time()))."</th>";
					$D2->addDay();
				}
			unset($D2);

			$html .= "
			</tr>";
		}
		for($i = 0; $i < $rows; $i++){
			$html .= "
			<tr ".($ansicht == "monat" ? "class=\"cellHeight\"" : ($ansicht == "tag" ? "style=\"height:584px;" : ""))."\">";
			for($j = 0; $j < $cols; $j++){
				
				$entry = "";
				
				$events = $K->getEventsOnDay(date("dmY", $D->time()));
				$holidays = $K->getHolidaysOnDay(date("dmY", $D->time()));
				
				$hasMultiDay = $K->hasMultiDayEvents($firstDay, $lastDay);
				
				if($ansicht == "tag"){
					#$dayContent = "";

					$dayDivs = "";
					for($i = 0; $i < 24; $i++){
						$dayDivs .= "
						<div style=\"height:40px;z-index:10;\" class=\"backgroundColor".($i % 2 == 0 ? "3" : "2")."\">
							";
						
						$BN = "";
						if(Session::isPluginLoaded("mTodo")){
							$BN = new Button("Neuer Termin", "./ubiquitous/Kalender/addToDo.png", "icon");
							$BN->className("KalenderButton");
							$BN->popup("", "Neuer Termin", "mKalender", "-1", "newTodo", array("-1", $D->time(), "'Kalender'", "-1", $i), "", "Kalender.popupOptions");
							$BN->style("float:left;margin-left:5px;margin-top:5px;");
							
						}
						
						$dayDivs .= "
							
							<div class=\"borderColor1\" style=\"height:35px;border-right-width:1px;border-right-style:dotted;float:left;width:40px;padding-top:5px;padding-right:5px;font-weight:bold;text-align:right;color:grey;\">".($i < 10 ? "0" : "")."$i:00</div>
						$BN</div>";
					}
					
					$eventsDiv = "";
					for($i = 0; $i < 24; $i++){
						if(count($events) > 0)
							foreach($events AS $time => $ev){
								if(substr($time, 0, 2) * 1 == $i)
									foreach($ev AS $KE)
										$eventsDiv .= $KE->getDayViewHTML($D->time());
							}

					}
					$entry = "
						<div style=\"overflow:auto;height:560px;width:961px;\" id=\"tagDiv\">
							<div style=\"height:961px;\">
							$dayDivs
							</div>
							<div style=\"margin-top:-961px;\">
								$eventsDiv
							</div>
						</div>";

				} elseif($ansicht == "woche"){
					$dayDivs = "";
					
					for($k = 0; $k < $hasMultiDay; $k++)
						$dayDivs .= "<div style=\"height:22px;z-index:10;\" class=\"borderColor0\"></div>";

					for($k = 0; $k < 24; $k++){
						$bgColor = "";
						if($k < 7 OR $k > 19)
							$bgColor = "background-color:rgba(255, 255, 255, 0.3)";
						
						$border = "border-top:1px dotted white;";
						if($k == 23)
							$border .= "border-bottom:1px dotted white;";
						
						if($k == 12)
							$border = "border-top:1px solid white;";
						
						$dayDivs .= "
						<div style=\"height:".($k < 6 ? "10" : "21")."px;z-index:10;$border$bgColor\" class=\"borderColor0\">
							";
						if($k > 5 AND $k < 21 AND $k % 2 == 0 AND $j % 2 == 1)
							$dayDivs .= "
								<div class=\"borderColor1\" style=\"color:#777;padding-left:3px;\">".($k < 10 ? "0" : "")."$k</div>";
						
						$dayDivs .= "
							
						</div>";
					}
					
					$eventsDiv = "";
					for($i = 0; $i < 24; $i++){
						if(count($events) > 0)
							foreach($events AS $time => $ev){
								if(substr($time, 0, 2) * 1 == $i)
									foreach($ev AS $KE)
										$eventsDiv .= $KE->getWeekViewHTML($D->time(), $hasMultiDay);
							}

					}
					
					$entry = "
						<div style=\"overflow:auto;height:".(11 * 6 + 22 * 18 + 1 + $hasMultiDay * 22)."px;width:$colWidth;;\">
							<div style=\"height:".(11 * 6 + 22 * 18 + 1)."px;\">
								$dayDivs
							</div>
							<div style=\"margin-top:-".(11 * 6 + 22 * 18 + 1)."px;\">
								$eventsDiv
							</div>
						</div>";
				} else {
					if($events != null)
						foreach($events AS $time => $ev){
							foreach($ev AS $v)
								$entry .= $v->getMinimal($D->time());
						}

					if($holidays != null)
						foreach($holidays AS $ev)
							foreach($ev AS $v)
								$entry .= $v->getMinimal($D->time());
						
				}

				$BD = new Button("Tagesansicht", "./ubiquitous/Kalender/showDetails.png");
				$BD->type("icon");
				$BD->rmePCR("mKalender", "-1", "setView", array("'tag'","'".$D->time()."'"), "contentManager.loadFrame('contentLeft','mKalender');");
				$BD->style("float:left;");

				$BN = "";
				if(Session::isPluginLoaded("mTodo")){
					$BN = new Button("Neuer Termin", "./ubiquitous/Kalender/addToDo.png");
					$BN->type("icon");
					$BN->popup("", "Neuer Termin", "mKalender", "-1", "newTodo", array("-1", $D->time(), "'Kalender'", "-1"), "", "Kalender.popupOptions");
					$BN->style("float:left;margin-left:5px;");
				}
				#".((date("m.Y",$D->time()) != date("m.Y",$currentMonth->time())) ? "color:grey;" : "")."
				if($j < $cols) $html .= "
				<td
					style=\"vertical-align:top;padding:0px;\"
					class=\"".((date("d.m.Y",$D->time()) == date("d.m.Y") AND $ansicht != "tag")? "backgroundColor1" : "")." Day borderColor1\">
					<div
						style=\"height:21px;padding-top:2px;padding-left:5px;text-align:right;padding-right:5px;\"
						class=\"".($ansicht == "tag" ? "backgroundColor0" : "")."\">
						".($ansicht != "tag" ? "<span class=\"dayOptions\">$BD$BN</span>" : "")."
						<span
							style=\"color:grey;\">
							".($ansicht != "tag" ? date("d",$D->time()) : "&nbsp;")."
						</span>
					</div>
					<div style=\"font-size:10px;overflow:auto;".($ansicht == "monat" ? "margin-top:0px;width:$colWidth;" : "")."\" class=\"".($ansicht == "monat" ? "cellHeight" : "")."\">$entry</div>
				</td>";
				$D->addDay();
			}
			
			for($j = 0; $j < 7 - $cols; $j++)
				$D->addDay();
			
			$html .= "
			</tr>";
		}
		$html .= "
		</table>";

		$nextMonths = new Datum();
		$nextMonths->setToMonth1st();
		$thisMonth = $nextMonths->time();
		$nextMonths->addMonth();
		$nextMonth = $nextMonths->time();
		$html .= OnEvent::script("\$j(function() {
		\$j('#calendar1stMonth').datepicker({ minDate: '".date("d.m.Y", $thisMonth)."'".($currentMonth->time() < $nextMonth ? ",defaultDate: '".date("d.m.Y",$currentMonth->time())."'" : "").", showWeek: true,  showOtherMonths: true, onSelect: function(dateText, inst) { var day = Math.round(+new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay, 0, 1, 0)/1000); ".OnEvent::rme($this, "setView", array("'tag'", "day"), "function(){ ".OnEvent::reload("Left")." }")." } });
	});")."
		<style type=\"text/css\">
			".($currentMonth->time() < $thisMonth ? "#calendar1stMonth .ui-state-default { border: 1px solid #D3D3D3; background-color:transparent; }" : "")."
			".($currentMonth->time() < $nextMonth ? "#calendar2ndMonth .ui-state-default { border: 1px solid #D3D3D3; background-color:transparent; }" : "")."
			.ui-datepicker-week-col { color:grey; text-align:left; }
			tr td.ui-datepicker-week-col {text-align:left;font-size:10px; }
			/*.ui-datepicker-week-end { background-color:#DDD; }*/
		</style>";

		$html .= OnEvent::script("\$j(function() {
		\$j('#calendar2ndMonth').datepicker({ minDate: '".date("d.m.Y",$nextMonth)."'".($currentMonth->time() >= $nextMonth ? ", defaultDate: '".date("d.m.Y",$currentMonth->time())."'" : "").", showWeek: true, showOtherMonths: true,  onSelect: function(dateText, inst) { var day = Math.round(+new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay, 0, 1, 0)/1000); ".OnEvent::rme($this, "setView", array("'tag'", "day"), "function(){ ".OnEvent::reload("Left")." }")." } });
	});");
		
		return $html;
	}
	
	function setView($to, $spec = null){
		$U = new mUserdata();
		$U->setUserdata("KalenderAnsicht",$to);

		if($to == "tag" AND $spec != null){
			$days = ceil(($spec - time()) / (3600 * 24));
			$this->setDisplay($days, $to);
		}
	}
	
	function setToday(){
		$this->setDisplay(0);
	}
	
	function setDisplay($to, $type = null){
		if($type == null){
			$ansicht = new mUserdata();
			$ansicht = $ansicht->getUDValue("KalenderAnsicht");
			if($ansicht == null) $ansicht = "monat";
			$type = $ansicht;
		}
		
		$_SESSION["BPS"]->registerClass(get_class($this));
		$_SESSION["BPS"]->setACProperty("noReloadRight","true");
		
		$U = new mUserdata();
		$U->setUserdata("KalenderDisplay".ucfirst($type),$to);
	}

	function getInfo($className, $classID, $day){
		$C = new $className($classID);
		echo $C->getCalendarDetails($className, $classID)->getInfo($day);
	}
	
	/*
	 * proxy for consistent popups
	 */
	public function newTodo($id, $date = null, $targetClass = null, $targetClassID = null, $time = null, $description = null, $location = null){
		$mT = new mTodoGUI();
		$mT->editInPopup($id, $date, $targetClass, $targetClassID, $time, $description, $location);
	}
	
	/*
	 * proxy for consistent popups
	 */
	public function editInPopup($className, $classID, $method){
		#"'".$this->className."'", $this->classID, "'{$this->editable[0]}'"
		$C = new $className(-1);
		$C->$method($classID);
	}
	
	public function getOverviewContent($echo = true){
		$time = mktime(0, 0, 1);
		$Datum = new Datum($time);
		$Datum->addMonth();
		$lastTime = $Datum->time();
		$Datum->subMonth();
		$Woche = date("W");
		$K = $this->getData($time, $lastTime);
		
		$hasEvent = array();
		
		$html = "<div class=\"Tab backgroundColor1\"><span class=\"lastUpdate\" id=\"lastUpdatemKalenderGUI\"></span><p>Kalender</p></div><div style=\"padding:10px;padding-left:0px;\">";
		
		$html .= "<div style=\"width:25px;float:left;margin-right:5px;color:grey;font-size:11px;\">%%SMALLCALCONTENT%%</div>";
		
		$html .= "<div style=\"border-bottom-width:1px;border-bottom-style:dashed;padding:3px;margin-left:30px;\" class=\"borderColor1\">Heute</div>";
		$list = new HTMLList();
		$list->addListStyle("list-style-type:none;margin-left:30px;");
		
		$events = $K->getEventsOnDay(date("dmY", $Datum->time()));
		if($events != null AND count($events) > 0)
			foreach($events AS $ev)
				foreach($ev AS $KE){
					$hasEvent[date("d", $K->parseDay($KE->getDay()))] = true;
					
					$B = new Button("", $KE->icon(), "icon");
					$B->style("float:left;margin-right:5px;margin-bottom:10px;");

					$list->addItem("$B<b style=\"font-size:15px;\">".$KE->title()."</b><br /><small>".Datum::getGerWeekArray(date("w", $K->parseDay($KE->getDay()))).", ".Util::CLDateParser($K->parseDay($KE->getDay()))." ".Util::CLTimeParser($K->parseTime($KE->getTime()))."</small>");
				}
		if(count($events) == 0)
			$list->addItem("<span style=\"color:grey;\">Kein Eintrag</span>");
				
		$html .= $list;
		$Datum->addDay();
		
		$html .= "<div style=\"border-bottom-width:1px;border-bottom-style:dashed;padding:3px;margin-top:15px;margin-left:30px;\" class=\"borderColor1\">Morgen</div>";
		$list = new HTMLList();
		$list->addListStyle("list-style-type:none;margin-left:30px;");
		
		$events = $K->getEventsOnDay(date("dmY", $Datum->time()));
		if($events != null AND count($events) > 0)
			foreach($events AS $ev)
				foreach($ev AS $KE){
					$hasEvent[date("d", $K->parseDay($KE->getDay()))] = true;
					
					$B = new Button("", $KE->icon(), "icon");
					$B->style("float:left;margin-right:5px;margin-bottom:10px;");

					$list->addItem("$B<b style=\"font-size:15px;\">".$KE->title()."</b><br /><small>".Datum::getGerWeekArray(date("w", $K->parseDay($KE->getDay()))).", ".Util::CLDateParser($K->parseDay($KE->getDay()))." ".Util::CLTimeParser($K->parseTime($KE->getTime()))."</small>");
				}
		if(count($events) == 0)
			$list->addItem("<span style=\"color:grey;\">Kein Eintrag</span>");
		
		$html .= $list;
		$Datum->addDay();
		
		$html .= "<div style=\"border-bottom-width:1px;border-bottom-style:dashed;padding:3px;margin-top:15px;margin-left:30px;\" class=\"borderColor1\">Später</div>";
		$list = new HTMLList();
		$list->addListStyle("list-style-type:none;margin-left:30px;");
		$c = 0;
		while($Datum->time() < $lastTime){
			
			$events = $K->getEventsOnDay(date("dmY", $Datum->time()));
			if($events != null AND count($events) > 0)
				foreach($events AS $ev)
					foreach($ev AS $KE){
						$hasEvent[date("d", $K->parseDay($KE->getDay()))] = true;
						
						$B = new Button("", $KE->icon(), "icon");
						$B->style("float:left;margin-right:5px;margin-bottom:10px;");

						$list->addItem("$B<b style=\"font-size:15px;\">".$KE->title()."</b><br /><small>".Datum::getGerWeekArray(date("w", $K->parseDay($KE->getDay()))).", ".Util::CLDateParser($K->parseDay($KE->getDay()))."</small>");
						if(date("W", $K->parseDay($KE->getDay())) > $Woche + 1)
							$list->addItemStyle ("color:grey;");
						$c++;
					}
			
			$Datum->addDay();
		}
		if($c == 0)
			$list->addItem("<span style=\"color:grey;\">Kein Eintrag</span>");
		
		$html .= $list."</div>";
		
		
		$smallCal = "";
		$DatumC = clone $Datum;
		for($i = 0; $i < 14; $i++){
			$smallCal .= "<div style=\"padding:5px;text-align:right;".(isset($hasEvent[date("d", $DatumC->time())]) ? "color:black;" : "")."\" ".(isset($hasEvent[date("d", $DatumC->time())]) ? "class=\"backgroundColor3\"" : "")."\">".date("d", $DatumC->time())."</div>";
			
			$DatumC->addDay();
		}
		
		$html = str_replace("%%SMALLCALCONTENT%%", $smallCal, $html);
		
		if($echo)
			echo $html;
		
		return $html;
	}
	
	public function getRepeatable($targetClass, $targetClassID, $targetClassMethod){
		$C = new $targetClass($targetClassID);
		echo $C->$targetClassMethod($targetClassID);
	}
	
	public function share(){
		$fields = array();
		$US = Users::getUsers();
		while($U = $US->getNextEntry()){
			if($U->getID() == Session::currentUser()->getID())
				continue;
			
			$fields[] = "User".$U->getID();
		}
		$US->resetPointer();
		
		echo "<p>In diesem Fenster bestimmen Sie andere Systembenutzer, die auf Ihren Kalender zugreifen können. Falls es andere Systembenutzer gibt.</p>";
		
		if(count($fields) == 0)
			return;
		
		$F = new HTMLForm("shareMailBox", $fields);
		$F->getTable()->setColWidth(1, 120);
		
		#$F->setValue("MailKontoID", $MailKontoID);
		#$F->setType("MailKontoID", "hidden");
		
		while($U = $US->getNextEntry()){
			$F->setLabel("User".$U->getID(), $U->A("name"));
			$F->setType("User".$U->getID(), "select", null, array("none" => "kein Zugriff", "read" => "lesen", "read.create" => "lesen und erstellen"));
		}
		
		$UD = new mUserdata();
		$shareWith = $UD->getAsArray("shareCalendar");
		
		foreach($shareWith AS $v => $n){
			$v = str_replace("shareCalendarTo", "", $v);
			
			$F->setValue("User$v", $n);
		}
		
		$F->setSaveRMEPCR("Speichern", "", "mKalender", "-1", "saveShare", OnEvent::closePopup("mKalender"));
		
		echo $F;
	}
	
	public function saveShare(){
		$args = func_get_args();
		
		$UD = new mUserdata();
		$UD->getAsArray("shareCalendar");
		
		$i = 0;
		$US = Users::getUsers();
		while($U = $US->getNextEntry()){
			if($U->getID() == Session::currentUser()->getID())
				continue;
			
			if($args[$i] != "none")
				mUserdata::setUserdataS("shareCalendarTo".$U->getID(), $args[$i], "shareCalendar");
			else{
				$UD = new mUserdata();
				$UD->delUserdata("shareCalendarTo".$U->getID());
			}
			
			$i++;
		}
	}
}
?>