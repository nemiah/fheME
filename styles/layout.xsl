<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes" doctype-public="-//W3C/DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd" />
	
	<!--<xsl:output
     method="xml"
     doctype-system="about:legacy-compat"
     encoding="UTF-8"
     indent="yes" />-->
	 <xsl:template match="/phynx/HTMLGUI">
		<html>
			<head>
				<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
				<meta name="revisit-after" content="14 days" />
				<meta http-equiv="content-encoding" content="gzip" />
				<meta http-equiv="cache-control" content="no-cache" />
				<title><xsl:value-of select="options/label[@for='title']" /></title>
				
				<link rel="shortcut icon" href="./images/FHSFavicon.ico" /> 
				
				
				<xsl:apply-templates select="javascripts/staticJsAbove" />
				
				
				<!--<script type="text/javascript">
					<xsl:attribute name="src">./interface/js.php?<xsl:apply-templates select="javascripts/js" />r=<xsl:value-of select="options/rand" /></xsl:attribute>
				</script>-->
				<xsl:apply-templates select="javascripts/js" />
				
				<xsl:apply-templates select="javascripts/staticJsBelow" />
				
				<script type="text/javascript">
					if(typeof contentManager == "undefined")
						alert("Die JavaScript-Dateien konnten nicht geladen werden.\nDies kann an der Server-Konfiguration liegen.\nBitte versuchen Sie, diese Anwendung in ein Unterverzeichnis zu installieren.");
				</script>
				
				<xsl:apply-templates select="stylesheets/css" />
				
				<!--[if lt IE 7]>
				<script type="text/javascript">
					alert("Sie benötigen mindestens Internet Explorer Version 7!");
				</script>
				<![endif]-->
				
			</head>
			<body>
				<div id="DynamicJS" style="display: none;"></div>
				<!--<div style="position:fixed;top:0px;left:0px;width:20px;" id="growlContainer"></div>-->
				<div id="lightOverlay" style="display: none;" class="backgroundColor0">
					<svg
					   xmlns:svg="http://www.w3.org/2000/svg"
					   xmlns:xlink="http://www.w3.org/1999/xlink">
					  <defs
						 id="defs3012">
						<linearGradient
						   x1="-42.290161"
						   y1="143.31378"
						   x2="403.77344"
						   y2="1179.3137"
						   id="linearGradient3282"
						   xlink:href="#linearGradient3276"
						   gradientUnits="userSpaceOnUse" />
						<linearGradient
						   id="linearGradient3276">
						  <stop
							 id="stop3278"
							 style="stop-color:#fdd99b;stop-opacity:1"
							 offset="0" />
						  <stop
							 id="stop3280"
							 style="stop-color:#fdd99b;stop-opacity:0"
							 offset="1" />
						</linearGradient>
						<linearGradient
						   x1="753.72754"
						   y1="157.27899"
						   x2="1185.8259"
						   y2="1161.8571"
						   id="linearGradient3025"
						   xlink:href="#linearGradient3276"
						   gradientUnits="userSpaceOnUse"
						   gradientTransform="translate(-652.92988,-535.04004)" />
						<linearGradient
						   x1="-0.46899444"
						   y1="-57.742737"
						   x2="88.643677"
						   y2="306.25726"
						   id="linearGradient3290"
						   xlink:href="#linearGradient3284"
						   gradientUnits="userSpaceOnUse" />
						<linearGradient
						   id="linearGradient3284">
						  <stop
							 id="stop3286"
							 style="stop-color:#cfbda8;stop-opacity:1"
							 offset="0" />
						  <stop
							 id="stop3288"
							 style="stop-color:#ffffff;stop-opacity:0"
							 offset="1" />
						</linearGradient>
						<linearGradient
						   x1="-0.46899444"
						   y1="-57.742737"
						   x2="88.643677"
						   y2="306.25726"
						   id="linearGradient3046"
						   xlink:href="#linearGradient3284"
						   gradientUnits="userSpaceOnUse"
						   gradientTransform="translate(-185.37404,592.87887)" />
						<linearGradient
						   x1="753.72754"
						   y1="157.27899"
						   x2="1519.8259"
						   y2="2141.8569"
						   id="linearGradient3083"
						   xlink:href="#linearGradient3284"
						   gradientUnits="userSpaceOnUse"
						   gradientTransform="translate(-925.25174,-953.99673)" />
					  </defs>
					  <g
						 transform="translate(0,147.63782)"
						 id="layer1">
						<path
						   d="M 1971.8271,1649.7169 C 1585.0954,1961.859 -818.24172,1014.1453 -905.09085,431.51644 720.11678,-1427.3773 9232.2538,-799.22686 1971.8271,1649.7169 z"
						   id="path3081"
						   style="opacity:0.48318092;fill:url(#linearGradient3083);fill-opacity:1;stroke:none" />
						<path
						   d="M 2244.1489,2068.6736 C 1857.5335,2380.6652 -409.38459,1008.6272 -806.76899,710.47313 725.51083,-911.45479 9384.8842,-75.05722 2244.1489,2068.6736 z"
						   id="rect3263"
						   style="opacity:0.48318092;fill:url(#linearGradient3025);fill-opacity:1;stroke:#a05a2c" />
					  </g>
					</svg>
				</div>
				<div id="darkOverlay" style="display: none; background-color:black;"></div>
				<div id="phim" style="display:none;"></div>

				<div id="boxInOverlay" style="display: none;" class="backgroundColor0 borderColor1">
					<xsl:apply-templates select="overlay" />
				</div>
				<div id="container" style="display:none;">
					<div id="messenger" style="left:-210px;top:0px;" class="backgroundColor3 borderColor1"></div>
					<div id="navigation"></div>
					
					<xsl:if test="options/isDesktop/@value='true'">
						<div id="desktopWrapper">
							<div id="wrapperHandler" class="backgroundColor1 borderColor1"></div>
							<div id="wrapper">
								<div id="contentScreen"></div>
								<table id="wrapperTable">
									<tr>
										<td id="wrapperTableTd1">
											<div id="contentLeft">
												<xsl:copy-of select="contentLeft" />
											</div>
										</td>
										<td id="wrapperTableTd2">
											<div id="contentRight">
												<xsl:copy-of select="contentRight" />
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</xsl:if>
					
					<xsl:if test="options/isDesktop/@value='false'">
						<div id="wrapper">
							<div id="contentScreen"></div>
							<table id="wrapperTable">
								<tr>
									<td id="wrapperTableTd1">
										<div id="contentLeft">
											<xsl:copy-of select="contentLeft" />
										</div>
									</td>
									<td id="wrapperTableTd2">
										<div id="contentRight">
											<xsl:copy-of select="contentRight" />
										</div>
									</td>
								</tr>
							</table>
						</div>
					</xsl:if>
					
					<div id="windows"></div>
					<div id="windowsPersistent"></div>
					<div id="footer">
						<p>
							<xsl:apply-templates select="footer" />
						</p>
					</div>
				</div>
				<script type="text/javascript">
					$j(document).ready(function() {
						Ajax.physion = '<xsl:value-of select="options/physion" />'

						contentManager.init();
						
						setTimeout(function(){
							if($j.jStorage.get('phynxUserCert', null) == null &amp;&amp; $j('#buttonCertificateLogin'))
								$j('#buttonCertificateLogin').css('opacity', '0.5');
							else
								userControl.autoCertificateLogin();
							} , 500);  
					});
					
				</script>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="overlay">
		<form id="loginForm" onsubmit="return false;">
			<table class="loginWindow">
				<colgroup>
					<col class="backgroundColor2" style="width:120px;" />
					<col class="backgroundColor3" />
				</colgroup>
				<tr>
					<td class="backgroundColor2"><label><xsl:value-of select="options/label[@for='username']" />:</label></td>
					<td><input style="width:285px;" tabindex="1" onfocus="focusMe(this);" onblur="blurMe(this);" type="text" name="loginUsername" id="loginUsername" onkeydown="userControl.abortAutoCertificateLogin(); if(event.keyCode == 13) userControl.doLogin();" /></td>
				</tr>
				<tr>
					<td><label><xsl:value-of select="options/label[@for='password']" />:</label></td>
					<td>
						<img
							style="float:right;"
							class="mouseoverFade"
							onclick="if($('loginOptions').style.display=='none') $('loginOptions').style.display=''; else $('loginOptions').style.display='none';"
							src="./images/i2/settings.png">
							<xsl:attribute name="title">
								<xsl:value-of select="options/label[@for='optionsImage']" />
							</xsl:attribute>
						</img>
						<img
							style="float:right;margin-right:5px;"
							class="mouseoverFade"
							onclick="rmeP('Users', -1, 'lostPassword', [$('loginUsername').value], 'checkResponse(transport);');"
							src="./images/i2/hilfe.png">
							<xsl:attribute name="title">
								<xsl:value-of select="options/label[@for='lostPassword']" />
							</xsl:attribute>
						</img>
						<input
							style="width:240px;"
							onfocus="focusMe(this);"
							onblur="blurMe(this);"
							type="password"
							id="loginPassword"
							tabindex="2"
							onkeydown="userControl.abortAutoCertificateLogin(); if(event.keyCode == 13) userControl.doLogin();"
						/>
					</td>
				</tr>
				<tr id="loginOptions">
					<xsl:if test="count(../applications/*) &lt;= 1 or options/showApplicationsList/@value='0'">
						<xsl:attribute name="style">display:none;</xsl:attribute>
					</xsl:if>
					<td><label><xsl:value-of select="options/label[@for='application']" />:</label></td>
					<td>
						<select
							style="width:110px;float:right;"
							id="loginSprache"
							name="loginSprache"
							tabindex="4"
							onkeydown="if(event.keyCode == 13) userControl.doLogin();">
							<xsl:apply-templates select="./languages/lang" />
						</select>
						<xsl:if test="options/showApplicationsList/@value='1'">
							<select
								style="width:160px;"
								id="anwendung"
								name="anwendung"
								tabindex="3"
								onkeydown="if(event.keyCode == 13) userControl.doLogin();">
								<xsl:apply-templates select="../applications/app" />
							</select>
						</xsl:if>
						<xsl:if test="options/showApplicationsList/@value='0'">
							<input
								type="hidden"
								id="anwendung"
								name="anwendung">
								<xsl:attribute name="value"><xsl:value-of select="options/showApplicationsList/@defaultApplicationIfFalse" /></xsl:attribute>
							</input>
						</xsl:if>
						</td>
				</tr>
				<tr>
					<td colspan="2">
						<input
							class="LPBigButton backgroundColor3"
							type="button"
							style="float:right;background-image:url(./images/navi/keys.png);"
							onclick="userControl.doLogin();">
							<!--<xsl:attribute name="value"><xsl:value-of select="options/label[@for='login']" /></xsl:attribute>-->
						</input>
						<xsl:if test="options/showCertificateLogin/@value='true'">
							<input
								class="LPBigButton backgroundColor3"
								type="button"
								style="float:right;background-image:url(./plugins/Users/certificateLogin.png);margin-right:10px;"
								onclick="userControl.doCertificateLogin();"
								id="buttonCertificateLogin">
								<!--<xsl:attribute name="value"><xsl:value-of select="options/label[@for='login']" /></xsl:attribute>-->
							</input>
							<div id="countdownCertificateLogin" style="float:right;margin-top:20px;width:20px;text-align:right;margin-right:5px;"></div>
						</xsl:if>
						<div style="padding-top:23px;">
							<input
								type="checkbox"
								style="margin-right:5px;float:left"
								name="saveLoginData"
								id="saveLoginData" />
							<label
								style="float:none;display:inline;font-weight:normal;"
								for="saveLoginData">
								<xsl:value-of select="options/label[@for='save']" />
							</label>
						</div>
						<input type="hidden" value="" name="loginSHAPassword" id="loginSHAPassword" />
					</td>
				</tr>
				<xsl:if test="options/isDemo/@value='true'">
					<tr>
						<td colspan="2">
							<xsl:value-of select="options/label[@for='isDemo']" />
						</td>
					</tr>
				</xsl:if>
				
				<tr id="loginCertOptions" style="display:none;" class="backgroundColor3">
					<td colspan="2">In diesem Browser wurde noch kein Zertifikat für die Ein-Klick-Anmeldung hinterlegt. Bitte bestellen Sie ein Zertifikat für Ihren Benutzer bei <a href="http://www.furtmeier.it/page-Kontakt" target="_blank">Furtmeier Hard- und Software</a> und kopieren Sie es anschließend in das Textfeld:
						<br />
						<br />
						<textarea id="loginNewCertificate" class="backgroundColor2" style="width:99%;font-size:10px;height:100px;"></textarea>
						<br />
						<br />
						<input type="button" class="bigButton backgroundColor2" style="background-image:url(./images/navi/bestaetigung.png);float:right;" onclick="userControl.saveCertificate();" value="speichern"/>
					</td>
				</tr>
				
				<xsl:if test="options/hasImpressum/@value!=''">
					<tr>
						<td colspan="2" style="text-align:right;font-size:10px;">
							<a target="_blank"><xsl:attribute name="href"><xsl:value-of select="options/hasImpressum/@value" /></xsl:attribute><xsl:attribute name="onclick">window.open('<xsl:value-of select="options/hasImpressum/@value" />','Impressum','height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes'); return false;</xsl:attribute>Impressum</a>
							<xsl:if test="options/hasDatenschutz/@value!=''">
								 | <a target="_blank"><xsl:attribute name="href"><xsl:value-of select="options/hasDatenschutz/@value" /></xsl:attribute><xsl:attribute name="onclick">window.open('<xsl:value-of select="options/hasDatenschutz/@value" />','Datenschutz','height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes'); return false;</xsl:attribute>Datenschutz</a>
							</xsl:if>
							<xsl:if test="options/hasRegistrierung/@value!=''">
								 | <a target="_blank"><xsl:attribute name="href"><xsl:value-of select="options/hasRegistrierung/@value" /></xsl:attribute><xsl:attribute name="onclick">window.open('<xsl:value-of select="options/hasRegistrierung/@value" />','Datenschutz','height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes'); return false;</xsl:attribute>Registrieren</a>
							</xsl:if>
						</td>
					</tr>
				</xsl:if>
				
				<xsl:if test="options/isExtendedDemo/@value='true'">
					<tr>
						<td colspan="2">
							<xsl:value-of select="options/label[@for='extDemo']" />
						</td>
					</tr>
				</xsl:if>
			</table>
		</form>
	</xsl:template>
	
	<xsl:template match="css">
		<link rel="stylesheet" type="text/css"><xsl:attribute name="href"><xsl:value-of select="." /></xsl:attribute></link>
	</xsl:template>
	
	<xsl:template match="app">
		<option><xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute><xsl:value-of select="." /></option>
	</xsl:template>
	
	<xsl:template match="lang">
		<option><xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute><xsl:value-of select="." /></option>
	</xsl:template>
	
	<!--<xsl:template match="js">path[]=<xsl:value-of select="." />&amp;</xsl:template>-->
	
	<xsl:template match="js">
		<script type="text/javascript"><xsl:attribute name="src"><xsl:value-of select="." /></xsl:attribute></script>
	</xsl:template>
	
	<xsl:template match="staticJsAbove">
		<script type="text/javascript"><xsl:attribute name="src"><xsl:value-of select="." /></xsl:attribute></script>
	</xsl:template>
	
	<xsl:template match="staticJsBelow">
		<script type="text/javascript"><xsl:attribute name="src"><xsl:value-of select="." /></xsl:attribute></script>
	</xsl:template>
	
	<xsl:template match="menu">
		<xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="entry">
		<div>
			<img><xsl:attribute name="src"><xsl:value-of select="icon" /></xsl:attribute></img>
			<xsl:value-of select="label" />
		</div>
	</xsl:template>
	
	<xsl:template match="logo">
		<img><xsl:attribute name="src"><xsl:value-of select="." /></xsl:attribute></img>
	</xsl:template>
	
	<xsl:template match="footer">
			<img
				style="margin-left:15px;float:left;"
				class="mouseoverFade"
				title="Abmelden"
				alt="Abmelden">
					<xsl:attribute name="src"><xsl:value-of select="iconLogout" /></xsl:attribute>
					<xsl:attribute name="onclick"><xsl:value-of select="options/onLogout" /></xsl:attribute>
				</img>

			<xsl:if test="options/showLayoutButton/@value='1'">
				<img
					onclick="phynxContextMenu.start(this, 'Colors','1','Einstellungen:','left', 'up');"
					style="float:right;margin-left:8px;margin-right:5px;"
					class="mouseoverFade"
					title="Layout"
					alt="Layout"><xsl:attribute name="src"><xsl:value-of select="iconLayout" /></xsl:attribute></img>
			</xsl:if>

			<xsl:if test="options/showHelpButton/@value='1'">
				<img
					onclick="window.open('http://www.phynx.de/support');"
					style="float:right;margin-left:8px;margin-right:5px;"
					class="mouseoverFade"
					title="Hilfe"
					alt="Hilfe"><xsl:attribute name="src"><xsl:value-of select="iconHelp" /></xsl:attribute></img>
			</xsl:if>

			<!--<xsl:if test="options/showDesktopButton/@value='true'">
				<img
					onclick="DesktopLink.toggle();"
					style="float:right;margin-left:8px;margin-right:5px;"
					class="mouseoverFade"
					title="Desktop"
					alt="Desktop"><xsl:attribute name="src"><xsl:value-of select="iconDesktop" /></xsl:attribute></img>
			</xsl:if>-->

			<xsl:if test="options/showCopyright/@value='1'">
				<xsl:copy-of select="copyright" />
			</xsl:if>
	</xsl:template>

</xsl:stylesheet>