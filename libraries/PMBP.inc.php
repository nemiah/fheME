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
			if (!mysql_query(trim($query), $con)) {
				$error = SQ_ERROR . " " . ($linenumber + 1) . "<br>" . nl2br(htmlentities(trim($query))) . "\n<br>" . htmlentities(mysql_error());
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
	#global $CONF;
	#global $PMBP_SYS_VAR;
	$error = false;
	
	// set max string size before writing to file
	#if (@ini_get("memory_limit"))
	#	$max_size = Util::getMaxMemory() - memory_get_peak_usage(true) - 1024 * 1024  * 20;
	#else
		$max_size = 1024 * 1024  * 20;

	#echo "<br />";
	#die(Util::formatByte($max_size));
	#die();
	// set backupfile name
	$time = date("Ymd");
	if ($zip == "gzip")
		$backupfile = $db . "." . $time . ".sql.gz";
	else
		$backupfile = $db . "." . $time . ".sql";
	
	$backupfile = PMBP_EXPORT_DIR . $backupfile;

	$con = @mysql_connect($CONF['sql_host'], $CONF['sql_user'], $CONF['sql_passwd']);
	if (!$con)
		return "DB_ERROR";
	
	//create comment
	$out = "# MySQL dump of database '" . $db . "' on host '" . $CONF['sql_host'] . "'\n";
	$out .= "# backup date and time: " . strftime($CONF['date'], $time) . "\n";
	$out .= "# built by phpMyBackupPro " . PMBP_VERSION . "\n";
	$out .= "# " . PMBP_WEBSITE . "\n\n";

	// write users comment
	if ($comment) {
		$out .= "# comment:\n";
		$comment = preg_replace("'\n'", "\n# ", "# " . $comment);
		foreach (explode("\n", $comment) as $line)
			$out .= $line . "\n";
		
		$out .= "\n";
	}

	// print "use database" if more than one databas is available
	if (count(PMBP_get_db_list($CONF)) > 1) {
		$out .= "CREATE DATABASE IF NOT EXISTS `" . $db . "`;\n\n";
		$out .= "USE `" . $db . "`;\n";
	}

	// select db
	@mysql_select_db($db);

	// get auto_increment values and names of all tables
	$res = mysql_query("show table status");
	$all_tables = array();
	while ($row = mysql_fetch_array($res)) {
		if ($row["Comment"] == "VIEW")
			continue;
		
		$all_tables[] = $row;
	}

	// get table structures
	foreach ($all_tables as $table) {
		$res1 = mysql_query("SHOW CREATE TABLE `" . $table['Name'] . "`");
		$tmp = mysql_fetch_array($res1);
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

	// order $all_tables and check for ring constraints
	#$all_tables_copy = $all_tables;
	#$all_tables = PMBP_order_sql_tables($all_tables, $fks);
	#$ring_contraints = false;

	// ring constraints found
	#if ($all_tables === false) {
	#	$ring_contraints = true;
	#	$all_tables = $all_tables_copy;

	#	$out.="\n# ring constraints workaround\n";
	#	$out.="SET FOREIGN_KEY_CHECKS=0;\n";
	#	$out.="SET AUTOCOMMIT=0;\n";
	#	$out.="START TRANSACTION;\n";
	#}
	#unset($all_tables_copy);

	// as long as no error occurred
	if ($error) {
		@unlink("./" . $backupfile);
		return false;
	}

	foreach ($all_tables as $row) {
		$tablename = $row['Name'];
		$auto_incr[$tablename] = $row['Auto_increment'];

		// don't backup tables in $PMBP_SYS_VAR['except_tables']
		if (in_array($tablename, explode(",", $PMBP_SYS_VAR['except_tables'])))
			continue;

		$out.="\n\n";
		// export tables
		if ($tables) {
			$out.="### structure of table `" . $tablename . "` ###\n\n";
			if ($drop)
				$out.="DROP TABLE IF EXISTS `" . $tablename . "`;\n\n";

			$out.=$table_sql[$tablename];

			// add auto_increment value
			if ($auto_incr[$tablename])
				$out.=" AUTO_INCREMENT=" . $auto_incr[$tablename];

			$out.=";";
		}
		$out.="\n\n\n";
		
		if ($error) {
			@unlink("./" . PMBP_EXPORT_DIR . $backupfile);
			return false;
		}
		
		// export data
		if ($data && !$error) {
			$out.="### data of table `" . $tablename . "` ###\n\n";

			// check if field types are NULL or NOT NULL
			$res3 = mysql_query("show columns from `" . $tablename . "`");

			$res2 = mysql_query("select * from `" . $tablename . "`");
			for ($j = 0; $j < mysql_num_rows($res2); $j++) {
				$out .= "insert into `" . $tablename . "` values (";
				$row2 = mysql_fetch_row($res2);
				// run through each field
				for ($k = 0; $k < $nf = mysql_num_fields($res2); $k++) {
					// identify null values and save them as null instead of ''
					if (is_null($row2[$k]))
						$out .="null";
					else
						$out .="'" . mysql_real_escape_string($row2[$k]) . "'";
					if ($k < ($nf - 1))
						$out .=", ";
				}
				$out .=");\n";

				// if saving is successful, then empty $out, else set error flag
				if (strlen($out) > $max_size) {
					if ($out = PMBP_save_to_file($backupfile, $zip, $out, "a"))
						$out = "";
					else
						$error = true;
				}
			}
		}

		// if saving is successful, then empty $out, else set error flag
		if (strlen($out) > $max_size) {
			if ($out = PMBP_save_to_file($backupfile, $zip, $out, "a"))
				$out = "";
			else
				$error = true;
		}
	}


	// if db contained ring constraints        
	#if ($ring_contraints) {
	#	$out.="\n\n# ring constraints workaround\n";
	#	$out .= "SET FOREIGN_KEY_CHECKS=1;\n";
	#	$out .= "COMMIT;\n";
	#}

	// save to file
	if ($backupfile = PMBP_save_to_file($backupfile, $zip, $out, "a")) {
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
	}
}


// saves the string in $fileData to the file $backupfile as gz file or not ($zip)
// returns backup file name if name has changed (zip), else TRUE. If saving failed, return value is FALSE
function PMBP_save_to_file($backupfile, $zip, &$fileData, $mode) {
	if ($zip == "gzip") {
		if (phynxBackup::$handler === null)
			phynxBackup::$handler = @gzopen("./" . $backupfile, $mode . "9");
		
		if (!phynxBackup::$handler)
			return false;
		
		@gzwrite(phynxBackup::$handler, $fileData);
		#@gzclose($zp);
		
		return $backupfile;
	}
	
	
	if (phynxBackup::$handler === null)
		phynxBackup::$handler = fopen($backupfile, $mode);
	
	if (!phynxBackup::$handler)
		return false;

	fwrite(phynxBackup::$handler, $fileData);
	#fclose($zp);
	
	return $backupfile;
	
}



// returns list of databases on $host host using $user user and $passwd password
function PMBP_get_db_list($CONF) {
	#global $CONF;
	// if there is given the name of a single database
	if ($CONF['sql_db']) {
		@mysql_connect($CONF['sql_host'], $CONF['sql_user'], $CONF['sql_passwd']);
		if (@mysql_select_db($CONF['sql_db']))
			$dbs = array($CONF['sql_db']);
		else
			$dbs = array();
		return $dbs;
	}

	// else try to get a list of all available databases on the server
	$list = array();
	@mysql_connect($CONF['sql_host'], $CONF['sql_user'], $CONF['sql_passwd']);
	$db_list = @mysql_list_dbs();
	while ($row = @mysql_fetch_array($db_list))
		if (@mysql_select_db($row['Database']))
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
