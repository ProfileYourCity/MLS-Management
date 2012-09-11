<?php
ini_set("memory_limit","10012M");
ini_set("auto_detect_line_endings", true);
define('MLSDATAPATH','data/mlsdata/');

class extractData extends mlsManagement
{
	public function zipCheck($server_file, $local_file, $target_file, $ftp_user_name, $ftp_user_pass, $ftp_server)
	{
		// set up basic connection
		$conn_id = ftp_connect($ftp_server);

		// login with username and password
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

		// try to download $server_file and save to $local_file
		if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) 
		{
		    $this->unZipFile($local_file, $target_file);
		} 
		else 
		{
		   return false;
		}

		// close the connection
		ftp_close($conn_id);
	}


	//function to unzip the zip
	public function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true)
	{
	  if ($zip = zip_open($src_file))
	  {
		if ($zip)
		{
		  $splitter = ($create_zip_name_dir === true) ? "." : "/";
		  if ($dest_dir === false) $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";

		  $this->create_dirs($dest_dir);

		  while ($zip_entry = zip_read($zip))
		  {

			$pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
			if ($pos_last_slash !== false)
			{
			  $this->create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));
			}

			if (zip_entry_open($zip,$zip_entry,"r"))
			{

			  $file_name = $dest_dir.zip_entry_name($zip_entry);

			  if ($overwrite === true || $overwrite === false && !is_file($file_name))
			  {
				$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

				file_put_contents($file_name, $fstream );
				chmod($file_name, 0755);
			  }

			  zip_entry_close($zip_entry);
			}
		  }
		  zip_close($zip);
		}
	  }
	  else
	  {
		return false;
	  }

	  return true;
	}
	
	/**
	 * This function creates recursive directories if it doesn't already exist
	 *
	 * @param String  The path that should be created
	 *
	 * @return  void
	 */
	public function create_dirs($path)
	{
	  if (!is_dir($path))
	  {
		$directory_path = "";
		$directories = explode("/",$path);
		array_pop($directories);

		foreach($directories as $directory)
		{
		  $directory_path .= $directory."/";
		  if (!is_dir($directory_path))
		  {
			mkdir($directory_path);
			chmod($directory_path, 0777);
		  }
		}
	  }
	}
	
	public function unZipFile($pluginfile,$target_file)
	{
		
		 /* Unzip the source_file in the destination dir
		 *
		 * @param   string      The path to the ZIP-file.
		 * @param   string      The path where the zipfile should be unpacked, if false the directory of the zip-file is used
		 * @param   boolean     Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false) (only if the destination directory is set to false!)
		 * @param   boolean     Overwrite existing files (true) or not (false)
		 *
		 * @return  boolean     Succesful or not
		 */ 
		$success = $this->unzip($pluginfile, MLSDATAPATH, true, true);	
		if($success)
		{
			unlink($pluginfile);
			return $target_file;
		}
		else
		{
			?>
			<div class="error">
				Error
			</div>
			<?php
		}
	}
    
    /** 
     * Converts datetime from RETS format to MySQL.
     * 
     * @param string $RETStime Datetime in RETS format [ $datetimeObj->format('Y-m-d') . 'T' . $datetimeObj->format('H:i:s') . 'Z' ] 
     * 
     * @return string datetime in MySQL format (can safely be inserted into DB as datetime datatype).
     * 
     * TODO Perhaps move these to a helper class?
     */
    public function RETSdatetimeToMySQL($RETSdatetime, $timezone = 'UTC') 
    {
        $DateTimeZone = new DateTimeZone($timezone);
        $datetime = new DateTime($RETSdatetime, $DateTimeZone);

        return $datetime->format('Y-m-d H:i:s'); 
    }
     
    /** 
     * Converts datetime from MySQL format to RETS format.
     * 
     * @param string $MySQLdatetime Datetime in MySQL format.
     * 
     * @return string Datetime in RETS format. 
     * 
     * TODO Perhaps move these to a helper class?
     */
    public function MySQLdatetimeToRETS($MySQLdatetime, $timezone = 'UTC') 
    {
        $DateTimeZone = new DateTimeZone($timezone);
        $datetime = new DateTime($MySQLdatetime, $DateTimeZone);

        return $datetime->format('Y-m-d') . 'T' . $datetime->format('H:i:s') . 'Z';
    }
    

	public function checkProperty($table, $mls_num)
	{
		try 
		{
	        $sql = "SELECT mls_listing_id FROM `{$table}` WHERE `mls_listing_id`='{$mls_num}'";
	        $sth = $this->dbh->prepare($sql);
	        $sth->execute();
	        $result = $sth->fetch(PDO::FETCH_ASSOC);
	        return $result;
        }
        catch(PDOException $e)
        {
            echo '<div class="error">'.$e->getMessage().'</div>';
        }
	}

	public function getQuestionMarks($arrayToCount)
	{		
		$questionMarks = '';
		foreach($arrayToCount as $array_item)
		{
			$questionMarks  .= "?,";
		}
		$questionMarks = substr($questionMarks,0,-1);
		return $questionMarks;
	}

	public function getPDOColumnNames($retsArray)
	{
		$columns = '';
		foreach($retsArray as $name => $value)
		{
			if($name == 'range')
			{
				$columns .= "`$name`,";
			}
			else
			{
				$columns .= "$name,";
			}
		}
		$columns = rtrim($columns, ",");
		return $columns;
	}

	public function checkDateDifferance($date_1, $date_2)
	{
		  return round(abs(strtotime($date_1)-strtotime($date_2))/86400);
	}
}
?>