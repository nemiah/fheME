<?php

class mSieHabenPostGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mSieHabenPost");

		$gui->name("SieHabenPost");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}


}
?>