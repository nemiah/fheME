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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class CalculatorGUI implements icontextMenu {
	public function getContextMenuHTML($identifier) {
		$I = new HTMLInput("CalculatorRechner");
		$I->onEnter(OnEvent::rme($this, "calculate", array("\$j('input[name=CalculatorRechner]').val()"), "function(t){ if(t.responseText != '##') { \$j('$identifier').val(t.responseText); ".OnEvent::closeContext()." } }"));
		
		$I->requestFocus(true);

		echo $I;
		
		echo "<div id=\"CalculatorErgebnisse\"></div>";
		echo "<p>Dieser Rechner unterstützt +, -, *, / sowie Klammern.</p>";
		echo OnEvent::script("\$j('input[name=CalculatorRechner]').val(\$j('$identifier').val());");
	}

	
	public function calculate($formula){
		$formula = trim($formula, "+-*/");
		
		require_once Util::getRootPath().'libraries/math-parser/lib/PHPMathParser/Math.php';

		$math = new PHPMathParser\Math();
		try {
			$answer = $math->evaluate($formula);
		} catch(Exception $e){
			die("##");
		}
		echo Util::CLNumberParserZ($answer);
	}
}
?>