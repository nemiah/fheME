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

class OnEvent {
	/**
	 *
	 * @param PersistentObject $targetObject
	 * @param string $targetMethod
	 * @param string $targetMethodParameters
	 * @param string $bps
	 * @param string $target window or tab
	 * 
	 * @return string
	 */
	public static function window($targetObject, $targetMethod, $targetMethodParameters = "", $bps = null, $target = null){
		
		return "windowWithRme('".str_replace("GUI", "", get_class($targetObject))."', ".($targetObject->getID() == "" ? "-1" : $targetObject->getID()).", '$targetMethod', ".(is_array($targetMethodParameters) ? "['".implode("','", $targetMethodParameters)."']" : "'$targetMethodParameters'").($bps != null ? ", '$bps'" : "").($target != null ? ", '$target'" : "")."); ";
	}
	
	public static function clear($frame){
		return "contentManager.emptyFrame('content$frame');";
	}
	
	public static function selectCustom($targtFrame, $callingPluginID, $selectPlugin, $selectJSFunction, $addBPS = "", $options = ""){
		return "contentManager.backupFrame('$targtFrame','selectionOverlay'); contentManager.customSelection('$targtFrame', '$callingPluginID', '$selectPlugin', '$selectJSFunction', '$addBPS', ".($options == "" ? "{}" : $options).");";
	}
	
	public static function context($plugin, $identifier, $title, $leftOrRight = "right", $upOrDown = "down", $options = "{}"){
		return "phynxContextMenu.start(this, '$plugin','$identifier','$title', '$leftOrRight', '$upOrDown', $options);";
	}
	
	public static function iframe($targetObject, $targetMethod, $targetMethodParameters = "", $targetFrame, $bps = null){
		
		return "contentManager.iframeRme('".str_replace("GUI", "", get_class($targetObject))."', ".$targetObject->getID().", '$targetMethod', ".(is_array($targetMethodParameters) ? "['".implode("','", $targetMethodParameters)."']" : "'$targetMethodParameters'").", '$targetFrame'".($bps != null ? ", '$bps'" : "")."); ";
	}
	
	public static function popupSidePanel($targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $popupName = "edit"){
		return "Popup.sidePanel('$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), '$popupName');";
	}
	
	public static function reloadSidePanel($targetClass, $popupName = "edit"){
		return "Popup.sidePanelRefresh('$targetClass', '$popupName');";
	}
	
	public static function droppable($elementID, $onDropFunction, $hoverClass = null){
		return "
		var onDropFunction$elementID = $onDropFunction;
		\$j( \"#$elementID\" ).droppable({
			drop: function( event, ui ) {
				onDropFunction$elementID(ui.draggable);
			}
			".($hoverClass != null ? ", hoverClass: '$hoverClass'" : "")."
		});";
	}
	
	public static function sortable($selector, $handle, $saveTo = null, $axis = "y", $connectWith = null, $placeholder = null, $onUpdateComplete = "", $additionalParameters = array()){
		
		if($saveTo != null){
			$ex = explode("::", $saveTo);
			$saveTo = OnEvent::rme($ex[0], $ex[1], array_merge(array("Sortable.serialize('$selector')"), $additionalParameters), $onUpdateComplete);
		}

		return "
		\$j('$selector').sortable({
			".($axis != "" ? "axis: '$axis', " : "")."
			update: function(){".($saveTo == null ? "" : $saveTo)."},
			".($connectWith != null ? "connectWith: \$j('$connectWith')," : "")."
			dropOnEmpty: true,
			".($placeholder != null ? "placeholder: '$placeholder', " : "")."
			".($handle != null ? "handle: \$j('$handle')" : "")."
		});";
	}
	
	public static function script($action){
		return "<script type=\"text/javascript\">$action</script>";
	}
	
	public static function rme($targetObject, $targetMethod, $targetMethodParameters = "", $onSuccessFunction = null, $bps = null, $onFailureFunction = null){
		$id = -1;
		if($targetObject instanceof PersistentObject)
			$id = $targetObject->getID();
		
		/*if($targetObject instanceof Collection)
			$id = -1;
		
		
		if(!$targetObject instanceof Collection AND !$targetObject instanceof PersistentObject)
			$id = -1*/
		
		
		$targetObject = str_replace("GUI", "", is_object($targetObject) ? get_class($targetObject) : $targetObject);
		
		if($onSuccessFunction != null AND strpos(trim($onSuccessFunction), "function") !== 0)
				$onSuccessFunction = "function(transport){ $onSuccessFunction }";
		
		if($onFailureFunction != null AND strpos(trim($onFailureFunction), "function") !== 0)
				$onFailureFunction = "function(transport){ $onFailureFunction }";
		
		return "contentManager.rmePCR('$targetObject', $id, '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), ".($onSuccessFunction != null ? $onSuccessFunction : "function(){}")."".($bps != null ? ", '$bps'" : ", ''").", 1, ".($onFailureFunction != null ? $onFailureFunction : "function(){}")."); ";
	}
	
	public static function closePopup($plugin, $id = "edit"){
		if($plugin instanceof Collection)
			$plugin = str_replace("GUI", "", get_class($plugin));
		
		return "Popup.close('$plugin', '$id');";
	}
	
	public static function reloadPopup($plugin, $bps = "", $firstParameter = null){
		if($plugin instanceof Collection)
			$plugin = str_replace("GUI", "", get_class($plugin));
		
		
		return "Popup.refresh('$plugin'".(($bps != "" OR $firstParameter != null) ? ", '$bps'" : "")." ".($firstParameter != null ? ", '$firstParameter'" : "").");";
	}
	
	public static function reload($frame, $bps = null){
		return "contentManager.reloadFrame('content$frame'".($bps != null ? ", '$bps'" : "").");";
	}
	
	public static function closeContext(){
		return "phynxContextMenu.stop();";
	}
	
	/**
	 *
	 * @param string $target Screen, Left or Right
	 * @param string $plugin
	 * @param string $withId
	 * @param string $page
	 * @param string $onSuccessFunction
	 * @param string $bps
	 * @return string 
	 */
	public static function frame($target, $plugin, $withId = -1, $page = 0, $onSuccessFunction = null, $bps = ""){
		
		if($onSuccessFunction != null AND strpos(trim($onSuccessFunction), "function") !== 0)
				$onSuccessFunction = "function(transport){ $onSuccessFunction }";
		
		if($target == "Left")
			$target = "contentLeft";
		
		if($target == "Right")
			$target = "contentRight";
		
		if($target == "Screen")
			$target = "contentScreen";
		
		return "contentManager.loadFrame('$target', '$plugin', ".($withId != "transport.responseText" ? "'$withId'" : $withId).", '$page', '$bps', ".($onSuccessFunction != null ? $onSuccessFunction : "function(){}").");";
	}
	
	public static function popup($title, $targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $bps = "", $popupOptions = null, $popupName = "edit"){
		return "Popup.load('".T::_($title)."', '$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), '$bps', '$popupName'".($popupOptions != null ? ", '".addslashes($popupOptions)."'" : "").");";
	}
}
?>