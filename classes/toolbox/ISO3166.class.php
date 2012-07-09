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

class ISO3166 {

	public static function getCountries(){
		$codes = "ABW    AW    Aruba
AFG    AF    Afghanistan
AGO    AO    Angola
AIA    AI    Anguilla
ALA    AX    Åland Inseln
ALB    AL    Albanien
AND    AD    Andorra
ANT    AN    Niederländische Antillen
ARE    AE    Vereinigte Arabische Emirate
ARG    AR    Argentinien
ARM    AM    Armenien
ASM    AS    Amerikanisch Samoa
ATA    AQ    Antarktis
ATF    TF    Französische Südgebiete
ATG    AG    Antigua und Barbuda
AUS    AU    Australien
AUT    AT    Österreich
AZE    AZ    Aserbaidschan
BDI    BI    Burundi
BEL    BE    Belgien
BEN    BJ    Benin
BFA    BF    Burkina Faso
BGD    BD    Bangladesch
BGR    BG    Bulgarien
BHR    BH    Bahrain
BHS    BS    Bahamas
BIH    BA    Bosnien und Herzegowin
BLM    BL    St. Barthélemy
BLR    BY    Weißrussland
BLZ    BZ    Belize
BMU    BM    Bermuda
BOL    BO    Bolivien
BRA    BR    Brasilien
BRB    BB    Barbados
BRN    BN    Brunei Darussalam
BTN    BT    Bhutan
BVT    BV    Bouvetinsel
BWA    BW    Botsuana
CAF    CF    Zentralafrikanische Republik
CAN    CA    Kanada
CCK    CC    Kokosinseln
CHE    CH    Schweiz
CHL    CL    Chile
CHN    CN    China
CIV    CI    Côte d´Ivoire
CMR    CM    Kamerun
COD    CD    Kongo, Dem. Rep.
COG    CG    Kongo
COK    CK    Cookinseln
COL    CO    Kolumbien
COM    KM    Komoren
CPV    CV    Kap Verde
CRI    CR    Costa Rica
CUB    CU    Kuba
CXR    CX    Weihnachtsinsel
CYM    KY    Kaimaninseln
CYP    CY    Zypern
CZE    CZ    Tschechische Republik
DEU    DE    Deutschland
DJI    DJ    Republik Dschibuti
DMA    DM    Dominica
DNK    DK    Dänemark
DOM    DO    Dominikanische Republik
DZA    DZ    Algerien
ECU    EC    Ecuador
EGY    EG    Ägypten
ERI    ER    Eritrea
ESH    EH    Westsahara
ESP    ES    Spanien
EST    EE    Estland
ETH    ET    Äthiopien
FIN    FI    Finnland
FJI    FJ    Fidschi
FLK    FK    Falklandinseln
FRA    FR    Frankreich
FRO    FO    Färöer
FSM    FM    Mikronesien, Föderierte Staaten von
GAB    GA    Gabun
GBR    GB    Vereinigtes Königreich
GEO    GE    Georgien
GGY    GG    Guernsey
GHA    GH    Ghana
GIB    GI    Gibraltar
GIN    GN    Guinea
GLP    GP    Guadeloupe
GMB    GM    Gambia
GNB    GW    Guinea-Bissau
GNQ    GQ    Äquatorialguinea
GRC    GR    Griechenland
GRD    GD    Grenada
GRL    GL    Grönland
GTM    GT    Guatemala
GUF    GF    Französisch Guiana
GUM    GU    Guam
GUY    GY    Guyana
HKG    HK    Hong Kong
HMD    HM    Heard Insel und McDonald Inseln
HND    HN    Honduras
HRV    HR    Kroatien
HTI    HT    Haiti
HUN    HU    Ungarn
IDN    ID    Indonesien
IMN    IM    Isle of Man
IND    IN    Indien
IOT    IO    Britische Territorien im Indischen Ozean
IRL    IE    Irland
IRN    IR    Iran, Islam. Rep.
IRQ    IQ    Irak
ISL    IS    Island
ISR    IL    Israel
ITA    IT    Italien
JAM    JM    Jamaika
JEY    JE    Jersey
JOR    JO    Jordanien
JPN    JP    Japan
KAZ    KZ    Kasachstan
KEN    KE    Kenia
KGZ    KG    Kirgisistan
KHM    KH    Kambodscha
KIR    KI    Kiribati
KNA    KN    St. Kitts und Nevis
KOR    KR    Korea, Rep.
KWT    KW    Kuwait
LAO    LA    Laos, Dem. Volksrep.
LBN    LB    Libanon
LBR    LR    Liberia
LBY    LY    Libysch-Arabische Dschamahirija
LCA    LC    St. Lucia
LIE    LI    Liechtenstein
LKA    LK    Sri Lanka
LSO    LS    Lesotho
LTU    LT    Litauen
LUX    LU    Luxemburg
LVA    LV    Lettland
MAC    MO    Macao
MAF    MF    St. Martin
MAR    MA    Marokko
MCO    MC    Monaco
MDA    MD    Moldau, Rep.
MDG    MG    Madagaskar
MDV    MV    Malediven
MEX    MX    Mexiko
MHL    MH    Marshallinseln
MKD    MK    Mazedonien, ehemalige jugoslawische Republik
MLI    ML    Mali
MLT    MT    Malta
MMR    MM    Myanmar
MNE    ME    Montenegro
MNG    MN    Mongolei
MNP    MP    Nördliche Marianen
MOZ    MZ    Mosambik
MRT    MR    Mauretanien
MSR    MS    Montserrat
MTQ    MQ    Martinique
MUS    MU    Mauritius
MWI    MW    Malawi
MYS    MY    Malaysia
MYT    YT    Mayotte
NAM    NA    Namibia
NCL    NC    Neukaledonien
NER    NE    Niger
NFK    NF    Norfolk Insel
NGA    NG    Nigeria
NIC    NI    Nicaragua
NIU    NU    Niue
NLD    NL    Niederlande
NOR    NO    Norwegen
NPL    NP    Nepal
NRU    NR    Nauru
NZL    NZ    Neuseeland
OMN    OM    Oman
PAK    PK    Pakistan
PAN    PA    Panama
PCN    PN    Pitcairn
PER    PE    Peru
PHL    PH    Philippinen
PLW    PW    Palau
PNG    PG    Papua-Neuguinea
POL    PL    Polen
PRI    PR    Puerto Rico
PRK    KP    Korea, Dem. Volksrep.
PRT    PT    Portugal
PRY    PY    Paraguay
PSE    PS    Palästinische Gebiete
PYF    PF    Französisch Polynesien
QAT    QA    Katar
REU    RE    Réunion
ROU    RO    Rumänien
RUS    RU    Russische Föderation
RWA    RW    Ruanda
SAU    SA    Saudi-Arabien
SDN    SD    Sudan
SEN    SN    Senegal
SGP    SG    Singapur
SGS    GS    Südgeorgien und die Südlichen Sandwichinseln
SHN    SH    Saint Helena
SJM    SJ    Svalbard und Jan Mayen
SLB    SB    Salomonen
SLE    SL    Sierra Leone
SLV    SV    El Salvador
SMR    SM    San Marino
SOM    SO    Somalia
SPM    PM    Saint Pierre und Miquelon
SRB    RS    Serbien
STP    ST    São Tomé und Príncipe
SUR    SR    Suriname
SVK    SK    Slowakei
SVN    SI    Slowenien
SWE    SE    Schweden
SWZ    SZ    Swasiland
SYC    SC    Seychellen
SYR    SY    Syrien, Arab. Rep.
TCA    TC    Turks- und Caicosinseln
TCD    TD    Tschad
TGO    TG    Togo
THA    TH    Thailand
TJK    TJ    Tadschikistan
TKL    TK    Tokelau
TKM    TM    Turkmenistan
TLS    TL    Timor-Leste
TON    TO    Tonga
TTO    TT    Trinidad und Tobago
TUN    TN    Tunesien
TUR    TR    Türkei
TUV    TV    Tuvalu
TWN    TW    Taiwan
TZA    TZ    Tansania, Vereinigte Rep.
UGA    UG    Uganda
UKR    UA    Ukraine
UMI    UM    United States Minor Outlying Islands
URY    UY    Uruguay
USA    US    Vereinigte Staaten von Amerika
UZB    UZ    Usbekistan
VAT    VA    Heiliger Stuhl
VCT    VC    St. Vincent und die Grenadinen
VEN    VE    Venezuela
VGB    VG    Britische Jungferninseln
VIR    VI    Amerikanische Jungferninseln
VNM    VN    Vietnam
VUT    VU    Vanuatu
WLF    WF    Wallis und Futuna
WSM    WS    Samoa
YEM    YE    Jemen
ZAF    ZA    Südafrika
ZMB    ZM    Sambia
ZWE    ZW    Simbabwe";

		$lines = explode("\n", $codes);
		$countries = array();

		foreach($lines AS $k => $v){
			$values = explode("    ", $v);
			$countries[$values[1]] = $values[2];
		}

		asort($countries);

		return $countries;
	}

	public static function getCountryToCode($code){
		$countries = self::getCountries();

		return $countries[$code];
	}

	public static function getCodeToCountry($country){
		$countries = self::getCountries();

		return array_search($country, $countries);
	}

	public static function getZones($code){
		$zones = array();
		$zones["AT"] = array(
			"1" => "Burgenland",
			"2" => "Kärnten",
			"3" => "Niederösterreich",
			"4" => "Oberösterreich",
			"5" => "Salzburg",
			"6" => "Steiermark",
			"7" => "Tirol",
			"8" => "Vorarlberg",
			"9" => "Wien");
		
		return $zones[$code];
	}
	
	public static function getDistricts($code, $zone){
		$csv = array("AT" => '1;"Burgenland";101;"Eisenstadt(Stadt)"
1;"Burgenland";102;"Rust(Stadt)"
1;"Burgenland";103;"Eisenstadt-Umgebung"
1;"Burgenland";104;"Güssing"
1;"Burgenland";105;"Jennersdorf"
1;"Burgenland";106;"Mattersburg"
1;"Burgenland";107;"Neusiedl am See"
1;"Burgenland";108;"Oberpullendorf"
1;"Burgenland";109;"Oberwart"
2;"Kärnten";201;"Klagenfurt(Stadt)"
2;"Kärnten";202;"Villach(Stadt)"
2;"Kärnten";203;"Hermagor"
2;"Kärnten";204;"Klagenfurt Land"
2;"Kärnten";205;"Sankt Veit an der Glan"
2;"Kärnten";206;"Spittal an der Drau"
2;"Kärnten";207;"Villach Land"
2;"Kärnten";208;"Völkermarkt"
2;"Kärnten";209;"Wolfsberg"
2;"Kärnten";210;"Feldkirchen"
3;"Niederösterreich";301;"Krems an der Donau(Stadt)"
3;"Niederösterreich";302;"Sankt Pölten(Stadt)"
3;"Niederösterreich";303;"Waidhofen an der Ybbs(Stadt)"
3;"Niederösterreich";304;"Wiener Neustadt(Stadt)"
3;"Niederösterreich";305;"Amstetten"
3;"Niederösterreich";306;"Baden"
3;"Niederösterreich";307;"Bruck an der Leitha"
3;"Niederösterreich";308;"Gänserndorf"
3;"Niederösterreich";309;"Gmünd"
3;"Niederösterreich";310;"Hollabrunn"
3;"Niederösterreich";311;"Horn"
3;"Niederösterreich";312;"Korneuburg"
3;"Niederösterreich";313;"Krems(Land)"
3;"Niederösterreich";314;"Lilienfeld"
3;"Niederösterreich";315;"Melk"
3;"Niederösterreich";316;"Mistelbach"
3;"Niederösterreich";317;"Mödling"
3;"Niederösterreich";318;"Neunkirchen"
3;"Niederösterreich";319;"Sankt Pölten(Land)"
3;"Niederösterreich";320;"Scheibbs"
3;"Niederösterreich";321;"Tulln"
3;"Niederösterreich";322;"Waidhofen an der Thaya"
3;"Niederösterreich";323;"Wiener Neustadt(Land)"
3;"Niederösterreich";324;"Wien-Umgebung"
3;"Niederösterreich";325;"Zwettl"
4;"Oberösterreich";401;"Linz(Stadt)"
4;"Oberösterreich";402;"Steyr(Stadt)"
4;"Oberösterreich";403;"Wels(Stadt)"
4;"Oberösterreich";404;"Braunau am Inn"
4;"Oberösterreich";405;"Eferding"
4;"Oberösterreich";406;"Freistadt"
4;"Oberösterreich";407;"Gmunden"
4;"Oberösterreich";408;"Grieskirchen"
4;"Oberösterreich";409;"Kirchdorf an der Krems"
4;"Oberösterreich";410;"Linz-Land"
4;"Oberösterreich";411;"Perg"
4;"Oberösterreich";412;"Ried im Innkreis"
4;"Oberösterreich";413;"Rohrbach"
4;"Oberösterreich";414;"Schärding"
4;"Oberösterreich";415;"Steyr-Land"
4;"Oberösterreich";416;"Urfahr-Umgebung"
4;"Oberösterreich";417;"Vöcklabruck"
4;"Oberösterreich";418;"Wels-Land"
5;"Salzburg";501;"Salzburg(Stadt)"
5;"Salzburg";502;"Hallein"
5;"Salzburg";503;"Salzburg-Umgebung"
5;"Salzburg";504;"Sankt Johann im Pongau"
5;"Salzburg";505;"Tamsweg"
5;"Salzburg";506;"Zell am See"
6;"Steiermark";601;"Graz(Stadt)"
6;"Steiermark";602;"Bruck an der Mur"
6;"Steiermark";603;"Deutschlandsberg"
6;"Steiermark";604;"Feldbach"
6;"Steiermark";605;"Fürstenfeld"
6;"Steiermark";606;"Graz-Umgebung"
6;"Steiermark";607;"Hartberg"
6;"Steiermark";610;"Leibnitz"
6;"Steiermark";611;"Leoben"
6;"Steiermark";612;"Liezen"
6;"Steiermark";613;"Mürzzuschlag"
6;"Steiermark";614;"Murau"
6;"Steiermark";615;"Radkersburg"
6;"Steiermark";616;"Voitsberg"
6;"Steiermark";617;"Weiz"
6;"Steiermark";620;"Murtal"
7;"Tirol";701;"Innsbruck-Stadt"
7;"Tirol";702;"Imst"
7;"Tirol";703;"Innsbruck-Land"
7;"Tirol";704;"Kitzbühel"
7;"Tirol";705;"Kufstein"
7;"Tirol";706;"Landeck"
7;"Tirol";707;"Lienz"
7;"Tirol";708;"Reutte"
7;"Tirol";709;"Schwaz"
8;"Vorarlberg";801;"Bludenz"
8;"Vorarlberg";802;"Bregenz"
8;"Vorarlberg";803;"Dornbirn"
8;"Vorarlberg";804;"Feldkirch"
9;"Wien";900;"Wien(Stadt)"
9;"Wien";901;"Wien  1.,Innere Stadt"
9;"Wien";902;"Wien  2.,Leopoldstadt"
9;"Wien";903;"Wien  3.,Landstraße"
9;"Wien";904;"Wien  4.,Wieden"
9;"Wien";905;"Wien  5.,Margareten"
9;"Wien";906;"Wien  6.,Mariahilf"
9;"Wien";907;"Wien  7.,Neubau"
9;"Wien";908;"Wien  8.,Josefstadt"
9;"Wien";909;"Wien  9.,Alsergrund"
9;"Wien";910;"Wien 10.,Favoriten"
9;"Wien";911;"Wien 11.,Simmering"
9;"Wien";912;"Wien 12.,Meidling"
9;"Wien";913;"Wien 13.,Hietzing"
9;"Wien";914;"Wien 14.,Penzing"
9;"Wien";915;"Wien 15.,Rudolfsheim-Fünfhaus"
9;"Wien";916;"Wien 16.,Ottakring"
9;"Wien";917;"Wien 17.,Hernals"
9;"Wien";918;"Wien 18.,Währing"
9;"Wien";919;"Wien 19.,Döbling"
9;"Wien";920;"Wien 20.,Brigittenau"
9;"Wien";921;"Wien 21.,Floridsdorf"
9;"Wien";922;"Wien 22.,Donaustadt"
9;"Wien";923;"Wien 23.,Liesing"');

		$zones = array();
		$ex = explode("\n", $csv[$code]);
		foreach($ex AS $line){
			$cells = explode(";", $line);
			
			if($cells[0] != $zone)
				continue;
			
			$zones[$cells[2]] = trim($cells[3], "\"");
		}
		
		return $zones;
	}
}
?>