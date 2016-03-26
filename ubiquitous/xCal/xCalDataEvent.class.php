<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class xCalDataEvent {
	
	const DTVALUE_DATE = "DATE";
	const DTVALUE_DATETIME = "DATE-TIME";
	
	protected $uid = null;
	protected $summary = "";
	protected $dtStart = "";
	protected $dtStartValue = "";
	protected $description = "";
	protected $url = "";
	protected $duration = "";
	protected $dateEnd = "";
	protected $dateEndValue = "";
	
	public function __construct() {
		$this->uid = uniqid("xCal_", rand());
	}

	public function getUid() {
		return $this->uid;
	}

	public function setUid($uid) {
		$this->uid = $uid;
		return $this;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function setSummary($summary) {
		$this->summary = $summary;
		return $this;
	}

	public function getDtStart() {
		return $this->dtStart;
	}

	public function setDtStart($dtStart) {
		$this->dtStart = $dtStart;
		return $this;
	}

	public function getDtStartValue() {
		return $this->dtStartValue;
	}

	/**
	 * Legt die Art des Formats fest.
	 * @param String $dtStartValue "DATE"|"DATE-TIME"
	 * @return xCalDataEvent
	 */
	public function setDtStartValue($dtStartValue) {
		$this->dtStartValue = $dtStartValue;
		return $this;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}
	
	public function getDuration() {
		return $this->duration;
	}

	public function setDuration($duration) {
		$this->duration = $duration;
		return $this;
	}

	public function getDtEnd() {
		return $this->dateEnd;
	}

	public function setDtEnd($dtEnd) {
		$this->dateEnd = $dtEnd;
		return $this;
	}

	public function getDtEndValue() {
		return $this->dateEndValue;
	}

	public function setDtEndValue($dtEndValue) {
		$this->dateEndValue = $dtEndValue;
		return $this;
	}

}

?>