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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
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

	private function fixErrors(){
		for($i = 0; $i < 20; $i++){
			try {
				$this->xml = new SimpleXMLElement($this->page);
			} catch(Exception $e){
				#echo "<pre>";
				#echo "Exception: ".$e->getMessage()."\n";
				#print_r(libxml_get_errors());

				$ex = explode("\n", $this->page);
				foreach(libxml_get_errors() AS $error){
					#echo trim($error->message).": ";
					#echo htmlentities($ex[$error->line - 1]);
					#echo htmlentities(substr($ex[$error->line - 1], $error->column - 10, 20));
					
					preg_match("/xmlParseCharRef: invalid xmlChar value ([0-9]+)/", trim($error->message), $matches);
					#print_r($matches);
					
					$this->page = str_replace("&#$matches[1];", "", $this->page);
				}

				#echo "</pre>";

				#echo $this->page;

				#return;
			}
		}
	}
	
	public function parseIt(){
		libxml_use_internal_errors(true);

		$this->load();
		$this->page = str_replace('xmlns=', 'ns=', $this->page);

		$this->fixErrors();
		$this->xml = new SimpleXMLElement($this->page);
		
		/*try {
			$this->xml = new SimpleXMLElement($this->page);
		} catch(Exception $e){
			echo "<pre>";
			echo "Exception: ".$e->getMessage()."\n";
			#print_r(libxml_get_errors());
			
			$ex = explode("\n", $this->page);
			foreach(libxml_get_errors() AS $error){
				echo trim($error->message).": ";
				#echo htmlentities($ex[$error->line - 1]);
				echo htmlentities(substr($ex[$error->line - 1], $error->column - 10, 20));
				preg_match("/xmlParseCharRef: invalid xmlChar value ([0-9]+)/", trim($error->message), $matches);
				print_r($matches);
				$this->page = str_replace("&#$matches[1];", "", $this->page);
			}
			
			echo "</pre>";

			#echo $this->page;

			return;
		}*/
	}

	public function getTag($path){
		if($this->xml == null) $this->parseIt();

		return $this->xml->xpath($path);
	}
}
?>
