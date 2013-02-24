<?php
/*
 *  This file is part of phynx.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class HTMLSlicer {
	private $url;
	private $content;
	private $xml;
	private $page;

	public function __construct($urlOrHTMLTidy = null){
		if($urlOrHTMLTidy instanceof HTMLTidy)
			$this->content = $urlOrHTMLTidy;
		else
			$this->url = $urlOrHTMLTidy;
	}

	public function setContent($content){
		$this->content = $content;
	}

	private function load(){
		if($this->page != null) return;

		if($this->url != null){
			$this->page = file_get_contents($this->url);
			return;
		}

		if($this->content instanceof HTMLTidy){
			$this->page = $this->content->getCleaned();
			#print_r($this->page);
			return;
		}

		$this->page = $this->content;
	}

	public function getStructure(){
		if($this->xml == null) $this->parseIt();

		return $this->xml;
	}

	public function parseIt(){
		libxml_use_internal_errors(true);

		$this->load();
		$this->page = str_replace('xmlns=', 'ns=', $this->page);

		try {
			$this->xml = new SimpleXMLElement($this->page);
		} catch(Exception $e){
			#echo "<pre>";
			echo "Exception: ".$e->getMessage()."\n";
			
			/*foreach(libxml_get_errors() as $error){
				if($error->level == LIBXML_ERR_WARNING) echo "Warning: ".$error->message;
				if($error->level == LIBXML_ERR_ERROR) echo "Error: ".$error->message;
				if($error->level == LIBXML_ERR_FATAL) echo "Fatal error: ".$error->message;
			}
			echo "</pre>";*/

			#echo $this->page;

			return;
		}
	}

	public function getTag($path){
		if($this->xml == null) $this->parseIt();

		return $this->xml->xpath($path);
	}
}
?>
