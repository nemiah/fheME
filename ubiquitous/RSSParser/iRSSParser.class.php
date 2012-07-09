<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author nemiah
 */
interface iRSSParser {
	public function parseTitle($title);
	public function parseDescription($description);
	public function getIcon();
}

?>
