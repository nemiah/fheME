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

class LinuxKeycodes {
	private static $temp;
	public static $table = "ESC,1
1,2
2,3
3,4
4,5
5,6
6,7
7,8
8,9
9,10
0,11
0,12
=,13
BS,14
TAB,15
Q,16
W,17
E,18
R,19
T,20
Y,21
U,22
I,23
O,24
P,25
[,26
],27
ENTER,28
L CTRL,29
A,30
S,31
D,32
F,33
G,34
H,35
J,36
K,37
L,38
;,39
',40
`,41
L SHIFT,42
\,43
Z,44
X,45
C,46
V,47
B,48
N,49
M,50
0,51
.,52
/,53
R SHIFT,54
*,55
L ALT,56
SPACE,57
CAPS LOCK,58
F1,59
F2,60
F3,61
F4,62
F5,63
F6,64
F7,65
F8,66
F9,67
F10,68
NUM LOCK,69
SCROLL LOCK,70
HOME 7,71
UP 8,72
PGUP 9,73
0,74
LEFT 4,75
5,76
RT ARROW 6,77
0,78
END 1,79
DOWN 2,80
PGDN 3,81
INS,82
DEL,83
,84
,85
,86
F11,87
F12,88
,89
,90
,91
,92
,93
,94
,95
R ENTER,96
R CTRL,97
/,98
PRT SCR,99
R ALT,100
,101
Home,102
Up,103
PgUp,104
Left,105
Right,106
End,107
Down,108
PgDn,109
Insert,110
Del,111
,112
,113
,114
,115
,116
,117
,118
Pause,119";
	
	public static function codeToKey($code){
		if(self::$temp == null)
			self::$temp = explode("\n", self::$table);
		
		foreach (self::$temp AS $line){
			$e = explode(",", $line);
			if($e[1] == $code)
				return $e[0];
		}
		
		return null;
	}
}

?>