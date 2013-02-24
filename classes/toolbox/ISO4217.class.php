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

class ISO4217 {
	public static function getCurrencies(){
		$codes = "AED	784	Dirham
AFN	971	Afghani
ALL	8	Lek
AMD	51	Dram
ANG	532	Gulden
AOA	973	Kwanza
ARS	32	Peso
AUD	36	Dollar
AWG	533	Florin
AZN	944	Manat
BAM	977	Konvertible Mark
BBD	52	Dollar
BDT	50	Taka
BGN	975	Lew
BHD	48	Dinar
BIF	108	Franc
BMD	60	Dollar
BND	96	Dollar
BOB	68	Boliviano
BOV	984	Mvdol
BRL	986	Real
BSD	44	Dollar
BTN	64	Ngultrum
BWP	72	Pula
BYR	974	Rubel
BZD	84	Dollar
CAD	124	Dollar
CDF	976	Franc
CHE	947	WIR Euro
CHF	756	Schweizer Franken
CHW	948	WIR Franc
CLF	990	Unidad de Fomento
CLP	152	Peso
CNY	156	Renminbi Yuan
COP	170	Peso
COU	970	Unidad de Valor Real
CRC	188	Colón
CUP	192	Peso
CVE	132	Escudo
CZK	203	Krone
DJF	262	Franc
DKK	208	Krone
DOP	214	Peso
DZD	12	Dinar
EGP	818	Pfund
ERN	232	Nakfa
ETB	230	Birr
EUR	978	Euro
FJD	242	Dollar
FKP	238	Pfund
GBP	826	Pfund
GEL	981	Lari
GHS	936	Ghana Cedi
GIP	292	Pfund
GMD	270	Dalasi
GNF	324	Franc
GTQ	320	Quetzal
GYD	328	Dollar
HKD	344	Dollar
HNL	340	Lempira
HRK	191	Kuna
HTG	332	Gourde
HUF	348	Forint
IDR	360	Rupiah
ILS	376	Schekel
INR	356	Rupie
IQD	368	Dinar
IRR	364	Rial
ISK	352	Krone
JMD	388	Dollar
JOD	400	Dinar
JPY	392	Yen
KES	404	Schilling
KGS	417	Som
KHR	116	Riel
KMF	174	Franc
KPW	408	Won
KRW	410	Won
KWD	414	Dinar
KYD	136	Dollar
KZT	398	Tenge
LAK	418	Kip
LBP	422	Pfund
LKR	144	Rupie
LRD	430	Dollar
LSL	426	Loti
LTL	440	Litas
LVL	428	Lats
LYD	434	Dinar
MAD	504	Dirham
MDL	498	Leu
MGA	969	Ariary
MKD	807	Denar
MMK	104	Kyat
MNT	496	Tögrög
MOP	446	Pataca
MRO	478	Ouguiya
MUR	480	Rupie
MVR	462	Rufiyaa
MWK	454	Kwacha
MXN	484	Peso
MXV	979	Mexican Unidad de Inversion (UDI)
MYR	458	Ringgit
MZN	943	Metical
NAD	516	Dollar
NGN	566	Naira
NIO	558	Córdoba Oro
NOK	578	Krone
NPR	524	Rupie
NZD	554	Dollar
OMR	512	Rial
PAB	590	Balboa
PEN	604	Nuevo Sol
PGK	598	Kina
PHP	608	Peso
PKR	586	Rupie
PLN	985	Złoty
PYG	600	Guaraní
QAR	634	Riyal
RON	946	Leu
RSD	941	Dinar
RUB	643	Rubel
RWF	646	Franc
SAR	682	Riyal
SBD	90	Dollar
SCR	690	Rupie
SDG	938	Sudanesisches Pfund
SEK	752	Krone
SGD	702	Dollar
SHP	654	Pfund
SLL	694	Leone
SOS	706	Schilling
SRD	968	Dollar
STD	678	Dobra
SVC	222	Colón
SYP	760	Pfund
SZL	748	Lilangeni
THB	764	Baht
TJS	972	Somoni
TMT	934	Manat
TND	788	Dinar
TOP	776	Paʻanga
TRY	949	Lira
TTD	780	Dollar
TWD	901	Dollar
TZS	834	Schilling
UAH	980	Hrywnja
UGX	800	Schilling
USD	840	Dollar
UYI	940	Uruguay Peso en Unidades Indexadas
UYU	858	Peso
UZS	860	Soʻm
VEF	937	Bolívar Fuerte
VND	704	Đồng
VUV	548	Vatu
WST	882	Tala
XAF	950	CFA-Franc (XAF)
XCD	951	Dollar
XOF	952	CFA-Franc (XOF)
XPF	953	Franc
YER	886	Rial
ZAR	710	Rand
ZMK	894	Kwacha
ZWR	935	Dollar";
		
		$lines = explode("\n", $codes);
		$currencies = array();

		foreach($lines AS $k => $v){
			$values = explode("	", $v);
			$currencies[$values[0]] = $values[0]." ".$values[2];
		}

		asort($currencies);

		return $currencies;
	}

	public static function getCurrencyToCode($code){
		$currencies = self::getCurrencies();

		return $currencies[$code];
	}

	public static function getCodeToLanguage($currency){
		$currencies = self::getCurrencies();

		return array_search($currency, $currencies);
	}
}

?>
