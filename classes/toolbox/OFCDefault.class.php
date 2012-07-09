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
abstract class OFCDefault {

	private $ofc;
	private $title;

	private $xLabels;

	private $yAxis;
	private $xAxis;

	protected abstract function getData();

	public function needLogin(){
		return true;
	}

	public function customize(){
		$active = mUserdata::getGlobalSettingValue("activeCustomizer");

		if($active == null) return;
		try {
			$this->customizer = new $active();
			$this->customizer->customizeClass($this);
		} catch (ClassNotFoundException $e){

		}
	}
	
	public function __construct(){
		$this->ofc = new stdClass();
		$this->title = new stdClass();

		$this->ofc->elements = array();

		$this->xLabels = new stdClass();

		$this->yAxis = new stdClass();
		$this->xAxis = new stdClass();

		$this->ofc->y_axis = $this->yAxis;
		$this->ofc->x_axis = $this->xAxis;

		$this->ofc->title = $this->title;
		$this->xAxis->labels = $this->xLabels;

		$this->mode("white");
		$this->xOffset(false);
	}

	protected function mode($mode){
		switch($mode){
			case "white":
				$this->bgColor("#FFFFFF");

				$this->yColor("#A2ACBA");
				$this->yGridColor("#cccccc");

				$this->xColor("#A2ACBA");
				$this->xGridColor("#cccccc");
			break;

			case "open3A":
				$this->bgColor("#FFFFFF");

				$this->yColor("#000000");
				$this->yGridColor("#cccccc");

				$this->xColor("#000000");
				$this->xGridColor("#cccccc");
			break;
		}
	}

	protected function title($label){
		$this->title->text = $label;
	}

	protected function bgColor($val){
		$this->ofc->bg_colour = $val;
	}

	protected function ySteps($number){
		$this->yAxis->steps = $number;
	}

	protected function xSteps($number){
		$this->xAxis->steps = $number;
	}

	protected function xOffset($val){
		$this->xAxis->offset = $val;
	}

	protected function xLabelRotate($val){
		$this->xLabels->rotate = $val;
	}

	protected function xColor($val){
		$this->xAxis->colour = $val;
	}

	protected function x3D($val){
		$this->xAxis->treeD = $val;
	}

	protected function xGridColor($val){
		$this->xAxis->gridColour = $val;
	}

	protected function xLabelLabels($array){
		$this->xLabels->labels = $array;
	}

	protected function yMinMax($minY, $maxY){
		$this->yAxis->min = $minY;
		$this->yAxis->max = $maxY;
	}

	protected function yColor($val){
		$this->yAxis->colour = $val;
	}

	protected function yGridColor($val){
		$this->yAxis->gridColour = $val;
	}

	protected function newDefaultLine($type, $label = "", $color = "#000000"){
		$elements = new stdClass();
		$elements->values = array();

		$elements->type = $type;
		$elements->colour = $color;
		if($label != "") $elements->text = $label;

		$this->ofc->elements[] = $elements;

		return $elements;
	}

	protected function newDotStyle($dotStyleClick = "", $dotStyleType = "", $dotStyleSize = "", $dotHaloSize = "", $dotColor = ""){
		$dotStyle = new stdClass();

		if($dotStyleType != "") $dotStyle->type = $dotStyleType;
		if($dotStyleSize != "") $dotStyle->dotSize = $dotStyleSize;
		if($dotStyleClick != "") $dotStyle->onClick = $dotStyleClick;
		if($dotHaloSize != "") $dotStyle->haloSize = $dotHaloSize;
		if($dotColor != "") $dotStyle->colour = $dotColor;

		return $dotStyle;
	}

	protected function newOnShow($type = "pop-up", $cascade = 2, $delay = 0.5){
		$onShow = new stdClass();

		$onShow->type = $type;
		$onShow->cascade = $cascade;
		$onShow->delay = $delay;

		return $onShow;
	}

	protected function newPie(){
		$elements = $this->newDefaultLine("pie");

		$elements->animate = array();
		$elements->tip = "#percent#\n#val#/#total#";
		$elements->animate[0] = new stdClass();
		$elements->animate[0]->type = "fade";

		$elements->animate[1] = new stdClass();
		$elements->animate[1]->type = "bounce";
		$elements->animate[1]->distance = 5;

		$elements->colours = array("#d01f3c", "#356aa0", "#C79810", "#75c710");

		return $elements;
	}

	protected function newArea($label = "", $color = "#000000", $fillColor = "#666666", $fillAlpha = "0.7", $dotStyle = null, $onShow = null){
		$elements = $this->newDefaultLine("area", $label, $color);

		$elements->fill = $fillColor;
		$elements->onShow = $onShow;
		$elements->fillAlpha = $fillAlpha;
		$elements->dotStyle = $dotStyle;
		return $elements;
	}

	protected function newLine($label = "", $color = "#000000", $dotStyle = null, $onShow = null){
		$elements = $this->newDefaultLine("line", $label, $color);
		$elements->dotStyle = $dotStyle;
		$elements->onShow = $onShow;
		return $elements;
	}

	protected function newBar3d($label = "", $color = "#000000"){
		$this->xOffset(true);
		return $this->newDefaultLine("bar_3d", $label, $color);
	}

	protected function newBarFilled($label = "", $color = "#AAAAAA", $outlineColor = "#333333"){
		$elements->outlineColor = $outlineColor;
		$this->xOffset(true);
		return $this->newDefaultLine("bar_filled", $label, $color);
	}

	public function getOFC($load = true){
		if($load)
			$this->getData();

		$json = str_replace("fillAlpha","fill-alpha", json_encode($this->ofc));
		$json = str_replace("gridColour","grid-colour",$json);
		$json = str_replace("outlineColor","outline-colour",$json);
		$json = str_replace("treeD","3d",$json);
		$json = str_replace("dotStyle","dot-style",$json);
		$json = str_replace("dotSize","dot-size",$json);
		$json = str_replace("onClick","on-click",$json);
		$json = str_replace("haloSize","halo-size",$json);
		$json = str_replace("onShow","on-show",$json);

		return $json;
	}
}
?>
