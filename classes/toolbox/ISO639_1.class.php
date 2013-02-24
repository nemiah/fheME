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

class ISO639_1 {
		public static function getLanguages(){
$codes = "aa 	Afar
ab 	Abchasisch
af 	Afrikaans
am 	Amharisch
ar 	Arabisch
as 	Assamesisch
ay 	Aymara
az 	Aserbaidschanisch
ba 	Baschkirisch
be 	Belorussisch
bg 	Bulgarisch
bh 	Biharisch
bi 	Bislamisch
bn 	Bengalisch
bo 	Tibetanisch
br 	Bretonisch
ca 	Katalanisch
co 	Korsisch
cs 	Tschechisch
cy 	Walisisch
da 	Dänisch
de 	Deutsch
dz 	Dzongkha, Bhutani
el 	Griechisch
en 	Englisch
eo 	Esperanto
es 	Spanisch
et 	Estnisch
eu 	Baskisch
fa 	Persisch
fi 	Finnisch
fj 	Fiji
fo 	Färöisch
fr 	Französisch
fy 	Friesisch
ga 	Irisch
gd 	Schottisches Gälisch
gl 	Galizisch
gn 	Guarani
gu 	Gujaratisch
ha 	Haussa
he 	Hebräisch
hi 	Hindi
hr 	Kroatisch
hu 	Ungarisch
hy 	Armenisch
ia 	Interlingua
id 	Indonesisch
ie 	Interlingue
ik 	Inupiak
is 	Isländisch
it 	Italienisch
iu 	Inuktitut (Eskimo)
iw 	Hebräisch (veraltet, nun: he)
ja 	Japanisch
ji 	Jiddish (veraltet, nun: yi)
jv 	Javanisch
ka 	Georgisch
kk 	Kasachisch
kl 	Kalaallisut (Grönländisch)
km 	Kambodschanisch
kn 	Kannada
ko 	Koreanisch
ks 	Kaschmirisch
ku 	Kurdisch
ky 	Kirgisisch
la 	Lateinisch
ln 	Lingala
lo 	Laotisch
lt 	Litauisch
lv 	Lettisch
mg 	Malagasisch
mi 	Maorisch
mk 	Mazedonisch
ml 	Malajalam
mn 	Mongolisch
mo 	Moldavisch
mr 	Marathi
ms 	Malaysisch
mt 	Maltesisch
my 	Burmesisch
na 	Nauruisch
ne 	Nepalesisch
nl 	Holländisch
no 	Norwegisch
oc 	Okzitanisch
om 	Oromo
or 	Oriya
pa 	Pundjabisch
pl 	Polnisch
ps 	Paschtu
pt 	Portugiesisch
qu 	Quechua
rm 	Rätoromanisch
rn 	Kirundisch
ro 	Rumänisch
ru 	Russisch
rw 	Kijarwanda
sa 	Sanskrit
sd 	Zinti
sg 	Sango
sh 	Serbokroatisch (veraltet)
si 	Singhalesisch
sk 	Slowakisch
sl 	Slowenisch
sm 	Samoanisch
sn 	Schonisch
so 	Somalisch
sq 	Albanisch
sr 	Serbisch
ss 	Swasiländisch
st 	Sesothisch
su 	Sudanesisch
sv 	Schwedisch
sw 	Suaheli
ta 	Tamilisch
te 	Tegulu
tg 	Tadschikisch
th 	Thai
ti 	Tigrinja
tk 	Turkmenisch
tl 	Tagalog
tn 	Sezuan
to 	Tongaisch
tr 	Türkisch
ts 	Tsongaisch
tt 	Tatarisch
tw 	Twi
ug 	Uigur
uk 	Ukrainisch
ur 	Urdu
uz 	Usbekisch
vi 	Vietnamesisch
vo 	Volapük
wo 	Wolof
xh 	Xhosa
yi 	Jiddish
yo 	Joruba
za 	Zhuang
zh 	Chinesisch
zu 	Zulu";
		$lines = explode("\n", $codes);
		$languages = array();

		foreach($lines AS $k => $v){
			$values = explode(" 	", $v);
			$languages[$values[0]] = $values[1];
		}

		asort($languages);

		return $languages;
	}

	public static function getLanguageToCode($code){
		$languages = self::getLanguages();

		return $languages[$code];
	}

	public static function getCodeToLanguage($language){
		$languages = self::getCountries();

		return array_search($language, $languages);
	}
}
?>