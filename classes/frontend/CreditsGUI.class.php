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
class CreditsGUI implements iGUIHTML2{
	public function getHTML($id){
		return "
		<div class=\"backgroundColor1 Tab\"><p>Credits</p></div>
		<table>
			<colgroup>
				<col class=\"backgroundColor2\" style=\"width:150px;\" />
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td style=\"font-weight:bold;\" colspan=\"2\" class=\"backgroundColor0\">Icons</td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://tango.freedesktop.org\">Tango Desktop Project</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://www.fatcow.com/free-icons/\">1000 Free \"Farm-Fresh Web Icons\"</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://www.gnome.org/\">Gnome icons</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://www.kde-look.org/content/show.php/Crystal+Clear?content=25668\">Crystal Clear by Everaldo Coelho</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://pc.de/icons/\">PC.de icons</a></td>
			</tr>
			<tr>
				<td style=\"font-weight:bold;\" colspan=\"2\" class=\"backgroundColor0\">Javascripts</td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Base64-Klasse:</td>
				<td><a href=\"http://www.webtoolkit.info/\">WebToolkit</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">AJAX-Bilderupload:</td>
				<td><a href=\"http://valums.com/ajax-upload/\">Andrew Valums</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">SHA1-Klasse:</td>
				<td><a href=\"http://www.webtoolkit.info/\">WebToolkit</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">AJAX-Framework:</td>
				<td><a href=\"http://www.jquery.com/\">jQuery</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Tooltips:</td>
				<td><a href=\"http://craigsworks.com/projects/qtip2\">qTip2</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">FlashReplace:</td>
				<td><a href=\"http://www.robertnyman.com\">Robert Nyman</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">jStorage:</td>
				<td><a href=\"http://www.jstorage.info/\">Andris Reinman</a></td>
			<tr>
				<td style=\"font-weight:bold;\" colspan=\"2\" class=\"backgroundColor0\">PHP</td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">MySQL-Backup:</td>
				<td><a href=\"http://www.phpmybackuppro.net\">phpMyBackupPro</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">JSMin:</td>
				<td><a href=\"http://code.google.com/p/jsmin-php/\">JSMin</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">PDF-Klasse:</td>
				<td><a href=\"http://www.fpdf.de\">FPDF</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Mail-Klasse:</td>
				<td><a href=\"http://www.phpguru.org/\">Richard Heyes</a></td>
			</tr>
		</table>";
	}
}
?>
