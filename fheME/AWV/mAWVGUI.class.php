<?php
/**
 *  This file is part of lightCRM.

 *  lightCRM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  lightCRM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2015, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class mAWVGUI extends UnpersistentClass implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$B = new Button("AWV für\nExport", "./lightCRM/AWV/AWV.png");
		$B->style("float:right;margin-right:10px;");
		$B->windowRme("mAWV", "-1", "downloadTrashExport");
		echo $B;
		
		$B = new Button("AWV in\nKalender", "./lightCRM/AWV/AWV.png");
		$B->popup("", "Müllabfuhr-Daten", "mAWV", "-1", "downloadTrashData");
		
		echo $B;
		
	}

	public function downloadTrashExport(){
		$json = file_get_contents("http://www.awv-nordschwaben.de/WebService/AWVService.svc/getData/00000000-0000-0000-0000-000000001190");

		header("Content-Type: text/plain");

		header("Content-Disposition: attachment; filename=\"Tonnen.txt\"");
		
		#echo "<pre style=\"font-size:10px;\">";
		$data = json_decode($json);
		$zaehler = array();
		foreach($data->calendar AS $day){
			if($day->fr == "")
				continue;
			
			if($day->dt < date("Ymd"))
				continue;
			
			#print_r($day);
			
			
			$tag = Util::parseDate("de_DE", substr($day->dt, 6).".".substr($day->dt, 4, 2).".".substr($day->dt, 0, 4));
			
			$tonnen = array();
			
			foreach($day->fr AS $k => $T){
				if($T == "PT")
					$tonnen[] = "3";
				
				if($T == "RM")
					$tonnen[] = "1";
				
				if($T == "GS")
					$tonnen[] = "2";
				
				if($T == "BT")
					$tonnen[] = "5";
			}
			
			for($i = 0; $i < 3; $i++)
				if(!isset($tonnen[$i]))
					$tonnen[$i] = 0;
			
			$D = new Datum($tag);
			$D->setToMonth1st();
			
			if(!isset($zaehler[date("Ym", $D->time())]))
				$zaehler[date("Ym", $D->time())] = 0;
			
			$zaehler[date("Ym", $D->time())]++;
			
			echo date("Ymd", $D->time())."_0000|101/".$zaehler[date("Ym", $D->time())]."|".implode("#", $tonnen)."#".Util::CLDateParserL($tag)."\r\n";
			
			$name = "";
			
			if($name == "")
				continue;
			
		}
		#echo "</pre>";
	}
	
	public static function getButton(){
		$AWVButton = new Button("Müllabfuhr-Daten herunterladen", "trash_stroke", "iconicL");
		$AWVButton->popup("", "Müllabfuhr-Daten", "mAWV", "-1", "downloadTrashData");
		return $AWVButton;
	}
	
	public function downloadTrashData(){
		$andreas = false;
		
		if(!$andreas)
			$json = file_get_contents("http://awido.cubefour.de/WebServices/Awido.Service.svc/getData/00000000-0000-0000-0000-000000001190?fractions=1,5,2,6,3,4,10&client=awv-nordschwaben");
		else
			$json = file_get_contents("http://awido.cubefour.de/WebServices/Awido.Service.svc/getData/00000000-0000-0000-0000-000000000629?fractions=1,5,2,6,3,4,10&client=awv-nordschwaben");
		
		echo "<pre style=\"font-size:10px;max-height:400px;overflow:auto;\">";
		$data = json_decode($json);
		foreach($data->calendar AS $day){
			if($day->fr == "")
				continue;
			
			if($day->dt < date("Ymd"))
				continue;
			
			print_r($day);
			
			
			$tag = new Datum(Util::parseDate("de_DE", substr($day->dt, 6).".".substr($day->dt, 4, 2).".".substr($day->dt, 0, 4)));
			
			if($andreas)
				$tag->subDay();
			
			$name = "";
			foreach($day->fr AS $T){
				if($T == "PT")
					$name .= ($name != "" ? ", " : "")."Papiertonne";
				
				if($T == "RT")
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
			$F->sA("TodoFromDay", $tag->time());
			$F->sA("TodoTillDay", $tag->time());
			
			$F->sA("TodoFromTime", "32400");
			$F->sA("TodoTillTime", "36000");
			$F->sA("TodoUserID", "-1");
			$F->sA("TodoRemind", "-1");
			
			if($andreas){
				$F->sA("TodoFromTime", Util::parseTime("de_DE", "18:00"));
				$F->sA("TodoTillTime", Util::parseTime("de_DE", "18:05"));
				$F->sA("TodoUserID", Session::currentUser()->getID());
				$F->sA("TodoRemind", 60);
			}
			
			$F->sA("TodoClass", "Kalender");
			$F->sA("TodoClassID", "-1");
			$F->sA("TodoType", "2");
			if($F->exists())
				continue;
			
			$F->store();
		}
		echo "</pre>";
	}
}
?>