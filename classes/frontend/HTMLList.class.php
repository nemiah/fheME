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

class HTMLList {
	private $items = array();
	private $id = null;
	private $style = "";
	private $itemData = array();
	private $itemClass = array();
	private $itemStyle = array();
	private $itemEvent = array();
	private $itemID = array();
	private $class = "";
	private $data = array();
	private $sortable = "";
	private $itemsStyle = "";
	
	public function noDots(){
		$this->addListStyle("list-style-type:none;");
	}
	
	public function minHeight($height){
		$this->style .= "min-height:{$height}px;";
	}
	

	/**
	 * Creates a new Button and adds it to the list.
	 * Then the Button will be returned to add some more functionality
	 *
	 * @param string $label
	 * @param string $image
	 * @return Button
	 */
	function addButton($label, $image = "", $type = "bigButton"){
		$B = new Button($label, $image, $type);

		$this->addItem($B);

		return $B;
	}
	
	public function sortable($handle, $saveTo = null, $connectWith = null, $dropPlaceholder = null, $axis = "y", $additionalParameters = array()){
		$this->sortable = OnEvent::sortable("%LISTID%", $handle, $saveTo, $axis, $connectWith, $dropPlaceholder, "", $additionalParameters);
	}
	
	public function maxHeight($height){
		$this->style .= "max-height:{$height}px;overflow:auto;";
	}
	
	public function addListData($name, $value){
		$this->data[$name] = $value;
	}
	
	public function addListClass($class){
		$this->class .= $class." ";
	}
	
	public function setListID($id){
		$this->id = $id;
	}
	
	public function addListStyle($style){
		$this->style .= $style;
	}
	
	public function addItem($content){
		$this->items[] = $content;
		
		if($this->itemsStyle != "")
			$this->addItemStyle($this->itemsStyle);
	}
	
	public function addItems(array $items){
		foreach($items AS $v)
			$this->items[] = $v;
	}
	
	public function zeroItem($content, $class = ""){
		if(count($this->items) > 0)
			return;
		
		$this->addItem ($content);
		$this->addItemClass($class);
	}
	
	public function addItemData($name, $value){
		if(!isset($this->itemData[count($this->items) - 1]))
			$this->itemData[count($this->items) - 1] = array();
		
		$this->itemData[count($this->items) - 1][$name] = $value;
	}
	
	public function addItemClass($class){
		if(!isset($this->itemClass[count($this->items) - 1]))
			$this->itemClass[count($this->items) - 1] = "";
		
		$this->itemClass[count($this->items) - 1] .= $class." ";
	}
	
	public function addItemStyle($style){
		if(!isset($this->itemStyle[count($this->items) - 1]))
			$this->itemStyle[count($this->items) - 1] = "";
		
		$this->itemStyle[count($this->items) - 1] .= $style." ";
	}
	
	public function setItemsStyle($style){
		$this->itemsStyle = $style;
	}
	
	public function setItemID($ID){
		$this->itemID[count($this->items) - 1] = $ID;
	}
	
	public function addItemEvent($event, $action){
		if(!isset($this->itemEvent[count($this->items) - 1]))
			$this->itemEvent[count($this->items) - 1] = "";
		
		$this->itemEvent[count($this->items) - 1] .= "$event=\"$action\"";
	}
	
	function __toString() {
		if($this->sortable AND $this->id == null)
			$this->id = "List".rand (10000, 100000);
			
		$listData = "";
		if(count($this->data) > 0)
			foreach($this->data AS $name => $data)
				$listData .= "data-$name=\"$data\" ";
		
		$html = "
			<ul $listData".($this->id != null ? "id=\"$this->id\"" : "")." ".($this->style != "" ? "style=\"$this->style\"" : "")." ".($this->class != "" ? "class=\"$this->class\"" : "").">";
		
		foreach($this->items AS $key => $item){
			$itemData = "";
			if(isset($this->itemData[$key]))
				foreach($this->itemData[$key] AS $name => $data)
					$itemData .= "data-$name=\"$data\" ";
			
			$html .= "<li ".(isset($this->itemEvent[$key]) ? $this->itemEvent[$key] : "")." $itemData ".(isset($this->itemClass[$key]) ? "class=\"".$this->itemClass[$key]."\"" : "")." ".(isset($this->itemStyle[$key]) ? "style=\"".$this->itemStyle[$key]."\"" : "")." ".(isset($this->itemID[$key]) ? "id=\"".$this->itemID[$key]."\"" : "").">$item</li>";
		}
		
		$html .= "
			</ul>";
		
		if($this->sortable)
			$html .= OnEvent::script(str_replace("%LISTID%", "#".$this->id, $this->sortable));
		
		
		return $html;
	}
}

?>