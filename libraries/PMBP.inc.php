<?php

/*
  +--------------------------------------------------------------------------+
  | phpMyBackupPro                                                           |
  +--------------------------------------------------------------------------+
  | Copyright (c) 2004-2007 by Dirk Randhahn                                 |
  | http://www.phpMyBackupPro.net                                            |
  | version information can be found in definitions.php.                     |
  |                                                                          |
  | This program is free software; you can redistribute it and/or            |
  | modify it under the terms of the GNU General Public License              |
  | as published by the Free Software Foundation; either version 2           |
  | of the License, or (at your option) any later version.                   |
  |                                                                          |
  | This program is distributed in the hope that it will be useful,          |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
  | GNU General Public License for more details.                             |
  |                                                                          |
  | You should have received a copy of the GNU General Public License        |
  | along with this program; if not, write to the Free Software              |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.|
  +--------------------------------------------------------------------------+
 */

class phynxBackup {
	public static $handler = null;
}


// function to execute the sql queries provided by the file handler $file
// $file can be a gzopen() or open() handler, $con is the database connection
// $linespersession says how many lines should be executed; if false, all lines will be executed
function PMBP_exec_sql($file, $con, $linespersession = false, $noFile = false) {
	set_time_limit(300);
	$query = "";
	$queries = 0;
	$error = "";
	if (isset($_GET["totalqueries"]))
		$totalqueries = $_GET["totalqueries"];
	else
		$totalqueries = 0;
	if (isset($_GET["start"]))
		$linenumber = $_GET["start"];
	else
		$linenumber = $_GET['start'] = 0;
	if (!$linespersession)
		$_GET['start'] = 1;
	$inparents = false;
	$querylines = 0;

	// $tableQueries and $insertQueries only count this session
	$tableQueries = 0;
	$insertQueries = 0;

	// stop if a query is longer than 300 lines long
	$max_query_lines = 300;

	// lines starting with these strings are comments and will be ignored
	$comment[0] = "#";
	$comment[1] = "-- ";

	while (($linenumber < $_GET["start"] + $linespersession || $query != "") && ($dumpline = gzgets($file, 65536))) {
		// increment $_GET['start'] when $linespersession was not set
		// so all lines of $file will be exeuted at once
		if (!$linespersession)
			$_GET['start']++;

		// handle DOS and Mac encoded linebreaks
		$dumpline = preg_replace("/\r\n$/", "\n", $dumpline);
		$dumpline = preg_replace("/\r$/", "\n", $dumpline);

		// skip comments and blank lines only if NOT in parents    
		if (!$inparents) {
			$skipline = false;
			foreach ($comment as $comment_value) {
				if (!$inparents && (trim($dumpline) == "" || strpos($dumpline, $comment_value) === 0)) {
					$skipline = true;
					break;
				}
			}
			if ($skipline) {
				$linenumber++;
				continue;
			}
		}

		// remove double back-slashes from the dumpline prior to count the quotes ('\\' can only be within strings)  
		$dumpline_deslashed = str_replace("\\\\", "", $dumpline);

		// count ' and \' in the dumpline to avoid query break within a text field ending by ;
		// please don't use double quotes ('"')to surround strings, it wont work
		$parents = substr_count($dumpline_deslashed, "'") - substr_count($dumpline_deslashed, "\\'");
		if ($parents % 2 != 0)
			$inparents = !$inparents;

		// add the line to query
		$query.=$dumpline;

		// don't count the line if in parents (text fields may include unlimited linebreaks)  
		if (!$inparents)
			$querylines++;

		// stop if query contains more lines as defined by $max_query_lines    
		if ($querylines > $max_query_lines) {
			$error = sprintf(BI_WRONG_FILE . "\n", $linenumber, $max_query_lines);
			break;
		}

		// execute query if end of query detected (; as last character) AND NOT in parents
		if (substr(trim($dumpline), -1) == ";" /* ereg(";$",trim($dumpline)) */ && !$inparents) {
			if (!mysqli_query($con, trim($query))) {
				$error = SQ_ERROR . " " . ($linenumber + 1) . "<br>" . nl2br(htmlentities(trim($query))) . "\n<br>" . htmlentities(mysqli_error());
				break;
			}

			if (strtolower(substr(trim($query), 0, 6)) == "insert")
				$tableQueries++;
			elseif (strtolower(substr(trim($query), 0, 12)) == "create table")
				$insertQueries++;
			$totalqueries++;
			$queries++;
			$query = "";
			$querylines = 0;
		}
		$linenumber++;
	}
	return array("queries" => $queries, "totalqueries" => $totalqueries, "linenumber" => $linenumber, "error" => $error, "tableQueries" => $tableQueries, "insertQueries" => $insertQueries);
}

// generates a dump of $db database
// $tables and $data set whether tables or data to backup. $comment sets the commment text
// $drop and $zip tell if to include the drop table statement or dry to pack
function PMBP_dump($CONF, $PMBP_SYS_VAR, $db, $tables, $data, $drop, $zip, $comment) {
	set_time_limit(0);
	$error = false;
	
	$time = date("Ymd");
	#if ($zip == "gzip")
		$backupfile = $db . "." . $time . "_utf8.sql.gz";
	#else
	#	$backupfile = $db . "." . $time . "_utf8.sql";
	
	$backupfile = PMBP_EXPORT_DIR . $backupfile;
	
	$con = @mysqli_connect($CONF['sql_host'], $CONF['sql_user'], $CONF['sql_passwd']);
	if (!$con)
		return "DB_ERROR";
	mysqli_set_charset($con, "utf8");
	mysqli_query($con, "SET SESSION sql_mode='';");
		
	//create comment
	$hout = "# MySQL dump of database '" . $db . "' on host '" . $CONF['sql_host'] . "'\n";
	$hout .= "# backup date and time: " . date("d.m.Y H:i:s") . "\n";
	$hout .= "# " . PMBP_WEBSITE . "\n\n";

	// write users comment
	if ($comment) {
		$hout .= "# comment:\n";
		$comment = preg_replace("'\n'", "\n# ", "# " . $comment);
		foreach (explode("\n", $comment) as $line)
			$hout .= $line . "\n";
		
		$hout .= "\n";
	}

	// print "use database" if more than one databas is available
	if (count(PMBP_get_db_list($CONF)) > 1) {
		$hout .= "CREATE DATABASE IF NOT EXISTS `" . $db . "`;\n\n";
		$hout .= "USE `" . $db . "`;\n";
	}
	
	PMBP_save_to_file($backupfile, $zip, $hout, "a");
	$hout = "";

	// select db
	mysqli_select_db($con, $db);

	// get auto_increment values and names of all tables
	$res = mysqli_query($con, "show table status");
	$all_tables = array();
	while ($row = mysqli_fetch_array($res)) {
		if ($row["Comment"] == "VIEW")
			continue;
		
		$all_tables[] = $row;
	}

	// get table structures
	foreach ($all_tables as $table) {
		$res1 = mysqli_query($con, "SHOW CREATE TABLE `" . $table['Name'] . "`");
		$tmp = mysqli_fetch_array($res1);
		$table_sql[$table['Name']] = $tmp["Create Table"];
	}

	// find foreign keys
	$fks = array();
	if (isset($table_sql)) {
		foreach ($table_sql as $tablenme => $table) {
			$tmp_table = $table;
			// save all tables, needed for creating this table in $fks
			while (($ref_pos = strpos($tmp_table, " REFERENCES ")) > 0) {
				$tmp_table = substr($tmp_table, $ref_pos + 12);
				$ref_pos = strpos($tmp_table, "(");
				$fks[$tablenme][] = substr($tmp_table, 0, $ref_pos);
			}
		}
	}

	foreach ($all_tables AS $row) {
		$tablename = $row['Name'];
		$auto_incr[$tablename] = $row['Auto_increment'];


		$kout = "\n\n";
		if ($tables) {
			if ($drop)
				$kout .= "DROP TABLE IF EXISTS `" . $tablename . "`;\n\n";

			$kout .= $table_sql[$tablename];

			// add auto_increment value
			if ($auto_incr[$tablename] AND strpos($kout, "AUTO_INCREMENT") === false)
				$kout .= " AUTO_INCREMENT=" . $auto_incr[$tablename];

			$kout .= ";";
		}
		$kout .= "\n\n\n";
		
		PMBP_save_to_file($backupfile, $zip, $kout, "a");
		$kout = "";
		
		if ($error) {
			@unlink("./" . PMBP_EXPORT_DIR . $backupfile);
			return false;
		}
		
		// export data
		if ($data && !$error) {
			// check if field types are NULL or NOT NULL
			#$res3 = mysqli_query($con, "show columns from `" . $tablename . "`");

			$res2 = mysqli_query($con, "SELECT * FROM `" . $tablename . "`", MYSQLI_USE_RESULT);
			while ($row2 = mysqli_fetch_row($res2)) {
				$sout = "INSERT INTO `" . $tablename . "` VALUES (";
				
				// run through each field
				foreach($row2 AS $k => $v){
					if (is_null($v))
						$sout .= "null";
					else
						$sout .= "'" . mysqli_real_escape_string($con, $v) . "'";
					
					if ($k < count($row2) - 1)
						$sout .= ", ";
				}
				$sout .=");\n";

				PMBP_save_to_file($backupfile, $zip, $sout, "a");
				$sout = "";
			}
			
			mysqli_free_result($res2);
		}
	}


	PMBP_save_to_file($backupfile, $zip, "", "a");
	
	if(phynxBackup::$handler)
		gzclose(phynxBackup::$handler);
	
	return basename($backupfile);
	
	/*if ($backupfile) {
		if ($zip != "zip")
			return basename($backupfile);
	} else {
		@unlink("./" . $backupfile);
		return false;
	}

	// create zip file in file system
	include_once("pclzip.lib.php");
	$pclzip = new PclZip($backupfile . ".zip");
	$pclzip->create($backupfile, PCLZIP_OPT_REMOVE_PATH, PMBP_EXPORT_DIR);

	// remove temporary plain text backup file used for zip compression
	@unlink(substr($backupfile, 0, strlen($backupfile)));

	if ($pclzip->error_code == 0)
		return basename($backupfile) . ".zip";
	else {
		// print pclzip error message
		echo "<div class=\"red\">pclzip: " . $pclzip->error_string . "</div>";

		// remove temporary plain text backup file 
		@unlink(substr($backupfile, 0, strlen($backupfile) - 4));
		@unlink("./" . $backupfile);
		return FALSE;
	}*/
}


// saves the string in $fileData to the file $backupfile as gz file or not ($zip)
// returns backup file name if name has changed (zip), else TRUE. If saving failed, return value is FALSE
function PMBP_save_to_file($backupfile, $zip, $fileData, $mode) {
	#if ($zip == "gzip") {
	if (phynxBackup::$handler === null)
		phynxBackup::$handler = gzopen($backupfile, $mode . "4");
	#var_dump(phynxBackup::$handler);
	if (!phynxBackup::$handler)
		throw new Exception("Backup file handler exception");

	gzwrite(phynxBackup::$handler, $fileData);
	
	return true;
	#}
	
	
	if (phynxBackup::$handler === null)
		phynxBackup::$handler = fopen($backupfile, $mode);
	
	if (!phynxBackup::$handler)
		throw new Exception("Backup file handler exception");

	$r = fwrite(phynxBackup::$handler, $fileData);
	if($r === false)
		throw new Exception("Backup file handler exception");
	#fclose($zp);
	
	return true;
}



// returns list of databases on $host host using $user user and $passwd password
function PMBP_get_db_list($CONF) {
	#global $CONF;
	// if there is given the name of a single database
	if ($CONF['sql_db']) {
		@mysqli_connect($CONF['sql_host'], $CONF['sql_user'], $CONF['sql_passwd']);
		if (@mysqli_select_db($CONF['sql_db']))
			$dbs = array($CONF['sql_db']);
		else
			$dbs = array();
		return $dbs;
	}

	// else try to get a list of all available databases on the server
	$list = array();
	@mysqli_connect($CONF['sql_host'], $CONF['sql_user'], $CONF['sql_passwd']);
	$db_list = @mysqli_list_dbs();
	while ($row = @mysqli_fetch_array($db_list))
		if (@mysqli_select_db($row['Database']))
			$list[] = $row['Database'];
	return $list;
}

// in dependency on $mode different modes can be selected (see below)
function PMBP_file_info($mode, $path) {
	$filename = preg_replace("#.*/#", "", $path);
	$parts = explode(".", $filename);

	switch ($mode) {

		// returns the name of the database a $path backup file belongs to
		case "db":
			return $parts[0];

		// returns the creation timestamp $path backup file
		case "time":
			return $parts[1];

		// returns "gz" if $path backup file is gziped
		case "gzip":
			if (isset($parts[3]))
				if ($parts[3] == "gz")
					return $parts[3];
			break;

		// returns "zip" if $path backup file is ziped
		case "zip":
			if (isset($parts[3]))
				if ($parts[3] == "zip")
					return $parts[3];
			break;

		// returns type of compression of $path backup file or no
		case "comp":
			if (PMBP_file_info("gzip", $path))
				return "gzip"; elseif (PMBP_file_info("zip", $path))
				return "zip";
			else
				return F_NO;

		// returns the size of $path backup file
		case "size":
			return filesize($path);

		// returns yes if the backup file contains 'drop table if exists' or no if not
		case "drop":
			while ($line = PMBP_getln($path)) {
				$line = trim($line);
				if (strtolower(substr($line, 0, 20)) == "drop table if exists") {
					PMBP_getln($path, true);
					return F_YES;
				} else {
					$drop = F_NO;
				}
			}
			PMBP_getln($path, true);
			return $drop;

		// returns yes if the $path backup files contains tables or no if not
		case "tables":
			while ($line = PMBP_getln($path)) {
				$line = trim($line);
				if (strtolower(substr($line, 0, 12)) == "create table") {
					PMBP_getln($path, true);
					return F_YES;
				} else {
					$table = F_NO;
				}
			}
			PMBP_getln($path, true);
			return $table;

		// returns yes if the $path backup files contains data or no if not
		case "data":
			while ($line = PMBP_getln($path)) {
				$line = trim($line);
				if (strtolower(substr($line, 0, 6)) == "insert") {
					PMBP_getln($path, true);
					return F_YES;
				} else {
					$data = F_NO;
				}
			}
			PMBP_getln($path, true);
			return $data;

		// returns the comment stored to the backup file
		case "comment":
			while ($line = PMBP_getln($path)) {
				$line = trim($line);
				if (isset($comment) && substr($line, 0, 1) == "#") {
					$comment.=substr($line, 2) . "<br>";
				} elseif (isset($comment) && substr($line, 0, 1) != "#") {
					PMBP_getln($path, true);
					return $comment;
				}
				if ($line == "# comment:")
					$comment = FALSE;
			}
			PMBP_getln($path, true);
			if (isset($comment))
				return $comment;
			else
				return FALSE;
	}
}

// returns the content of the [gziped] $path backup file line by line
function PMBP_getln($path, $close = false, $org_path = false) {
	if (!isset($GLOBALS['lnFile']))
		$GLOBALS['lnFile'] = null;
	if (!$org_path)
		$org_path = $path;
	else
		$org_path = PMBP_EXPORT_DIR . $org_path;

	// gz file
	if (PMBP_file_info("gzip", $org_path) == "gz") {
		if (!$close) {
			if ($GLOBALS['lnFile'] == null) {
				$GLOBALS['lnFile'] = gzopen($path, "r");
			}

			if (!gzeof($GLOBALS['lnFile'])) {
				return gzgets($GLOBALS['lnFile']);
			} else {
				$close = true;
			}
		}

		if ($close) {
			// remove the file handler
			@gzclose($GLOBALS['lnFile']);
			$GLOBALS['lnFile'] = null;
			return null;
		}

		// zip file
	} elseif (PMBP_file_info("zip", $org_path) == "zip") {
		if (!$close) {
			if ($GLOBALS['lnFile'] == null) {
				// try to guess the filename of the packed file
				// known problem: ZIP file xyz.sql.zip contains file abc.sql which already exists with different content! 
				if (!file_exists(substr($org_path, 0, strlen($org_path) - 4))) {
					// extract the file
					include_once("pclzip.lib.php");
					$pclzip = new PclZip($path);
					$extracted_file = $pclzip->extract(PMBP_EXPORT_DIR, "");

					if ($pclzip->error_code != 0) {
						// print pclzip error message
						echo "<div class=\"red\">pclzip: " . $pclzip->error_string . "<br>" . BI_BROKEN_ZIP . "!</div>";
						return false;
					} else {
						unset($pclzip);
					}
				}
			}

			// read the extracted file
			$line = PMBP_getln(substr($org_path, 0, strlen($org_path) - 4));
			if ($line == null)
				$close = true;
			else
				return $line;
		}

		// remove the temporary file
		if ($close) {
			@fclose($GLOBALS['lnFile']);
			$GLOBALS['lnFile'] = null;
			@unlink(substr($org_path, 0, strlen($org_path) - 4));
			return null;
		}

		// sql file
	} else {
		if (!$close) {
			if ($GLOBALS['lnFile'] == null) {
				$GLOBALS['lnFile'] = fopen($path, "r");
			}

			if (!feof($GLOBALS['lnFile'])) {
				return fgets($GLOBALS['lnFile']);
			} else {
				$close = true;
			}
		}

		if ($close) {
			// remove the file handler
			@fclose($GLOBALS['lnFile']);
			$GLOBALS['lnFile'] = null;
			return null;
		}
	}
}

?>
