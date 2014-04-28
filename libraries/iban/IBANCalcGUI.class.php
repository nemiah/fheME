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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class IBANCalcGUI {
	public static function getButton($ibanField, $bicField, $ktoField = null, $blzField = null){
		$attr = array("'$ibanField'", "'$bicField'");
		if($ktoField != null AND $blzField != null){
			$attr[] = "\$j('[name=$ktoField]').val()";
			$attr[] = "\$j('[name=$blzField]').val()";
		}
		
		$B = new Button("IBAN-Rechner", "./images/i2/calc.png", "icon");
		$B->popup("", "IBAN-Rechner", "IBANCalc", "-1", "popup", $attr);
		
		return $B;
	}
	
	function popup($ibanField, $bicField, $kto = "", $blz = ""){
		$F = new HTMLForm("ibanCalc", array("land", "kontonummer", "bankleitzahl", "ibanField", "bicField"));
		$F->getTable()->setColWidth(1, 120);
		
		$I = new IBAN();
		
		$l = array();
		foreach($I->Countries() AS $c){
			$IC = new IBANCountry($c);
			if(!$IC->IsSEPA())
				continue;
			
			$l[$c] = ISO3166::getCountryToCode($c)." ($c)";
		}
		
		asort($l);
		
		$F->setType("land", "select", "DE", $l);
		$F->setType("ibanField", "hidden");
		$F->setType("bicField", "hidden");
		
		$F->setValue("kontonummer", $kto);
		$F->setValue("bankleitzahl", $blz);
		$F->setValue("ibanField", $ibanField);
		$F->setValue("bicField", $bicField);
		
		$F->setSaveRMEPCR("Berechnen", "", "IBANCalc", "-1", "calc", "function(t){ \$j('#ibanCalcResult').html(t.responseText); }");
		
		echo $F."<div id=\"ibanCalcResult\"></div>";
	}
	
	function calc($land, $kontonummer, $bankleitzahl, $ibanField, $bicField){
		if($kontonummer == "" OR $bankleitzahl == "")
			Red::alertD ("Bitte tragen Sie Bankleitzahl und Kontonummer ein");
		
		$url = "http://www.iban.de/iban-berechnen.html";
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0',
			'Referer: http://www.iban.de/iban-berechnen.html',
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
			'X-Requested-With: XMLHttpRequest'#,
			#'Cookie: _pk_ref.37.dcb6=%5B%22%22%2C%22%22%2C1389277849%2C%22http%3A%2F%2Fwww.google.com%2Furl%3Fsa%3Dt%26rct%3Dj%26q%3D%26esrc%3Ds%26source%3Dweb%26cd%3D4%26ved%3D0CD0QFjAD%26url%3Dhttp%253A%252F%252Fwww.iban.de%252Fiban-berechnen.html%26ei%3DkrLOUr38AYnOtAb254Bw%26usg%3DAFQjCNGmoBaufRtRQUZSnlQCttjbtAYFRA%26bvm%3Dbv.59026428%2Cd.Yms%22%5D; _pk_id.37.dcb6=9e7a5507137b9501.1386026862.5.1389277849.1389275561.; _pk_ses.37.dcb6=*'
		));

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "ibanrechnerCountry=$land&ibanrechnerBlz=$bankleitzahl&ibanrechnerKonto=$kontonummer&ibanToolkit=ibanrechner");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		curl_close($ch);
		
		$I = new IBAN(trim(str_replace("Die IBAN lautet: ", "", strip_tags($result))));
		$iban = $I->MachineFormat();
		
		
		$url = "https://www.s-bausparkasse.at/portal/if_ajax.asp";
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=calc.ibanbic.listofbic&cuid=&alt_iban=&iban=$iban&rechnername=IBAN%2FBIC-Rechner&currentpageid=87&berechnungsdaten=&autocalc=&getresult=&country=$land&bank=$bankleitzahl&account=".  str_pad($kontonummer, 10, "0", STR_PAD_LEFT));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		curl_close($ch);
		$ex = explode("&&&", $result);
		
		$ex[0] = str_replace("listofbic==", "", strip_tags(trim($ex[0])));
		$ex[1] = str_replace("iban==", "", trim($ex[1]));
		
		$T = new HTMLTable(3, "Gefundene Ergebnisse");
		$T->weight("light");
		$T->maxHeight(400);
		$T->useForSelection(false);
		$T->setColWidth(1, 20);
		$T->addHeaderRow(array("", "BIC", "IBAN"));
		
		foreach(explode("\n", $ex[0]) AS $bic){
			$B = new Button("Diese Kombination verwenden", "arrow_right", "iconic");
			$T->addRow(array($B, trim($bic), $ex[1]));
			$T->addRowEvent("click", "\$j('[name=$ibanField]').val('$ex[1]'); \$j('[name=$bicField]').val('".trim($bic)."'); ".OnEvent::closePopup("IBANCalc"));
		}
		echo $T;
		#echo "<pre>";
		#echo htmlentities(print_r($ex, true));
		
		#echo($result);
		
		#echo "</pre>";
	}
}
?>