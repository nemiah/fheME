<?php
/**
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
require "../system/connect.php";
header('Content-type: application/xml; charset="utf-8"',true);
echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>ClockGUI</title>
	<script type="text/javascript" src="../libraries/scriptaculous/prototype.js"></script>
	<script type="text/javascript" src="../libraries/scriptaculous/effects.js"></script>
	<script type="text/javascript" src="../libraries/scriptaculous/dragdrop.js"></script>
	
	<link rel="stylesheet" type="text/css" href="../styles/standard/general.css"></link>
	<link rel="stylesheet" type="text/css" href="../styles/standard/colors.css"></link>
</head>
<body class="backgroundColor0">
	<div id="clock" style="height:240px;width:240px;-moz-user-select:none;">
		<svg xmlns="http://www.w3.org/2000/svg" version="1.1" preserveAspectRatio="xMidYMid slice" style="">

			<circle cx="120px" cy="120px" r="110px" style="fill:#E6E6FF;" />
			
			<line id="st1"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st2"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st3"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st4"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st5"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st6"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st7"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st8"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st9"  x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st10" x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st11" x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			<line id="st12" x1="120px" y1="120px" x2="120px" y2="10px" style="stroke:#6868C9;stroke-width:2"/>
			
			<circle cx="120px" cy="120px" r="100px" style="fill:#E6E6FF;" />
			
			<circle cx="120px" cy="120px" r="82px" style="fill:#B8B8F0;" />
			
			<circle cx="120px" cy="120px" r="80px" style="fill:white;" />
			
			<circle cx="120px" cy="120px" r="3px" style="fill:#6868C9;" />
			
			<text id="time1"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(28, 120, 120) translate(0, -112)">1</text>
			<text id="time2"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(58, 120, 120) translate(0, -112)">2</text>
			<text id="time3"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(88, 120, 120) translate(0, -112)">3</text>
			<text id="time4"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(118, 120, 120) translate(0, -112)">4</text>
			<text id="time5"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(148, 120, 120) translate(0, -112)">5</text>
			<text id="time6"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(178, 120, 120) translate(0, -112)">6</text>
			<text id="time7"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(208, 120, 120) translate(0, -112)">7</text>
			<text id="time8"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(238, 120, 120) translate(0, -112)">8</text>
			<text id="time9"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(268, 120, 120) translate(0, -112)">9</text>
			<text id="time10"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(297, 120, 120) translate(0, -112)">10</text>
			<text id="time11"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(327, 120, 120) translate(0, -112)">11</text>
			<text id="time12"  x="120" y="120" style="font-size:10px;fill:#6868C9;" transform="rotate(358, 120, 120) translate(0, -112)">0</text>
			
			<text id="time13"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(27, 120, 120) translate(0, -112)">13</text>
			<text id="time14"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(57, 120, 120) translate(0, -112)">14</text>
			<text id="time15"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(87, 120, 120) translate(0, -112)">15</text>
			<text id="time16"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(117, 120, 120) translate(0, -112)">16</text>
			<text id="time17"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(147, 120, 120) translate(0, -112)">17</text>
			<text id="time18"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(177, 120, 120) translate(0, -112)">18</text>
			<text id="time19"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(207, 120, 120) translate(0, -112)">19</text>
			<text id="time20"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(237, 120, 120) translate(0, -112)">20</text>
			<text id="time21"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(267, 120, 120) translate(0, -112)">21</text>
			<text id="time22"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(297, 120, 120) translate(0, -112)">22</text>
			<text id="time23"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(327, 120, 120) translate(0, -112)">23</text>
			<text id="time24"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(358, 120, 120) translate(0, -112)">12</text>
			
			<text id="timeM1"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(28, 120, 120) translate(0, -112)">5</text>
			<text id="timeM2"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(57, 120, 120) translate(0, -112)">10</text>
			<text id="timeM3"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(87, 120, 120) translate(0, -112)">15</text>
			<text id="timeM4"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(117, 120, 120) translate(0, -112)">20</text>
			<text id="timeM5"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(147, 120, 120) translate(0, -112)">25</text>
			<text id="timeM6"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(177, 120, 120) translate(0, -112)">30</text>
			<text id="timeM7"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(207, 120, 120) translate(0, -112)">35</text>
			<text id="timeM8"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(237, 120, 120) translate(0, -112)">40</text>
			<text id="timeM9"   x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(267, 120, 120) translate(0, -112)">45</text>
			<text id="timeM10"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(297, 120, 120) translate(0, -112)">50</text>
			<text id="timeM11"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(327, 120, 120) translate(0, -112)">55</text>
			<text id="timeM12"  x="120" y="120" style="display:none;font-size:10px;fill:#6868C9;" transform="rotate(358, 120, 120) translate(0, -112)">0</text>
			
			<line id="minutenZeiger2" x1="120px" y1="120px" x2="120px" y2="30px" style="stroke:#6868C9;stroke-width:2"/>
		</svg>
	</div>
	<div style="margin-top:15px;width:240px;">
		<?php
		$B = new Button("Vormittag/Nachmittag","../images/navi/daytime.png");
		$B->type("icon");
		$B->onclick("swapDaytime();");
		$B->style("margin-left:20px;float:left;");
		
		$B2 = new Button("Stunde bearbeiten","../images/navi/up.png");
		$B2->type("icon");
		$B2->onclick("switchMode(1);");
		
		$B3 = new Button("Minuten bearbeiten","../images/navi/up.png");
		$B3->type("icon");
		$B3->onclick("switchMode(2);");
		echo $B;
		?>
		<div style="text-align:right;width:40px;margin-left:20px;float:left;">
			<?php echo $B2; ?><br />
			<span id="stunden" style="font-size:18px;font-weight:bold;">00</span>
		</div>
		
		<div style="text-align:center;width:13px;float:left;">
			<span style="display:block;font-size:18px;font-weight:bold;margin-top:36px;">:</span>
		</div>
			
		<div style="width:40px;float:left;">
			<?php echo $B3; ?><br />
			<span id="minuten" style="font-size:18px;font-weight:bold;">00</span>
		</div>
	</div>
	<script type="text/javascript">
		var isActive = false;
		var pi = 3.1415926;
		var vormittag = true;
		var mode = 1;
		
		var CurrStunde = <?php echo date("H"); ?>;
		var CurrMinute = <?php echo date("i"); ?>;

		var minuten = Math.ceil(CurrMinute / 5) * 5;
		
		if(minuten == 60) {
			minuten = 0;
			CurrStunde += 1;
		}
		if(CurrStunde == 24) CurrStunde = 0;
		var stunden = (CurrStunde > 12 ? CurrStunde - 12 : CurrStunde);

		function swapDaytime(){
			if(mode == 1)
				for(i=1;i &lt;= 12;i++){
					$('time'+i).style.display = vormittag ? "none" : "";
					$('time'+(i+12)).style.display = vormittag ? "" : "none";
				}
				
			stunden = vormittag ? stunden + 12 : stunden - 12;
			$('stunden').update((stunden &lt; 10 ? "0" : "")+stunden);
			
			vormittag = !vormittag;
		}
		
		function rotate(){
			x1 = $('minutenZeiger2').x1.baseVal.value;
			y1 = $('minutenZeiger2').y1.baseVal.value;
			
			for(i=1;i&lt;=12;i++){
				rad = 2 * pi / 12 * i;
				y2 = Math.sin(rad) * 108;
				x2 = Math.cos(rad) * 108;
				
				$('st'+i).x2.baseVal.value = x1 - x2;
				$('st'+i).y2.baseVal.value = y1 - y2;
				
				
			}
		}
		
		function switchMode(toMode){
			mode = toMode;
			
			if(toMode == 1){
				for(i=1;i &lt;= 12;i++){
					if(vormittag) $('time'+i).style.display = "";
					else $('time'+(i+12)).style.display = "";
					
					$('timeM'+i).style.display = "none";
				}
				setTime(stunden);
			}
			if(toMode == 2){
				for(i=1;i &lt;= 12;i++){
					$('time'+i).style.display = "none";
					$('time'+(i+12)).style.display = "none";
					
					$('timeM'+i).style.display = "";
				}
				setTime(minuten);
			}
		}
		
		function setTime(time){
			rad = (2 * pi / 12) * time / (mode == 2 ? 5 : 1);
			if(mode == 1 &amp;&amp; time > 12 &amp;&amp; vormittag) swapDaytime();
			
			$('minutenZeiger2').x2.baseVal.value = $('minutenZeiger2').x1.baseVal.value + Math.sin(rad) * 90;
			$('minutenZeiger2').y2.baseVal.value = $('minutenZeiger2').y1.baseVal.value - Math.cos(rad) * 90;
		}
		
		function setMinutes(event){
			x1 = $('minutenZeiger2').x1.baseVal.value;
			y1 = $('minutenZeiger2').y1.baseVal.value;
			
			x2 = event.clientX;
			y2 = event.clientY;
		
			hyp = Math.sqrt(Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2));
			
			rad = Math.asin((y1 - y2) / hyp) - pi / 2;
			
			if(x1 - x2 &lt; 0)
				rad = -1 * rad;
			else
				rad = pi + rad + pi;
			
			rad = Math.round(rad / (pi / 6)) * (pi / 6);

			if(mode == 1) {
				stunden = rad / (pi / 6) + (!vormittag ? 12 : 0);
				if(stunden == 12 &amp;&amp; vormittag) stunden = 0;
				if(stunden == 24 &amp;&amp; !vormittag) stunden = 12;
				$('stunden').update((stunden &lt; 10 ? "0" : "")+stunden);
			}
			else {
				minuten = rad / (pi / 6) * 5;
				if(minuten == 60) minuten = 0;
				$('minuten').update((minuten &lt; 10 ? "0" : "")+minuten);
			}
			$('minutenZeiger2').x2.baseVal.value = x1 + Math.sin(rad) * 90;
			$('minutenZeiger2').y2.baseVal.value = y1 - Math.cos(rad) * 90;
		}
		
		Event.observe('clock', 'mousedown', setMinutes);
		rotate();
		setTime(CurrStunde);
		$('minuten').update((minuten &lt; 10 ? "0" : "")+minuten);
		$('stunden').update((stunden &lt; 10 ? "0" : "")+stunden);
		
	</script>
</body>
</html>
