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

class RSSFeed {
	private $entries = array();
	private $title;
	private $link;
	private $description;
	private $language;
	private $copyright;
	
	public function __construct($title, $link, $description, $language, $copyright) {
		$this->title = $title;
		$this->link = $link;
		$this->description = $description;
		$this->language = $language;
		$this->copyright = $copyright;
	}
	
	public function addEntry($title, $description, $link, $author, $guid, $pubDate){
		$E = new stdClass();
		
		$E->title = $title;
		$E->description = $description;
		$E->link = $link;
		$E->author = $author;
		$E->guid = $guid;
		$E->pubDate = $pubDate;
		
		$this->entries[] = $E;
	}
	
	function __toString() {
		$feed = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<rss version=\"2.0\">
	<channel>
		<title>$this->title</title>
		<link>$this->link</link>
		<description>$this->description</description>
		<language>$this->language</language>
		<copyright>$this->copyright</copyright>
		<pubDate>".date("r")."</pubDate>
		
		<!--<image>
			<url>URL einer einzubindenden Grafik</url>
			<title>Bildtitel</title>
			<link>URL, mit der das Bild verknüpft ist</link>
		</image>-->";
		
		foreach($this->entries AS $entry)
			$feed .= "
		<item>
			<title>$entry->title</title>
			<description>$entry->description</description>
			<link>$entry->link</link>
			<author>$entry->author</author>
			<guid>$entry->guid</guid>
			<pubDate>$entry->pubDate</pubDate>
		</item>";
		
		$feed .= "
	</channel>
</rss>";
		
		return $feed;
	}
}

?>