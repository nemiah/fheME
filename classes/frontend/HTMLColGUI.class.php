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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class HTMLColGUI {
	private $content = array();
	private $object;
	private $cols = 3;
	private $resize = "";
	private $widths;
	
	function __construct($object) {
		$this->object = $object;
	}
	
	function cols($count){
		$this->cols = $count;
	}
	
	function widths(array $cols){
		$this->widths = $cols;
	}
	
	function content($col, $content, $id = -1){
		if($content instanceof HTMLGUIX)
			$content = $content->getBrowserHTML($id);
		
		$this->content[$col] = $content;
	}
	
	function resize($action){
		$this->resize = $action;
	}
	
	function __toString() {
		$html = "";
		
		if($this->cols == 1){
			$html = "
		<div style=\"overflow:auto;margin:0px;min-height:400px;\" id=\"contentScreenRight\" class=\"borderColor1\">
			".$this->content["right"]."
		</div>";
		
			$js = "function fitFrames(){
				if(!\$j('#contentScreenRight').length)
					return;

				$this->resize

				\$j('#contentScreenRight').css('height', contentManager.maxHeight()+'px');
			}";
		}
		
		if($this->cols == 2){
			$html = "
		<div style=\"display:inline-block;vertical-align:top;margin:0px;padding:0px;width:50%;\" id=\"contentScreenLeft\">
			".$this->content["left"]."
		</div><div style=\"-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;overflow:auto;display:inline-block;width:50%;vertical-align:top;border-left-style:solid;border-left-width:1px;margin:0px;min-height:400px;\" id=\"contentScreenRight\" class=\"borderColor1\">
			".$this->content["right"]."
		</div>";
		
			$js = "function fitFrames(){
				if(!\$j('#contentScreenLeft').length)
					return;

				var height = contentManager.maxHeight();
				
				\$j('#contentScreenLeft').css('height', height+'px');
				\$j('#contentScreenRight').css('height', height+'px');
			}";
		}
		
		if($this->cols == 3){
			$html = "
		<div style=\"-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;display:inline-block;overflow:auto;vertical-align:top;width:".($this->widths != null ? $this->widths["left"] : "33%").";margin:0px;padding:0px;min-height:500px;\" id=\"contentScreenLeft\">
			
				".$this->content["left"]."
			
		</div><div style=\"-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;overflow:auto;display:inline-block;width:".($this->widths != null ? $this->widths["center"] : "33%").";vertical-align:top;border-left-style:solid;border-left-width:1px;margin:0px;padding:0px;min-height:400px;\" id=\"contentScreenCenter\" class=\"borderColor1\">
			".$this->content["center"]."
		</div><div style=\"-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;overflow:auto;display:inline-block;width:".($this->widths != null ? $this->widths["right"] : "33%").";vertical-align:top;border-left-style:solid;border-left-width:1px;margin:0px;min-height:400px;\" id=\"contentScreenRight\" class=\"borderColor1\">
			".$this->content["right"]."
		</div>";
		
			$js = "function fitFrames(){
				if(!\$j('#contentScreenLeft').length)
					return;

				//var widthSL = (contentManager.maxWidth() - \$j('#contentScreenRight').outerWidth() - \$j('#contentScreenCenter').outerWidth() - 1)+'px';


				//\$j('#contentScreenLeft').css('width', widthSL);

				var height = contentManager.maxHeight();// + 20;
				
				\$j('#contentScreenLeft').css('height', height+'px');
				\$j('#contentScreenRight').css('height', height+'px');
				\$j('#contentScreenCenter').css('height', height+'px');
			}";
		}
		
			$html .= "<script type=\"text/javascript\">
			$js

			\$j(window).resize(function() {
				fitFrames();
			});

			\$j(function(){
				if(!contentManager.".get_class($this->object)."Init)
					setTimeout(function(){fitFrames();}, 200);
				else
					fitFrames();
					
				contentManager.".get_class($this->object)."Init = true;
			});
		</script>";
		
		return $html;
	}
}

?>