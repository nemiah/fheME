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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class mKalenderGUI extends mKalender implements iGUIHTML2 {
	function  __construct() {
		parent::__construct();
		
		$this->customize();
	}
	
	public static $colors = array();
	
	public function getHTML($id){
		$bps = $this->getMyBPSData();
		#$_SESSION["BPS"]->unregisterClass(get_class($this));
		
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
		

		$K = $this->getData($firstDay, $lastDay, isset($bps["KID"]) ? $bps["KID"] : Session::currentUser()->getID());
		
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
			
						
		</script>
		
		<style type=\"text/css\">
		.Day {
			-moz-user-select:none;
			border-left:1px solid #EEE;
			border-bottom:1px solid #EEE;
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
		
		.KalenderUser {
			margin-left:10px;
		}
		
		.KalenderUser div {
			padding:10px;
			padding-top:10px;
			padding-bottom:5px;
			display:inline-block;
			margin-right:20px;
			cursor:pointer;
			min-width:150px;
		}
		
		.cellHeight {
		}
		
		.ui-datepicker {
			width: auto;
		}

		#contentScreen tr:hover {
			background-color:inherit;
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
		
		/*@media only screen and (max-height: 820px) {
			.cellHeight {
				height:55px;
			}
		}*/
		</style>";
		// </editor-fold>

		$BLeft = new Button("Zurück","back", "icon");
		$BLeft->rmePCR("mKalender", "", "setDisplay", $display - 1, "contentManager.loadFrame('contentScreen','mKalender');");
		$BLeft->style("margin-right:10px;");

		$BRight = new Button("Weiter","navigation", "icon");
		$BRight->rmePCR("mKalender", "", "setDisplay", $display + 1, "contentManager.loadFrame('contentScreen','mKalender');");
		$BRight->style("margin-right:10px;");

		$BToday = new Button("Aktuelles Datum","down", "icon");
		$BToday->rmePCR("mKalender", "", "setToday", '', "contentManager.loadFrame('contentScreen','mKalender');");
		$BToday->style("margin-right:10px;");

		$BMonat = new Button("Monat","./ubiquitous/Kalender/month.png", "icon");
		$BMonat->rmePCR("mKalender", "", "setView", "monat", "contentManager.loadFrame('contentScreen','mKalender');");
		$BMonat->style("float:right;margin-right:100px;");
		$BMonat->id("monatButton");

		$BWoche = new Button("Woche","./ubiquitous/Kalender/workweek.png", "icon");
		$BWoche->rmePCR("mKalender", "", "setView", "woche", "contentManager.loadFrame('contentScreen','mKalender');");
		$BWoche->style("float:right;margin-right:10px;");
		$BWoche->id("wocheButton");

		$BTag = new Button("Tag","./ubiquitous/Kalender/day.png", "icon");
		$BTag->rmePCR("mKalender", "", "setView", "tag", "contentManager.loadFrame('contentScreen','mKalender');");
		$BTag->style("float:right;margin-right:10px;");
		$BTag->id("tagButton");



		$ST = new HTMLTable(1);
		$ST->setColClass(1, "");
		#$ST->setTableStyle("width:40px;margin:0px;margin-right:-215px;float:right;/*margin-right:-50px;margin-top:95px;*/");
		
		$newWindow = new Button("Kalender in neuem Fenster öffnen", "new_window", "iconicL");
		$newWindow->style("margin-right:10px;");
		#$newWindow->onclick("contentManager.newSession('Mail', 'mMail');");
		$newWindow->newSession("Mail", Applications::activeApplication(), "mKalender");
		if(Session::physion())
			$newWindow = "";
		
		$reminder = "";
		if(Session::isPluginLoaded("mReminder")){
			$reminder = Reminder::getButton();
			$reminder->style("margin-right:10px;");
		}
		
		$ST->addRow("<div id=\"calendar1stMonth\"></div>");
		$ST->addRow("<div id=\"calendar2ndMonth\"></div>");
		
		
		$BrowserColNum = 10;
		
		$BThis = new Button("", "arrow_down", "iconicG");
		$BThis->style("float:left;margin-top:-6px;margin-right:5px;");
		
		$TCalendars = "<div>";
		#$TCalendars = new HTMLTable(1);
		#$TCalendars->setTableStyle("margin-left:10px;border-collapse:collapse;");
		$Calendars = "<div onclick=\"".OnEvent::reload("Screen", "_mKalenderGUI;KID:".Session::currentUser()->getID())."\">".((!isset($bps["KID"]) OR $bps["KID"] == Session::currentUser()->getID()) ? $BThis : "")." Mein Kalender</div>";
		
		#$TS = new HTMLTable(3);
		#$TS->setColClass(1, "");
		#$TS->setColClass(3, "");
		#$TS->setColClass(2, "");
		#$TS->setColWidth(2, "20");
		#$TS->setColWidth(1, "10");
		$ACS = anyC::get("Userdata", "name", "shareCalendarTo".Session::currentUser()->getID());
		while($Share = $ACS->getNextEntry()){
			#$show = mUserdata::getUDValueS("showCalendarOf".$Share->A("UserID"), "1");
			
			$U = new User($Share->A("UserID"));
			#$I = new HTMLInput("showCalendar".$Share->A("UserID"), "checkbox", $show);
			#$I->onclick(OnEvent::rme($this, "saveShowCalendarOf", array($Share->A("UserID"), $show == "1" ? "0" : "1"), OnEvent::reload("Left")));
			#$TS->addRow(array("", $I, $U->A("name")));
			#self::$colors[$Share->A("UserID")] = KalenderEntry::$bgColors[count(self::$colors)];
			
			#$TS->addCellStyle(1, "background-color:".self::$colors[$Share->A("UserID")].";");
			
			$Calendars .= "<div onclick=\"".OnEvent::reload("Screen", "_mKalenderGUI;KID:".$U->getID())."\">".(($bps["KID"] == $U->getID()) ? $BThis : "")." ".$U->A("name")."</div>";
		}
		
		if($ACS->numLoaded() > 0){
			$TCalendars .= "<div class=\"KalenderUser\">".$Calendars."</div>";
			#$TCalendars->addRow($Calendars);
			#$TCalendars->addCellClass(1, "borderColor1");
			#$TCalendars->addCellStyle(1, "border-top-style:solid;border-top-width:1px;");
			#$TCalendars->addRowClass("backgroundColor0");
			#$TCalendars->addRowClass("KalenderUser");
		}
		$TCalendars .= "</div>";
		
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
		
		if (Session::isPluginLoaded("mxCal")) {
			$xCalButton = xCal::getButton();
			$xCalButton->style("margin-right:10px;");
		}
		
		#if (Session::isPluginLoaded("mJabber")) {
		#	$jabberButton = Jabber::getButton();
		#	$jabberButton->style("margin-right:10px;");
		#}

		$BShare = new Button("Kalender teilen", "fork", "iconicL");
		$BShare->popup("", "Kalender teilen", "mKalender", "-1", "share");
		$BShare->style("margin-right:10px;");
		#$BShare->style("margin-right:10px;");
			
		
		#$AWVButton = new Button("Müllabfuhr-Daten herunterladen", "trash_stroke", "iconicL");
		#$AWVButton->popup("", "Müllabfuhr-Daten", "mKalender", "-1", "downloadTrashData");
		$AWVButton = "";
		
		$ST->addRow($pCalButton.$GoogleButton.$GoogleDLButton);
		
		
		$DBrowser = clone $currentMonth;
		
		$TBrowser = new HTMLTable($BrowserColNum);
		$TBrowser->setTableStyle("width:100%;margin-left:10px;border-collapse:collapse;");
		
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
			#$BLeft->rmePCR("mKalender", "", "setDisplay", $display - 1, "contentManager.loadFrame('contentScreen','mKalender');");
			$TBrowser->addCellEvent($i + 1, "click", OnEvent::rme($this, "setDisplay", $display + $i - 2, OnEvent::reload("Screen")));
			$TBrowser->setColWidth($i, (100/$BrowserColNum)."%");
			$TBrowser->addCellStyle($i + 1, "padding-top:10px;padding-bottom:5px;");
		}
		
		#$bgcolors = array("#FFF", "#F4F4F4");
		
		$html .= "
		<div style=\"width:205px;float:right;margin-right:40px;\">
				<div style=\"padding-top:30px;padding-bottom:15px;padding-left:0px;\">
					$newWindow$BShare$AWVButton$xCalButton$reminder
				</div>
		</div>
		
		<div style=\"margin-right:270px;\">
		<!--<div style=\"\">
			$TBrowser
		</div>-->
		<div id=\"KalenderTitle\" class=\"prettyTitle\">
			
			<span style=\"float:right;\">
				$BLeft$BToday$BRight
			</span>
			$BMonat$BWoche$BTag
			".($ansicht == "monat" ? "Monat ".Util::CLMonthName(date("m",$currentMonth->time())).date(" Y",$currentMonth->time()) : ($ansicht == "woche" ? "".date("W",$currentMonth->time()).". Woche ".date("Y",$currentMonth->time() + 6 * 24 * 3600) : Util::CLDateParserL($currentMonth->time(), "load")))."
		</div>
		</div>
		<div id=\"KalenderAuswahl\">
			$TCalendars
		</div>
		<div style=\"width:205px;float:right;margin-right:40px;\">
			<div style=\"height:23px;\"></div>$ST
		</div>
		<div style=\"margin-right:270px;\">
		
		<table style=\"margin-left:10px;border-spacing: 0px;\" id=\"KalenderTable\">
			<colgroup>";
		for($j = 0; $j < $cols -2; $j++)#
			$html .= "
				<col ".($ansicht == "woche" ? "class=\"backgroundColor".($j % 2 + 2)."\" " : "")." style=\"width:".(100 / $cols)."%;\" />";
			
			$html .= "
				<col style=\"background-color:#F4F4F4;width:".(100 / $cols)."%;\" />
				<col style=\"background-color:#EBEBEB;width:".(100 / $cols)."%;\" />
			</colgroup>";

		if($ansicht != "tag"){
			$html .= "
				<tr>";

			$D2 = clone $Date;
				for($j = 0; $j < $cols; $j++) {
					$html .= "
						<th style=\"border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#EEE;padding-top:10px;\" class=\"backgroundColor0\">".Util::CLWeekdayName(date("w",$D2->time()))."</th>";
					$D2->addDay();
				}
			unset($D2);

			$html .= "
			</tr>";
		}
		for($i = 0; $i < $rows; $i++){
			$html .= "
			<tr class=\"cellHeight\">";
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
								if(substr($time, 0, 2) * 1 != $i)
									continue;
								
								foreach($ev AS $KE)
									$eventsDiv .= $KE->getDayViewHTML($D->time());
							}

					}
					$entry = "
						<div class=\"cellHeight\" style=\"overflow:auto;width:961px;\" id=\"tagDiv\">
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
								if(substr($time, 0, 2) * 1 != $i)
									continue;
								
								foreach($ev AS $KE)
									$eventsDiv .= $KE->getWeekViewHTML($D->time(), $hasMultiDay);
							}

					}
					
					$entry = "
						<div style=\"overflow:auto;height:".(11 * 6 + 22 * 18 + 1 + $hasMultiDay * 22)."px;width:100%;\">
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
				$BD->rmePCR("mKalender", "-1", "setView", array("'tag'","'".$D->time()."'"), "contentManager.loadFrame('contentScreen','mKalender');");
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
						style=\"".($ansicht == "tag" ? "display:none;" : "")."height:21px;padding-top:2px;padding-left:5px;text-align:right;padding-right:5px;\"
						class=\"innerCellTitle\">
						".($ansicht != "tag" ? "<span class=\"dayOptions\">$BD$BN</span>" : "")."
						<span
							style=\"color:grey;\">
							".($ansicht != "tag" ? date("d",$D->time()) : "&nbsp;")."
						</span>
					</div>
					<div style=\"font-size:10px;overflow:auto;".($ansicht == "monat" ? "margin-top:0px;width:100%;" : "")."\" class=\"".($ansicht == "monat" ? "innerCellHeight" : "")."\">$entry</div>
				</td>";
				$D->addDay();
			}
			
			for($j = 0; $j < 7 - $cols; $j++)
				$D->addDay();
			
			$html .= "
			</tr>";
		}
		$html .= "
		</table>
		</div>";

		$nextMonths = new Datum();
		$nextMonths->setToMonth1st();
		$thisMonth = $nextMonths->time();
		$nextMonths->addMonth();
		$nextMonth = $nextMonths->time();
		$html .= OnEvent::script("\$j(function() {
		\$j('#calendar1stMonth').datepicker({ minDate: '".date("d.m.Y", $thisMonth)."'".($currentMonth->time() < $nextMonth ? ",defaultDate: '".date("d.m.Y",$currentMonth->time())."'" : "").", showWeek: true,  showOtherMonths: true, onSelect: function(dateText, inst) { var day = Math.round(+new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay, 0, 1, 0)/1000); ".OnEvent::rme($this, "setView", array("'tag'", "day"), "function(){ ".OnEvent::reload("Screen")." }")." } });
			
		\$j('.KalenderUser div[class!=backgroundColor1]').hover(function(){ \$j(this).addClass('backgroundColor2'); }, function(){ \$j(this).removeClass('backgroundColor2'); });
		fitKalender();
			
		\$j(window).resize(function() {
			fitKalender();
		});
		
		});
		
		function fitKalender(){
			if(!\$j('#KalenderTitle').length)
				return;
				
			var height = ((contentManager.maxHeight() - \$j('#KalenderTable tr:first th').parent().outerHeight() - \$j('#KalenderAuswahl').outerHeight() - \$j('#KalenderTitle').outerHeight()) / (\$j('#KalenderTable tr').length - ".($ansicht == "monat" ? "1" : "0")."));
			\$j('.cellHeight').css('height', height+'px');
			\$j('.innerCellHeight').css('height', (height - \$j('.innerCellTitle:visible').outerHeight())+'px');
			
			if(\$j('#tagDiv').length) {
				\$j('#tagDiv').css('width', \$j('#KalenderTable tr').width()+'px');
				\$j('#tagDiv').animate({scrollTop: 7*40}, 0);
				var pos = \$j('#tagDiv').offset();
				pos.position = 'absolute';

				\$j('#tagDiv').css(pos)
			}
		}")."
		<style type=\"text/css\">
			".($currentMonth->time() < $thisMonth ? "#calendar1stMonth .ui-state-default { border: 1px solid #D3D3D3; background-color:transparent; }" : "")."
			".($currentMonth->time() < $nextMonth ? "#calendar2ndMonth .ui-state-default { border: 1px solid #D3D3D3; background-color:transparent; }" : "")."
			.ui-datepicker-week-col { color:grey; text-align:left; }
			tr td.ui-datepicker-week-col {text-align:left;font-size:10px; }
			/*.ui-datepicker-week-end { background-color:#DDD; }*/
		</style>";

		$html .= OnEvent::script("\$j(function() {
		\$j('#calendar2ndMonth').datepicker({ minDate: '".date("d.m.Y",$nextMonth)."'".($currentMonth->time() >= $nextMonth ? ", defaultDate: '".date("d.m.Y",$currentMonth->time())."'" : "").", showWeek: true, showOtherMonths: true,  onSelect: function(dateText, inst) { var day = Math.round(+new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay, 0, 1, 0)/1000); ".OnEvent::rme($this, "setView", array("'tag'", "day"), "function(){ ".OnEvent::reload("Screen")." }")." } });
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
		
		#$_SESSION["BPS"]->registerClass(get_class($this));
		#$_SESSION["BPS"]->setACProperty("noReloadRight","true");
		
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
		
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemKalenderGUI\"></span><p>Kalender</p></div><div style=\"padding:10px;padding-left:0px;\">";
		
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
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mKalenderGUI", "Kalender", 130);
		$P->updateInterval(900);
		
		return $P;
	}
	
	public function getRepeatable($targetClass, $targetClassID, $targetClassMethod){
		$C = new $targetClass($targetClassID);
		echo $C->$targetClassMethod($targetClassID);
	}
	
	public function getInviteForm($targetClass, $targetClassId, $targetClassMethod) {
		$class = new $targetClass($targetClassId);
		echo $class->$targetClassMethod($targetClassId);
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
		
		#$UD = new mUserdata();
		#$UD->getAsArray("shareCalendar");
		
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
	
	public function saveShowCalendarOf($UserID, $value){
		mUserdata::setUserdataS("showCalendarOf".$UserID, $value);
	}
	
	public function downloadTrashData(){
		$json = file_get_contents("http://www.awv-nordschwaben.de/WebService/AWVService.svc/getData/00000000-0000-0000-0000-000000001190");

		echo "<pre style=\"font-size:10px;max-height:400px;overflow:auto;\">";
		$data = json_decode($json);
		foreach($data->calendar AS $day){
			if($day->fr == "")
				continue;
			
			if($day->dt < date("Ymd"))
				continue;
			
			print_r($day);
			
			
			$tag = Util::parseDate("de_DE", substr($day->dt, 6).".".substr($day->dt, 4, 2).".".substr($day->dt, 0, 4));

			$name = "";
			foreach($day->fr AS $T){
				if($T == "PT")
					$name .= ($name != "" ? ", " : "")."Papiertonne";
				
				if($T == "RM")
					$name .= ($name != "" ? ", " : "")."Restmüll";
				
				if($T == "GS")
					$name .= ($name != "" ? ", " : "")."Gelber Sack";
				
				if($T == "BT")
					$name .= ($name != "" ? ", " : "")."Biotonne";
			}
			
			if($name == "")
				continue;
			
			$F = new Factory("Todo");
			$F->sA("TodoName", $name);
			$F->sA("TodoFromDay", $tag);
			$F->sA("TodoFromTime", "32400");
			$F->sA("TodoTillDay", $tag);
			$F->sA("TodoTillTime", "36000");
			$F->sA("TodoUserID", "-1");
			$F->sA("TodoClass", "Kalender");
			$F->sA("TodoClassID", "-1");
			$F->sA("TodoType", "2");
			$F->sA("TodoRemind", "-1");
			if($F->exists())
				continue;
			
			$F->store();
		}
		echo "</pre>";
	}
}
?>