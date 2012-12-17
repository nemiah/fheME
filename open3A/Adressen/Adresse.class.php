<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class Adresse extends PersistentObject implements iDeletable, iXMLExport/*, iLDAPExport*/ {

	public function deleteMe(){
		if(Session::isPluginLoaded("Kunden") AND Session::isPluginLoaded("Auftraege")){
			$K = Kappendix::getKappendixToAdresse($this->getID());
			
			if($K != null){
				$AC = anyC::get("Auftrag", "kundennummer", $K->A("kundennummer"));
				$AC->setLimitV3(1);
				$A = $AC->getNextEntry();
				
				if($A === null)
					$K->deleteMe();
				
			}
		}
		
		parent::deleteMe();
	}
	
	public function getLDAPSchema($client = ""){
		$objectclass = array();
		$objectclass[] = "top";
		$objectclass[] = "person";
		$objectclass[] = "organizationalPerson";
		$objectclass[] = "inetOrgPerson";
		$objectclass[] = "mozillaAbPersonObsolete";
	
		return array(
			"uid" => "{AdresseID}",
			"cn" => "{vorname} {nachname}",
			"sn" => "{nachname}",
			"givenname" => "{vorname}",
			"mail" => "{email}",
			"xmozillausehtmlmail" => "TRUE",
			"telephoneNumber" => "{tel}",
			"mobile" => "{mobil}",
			"title" => "",
			"o" => "{firma}",
			"postOfficeBox" => "{strasse} {nr}",
			"l" => "{ort}",
			"st" => "",
			"postalCode" => "{plz}",
			"countryname" => "{land}",
			"description" => "{bemerkung}",
			"objectclass" => $objectclass);
	}

	// <editor-fold defaultstate="collapsed" desc="__toString">
	function __toString(){
		if($this->A == null) $this->loadMe();
		
		return $this->getHTMLFormattedAddress();
	}
	// </editor-fold>
	
	public function __construct($id){
		$this->myAdapterClass = "AdresseAdapter";
		#if($_SESSION["S"]->checkForPlugin("mLDAP"))
		#	$this->myAdapterClass = "AdresseLDAPAdapter";
	
		parent::__construct($id);
			
		if(!isset($_SESSION["viaInterface"]))
			$this->customize();
	}
	
	public function getA(){
		if($this->A == null) $this->loadMe();
		return $this->A;
	}

	public function newAttributes(){
		$A = parent::newAttributes();
		
		$A->AuftragID = "-1";
		$A->KategorieID = "0";
		$A->type = "default";
		
		$mwst = 0;
		if(Session::isPluginLoaded("Kategorien")){
			$AC = anyC::get("Kategorie", "type", "1");
			$AC->addAssocV3("isDefault", "=", "1");
			$M = $AC->getNextEntry();
			if($M != null)
				$A->KategorieID = $M->getID();
		}
		
		if($this->customizer != null)
			$this->customizer->customizeNewAttributes($this->getClearClass(get_class($this)), $A);
			
		return $A;
	}
	
	public function getHTMLFormattedAddress(){
		if($this->A == null) $this->loadMe();
		if($this->A == null) return "Adresse unbekannt";

		return nl2br($this->getFormattedAddress());
	}

	public function getShortAddress(){
		return $this->A("firma") != "" ? $this->A("firma") : trim($this->A("vorname")." ".$this->A("nachname"));
	}

	public function getFormattedAddress($withAnrede = false, $language = "de_DE"){
		/*$r = "";

		switch(ISO3166::getCodeToCountry($this->A("land"))){
			case "GB":
				if($this->A->firma != "") $r .= $this->A->firma."\n";

				if($this->A->nachname != "") $r .= ($this->A("position") != "" ? $this->A("position").", " : "").($withAnrede ? Util::formatAnrede($language, $this, true)." " : "").$this->A->vorname.($this->A->vorname != "" ? " " : "").$this->A->nachname."\n";
				if($this->A("zusatz1") != "") $r .= $this->A("zusatz1")."\n";
				$r .= $this->A->nr." ".$this->A->strasse."\n";
				$r .= ($this->A("ort") != "" ? trim($this->A("ort"))."\n" : "").($this->A("plz") != "" ? $this->A("plz")."\n" : "").($this->A->land != "" ? $this->A->land : "");
			break;

			default:
				if($this->A("firma") != "") $r .= $this->A("firma")."\n";
				if($this->A("nachname") != "") $r .= ($withAnrede ? Util::formatAnrede($language, $this, true)." " : "").$this->A("vorname").($this->A("vorname") != "" ? " " : "").$this->A("nachname")."\n";
				$r .= "".$this->A("strasse")." ".$this->A("nr")."\n";
				$r .= trim($this->A("plz")." ".$this->A("ort")).($this->A("land") != "" ? "\n".$this->A("land") : "");
			break;
		}*/

		if($withAnrede){
			if($this->A("vorname") != "")
				$this->changeA("vorname", Util::formatAnrede($language, $this, true)." ".$this->A("vorname"));
			else
				$this->changeA("nachname", Util::formatAnrede($language, $this, true)." ".$this->A("nachname"));
		}

		$format = Util::getCountryAddressFormat(ISO3166::getCodeToCountry($this->A("land")));

		preg_match_all("/\{([a-zA-Z1-9]*)\}/", $format, $matches);

		foreach($matches[1] AS $var)
			$format = str_replace("{".$var."}", $this->A($var) == null ? "" : $this->A($var)." ", $format);
		
		$ex = explode("\n", $format);
		foreach($ex AS $n => $l){
			$nl = trim($l);
			if($nl == "")
				unset($ex[$n]);
			else
				$ex[$n] = $nl;
		}

		return trim(implode("\n", $ex));
	}

	function newMe($checkUserData = true, $output = false){
		$ps = mUserdata::getPluginSpecificData("Adressen");
		
		if(isset($ps["pluginSpecificCanUse1xAdresse"]) AND $this->A->AuftragID == -1) die("<p>Erstellen nicht möglich!<br />Plugin-spezifische Einstellungen aktiv.<br /><br />Sie können das Erstellen von Einträgen im Adressen-Plugin auch verbieten, dann wird die Option \"Adresse neu anlegen\" nicht mehr angezeigt.<br />Neue 1x-Adressen können weiterhin verwendet werden.</p>");
		elseif(!isset($ps["pluginSpecificCanUse1xAdresse"])) mUserdata::checkRestrictionOrDie("cantCreate".str_replace("GUI","",get_class($this)));
		
		$this->changeA("lastChange", time());
		
		$id = parent::newMe(false, $output);
		
		
		if($this->A("AuftragID") != -1 AND $this->A("type") == "auftragsAdresse"){
			$Auftrag = new Auftrag($this->A("AuftragID"));
			$Auftrag->updateAdressID($id);
		}
		
		if(Session::isPluginLoaded("mSync") AND $this->A("AuftragID") == -1)
			mSync::newGUID("Adresse", $id, null, true);
		
		return $id;
	}

	function saveMe($checkUserData = true, $output = true){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>

		$ps = mUserdata::getPluginSpecificData("Adressen");

		if(isset($ps["pluginSpecificCanUse1xAdresse"]) AND $this->A->AuftragID == -1) die("Speichern nicht möglich!");

		$this->changeA("lastChange", time());
		
		if($this->A("AuftragID") != -1 AND ($this->A("type") == "auftragsAdresse" OR $this->A("type") == "default")){
			$Auftrag = new Auftrag($this->A("AuftragID"));
			$Auftrag->updateAdressID($this->getID());
		}
		
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		return Aspect::joinPoint("after", $this, __METHOD__, parent::saveMe($checkUserData, $output));
		// </editor-fold>
	}
	
	public function newFromAdresse($AuftragID){
		$this->loadMe();
		$this->A->AuftragID = $AuftragID;
		$this->A->type = "default";
		return $this->newMe(true, false);
	}
	
	public function getHeader(){
		return ($this->A->firma != "" ? $this->A->firma : $this->A->vorname." ".$this->A->nachname);
	}

	public function getCalendarTitle(){
		return trim($this->A("firma") != "" ? $this->A("firma") : $this->A("vorname")." ".$this->A("nachname"));
	}
	
	public static function getAnreden(){
		return array(2 => "Herr", 1 => "Frau", 3 => "keine/Firma", 4 => "Familie");#array("2","1","3","4"), array("Herr","Frau","keine/Firma", "Familie")
	}
	
	public function getEmailData(){
		$recipients = array();
		$recipients[0] = array($this->A("firma") != "" ? $this->A("firma") : $this->A("vorname")." ".$this->A("nachname"), $this->A("email"));
		
		if(Session::isPluginLoaded("mAnsprechpartner")){
			$AC = Ansprechpartner::getAllAnsprechpartnerToAdresse($this->getID());
			while($A = $AC->getNextEntry())
				$recipients[$A->getID()] = array($A->A("AnsprechpartnerVorname")." ".$A->A("AnsprechpartnerNachname"), $A->A("AnsprechpartnerEmail"));
		}
		
		return array("fromName" => Session::currentUser()->A("name"), "fromAddress" => Session::currentUser()->A("UserEmail"), "recipients" => $recipients, "subject" => "", "body" => "");
	}
	
	public function replaceByAnsprechpartner($AnsprechpartnerID){
		if($AnsprechpartnerID == 0)
			return;
		
		$A = new Ansprechpartner($AnsprechpartnerID);
		$this->changeA("anrede", $A->A("AnsprechpartnerAnrede"));
		$this->changeA("email", $A->A("AnsprechpartnerEmail"));
		$this->changeA("vorname", $A->A("AnsprechpartnerVorname"));
		$this->changeA("nachname", $A->A("AnsprechpartnerNachname"));
	}
}
?>