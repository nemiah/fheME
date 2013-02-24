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

class CookieCart {
	
	private $cookieName = "CookieCart";
	private $cookie;
	private $lifeTime;
	
	private $bestellungID;
	
	private $elements;
	private $elementPointer = 0;
	
	private $useClass = array("Artikel");
	
	private $preisField = array("preis");
	private $mwstField = array("mwst");
	private $nameField = array("name");
	private $artikelnummerField = array("artikelnummer");
	
	private $imagePathCallback = array("CookieCart::imagePathCallback");
	private $cartHandlerID;
	private $trashImage;
	
	private $sellerName;
	private $sellerEmail;
	private $sellerSignature;
	private $sellerBank;

	private $payment;
	
	private $table;
	
	private $sum = 0;
	private $count = 0;
	
	private $payNowButton;
	private $payNowURL;
	private $PayPalButton = "";
	
	private $versandkostenBrutto;
	
	private $imageParser;

	private $couponCallback;
	private $currentCouponCode;

	private $CCHSessionVariable = "Customer";

	function __construct($cookieName = "", $lifeTime = ""){
		if($cookieName != "") $this->cookieName = $cookieName;
		
		$this->cookie = isset($_COOKIE[$this->cookieName]) ? $_COOKIE[$this->cookieName] : "";
		
		if($lifeTime == "") $this->lifeTime = time() + 2 * 24 * 3600;
		else $this->lifeTime = $lifeTime;
		
		$this->table = new HTMLTable(6);
	}

	/**
	 * Use this method to set a callback for checking if a coupon code is valid
	 * and to invalidate the code if used.
	 *
	 * The callback-method should look like this:
	 *
	 * public static function couponCallback($action, $values);
	 *
	 * And handle the $action values
	 *  - isCouponValid: return true or false
	 *  - invalidateCoupon
	 *  - showCouponField: returns true or string with explanation why the field is not displayed
	 *  - getPostenValues: return an array like this: array("mwst" => 19, "preis" => -5.00, "artikelnummer" => "8dh33f6f9shd", "name" => "Gutschein");
	 *
	 * @param Class::Method $callback
	 */
	public function setCouponCallback($callback){
		$this->couponCallback = $callback;
	}

	public function getCouponCallback(){
		return $this->couponCallback;
	}

	public function setCCHSessionVariable($Variable){
		$this->CCHSessionVariable = $Variable;
	}

	public function getCCHSessionVariable(){
		return $this->CCHSessionVariable;
	}

	public function invalidateCoupon(){
		if($this->couponCallback != null)
			$this->invokeParser($this->couponCallback, "invalidateCoupon", null);
	}

	function getA(){
		$A = new stdClass();
		
		switch($this->PostenID){
			case "1":
				$values = $this->invokeParser($this->couponCallback, "getPostenValues", $this->currentCouponCode);


				$A->mwst = $values["mwst"];
				$A->artikelname = $values["name"];
				$A->preis = $values["preis"];
				$A->artikelnummer = $values["artikelnummer"];
			break;
		}

		return $A;
	}

	public function getTable(){
		return $this->table;
	}
	
	/**
	 * Available payment Options:
	 * 
	 * Überweisung
	 * PayPal
	 * Kreditkarte
	 */
	public function setPaymentOptions($options){
		if(!is_array($options)) $options = array($options);
		$this->payment = $options;
	}
	
	public function setImagePathParser($callback){
		if(!is_array($callback)) $callback = array($callback);
		$this->imagePathCallback = $callback;
	}
	
	public function setImageParser($callback){
		if(!is_array($callback)) $callback = array($callback);
		$this->imageParser = $callback;
	}
	
	public function setCartHandlerID($id){
		$this->cartHandlerID = $id;
	}
	
	
	public function setVersandkostenBrutto($Name, $PreisBrutto, $MwSt){
		$this->versandkostenBrutto = array($Name, $PreisBrutto, $MwSt);
	}
	
	public function setUsedClass($className){
		if(!is_array($className)) $className = array($className);
		$this->useClass = $className;
	}
	

	public function setPriceField($name){
		if(!is_array($name)) $name = array($name);
		$this->preisField = $name;
	}
	
	public function setArtikelnummerField($name){
		if(!is_array($name)) $name = array($name);
		$this->artikelnummerField = $name;
	}

	
	public function setMwstField($name){
		if(!is_array($name)) $name = array($name);
		$this->mwstField = $name;
	}

	
	public function setNameField($name){
		if(!is_array($name)) $name = array($name);
		$this->nameField = $name;
	}
	
	
	protected function add($artikelID, $menge, $type = null){
		if($type == null) $type = $this->useClass[0];
		if($this->cookie == "") $this->cookie = "--$artikelID:__:$menge:__:$type--";
		else $this->cookie .= "--$artikelID:__:$menge:__:$type--";
	}
	
	public function put($artikelID, $menge, $type = null){
		if($type == null) $type = $this->useClass[0];
		if($this->exists($artikelID, $type)){
			$regs = array();
			ereg("--$artikelID:\_\_:([0-9]+):\_\_:$type--",$this->cookie, $regs);
			$alteMenge = $regs[1];
			
			$this->cookie = ereg_replace("--$artikelID:\_\_:[0-9]+:\_\_:$type--","--$artikelID:__:".($menge + $alteMenge).":__:$type--",$this->cookie);
		}
		else $this->add($artikelID, $menge, $type);
		
		if($menge == 0) $this->delete($artikelID, false);

		$this->setCookie();
	}
	
	public function setTrashImagePath($path){
		$this->trashImage = $path;
	}
	
	public function update($artikelID, $menge, $type = null){
		if($type == null) $type = $this->useClass[0];
		if($this->exists($artikelID, $type))
			$this->cookie = ereg_replace("--$artikelID:\_\_:[0-9]+:\_\_:$type--","--$artikelID:__:$menge:__:$type--",$this->cookie);
		else $this->add($artikelID, $menge);
		
		if($menge == 0) $this->delete($artikelID, $type, false);
		
		$this->setCookie();
	}
	
	public function exists($artikelID, $type = null){
		if($type == null) $type = $this->useClass[0];
		if(strpos($this->cookie, "--$artikelID:__:") !== false AND strpos($this->cookie, ":__:$type--") !== false) return true;
		else return false;
	}
	
	protected function setCookie(){
		setcookie($this->cookieName, $this->cookie, $this->lifeTime, "/");
	}
	
	public function getAll() {
		return $this->cookie;
	}
	
	public function setAll($cookie){
		$this->cookie = $cookie;
	}
	
	public function delete($artikelID, $type = null, $set = true) {
		if($type == null) $type = $this->useClass[0];
		$this->cookie = preg_replace("/--$artikelID:\_\_:[0-9]+:\_\_:$type--/","",$this->cookie);
		#echo "error:\n --$artikelID:\_\_:[0-9]+:\_\_:$type--\n\n";
		#print_r($this->cookie);
		#die("TEST");
		if($set) $this->setCookie();
	}
	
	public function clear(){
		$this->cookie = "";
		$this->setCookie();
	}

	public function setSellerEMail($mail){
		$this->sellerEmail = $mail;
	}
	
	public function setSellerName($name){
		$this->sellerName = $name;
	}
	
	public function getSellerEMail(){
		return $this->sellerEmail;
	}
	
	public function getSellerName(){
		return $this->sellerName;
	}

	public function setSellerSignature($signature){
		$this->sellerSignature = $signature;
	}

	public function getSellerSignature(){
		return $this->sellerSignature;
	}

	public function setSellerBank($empfaenger, $kontonummer, $bank, $blz){
		$this->sellerBank = array($empfaenger, $kontonummer, $bank, $blz);
	}

	public function getSellerBank(){
		return $this->sellerBank;
	}


	private function invokeParser($function, $value, $parameters){
		$c = explode("::", $function);
		$method = new ReflectionMethod($c[0], $c[1]);
		return $method->invoke(null, $value, $parameters);
	}
	
	function setPayNowButton($path, $url){
		$this->payNowButton = $path;
		$this->payNowURL = $url;
	}

	function getCartHTML($previewMode = false){
		$payVia = "";
		if(count($this->payment) > 0)
			$payVia = "&payVia='+CookieCart.getPayVia()+'";
		#parameters: 'formID=nix&HandlerName=CookieCartHandler&artikelid='+id+'&action=delete&type='+type,
		if($this->cookie != "") $html = "
				<script type=\"text/javascript\">
					<!--
						var CookieCart = {
							joinFormFields: function(formID){
								setString = '';
								for(i = 0;i < $(formID).elements.length;i++) {
									if($(formID).elements[i].type == 'button') continue;

									if($(formID).elements[i].type == 'radio'){
										if($(formID).elements[i].checked) setString += '&'+$(formID).elements[i].name+'='+encodeURIComponent($(formID).elements[i].value);
									} else if($(formID).elements[i].type == 'checkbox'){
										if($(formID).elements[i].checked) setString += '&'+$(formID).elements[i].name+'=1';
										else setString += '&'+$(formID).elements[i].name+'=0';
									} else if($(formID).elements[i].type == 'select-multiple'){
										setString += '&'+$(formID).elements[i].name+'=';
										subString = '';
										for(j = 0; j < $(formID).elements[i].length; j++)
											if($(formID).elements[i].options[j].selected) subString += (subString != '' ? ';:;' : '')+$(formID).elements[i].options[j].value;

										setString += subString;

									} else setString += '&'+$(formID).elements[i].name+'='+encodeURIComponent($(formID).elements[i].value);
								}
								return setString;
							},

							getPayVia: function(){
								for(i = 0;i < $('cart').payVia.length; i++)
									if($('cart').payVia[i].checked) return $('cart').payVia[i].value;
							},

							deleteMe: function(id, type){
								new Ajax.Request('/index.php', {
									method:'post',
									parameters:'r=".mt_rand(0, 10000)."&formID=nix&HandlerName=CookieCartHandler&artikelid='+id+'&action=delete&type='+type,
									onSuccess: function(transport){
										if(multiCMS.checkResponse(transport))
											document.location.reload();
								}});
							},

							orderNow: function(){
								$('cart').action.value = 'order';

								//if(confirm('Jetzt kaufen?'))
								new Ajax.Request('/index.php', {
									method:'post',
									parameters: 'r=".mt_rand(0, 10000)."&formID=nix&HandlerName=CookieCartHandler$payVia'+CookieCart.joinFormFields('cart'),
									onSuccess: function(transport){
										if(multiCMS.checkResponse(transport))
											document.location.reload();
								}});
							},
										
							updateAmounts: function(){
								multiCMS.formHandler('cart', function(transport){
									if(multiCMS.checkResponse(transport))
										document.location.reload();
								});
							},

							insertCoupon: function(){
								if($('CouponCode').value == '') {
									alert('Bitte geben Sie einen Gutscheincode ein!');
									return;
								}

								new Ajax.Request('/index.php', {
									method:'post',
									parameters: 'r=".mt_rand(0, 10000)."&formID=nix&HandlerName=CookieCartHandler&action=insertCoupon&couponCode='+$('CouponCode').value,
									onSuccess: function(transport){
										if(multiCMS.checkResponse(transport))
											document.location.reload();}});
							}

						}
					-->
				</script>
				
				<style type=\"text/css\">
					#CookieCart .CookieCartBorderColor td {
						border-width:0px;
					}
					
					#CookieCart table th {
						text-align:left;
					}
				</style>
				
				<form ".(!$previewMode ? "id=\"cart\"" : "")." onsubmit=\"return false;\">";
				
				$tab = $this->table;

				$tab->addHeaderRow(array("","Menge","Artikel","Einzelpreis", "Gesamtpreis",""));
				$tab->addColStyle(2, "text-align:right;");
				$tab->addColStyle(4, "text-align:right;");
				$tab->addColStyle(5, "text-align:right;");
				$tab->addColStyle(6, "text-align:right;");
		$steuern = array();
		$gesamt = 0;
		$netto = 0;
		
		$i = 0;
		if($this->cookie != ""){
			while($t = $this->getNextElement()){
				$num = array_search($t[2], $this->useClass);
				
				if($t[2] != "CookieCart"){
					$c = $this->useClass[$num];
					$A = new $c($t[0]);
					$A->loadMe();

					$mwst = $this->mwstField[$num];
					$name = $this->nameField[$num];
					$preis = $this->preisField[$num];
					$artikelnummer = $this->artikelnummerField[$num];

				} else {
					$A = $this;
					$this->PostenID = $t[0];

					$mwst = "mwst";
					$name = "artikelname";
					$preis = "preis";
					$artikelnummer = "artikelnummer";
				}
		
				try {
					new Staffelpreis(-1);
					if(class_exists("Staffelpreis")){
						$ac = new anyC();
						$ac->setCollectionOf("Staffelpreis");
						$ac->addAssocV3("StaffelpreisClass", "=", $this->useClass);
						$ac->addAssocV3("StaffelpreisClassID", "=", $t[0]);
						$ac->addAssocV3("StaffelpreisAmount", "<=", $t[1]);
						$ac->addOrderV3("StaffelpreisAmount", "DESC");
						$ac->setLimitV3("1");
						
						$ac2 = $ac->getNextEntry();
						
						if($ac2 != null) 
							$A->changeA($preis, $ac2->A("StaffelpreisPrice"));
					}
				} catch(Exception $e) { }
				
				if(!isset($steuern[$A->getA()->$mwst])) $steuern[$A->getA()->$mwst] = 0;
				$gesamt += $A->getA()->$preis * 1 * (($A->getA()->$mwst / 100) + 1) * $t[1];
				$netto += $A->getA()->$preis * 1 * $t[1];
				$steuern[$A->getA()->$mwst] += $A->getA()->$preis * 1 * ($A->getA()->$mwst / 100) * $t[1];
				
				$image = $this->invokeParser($this->imagePathCallback[$num], $t[0], $A);
				
				if($image != "") $parsedImage = "<img src=\"$image\" />";
				else $parsedImage = "";
				if($this->imageParser != null) 
					$parsedImage = $this->invokeParser($this->imageParser[$num], $t[0], $A);
					
				#"<input type=\"input\" style=\"text-align:right;width:60px;\" name=\"amountOf_$t[0]_$t[2]\" value=\"$t[1]\" onkeydown=\"if(event.keyCode == 13) CookieCart.updateAmounts();\" />"
				$IMenge = new HTMLInput("amountOf_$t[0]_$t[2]", "text", $t[1]);
				$IMenge->style("text-align:right;width:60px;");
				$IMenge->onEnter("CookieCart.updateAmounts();");

				if($t[2] == "CookieCart" AND $t[0] == "1"){
					$IMenge = $t[1];
				}

				$tab->addRow(array(
					$parsedImage,
					!$previewMode ? $IMenge : "$t[1]",
					$A->getA()->$name.((isset($A->getA()->$artikelnummer) AND $A->getA()->$artikelnummer != "") ? "<br /><small>Art.Nr. ".$A->getA()->$artikelnummer."</small>" : ""),
					Util::formatCurrency("de_DE",$A->getA()->$preis * 1 * (($A->getA()->$mwst / 100) + 1),true)."<br /><small>".Util::formatNumber("de_DE", $A->getA()->$mwst * 1, 2, true, false)."%</small>",
					Util::formatCurrency("de_DE",$A->getA()->$preis * 1 * (($A->getA()->$mwst / 100) + 1) * $t[1],true),
					!$previewMode ? "<img style=\"cursor:pointer;\" onclick=\"CookieCart.deleteMe('{$t[0]}','$t[2]');\" alt=\"Artikel aus Warenkorb löschen\" title=\"Artikel aus Warenkorb löschen\" src=\"$this->trashImage\" />" : ""));
				
				$tab->addCellStyle(1, "vertical-align:top;");
				$i++;
			}
		
			if($this->versandkostenBrutto != null){
				$tab->addRow(array(
					"",
					"1",
					$this->versandkostenBrutto[0],
					Util::formatCurrency("de_DE",$this->versandkostenBrutto[1],true),
					Util::formatCurrency("de_DE",$this->versandkostenBrutto[1],true)));
				
				$gesamt += $this->versandkostenBrutto[1];
				$netto += Util::kRound($this->versandkostenBrutto[1] / ($this->versandkostenBrutto[2] + 100) * 100, 2);
				$steuern[number_format($this->versandkostenBrutto[2],2)] += Util::kRound($this->versandkostenBrutto[1] / ($this->versandkostenBrutto[2] + 100) * $this->versandkostenBrutto[2], 2);
			}
			
			$s = "";
			foreach($steuern AS $key => $value)
				$s .= ($s != "" ? "<br />" : "")."".Util::formatNumber("de_DE", $key*1, 2, true, false)."%: ".Util::formatCurrency("de_DE",$value,true);
			
			
			$tab->addRow(array("",!$previewMode ? "
							<input
								type=\"button\"
								value=\"Mengen speichern\"
								onclick=\"CookieCart.updateAmounts();\"
							/>" : "","","Gesamt Netto",Util::formatCurrency("de_DE",$netto,true),""));
			$tab->addCellStyle(1, "border-top-width:1px;border-top-style:solid;");
			$tab->addCellStyle(2, "border-top-width:1px;border-top-style:solid;");
			$tab->addCellStyle(3, "border-top-width:1px;border-top-style:solid;");
			$tab->addCellStyle(4, "border-top-width:1px;border-top-style:solid;");
			$tab->addCellStyle(5, "border-top-width:1px;border-top-style:solid;");
			$tab->addCellStyle(6, "border-top-width:1px;border-top-style:solid;");
			$tab->addRowClass("CookieCartBorderColor");
			$tab->addRowColspan(2, 2);
			
			$tab->addRow(array("","","","Gesamt MwSt",$s,""));
			
			$tab->addRow(array("","","","Gesamt",Util::formatCurrency("de_DE",$gesamt,true),""));
			$tab->addCellStyle(4, "font-weight:bold;border-top-style:solid;border-top-width:1px;");
			$tab->addCellStyle(5, "font-weight:bold;border-top-style:solid;border-top-width:1px;");
			$tab->addRowClass("CookieCartBorderColor");
			#$tab->addCellStyle(2, "text-align:right;");
			
			if(!$previewMode) {
				$coupon = "";
				if($this->couponCallback !== null){
					$showCouponField = $this->invokeParser($this->couponCallback, "showCouponField", null);

					if($showCouponField === true){
						$CI = new HTMLInput("CouponCode");
						$CI->id("CouponCode");
						$CI->onEnter("CookieCart.insertCoupon();");

						$CB = new HTMLInput("CouponInsert","button","Gutschein einlösen");
						$CB->style("margin-left:10px;");
						$CB->onclick("CookieCart.insertCoupon();");
					} else {
						$CI = "";
						$CB = $showCouponField;
					}

					$coupon = "<div class=\"CookieCartCoupon\"><p><b>Gutschein einlösen</b><br />$CI$CB</p></div>";
				}

				$tab->addRow(array($coupon,"",""));
				$tab->addRowStyle("height:30px;");
				$tab->addRowColspan(1, 4);
			}
			
			if(count($this->payment) > 0 AND !$previewMode AND $this->payNowButton == null) {
				
				$pay = "";
				foreach($this->payment as $k => $v){
					if($pay != "") $pay .= "<br /><br />";
					
					switch($v) {
						case "PayPal":
							$pay .= "<input name=\"payVia\" value=\"$v\" type=\"radio\" ".($pay == "" ? "checked=\"checked\"" : "")." />
							<img src=\"https://www.paypal.com/de_DE/DE/i/logo/logo_80x35.gif\" alt=\"PayPal\">";
						break;

						case "Überweisung":
							$pay .= "<input name=\"payVia\" value=\"$v\" type=\"radio\" ".($pay == "" ? "checked=\"checked\"" : "")." /> Überweisung";
						break;

						case "Kreditkarte":
							$pay .= "<input name=\"payVia\" value=\"$v\" type=\"radio\" ".($pay == "" ? "checked=\"checked\"" : "")." /> Kreditkarte";
						break;
					}
				}
				
				$tab->addRow(array("","","","Zahlung via","$pay"));
				$tab->addCellStyle(4, "vertical-align:top;");
				
				$tab->addRow(array("","","","",""));
				$tab->addRowStyle("height:30px;");
				
			}
			
			if(!$previewMode AND $this->payNowButton === null) {
				$tab->addRow(array("","","","
							<input
								type=\"button\"
								value=\"Jetzt verbindlich bestellen\"
								style=\"border-color:green;width:220px;height:30px;color:green;\"
								onclick=\"CookieCart.orderNow();\"
							/>"));
				$tab->addRowColspan(4, 3);
			}
			elseif(!$previewMode AND $this->payNowButton != null AND $this->payNowButton != "") {
				$tab->addRow(array("<a href=\"$this->payNowURL\"><img alt=\"jetzt Bezahlen\" title=\"jetzt Bezahlen\" src=\"$this->payNowButton\" /></a>"));
				$tab->addRowColspan(1, 6);
				$tab->addCellStyle(1,"text-align:right;");
			}
			
			$html .= $tab->getHTML();
			$html .= "<input type=\"hidden\" name=\"returnP\" value=\"$_GET[p]\" />
						<input type=\"hidden\" name=\"HandlerName\" value=\"CookieCartHandler\" />
						<input type=\"hidden\" name=\"action\" value=\"updateAll\" />
				</form>";
		}
		else
			$html = "<p>Ihr Warenkorb enthält keine Artikel.</p>";
		
		$_SESSION["CookieCart"] = $this;
		$this->elementPointer = 0;
		
		/*echo "<pre>";
		echo $this->getCartText(false);
		echo "</pre>";*/
		
		return "<div id=\"CookieCart\">".$html."</div>";
	}
	
	public function getSum(){
		$this->sum = 0;
		if($this->sum == null) $this->getCartText();
		return $this->sum;
	}
	
	public function getCount(){
		$this->count = 0;
		if($this->count == null) $this->getCartText();
		return $this->count;
	}
	
	public function getPayPalButton(){
		$this->getCartText(true);
		return $this->PayPalButton;
	}
	
	public function getCartText($withPayPal = false){
		$text = "";
		
		$paypalHTML = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="currency_code" value="EUR" />
	<input type="hidden" name="charset" value="utf-8" />
	<input type="hidden" name="invoice" value=";;;REPLACETHIS;;;" />
	<input type="hidden" name="business" value="'.$this->sellerEmail.'" />';
		
		$steuern = array();
		$gesamt = 0;
		$netto = 0;
		/*$c = $this->useClass;
		
		$mwst = $this->mwstField;
		$name = $this->nameField;
		$preis = $this->preisField;*/
		$i = 0;
		if($this->cookie != ""){
			$text .= "
        ".str_pad("Artikel",40," ")." MwSt      Preis            Gesamt
-------------------------------------------------------------------------------------------";
			
			while($t = $this->getNextElement()){
				$num = array_search($t[2], $this->useClass);

				if($t[2] != "CookieCart"){
					$c = $this->useClass[$num];
					$A = new $c($t[0]);
					$A->loadMe();

					$mwst = $this->mwstField[$num];
					$name = $this->nameField[$num];
					$preis = $this->preisField[$num];
					$artikelnummer = $this->artikelnummerField[$num];

				} else {
					$A = $this;
					$this->PostenID = $t[0];

					$mwst = "mwst";
					$name = "artikelname";
					$preis = "preis";
					$artikelnummer = "artikelnummer";
				}

				try {
					new Staffelpreis(-1);
					if(class_exists("Staffelpreis")){
						$ac = new anyC();
						$ac->setCollectionOf("Staffelpreis");
						$ac->addAssocV3("StaffelpreisClass", "=", $this->useClass);
						$ac->addAssocV3("StaffelpreisClassID", "=", $t[0]);
						$ac->addAssocV3("StaffelpreisAmount", "<=", $t[1]);
						$ac->addOrderV3("StaffelpreisAmount", "DESC");
						$ac->setLimitV3("1");
						
						$ac2 = $ac->getNextEntry();
						
						if($ac2 != null) 
							$A->changeA($preis, $ac2->A("StaffelpreisPrice"));
					}
				} catch(Exception $e) { }
				
				if(!isset($steuern[$A->getA()->$mwst])) $steuern[$A->getA()->$mwst] = 0;
				$gesamt += $A->getA()->$preis * 1 * (($A->getA()->$mwst / 100) + 1) * $t[1];
				$netto += $A->getA()->$preis * 1 * $t[1];
				$steuern[$A->getA()->$mwst] += $A->getA()->$preis * 1 * ($A->getA()->$mwst / 100) * $t[1];
				
				#$image = $this->invokeParser($this->imagePathCallback, $t[0], $A);
				
				$tName = str_pad(substr($A->getA()->$name,0,38), 40, " ", STR_PAD_RIGHT);
				$tName .= str_pad("",Util::countUmlaute($tName)," ");
				
				$brutto = $A->getA()->$preis * 1 * (($A->getA()->$mwst / 100) + 1) * $t[1];
				$this->sum += $brutto;
				$this->count += $t[1];
				$text .= "
".str_pad($t[1], 5, " ", STR_PAD_LEFT)." x ".$tName."|".str_pad(Util::formatNumber("de_DE", $A->getA()->$mwst * 1, 2, true, false), 7, " ", STR_PAD_LEFT)."% |".str_pad(Util::conv_euro8(Util::formatCurrency("de_DE",$A->getA()->$preis * 1 * (($A->getA()->$mwst / 100) + 1),true)), 15, " ", STR_PAD_LEFT)." |".str_pad(Util::conv_euro8(Util::formatCurrency("de_DE",$brutto,true)), 15, " ", STR_PAD_LEFT)."";

				/**
				 * Artikelnummer in neuer Zeile
				 */
				if(isset($A->getA()->$artikelnummer) AND $A->getA()->$artikelnummer != ""){
					$text .= "
".str_pad("", 5, " ", STR_PAD_LEFT)."   ".str_pad(substr("Art.Nr. ".$A->getA()->$artikelnummer,0,38), 40, " ", STR_PAD_RIGHT)."|         |                |";
					;
				}
				
				$i++;
				
				$ppName = str_replace(array("Ä", "Ö", "Ü", "ß", "ä" , "ö", "ü"), array("Ae", "Oe", "Ue", "ss", "ae", "oe", "ue"), $tName);

				if($t[2] == "CookieCart" AND $t[0] == "1")
					$paypalHTML .= '<input type="hidden" name="discount_amount_cart" value="'.abs($brutto).'" />';
				else $paypalHTML .= '
	<input type="hidden" name="item_name_'.$i.'" value="'.trim($ppName).'"/ >
	<input type="hidden" name="amount_'.$i.'" value="'.Util::formatCurrency("en_GB",$brutto,false).'" />';
				
			}
		
		
			if($this->versandkostenBrutto != null){
				$tName = str_pad(substr($this->versandkostenBrutto[0],0,38), 40, " ", STR_PAD_RIGHT);
				$tName .= str_pad("",Util::countUmlaute($tName)," ");
				
				$text .= "
".str_pad("1", 5, " ", STR_PAD_LEFT)." x ".$tName."|".str_pad(Util::formatNumber("de_DE",$this->versandkostenBrutto[2], 2,true, false), 7, " ", STR_PAD_LEFT)."% |".str_pad(Util::conv_euro8(Util::formatCurrency("de_DE",$this->versandkostenBrutto[1],true)), 15, " ", STR_PAD_LEFT)." |".str_pad(Util::conv_euro8(Util::formatCurrency("de_DE",$this->versandkostenBrutto[1],true)), 15, " ", STR_PAD_LEFT)."";
				
				$gesamt += $this->versandkostenBrutto[1];
				$netto += Util::kRound($this->versandkostenBrutto[1] / ($this->versandkostenBrutto[2] + 100) * 100, 2);
				$steuern[number_format($this->versandkostenBrutto[2],2)] += Util::kRound($this->versandkostenBrutto[1] / ($this->versandkostenBrutto[2] + 100) * $this->versandkostenBrutto[2], 2);
			}
			
			$s = "";
			foreach($steuern AS $key => $value)
				$s .= ($s != "" ? "\n" : "")."".str_pad(Util::conv_euro8("Gesamt MwSt".str_pad(Util::formatNumber("de_DE", $key*1, 2, true, false),7," ",STR_PAD_LEFT)."%: ".str_pad(Util::formatCurrency("de_DE",$value,true),15," ",STR_PAD_LEFT)),91," ",STR_PAD_LEFT);
		
			$text .= "
-------------------------------------------------------------------------------------------
                                                        Gesamt Netto        ".str_pad(Util::conv_euro8(Util::formatCurrency("de_DE",$netto,true)),15," ",STR_PAD_LEFT)."
$s
                                                      -------------------------------------
                                                              Gesamt        ".str_pad(Util::conv_euro8(Util::formatCurrency("de_DE",$gesamt,true)),15," ",STR_PAD_LEFT);
		
		
		} else $text = "Ihr Warenkorb enthält keine Artikel.";

		$this->elementPointer = 0;
		
		$paypalHTML .= '
	<p>
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc.gif" style="width:auto;border:0px;" name="submit" />
	</p>
</form>';
		
		#if($withPayPal) $_SESSION["CookieCart_payPalHTML"] = $paypalHTML;
		#elseif(isset($_SESSION["CookieCart_payPalHTML"])) unset($_SESSION["CookieCart_payPalHTML"]);
		
		$this->PayPalButton = $paypalHTML;
		
		return $text;
	}

	public function getKKDataForm(){
		$KKForm = new HTMLForm("KKData", array("KKType","KKNumber","KKValid"));
		$KKForm->setType("KKType", "select", "none", array("none" => "bitte auswählen...", "Visa" => "Visa", "MasterCard" => "MasterCard", "American Express" => "American Express"));
		$KKForm->hasFormTag(false);
		
		$KKForm->setLabel("KKType", "Kreditkarte");
		$KKForm->setLabel("KKNumber", "Nummer");
		$KKForm->setLabel("KKValid", "gültig bis");

		return $KKForm;
	}
	
	public function getInfo($artikelID, $type = null){
		if($type == null) $type = $this->useClass[0];
		if(!$this->exists($artikelID)) return false;
		else {
			$regs = array();
			ereg("--$artikelID:__:([0-9]+):__:$type--", $this->cookie, $regs);
 			return $regs[1];
		}
	}
	
	public function getNextElement(){
		if($this->elements == null) {
			$this->elements = split("----",$this->cookie);
			$this->elements[0] = ereg_replace("^--","",$this->elements[0]);
			$this->elements[count($this->elements)-1] = ereg_replace("--$","",$this->elements[count($this->elements)-1]);
		}
		
		if(!isset($this->elements[$this->elementPointer])) return null;
		$s = split(":__:",$this->elements[$this->elementPointer++]);
		return $s;
	}
	
	public static function imagePathCallback($t, $A){
		return "./index.php?a=DBImage&amp;id=Artikel:::".$t.":::bild&amp;r=".rand();
	}
}
?>