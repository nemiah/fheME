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

class ISO3166 {

	public static function getCountries($lang = "de"){
		switch($lang){
			case "de":
				return self::getCountriesDE();
			break;
			
			case "en":
				return self::getCountriesEN();
			break;
		}
	}
	
	public static function getCountriesEN(){
		//FROM https://github.com/lukes/ISO-3166-Countries-with-Regional-Codes
		
		$codes = "Afghanistan,AF,AFG,004,ISO 3166-2:AF,142,034
Åland Islands,AX,ALA,248,ISO 3166-2:AX,150,154
Albania,AL,ALB,008,ISO 3166-2:AL,150,039
Algeria,DZ,DZA,012,ISO 3166-2:DZ,002,015
American Samoa,AS,ASM,016,ISO 3166-2:AS,009,061
Andorra,AD,AND,020,ISO 3166-2:AD,150,039
Angola,AO,AGO,024,ISO 3166-2:AO,002,017
Anguilla,AI,AIA,660,ISO 3166-2:AI,019,029
Antarctica,AQ,ATA,010,ISO 3166-2:AQ
Antigua and Barbuda,AG,ATG,028,ISO 3166-2:AG,019,029
Argentina,AR,ARG,032,ISO 3166-2:AR,019,005
Armenia,AM,ARM,051,ISO 3166-2:AM,142,145
Aruba,AW,ABW,533,ISO 3166-2:AW,019,029
Australia,AU,AUS,036,ISO 3166-2:AU,009,053
Austria,AT,AUT,040,ISO 3166-2:AT,150,155
Azerbaijan,AZ,AZE,031,ISO 3166-2:AZ,142,145
Bahamas,BS,BHS,044,ISO 3166-2:BS,019,029
Bahrain,BH,BHR,048,ISO 3166-2:BH,142,145
Bangladesh,BD,BGD,050,ISO 3166-2:BD,142,034
Barbados,BB,BRB,052,ISO 3166-2:BB,019,029
Belarus,BY,BLR,112,ISO 3166-2:BY,150,151
Belgium,BE,BEL,056,ISO 3166-2:BE,150,155
Belize,BZ,BLZ,084,ISO 3166-2:BZ,019,013
Benin,BJ,BEN,204,ISO 3166-2:BJ,002,011
Bermuda,BM,BMU,060,ISO 3166-2:BM,019,021
Bhutan,BT,BTN,064,ISO 3166-2:BT,142,034
Bolivia\, Plurinational State of,BO,BOL,068,ISO 3166-2:BO,019,005
Bonaire\, Sint Eustatius and Saba,BQ,BES,535,ISO 3166-2:BQ,019,029
Bosnia and Herzegovina,BA,BIH,070,ISO 3166-2:BA,150,039
Botswana,BW,BWA,072,ISO 3166-2:BW,002,018
Bouvet Island,BV,BVT,074,ISO 3166-2:BV
Brazil,BR,BRA,076,ISO 3166-2:BR,019,005
British Indian Ocean Territory,IO,IOT,086,ISO 3166-2:IO
Brunei Darussalam,BN,BRN,096,ISO 3166-2:BN,142,035
Bulgaria,BG,BGR,100,ISO 3166-2:BG,150,151
Burkina Faso,BF,BFA,854,ISO 3166-2:BF,002,011
Burundi,BI,BDI,108,ISO 3166-2:BI,002,014
Cambodia,KH,KHM,116,ISO 3166-2:KH,142,035
Cameroon,CM,CMR,120,ISO 3166-2:CM,002,017
Canada,CA,CAN,124,ISO 3166-2:CA,019,021
Cape Verde,CV,CPV,132,ISO 3166-2:CV,002,011
Cayman Islands,KY,CYM,136,ISO 3166-2:KY,019,029
Central African Republic,CF,CAF,140,ISO 3166-2:CF,002,017
Chad,TD,TCD,148,ISO 3166-2:TD,002,017
Chile,CL,CHL,152,ISO 3166-2:CL,019,005
China,CN,CHN,156,ISO 3166-2:CN,142,030
Christmas Island,CX,CXR,162,ISO 3166-2:CX
Cocos (Keeling) Islands,CC,CCK,166,ISO 3166-2:CC
Colombia,CO,COL,170,ISO 3166-2:CO,019,005
Comoros,KM,COM,174,ISO 3166-2:KM,002,014
Congo,CG,COG,178,ISO 3166-2:CG,002,017
Congo\, the Democratic Republic of the,CD,COD,180,ISO 3166-2:CD,002,017
Cook Islands,CK,COK,184,ISO 3166-2:CK,009,061
Costa Rica,CR,CRI,188,ISO 3166-2:CR,019,013
Côte d'Ivoire,CI,CIV,384,ISO 3166-2:CI,002,011
Croatia,HR,HRV,191,ISO 3166-2:HR,150,039
Cuba,CU,CUB,192,ISO 3166-2:CU,019,029
Curaçao,CW,CUW,531,ISO 3166-2:CW,019,029
Cyprus,CY,CYP,196,ISO 3166-2:CY,142,145
Czech Republic,CZ,CZE,203,ISO 3166-2:CZ,150,151
Denmark,DK,DNK,208,ISO 3166-2:DK,150,154
Djibouti,DJ,DJI,262,ISO 3166-2:DJ,002,014
Dominica,DM,DMA,212,ISO 3166-2:DM,019,029
Dominican Republic,DO,DOM,214,ISO 3166-2:DO,019,029
Ecuador,EC,ECU,218,ISO 3166-2:EC,019,005
Egypt,EG,EGY,818,ISO 3166-2:EG,002,015
El Salvador,SV,SLV,222,ISO 3166-2:SV,019,013
Equatorial Guinea,GQ,GNQ,226,ISO 3166-2:GQ,002,017
Eritrea,ER,ERI,232,ISO 3166-2:ER,002,014
Estonia,EE,EST,233,ISO 3166-2:EE,150,154
Ethiopia,ET,ETH,231,ISO 3166-2:ET,002,014
Falkland Islands (Malvinas),FK,FLK,238,ISO 3166-2:FK,019,005
Faroe Islands,FO,FRO,234,ISO 3166-2:FO,150,154
Fiji,FJ,FJI,242,ISO 3166-2:FJ,009,054
Finland,FI,FIN,246,ISO 3166-2:FI,150,154
France,FR,FRA,250,ISO 3166-2:FR,150,155
French Guiana,GF,GUF,254,ISO 3166-2:GF,019,005
French Polynesia,PF,PYF,258,ISO 3166-2:PF,009,061
French Southern Territories,TF,ATF,260,ISO 3166-2:TF
Gabon,GA,GAB,266,ISO 3166-2:GA,002,017
Gambia,GM,GMB,270,ISO 3166-2:GM,002,011
Georgia,GE,GEO,268,ISO 3166-2:GE,142,145
Germany,DE,DEU,276,ISO 3166-2:DE,150,155
Ghana,GH,GHA,288,ISO 3166-2:GH,002,011
Gibraltar,GI,GIB,292,ISO 3166-2:GI,150,039
Greece,GR,GRC,300,ISO 3166-2:GR,150,039
Greenland,GL,GRL,304,ISO 3166-2:GL,019,021
Grenada,GD,GRD,308,ISO 3166-2:GD,019,029
Guadeloupe,GP,GLP,312,ISO 3166-2:GP,019,029
Guam,GU,GUM,316,ISO 3166-2:GU,009,057
Guatemala,GT,GTM,320,ISO 3166-2:GT,019,013
Guernsey,GG,GGY,831,ISO 3166-2:GG,150,154
Guinea,GN,GIN,324,ISO 3166-2:GN,002,011
Guinea-Bissau,GW,GNB,624,ISO 3166-2:GW,002,011
Guyana,GY,GUY,328,ISO 3166-2:GY,019,005
Haiti,HT,HTI,332,ISO 3166-2:HT,019,029
Heard Island and McDonald Islands,HM,HMD,334,ISO 3166-2:HM
Holy See (Vatican City State),VA,VAT,336,ISO 3166-2:VA,150,039
Honduras,HN,HND,340,ISO 3166-2:HN,019,013
Hong Kong,HK,HKG,344,ISO 3166-2:HK,142,030
Hungary,HU,HUN,348,ISO 3166-2:HU,150,151
Iceland,IS,ISL,352,ISO 3166-2:IS,150,154
India,IN,IND,356,ISO 3166-2:IN,142,034
Indonesia,ID,IDN,360,ISO 3166-2:ID,142,035
Iran\, Islamic Republic of,IR,IRN,364,ISO 3166-2:IR,142,034
Iraq,IQ,IRQ,368,ISO 3166-2:IQ,142,145
Ireland,IE,IRL,372,ISO 3166-2:IE,150,154
Isle of Man,IM,IMN,833,ISO 3166-2:IM,150,154
Israel,IL,ISR,376,ISO 3166-2:IL,142,145
Italy,IT,ITA,380,ISO 3166-2:IT,150,039
Jamaica,JM,JAM,388,ISO 3166-2:JM,019,029
Japan,JP,JPN,392,ISO 3166-2:JP,142,030
Jersey,JE,JEY,832,ISO 3166-2:JE,150,154
Jordan,JO,JOR,400,ISO 3166-2:JO,142,145
Kazakhstan,KZ,KAZ,398,ISO 3166-2:KZ,142,143
Kenya,KE,KEN,404,ISO 3166-2:KE,002,014
Kiribati,KI,KIR,296,ISO 3166-2:KI,009,057
Korea\, Democratic People's Republic of,KP,PRK,408,ISO 3166-2:KP,142,030
Korea\, Republic of,KR,KOR,410,ISO 3166-2:KR,142,030
Kuwait,KW,KWT,414,ISO 3166-2:KW,142,145
Kyrgyzstan,KG,KGZ,417,ISO 3166-2:KG,142,143
Lao People's Democratic Republic,LA,LAO,418,ISO 3166-2:LA,142,035
Latvia,LV,LVA,428,ISO 3166-2:LV,150,154
Lebanon,LB,LBN,422,ISO 3166-2:LB,142,145
Lesotho,LS,LSO,426,ISO 3166-2:LS,002,018
Liberia,LR,LBR,430,ISO 3166-2:LR,002,011
Libya,LY,LBY,434,ISO 3166-2:LY,002,015
Liechtenstein,LI,LIE,438,ISO 3166-2:LI,150,155
Lithuania,LT,LTU,440,ISO 3166-2:LT,150,154
Luxembourg,LU,LUX,442,ISO 3166-2:LU,150,155
Macao,MO,MAC,446,ISO 3166-2:MO,142,030
Macedonia\, the former Yugoslav Republic of,MK,MKD,807,ISO 3166-2:MK,150,039
Madagascar,MG,MDG,450,ISO 3166-2:MG,002,014
Malawi,MW,MWI,454,ISO 3166-2:MW,002,014
Malaysia,MY,MYS,458,ISO 3166-2:MY,142,035
Maldives,MV,MDV,462,ISO 3166-2:MV,142,034
Mali,ML,MLI,466,ISO 3166-2:ML,002,011
Malta,MT,MLT,470,ISO 3166-2:MT,150,039
Marshall Islands,MH,MHL,584,ISO 3166-2:MH,009,057
Martinique,MQ,MTQ,474,ISO 3166-2:MQ,019,029
Mauritania,MR,MRT,478,ISO 3166-2:MR,002,011
Mauritius,MU,MUS,480,ISO 3166-2:MU,002,014
Mayotte,YT,MYT,175,ISO 3166-2:YT,002,014
Mexico,MX,MEX,484,ISO 3166-2:MX,019,013
Micronesia\, Federated States of,FM,FSM,583,ISO 3166-2:FM,009,057
Moldova\, Republic of,MD,MDA,498,ISO 3166-2:MD,150,151
Monaco,MC,MCO,492,ISO 3166-2:MC,150,155
Mongolia,MN,MNG,496,ISO 3166-2:MN,142,030
Montenegro,ME,MNE,499,ISO 3166-2:ME,150,039
Montserrat,MS,MSR,500,ISO 3166-2:MS,019,029
Morocco,MA,MAR,504,ISO 3166-2:MA,002,015
Mozambique,MZ,MOZ,508,ISO 3166-2:MZ,002,014
Myanmar,MM,MMR,104,ISO 3166-2:MM,142,035
Namibia,NA,NAM,516,ISO 3166-2:NA,002,018
Nauru,NR,NRU,520,ISO 3166-2:NR,009,057
Nepal,NP,NPL,524,ISO 3166-2:NP,142,034
Netherlands,NL,NLD,528,ISO 3166-2:NL,150,155
New Caledonia,NC,NCL,540,ISO 3166-2:NC,009,054
New Zealand,NZ,NZL,554,ISO 3166-2:NZ,009,053
Nicaragua,NI,NIC,558,ISO 3166-2:NI,019,013
Niger,NE,NER,562,ISO 3166-2:NE,002,011
Nigeria,NG,NGA,566,ISO 3166-2:NG,002,011
Niue,NU,NIU,570,ISO 3166-2:NU,009,061
Norfolk Island,NF,NFK,574,ISO 3166-2:NF,009,053
Northern Mariana Islands,MP,MNP,580,ISO 3166-2:MP,009,057
Norway,NO,NOR,578,ISO 3166-2:NO,150,154
Oman,OM,OMN,512,ISO 3166-2:OM,142,145
Pakistan,PK,PAK,586,ISO 3166-2:PK,142,034
Palau,PW,PLW,585,ISO 3166-2:PW,009,057
Palestinian Territory\, Occupied,PS,PSE,275,ISO 3166-2:PS,142,145
Panama,PA,PAN,591,ISO 3166-2:PA,019,013
Papua New Guinea,PG,PNG,598,ISO 3166-2:PG,009,054
Paraguay,PY,PRY,600,ISO 3166-2:PY,019,005
Peru,PE,PER,604,ISO 3166-2:PE,019,005
Philippines,PH,PHL,608,ISO 3166-2:PH,142,035
Pitcairn,PN,PCN,612,ISO 3166-2:PN,009,061
Poland,PL,POL,616,ISO 3166-2:PL,150,151
Portugal,PT,PRT,620,ISO 3166-2:PT,150,039
Puerto Rico,PR,PRI,630,ISO 3166-2:PR,019,029
Qatar,QA,QAT,634,ISO 3166-2:QA,142,145
Réunion,RE,REU,638,ISO 3166-2:RE,002,014
Romania,RO,ROU,642,ISO 3166-2:RO,150,151
Russian Federation,RU,RUS,643,ISO 3166-2:RU,150,151
Rwanda,RW,RWA,646,ISO 3166-2:RW,002,014
Saint Barthélemy,BL,BLM,652,ISO 3166-2:BL,019,029
Saint Helena\, Ascension and Tristan da Cunha,SH,SHN,654,ISO 3166-2:SH,002,011
Saint Kitts and Nevis,KN,KNA,659,ISO 3166-2:KN,019,029
Saint Lucia,LC,LCA,662,ISO 3166-2:LC,019,029
Saint Martin (French part),MF,MAF,663,ISO 3166-2:MF,019,029
Saint Pierre and Miquelon,PM,SPM,666,ISO 3166-2:PM,019,021
Saint Vincent and the Grenadines,VC,VCT,670,ISO 3166-2:VC,019,029
Samoa,WS,WSM,882,ISO 3166-2:WS,009,061
San Marino,SM,SMR,674,ISO 3166-2:SM,150,039
Sao Tome and Principe,ST,STP,678,ISO 3166-2:ST,002,017
Saudi Arabia,SA,SAU,682,ISO 3166-2:SA,142,145
Senegal,SN,SEN,686,ISO 3166-2:SN,002,011
Serbia,RS,SRB,688,ISO 3166-2:RS,150,039
Seychelles,SC,SYC,690,ISO 3166-2:SC,002,014
Sierra Leone,SL,SLE,694,ISO 3166-2:SL,002,011
Singapore,SG,SGP,702,ISO 3166-2:SG,142,035
Sint Maarten (Dutch part),SX,SXM,534,ISO 3166-2:SX,019,029
Slovakia,SK,SVK,703,ISO 3166-2:SK,150,151
Slovenia,SI,SVN,705,ISO 3166-2:SI,150,039
Solomon Islands,SB,SLB,090,ISO 3166-2:SB,009,054
Somalia,SO,SOM,706,ISO 3166-2:SO,002,014
South Africa,ZA,ZAF,710,ISO 3166-2:ZA,002,018
South Georgia and the South Sandwich Islands,GS,SGS,239,ISO 3166-2:GS
South Sudan,SS,SSD,728,ISO 3166-2:SS,002,015
Spain,ES,ESP,724,ISO 3166-2:ES,150,039
Sri Lanka,LK,LKA,144,ISO 3166-2:LK,142,034
Sudan,SD,SDN,729,ISO 3166-2:SD,002,015
Suriname,SR,SUR,740,ISO 3166-2:SR,019,005
Svalbard and Jan Mayen,SJ,SJM,744,ISO 3166-2:SJ,150,154
Swaziland,SZ,SWZ,748,ISO 3166-2:SZ,002,018
Sweden,SE,SWE,752,ISO 3166-2:SE,150,154
Switzerland,CH,CHE,756,ISO 3166-2:CH,150,155
Syrian Arab Republic,SY,SYR,760,ISO 3166-2:SY,142,145
Taiwan\, Province of China,TW,TWN,158,ISO 3166-2:TW,142,030
Tajikistan,TJ,TJK,762,ISO 3166-2:TJ,142,143
Tanzania\, United Republic of,TZ,TZA,834,ISO 3166-2:TZ,002,014
Thailand,TH,THA,764,ISO 3166-2:TH,142,035
Timor-Leste,TL,TLS,626,ISO 3166-2:TL,142,035
Togo,TG,TGO,768,ISO 3166-2:TG,002,011
Tokelau,TK,TKL,772,ISO 3166-2:TK,009,061
Tonga,TO,TON,776,ISO 3166-2:TO,009,061
Trinidad and Tobago,TT,TTO,780,ISO 3166-2:TT,019,029
Tunisia,TN,TUN,788,ISO 3166-2:TN,002,015
Turkey,TR,TUR,792,ISO 3166-2:TR,142,145
Turkmenistan,TM,TKM,795,ISO 3166-2:TM,142,143
Turks and Caicos Islands,TC,TCA,796,ISO 3166-2:TC,019,029
Tuvalu,TV,TUV,798,ISO 3166-2:TV,009,061
Uganda,UG,UGA,800,ISO 3166-2:UG,002,014
Ukraine,UA,UKR,804,ISO 3166-2:UA,150,151
United Arab Emirates,AE,ARE,784,ISO 3166-2:AE,142,145
United Kingdom,GB,GBR,826,ISO 3166-2:GB,150,154
United States,US,USA,840,ISO 3166-2:US,019,021
United States Minor Outlying Islands,UM,UMI,581,ISO 3166-2:UM
Uruguay,UY,URY,858,ISO 3166-2:UY,019,005
Uzbekistan,UZ,UZB,860,ISO 3166-2:UZ,142,143
Vanuatu,VU,VUT,548,ISO 3166-2:VU,009,054
Venezuela\, Bolivarian Republic of,VE,VEN,862,ISO 3166-2:VE,019,005
Viet Nam,VN,VNM,704,ISO 3166-2:VN,142,035
Virgin Islands\, British,VG,VGB,092,ISO 3166-2:VG,019,029
Virgin Islands\, U.S.,VI,VIR,850,ISO 3166-2:VI,019,029
Wallis and Futuna,WF,WLF,876,ISO 3166-2:WF,009,061
Western Sahara,EH,ESH,732,ISO 3166-2:EH,002,015
Yemen,YE,YEM,887,ISO 3166-2:YE,142,145
Zambia,ZM,ZMB,894,ISO 3166-2:ZM,002,014
Zimbabwe,ZW,ZWE,716,ISO 3166-2:ZW,002,014";
		
		$lines = explode("\n", $codes);
		$countries = array();

		foreach($lines AS $k => $v){
			if(strpos($v, "\,") === false){
				$values = explode(",", $v);
				$countries[$values[1]] = $values[0];
			} else {
				$values = explode(",", $v);
				$countries[$values[2]] = str_replace("\\", "", $values[0]).",".$values[1];
			}
		}

		asort($countries);

		return $countries;
	}
	
	public static function getTelephonePrefix($code){
		$prefixes = "AF ,93
EG ,20
AX ,358
AL ,355
DZ ,213
AS ,1
VI ,1
AD ,376
AO ,244
AI ,1
AQ ,0
AG ,1
GQ ,240
AR ,54
AM ,374
AW ,297
AC ,247
AZ ,994
ET ,251
AU ,61
BS ,1
BH ,973
BD ,880
BB ,1
BE ,32
BZ ,501
BJ ,229
BM ,1
BT ,975
BO ,591
BA ,387
BW ,267
BV ,0
BR ,55
VG ,1
IO ,0
BN ,673
BG ,359
BF ,226
BI ,257
KY ,1
CL ,56
CN ,86
CK ,682
CR ,506
DK ,45
DE ,49
DJ ,253
DM ,1
DO ,1
EC ,593
SV ,503
ER ,291
EE ,372
EU ,0
FK ,500
FO ,298
FJ ,679
FI ,358
FM ,691
FR ,33
FX ,0
GF ,594
PF ,689
TF ,0
GA ,241
GM ,220
AERO ,0
ASIA ,0
BIZ ,0
CAT ,0
COM ,0
COOP ,0
INFO ,0
JOBS ,0
MOBI ,0
MUSEUM ,0
NAME ,0
NET ,0
ORG ,0
PRO ,0
TRAVEL ,0
GE ,995
GH ,233
GI ,350
GD ,1
GR ,30
GL ,299
GB ,44
GP ,590
GU ,1
GT ,502
GN ,224
GW ,245
GY ,592
HT ,509
HM ,61
HN ,504
HK ,852
IN ,91
ID ,62
IQ ,964
IR ,98
IE ,353
IS ,354
IM ,44
IL ,972
IT ,390
JM ,1
JP ,81
YE ,967
JO ,962
YU,381
KH ,855
CM ,237
CA ,1
CV ,238
KZ ,7
KE ,254
KI ,686
CC ,672
CO ,57
KM ,269
CG ,242
CD ,243
HR ,385
CU ,53
KW ,965
KG ,996
LA ,856
LS ,266
LV ,371
LB ,961
LR ,231
LY ,218
LI ,423
LT ,370
LU ,352
MO ,853
MG ,261
MW ,265
MY ,60
MV ,960
ML ,223
MT ,356
MA ,212
MH ,692
MQ ,596
MR ,222
MU ,230
YT ,269
MK ,389
MX ,52
MZ ,258
MD ,373
MC ,377
MN ,976
ME ,382
MS ,1
MM ,95
NA ,264
NR ,674
NP ,977
NC ,687
NZ ,64
NI ,505
NL ,31
AN ,599
NE ,227
NG ,234
NU ,683
MP ,1
NF ,672
NO ,47
OM ,968
TP ,670
AT ,43
PK ,82
PS ,970
PW ,680
PA ,507
PG ,675
PY ,595
PE ,51
PH ,63
PN ,649
PL ,48
PT ,351
PR ,1
QA ,974
RE ,262
RW ,250
RO ,40
RU ,7
SB ,677
ZM ,260
WS ,685
SM ,378
ST ,239
SA ,966
SE ,46
CH ,41
SN ,221
RS ,381
SC ,248
SL ,232
SG ,65
SK ,421
SI ,386
SO ,252
ES ,34
LK ,94
SH ,1
KN ,1
LC ,1
PM ,508
VC ,1
ZA ,27
SD ,249
GS ,500
KR ,82
N.N. ,211
SR ,597
SJ ,47
SZ ,268
SY ,963
TJ ,992
TW ,886
TZ ,255
TH ,66
TG ,228
TK ,690
TO ,676
TT ,1
TD ,235
CZ ,420
TN ,216
TR ,90
TM ,993
TC ,1
TV ,688
UG ,256
UA ,380
HU ,36
UK ,44
UM ,1
UY ,598
US ,1
UZ ,998
VU ,678
VA ,379
VE ,58
AE ,971
VN ,84
WF ,681
CX ,672
BY ,375
EH ,212
CF ,236
ZW ,263
CY ,357";
		
		$lines = explode("\n", $prefixes);
		$countries = array();

		foreach($lines AS $k => $v){
			$values = explode(" ,", $v);
			$countries[$values[0]] = $values[1];
		}

		#asort($countries);

		if(isset($countries[$code]))
			return "+".$countries[$code];
		else
			return "";
		
	}
	
	public static function getCountriesDE(){
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

	public static function getCountryToCode($code, $lang = "de"){
		$countries = self::getCountries($lang);

		return $countries[$code];
	}

	public static function getCodeToCountry($country, $lang = "de"){
		$countries = self::getCountries($lang);

		return array_search($country, $countries);
	}

	public static function getZones($code){
		$zones = array();
		$zones["DE"] = array(
			"1" => "Baden-Württemberg",
			"2" => "Bayern",
			"3" => "Berlin",
			"4" => "Brandenburg",
			"5" => "Bremen",
			"6" => "Hamburg",
			"7" => "Hessen",
			"8" => "Mecklenburg-Vorpommern",
			"9" => "Niedersachsen",
			"10" => "Nordrhein-Westfalen",
			"11" => "Rheinland-Pfalz",
			"12" => "Saarland",
			"13" => "Sachsen",
			"14" => "Sachsen-Anhalt",
			"15" => "Schleswig-Holstein",
			"16" => "Thüringen");
		
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
9;"Wien";923;"Wien 23.,Liesing"',

"DE" => "1;;8425;Alb-Donau-Kreis
1;;8426;Biberach
1;;8115;Böblingen
1;;8435;Bodenseekreis
1;;8315;Breisgau-Hochschwarzwald
1;;8235;Calw
1;;8316;Emmendingen
1;;8236;Enzkreis
1;;8116;Esslingen
1;;8237;Freudenstadt
1;;8117;Göppingen
1;;8135;Heidenheim
1;;8125;Heilbronn
1;;8126;Hohenlohekreis
1;;8215;Karlsruhe
1;;8335;Konstanz
1;;8336;Lörrach
1;;8118;Ludwigsburg
1;;8128;Main-Tauber-Kreis
1;;8225;Neckar-Odenwald-Kreis
1;;8317;Ortenaukreis
1;;8136;Ostalbkreis
1;;8216;Rastatt
1;;8436;Ravensburg
1;;8119;Rems-Murr-Kreis
1;;8415;Reutlingen
1;;8226;Rhein-Neckar-Kreis
1;;8325;Rottweil
1;;8127;Schwäbisch Hall
1;;8326;Schwarzwald-Baar-Kreis
1;;8437;Sigmaringen
1;;8416;Tübingen
1;;8327;Tuttlingen
1;;8337;Waldshut
1;;8417;Zollernalbkreis
2;;9771;Aichach-Friedberg
2;;9171;Altötting
2;;9371;Amberg-Sulzbach
2;;9571;Ansbach
2;;9671;Aschaffenburg
2;;9772;Augsburg
2;;9672;Bad Kissingen
2;;9173;Bad Tölz-Wolfratshausen
2;;9471;Bamberg
2;;9472;Bayreuth
2;;9172;Berchtesgadener Land
2;;9372;Cham
2;;9473;Coburg
2;;9174;Dachau
2;;9271;Deggendorf
2;;9773;Dillingen an der Donau
2;;9279;Dingolfing-Landau
2;;9779;Donau-Ries
2;;9175;Ebersberg
2;;9176;Eichstätt
2;;9177;Erding
2;;9572;Erlangen-Höchstadt
2;;9474;Forchheim
2;;9178;Freising
2;;9272;Freyung-Grafenau
2;;9179;Fürstenfeldbruck
2;;9573;Fürth
2;;9180;Garmisch-Partenkirchen
2;;9774;Günzburg
2;;9674;Haßberge
2;;9475;Hof
2;;9273;Kelheim
2;;9675;Kitzingen
2;;9476;Kronach
2;;9477;Kulmbach
2;;9181;Landsberg am Lech
2;;9274;Landshut
2;;9478;Lichtenfels
2;;9776;Lindau (Bodensee)
2;;9677;Main-Spessart
2;;9182;Miesbach
2;;9676;Miltenberg
2;;9183;Mühldorf am Inn
2;;9184;München
2;;9775;Neu-Ulm
2;;9185;Neuburg-Schrobenhausen
2;;9373;Neumarkt in der Oberpfalz
2;;9575;Neustadt an der Aisch-Bad Windsheim
2;;9374;Neustadt an der Waldnaab
2;;9574;Nürnberger Land
2;;9780;Oberallgäu
2;;9777;Ostallgäu
2;;9275;Passau
2;;9186;Pfaffenhofen an der Ilm
2;;9276;Regen
2;;9375;Regensburg
2;;9673;Rhön-Grabfeld
2;;9187;Rosenheim
2;;9576;Roth
2;;9277;Rottal-Inn
2;;9376;Schwandorf
2;;9678;Schweinfurt
2;;9188;Starnberg
2;;9278;Straubing-Bogen
2;;9377;Tirschenreuth
2;;9189;Traunstein
2;;9778;Unterallgäu
2;;9190;Weilheim-Schongau
2;;9577;Weißenburg-Gunzenhausen
2;;9479;Wunsiedel im Fichtelgebirge
2;;9679;Würzburg
3;;11000;Berlin
4;;12060;Barnim
4;;12061;Dahme-Spreewald
4;;12062;Elbe-Elster
4;;12063;Havelland
4;;12064;Märkisch-Oderland
4;;12065;Oberhavel
4;;12066;Oberspreewald-Lausitz
4;;12067;Oder-Spree
4;;12068;Ostprignitz-Ruppin
4;;12069;Potsdam-Mittelmark
4;;12070;Prignitz
4;;12071;Spree-Neiße
4;;12072;Teltow-Fläming
4;;12073;Uckermark
5;;4011;Bremen
6;;2000;Hamburg
7;;6431;Bergstraße
7;;6432;Darmstadt-Dieburg
7;;6631;Fulda
7;;6531;Gießen
7;;6433;Groß-Gerau
7;;6632;Hersfeld-Rotenburg
7;;6434;Hochtaunuskreis
7;;6633;Kassel
7;;6532;Lahn-Dill-Kreis
7;;6533;Limburg-Weilburg
7;;6435;Main-Kinzig-Kreis
7;;6436;Main-Taunus-Kreis
7;;6534;Marburg-Biedenkopf
7;;6437;Odenwaldkreis
7;;6438;Offenbach
7;;6439;Rheingau-Taunus-Kreis
7;;6634;Schwalm-Eder-Kreis
7;;6535;Vogelsbergkreis
7;;6635;Waldeck-Frankenberg
7;;6636;Werra-Meißner-Kreis
7;;6440;Wetteraukreis
8;;13076;Ludwigslust-Parchim
8;;13071;Mecklenburgische Seenplatte
8;;13074;Nordwestmecklenburg
8;;13072;Rostock
8;;13075;Vorpommern-Greifswald
8;;13073;Vorpommern-Rügen
9;;3451;Ammerland
9;;3452;Aurich
9;;3351;Celle
9;;3453;Cloppenburg
9;;3352;Cuxhaven
9;;3251;Diepholz
9;;3454;Emsland
9;;3455;Friesland
9;;3151;Gifhorn
9;;3153;Goslar
9;;3152;Göttingen
9;;3456;Grafschaft Bentheim
9;;3252;Hameln-Pyrmont
9;;3241;Hannover Region
9;;3353;Harburg
9;;3358;Heidekreis
9;;3154;Helmstedt
9;;3254;Hildesheim
9;;3255;Holzminden
9;;3457;Leer
9;;3354;Lüchow-Dannenberg
9;;3355;Lüneburg
9;;3256;Nienburg/Weser
9;;3155;Northeim
9;;3458;Oldenburg
9;;3459;Osnabrück
9;;3356;Osterholz
9;;3156;Osterode am Harz
9;;3157;Peine
9;;3357;Rotenburg (Wümme)
9;;3257;Schaumburg
9;;3359;Stade
9;;3360;Uelzen
9;;3460;Vechta
9;;3361;Verden
9;;3461;Wesermarsch
9;;3462;Wittmund
9;;3158;Wolfenbüttel
10;;5334;Aachen Städteregion
10;;5554;Borken
10;;5558;Coesfeld
10;;5358;Düren
10;;5954;Ennepe-Ruhr-Kreis
10;;5366;Euskirchen
10;;5754;Gütersloh
10;;5370;Heinsberg
10;;5758;Herford
10;;5958;Hochsauerlandkreis
10;;5762;Höxter
10;;5154;Kleve
10;;5766;Lippe
10;;5962;Märkischer Kreis
10;;5158;Mettmann
10;;5770;Minden-Lübbecke
10;;5374;Oberbergischer Kreis
10;;5966;Olpe
10;;5774;Paderborn
10;;5562;Recklinghausen
10;;5362;Rhein-Erft-Kreis
10;;5162;Rhein-Kreis Neuss
10;;5382;Rhein-Sieg-Kreis
10;;5378;Rheinisch-Bergischer Kreis
10;;5970;Siegen-Wittgenstein
10;;5974;Soest
10;;5566;Steinfurt
10;;5978;Unna
10;;5166;Viersen
10;;5570;Warendorf
10;;5170;Wesel
11;;7131;Ahrweiler
11;;7132;Altenkirchen (Westerwald)
11;;7331;Alzey-Worms
11;;7332;Bad Dürkheim
11;;7133;Bad Kreuznach
11;;7231;Bernkastel-Wittlich
11;;7134;Birkenfeld
11;;7135;Cochem-Zell
11;;7333;Donnersbergkreis
11;;7232;Eifelkreis Bitburg-Prüm
11;;7334;Germersheim
11;;7335;Kaiserslautern
11;;7336;Kusel
11;;7339;Mainz-Bingen
11;;7137;Mayen-Koblenz
11;;7138;Neuwied
11;;7140;Rhein-Hunsrück-Kreis
11;;7141;Rhein-Lahn-Kreis
11;;7338;Rhein-Pfalz-Kreis
11;;7337;Südliche Weinstraße
11;;7340;Südwestpfalz
11;;7235;Trier-Saarburg
11;;7233;Vulkaneifel
11;;7143;Westerwaldkreis
12;;10042;Merzig-Wadern
12;;10043;Neunkirchen
12;;10041;Saarbrücken Regionalverband
12;;10044;Saarlouis
12;;10045;Saarpfalz-Kreis
12;;10046;St. Wendel
13;;14625;Bautzen
13;;14521;Erzgebirgskreis
13;;14626;Görlitz
13;;14729;Leipzig
13;;14627;Meißen
13;;14522;Mittelsachsen
13;;14730;Nordsachsen
13;;14628;Sächsische Schweiz-Osterzgebirge
13;;14523;Vogtlandkreis
13;;14524;Zwickau
14;;15081;Altmarkkreis Salzwedel
14;;15082;Anhalt-Bitterfeld
14;;15083;Börde
14;;15084;Burgenlandkreis
14;;15085;Harz
14;;15086;Jerichower Land
14;;15087;Mansfeld-Südharz
14;;15088;Saalekreis
14;;15089;Salzlandkreis
14;;15090;Stendal
14;;15091;Wittenberg
15;;1051;Dithmarschen
15;;1053;Herzogtum Lauenburg
15;;1054;Nordfriesland
15;;1055;Ostholstein
15;;1056;Pinneberg
15;;1057;Plön
15;;1058;Rendsburg-Eckernförde
15;;1059;Schleswig-Flensburg
15;;1060;Segeberg
15;;1061;Steinburg
15;;1062;Stormarn
16;;16077;Altenburger Land
16;;16061;Eichsfeld
16;;16067;Gotha
16;;16076;Greiz
16;;16069;Hildburghausen
16;;16070;Ilm-Kreis
16;;16065;Kyffhäuserkreis
16;;16062;Nordhausen
16;;16074;Saale-Holzland-Kreis
16;;16075;Saale-Orla-Kreis
16;;16073;Saalfeld-Rudolstadt
16;;16066;Schmalkalden-Meiningen
16;;16068;Sömmerda
16;;16072;Sonneberg
16;;16064;Unstrut-Hainich-Kreis
16;;16063;Wartburgkreis
16;;16071;Weimarer Land");

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