<?php
class imageDownloader extends mlsManagement
{
    /**
     * Path for storing images.
     * @var string
     */
	public $imagePath;

    /**
     * Path to keep log of successful runs.
     * @var string 
     */
    public $activity_log_path;

    
    /**
     * Path to keep error log.
     * @var string 
     */
    public $error_log_path;

    /**
     * Memory limit for PHP during runs.
     * @var string
     */
    public $mem_limit;

    public $placeholder;


	public function __construct()
	{
        $this->imagePath = GSROOTPATH . $this->getData('image_archive_path').'/';
		$this->activity_log_path = GSROOTPATH . $this->getData('activity_log_path');
		$this->error_log_path = GSROOTPATH . $this->getData('error_log_path'); 
        $this->mem_limit = $this->getData('phretsimg_mem_limit');
        $this->placeholder = $this->imagePath.$this->getData('placeholder_image');


		// Set memory limit.
		ini_set('memory_limit', $this->mem_limit);
	}

    /**
     * Get mls listing numbers to download images for
     * 
     * @param array $mls information
     * @param string $table the table (property type) to select from
     * @param string $dateRange the range of properties to sleect
     * @param int $limit the limit for the sql statement
     * @param int $offset the offset for the sql statement
     * 
     */
	public function getPropertyIds($mls, $table, $dateRange=null, $limit=null, $offset=null)
	{
		$this->dbh = $this->connectDB($this->getData("dbhost"), $mls['mlsdatabase'], $this->getData("dbuser"), $this->getData("dbpass"));
		try 
		{
			$this->dbh = $this->connectDB($this->getData("dbhost"), $mls['mlsdatabase'], $this->getData("dbuser"), $this->getData("dbpass"));
			if (!$this->dbh) 
			{
				$error = 'mlsImgModel: Failed to connect to database.';
				throw new Exception($error);
				return FALSE;
			} 
			else 
			{
				$sql = "SELECT mls_listing_id,listing_rid FROM {$table}";
				if(!is_null($dateRange))
				{
					$current_date = date('Y-m-d H:i:s');
					$subtractDays = strtotime ('-'.$dateRange.' day', strtotime($current_date));
					$newdate = date ( 'Y-m-d H:i:s' , $subtractDays);
					$sql .= " WHERE `date_last_transaction` > '{$newdate}'";
					//$sql .= " WHERE `status_code` = 'CS-Closed Sale'";
				}
				if($limit != null)
				{
					$sql .= " LIMIT {$limit}";
				}
				if($offset != null)
				{
					$sql .= " OFFSET {$offset}";
				}
				echo "\nSQL Statement: ".$sql."\n";
				$stmt = $this->dbh->prepare($sql);
				$stmt->execute();
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$count = 0;
				$mls_listing_ids = array();
				foreach ($results as $result) 
				{
					$mls_listing_ids[$count]['mls_listing_id'] = $result['mls_listing_id'];
					$mls_listing_ids[$count]['listing_rid'] = $result['listing_rid'];
					$count++;
				}
				if(is_array($mls_listing_ids))
				{
					return $mls_listing_ids;
				}
				else
				{
					return false;
				}
			}
		}
		catch (Exception $e) 
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

    /**
     * Get image binary data
     * 
     * @param array $mls information
     * @param array $properties the mls listing ids
     * 
     */
	public function getImageData($mls, $properties)
	{
        $rets = new phRetsModel($mls['retsurl'], $mls['retsuser'], $mls['retspass'], '1.7.2', $mls['retsprofile']);
        $rets = $rets->connect();
        if(is_array($properties))
        {
        	$countImages = 0;
        	foreach($properties as $property)
        	{
        		$listing_id = (isset($property['listing_rid']) && empty($property['listing_rid'])) ? $property['mls_listing_id'] : $property['listing_rid'];
        		$imageData[$property['mls_listing_id']] = $rets->GetObject("Property", "Photo", $listing_id);
        		$countImages++;
        	}
        	if(is_array($imageData))
        	{
        		return $imageData;
        	}
        	else
        	{
        		return false;
        	}
        }
        else
        {
        	return false;
        }
	}

    /**
     * Save image from binary data
     * 
     * @param array $photo the binary data of the image and other information on the image
     * @param string $table the table (property type) to select from
     * 
     */
	public function saveImageData($photo, $mls_id, $table, $listing_id=null)
	{
		//$path = $this->imagePath.$mls_id.'/'.$table."/";
		$path = $this->imagePath.$mls_id.'/';
		if(!empty($photo['Content-ID']))
		{
			if(is_null($listing_id))
			{
				$listing = $photo['Content-ID'];
			}
			else
			{
				$listing = $listing_id;
			}
			$number = $photo['Object-ID'];
			if ($number == 1) 
			{
				$filename = $path. $listing . ".jpg";
			}
			else 
			{
				$filename = $path . "{$listing}_{$number}.jpg";
			}

			// Resize or adjust quality, if needed, and generate .jpg.
			if ($photo['Success'] == true) 
			{ 
				$jpg = imagecreatefromstring($photo['Data']);
				if (imagejpeg($jpg, $filename)) 
				{
					// Free up memory
					imagedestroy($jpg);
					$this->messages[] = "Image created: {$filename}."; // Just for debugging purposes.
				} 
				if(file_exists($filename))
				{
					$this->_logError('File Created!: '.$filename);
					return $filename;
				}
				else
				{
					echo 'File Creation Failed!';
					$this->_logError('File Creation Failed!: '.$filename);
					$this->createPlaceHolder($filename);
				}
			}
			else 
			{
				return false;
			}
		}
		else
		{
			echo 'WTF $photo["Content-ID"] is Empty /RAGE AHJHAHA!!<br/>';
			return false;
		}
	}

    /**
     * Create placeholder image if image creation failed
     * 
     * @param string $filename the filename of the image to be created
     * 
     */
	public function createPlaceHolder($filename)
	{
		copy($this->placeholder, $filename);
		if(file_exists($filename))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

    /**
     * Log error message.
     * 
     * Simple error logger.
     * 
     * @param string $error_msg Text of error message to log.
     * 
     */
    public function _logError($error_msg) 
    {
        include_once(GSPLUGINPATH.'mls_management/inc/KLogger.php' );
        $KLogger = new KLogger($this->error_log_path, 3);
        $KLogger->logError($error_msg);
    }
}