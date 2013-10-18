<?php

class RCGUI extends RC implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("RC");
	
		return $gui->getEditHTML();
	}
}
?>