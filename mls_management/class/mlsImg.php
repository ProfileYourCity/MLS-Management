<?php

/**
 * MLS Image Class: A GetSimple CMS / PYC class for MLS-derived image management.
 * 
 * MLS Image Class supports the MLS image generation and manipulation functions
 * of the MLSdata plugin for GetSimple CMS / PYC CMS.
 * 
 * @author Jay Scott <jason@jasonpscott.com>
 * 
 * @version 0.1
 * @since 0.1
 * 
 * @param int    $listing_ids      Records to select identified by mls_listing_id numbers.
 * 
 * TODO Add proper phpdoc stuff for attributes.
 * TODO Harmonize the versions of this into one universal library.
 * TODO Error/notice messaging needs to be reworked and the messages cleaned-up.
 */
class MLSimg {
    
    /**
     * Path for storing images.
     * @var string
     */
    public $img_path;
    
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
    
    /**
     * mls_listing_id numbers of recrods to select.
     * @var mixed
     */
    public $listing_ids;
    
    /**
     * Contains messages generating during execution.
     * @var array 
     */
    public $messages = array();
    
    /**
     * RETS URL.
     * @var string
     */
    public $rets_url;

    /**
     * RETS username.
     * @var string 
     */
    public $rets_user;

    /**
     * RETS password.
     * @var string
     */
    public $rets_pass;

    /**
     * RETS version number.
     * @var string
     */
    public $rets_version = '1.7.2';

    /**
     * RETS Profile.
     * @var string
     */
    public $rets_profile;

    /**
     * Names of MLS systems to access.
     * @var array
     */
    public $all_mls = array();
    
    /**
     * Set up.
     * 
     * @param mixed $listing_ids string or int or array of mls_listing_id number(s).
     * 
     * TODO Make sure connections only occur when methods that need them are
     * called and close all connections when no longer needed.
     */
    function __construct($listing_ids = '', $mls)
    {
         // Make sure configuration file exists and set path, if so. // TODO Figure out if I need to check for config files anymore.
            
            // Set paths for writing images and for logging, and set RETS credentials.
            $mlsData = new mlsData;
            
            $this->rets_url = $mls['retsurl'];
            $this->rets_user = $mls['retsuser'];
            $this->rets_pass = $mls['retspass'];
            $this->rets_profile = $mls['retsprofile'];
            
            $this->img_path = GSROOTPATH . $mlsData->getData('image_archive_path').'/'.$mls['mlsname'].'/';
            $this->placeholder_path = GSROOTPATH . $mlsData->getData('placeholder_image');
            $this->activity_log_path = GSROOTPATH . $mlsData->getData('activity_log_path');
            $this->error_log_path = GSROOTPATH . $mlsData->getData('error_log_path'); 
            
            // Set miscellaneous class settings.
            $this->mem_limit = $mlsData->getData('phretsimg_mem_limit');
            
            // TODO Error handling could still be better.
            // TODO Have these set message and return them instead of just croaking on the spot.
            // Check that paths are set.
            if(empty($this->img_path)) 
            {
                die('Error setting image path.');
            } 
            elseif(empty($this->error_log_path)) 
            {
                die('Error setting error log path.');
            } 
            elseif(empty($this->activity_log_path)) 
            {
                die('Error setting success log path.');
            } 
            elseif(empty($this->mem_limit)) 
            {
                die('Problem setting PHP memory limit.');
            }
                
            // Set memory limit.
            ini_set('memory_limit', $this->mem_limit);
            
            // Finally, get mls_listing_id numbers if passed in.
            $this->listing_ids = $listing_ids;
            //echo 'Listing IDS: '.$this->listing_ids;
    }
    
    /**
     * Generate images by mls_listing_id number.
     * 
     * @param $listing_ids    mixed  mls_listing_id number(s); accepts string, int, or array of strings and ints.
     * @param $output_format  string Format of image data coming from RETS.
     * @param $output_quality int    Image quality, 0 to 100 for jpg. 
     * 
     * @return mixed
     * 
     */
    public function generateImagesByID($output_format = 'jpg', $output_quality = 100)
    {
        // TODO Refactor this, same can be done with much less code.
        if (is_string($this->listing_ids) || is_int($this->listing_ids)) 
        {
            $listing_id = $this->listing_ids; // TODO Figure out better way to do this. Since single ID has been passed-in, this makes the var name more accurate.
            switch ($output_format) 
            {
                case $output_format = 'jpg':
                    if ( $this->_processJPGs($listing_id, $output_quality) ) 
                    {
                        $this->messages[] = 'Put down your cocktail -- phRETSimg is finished.<br />';
                        return array('run_status' => '1', 'messages' => $this->messages);
                    } 
                    else 
                    {
                        $this->messages[] = 'Something went horribly wrong and phRETSimg failed to finish.<br />';
                        return FALSE;
                    }
                case $output_format = 'test': // TODO Build support for other output types.
                    $this->messages[] = 'Second output type would come from here!';
                    return FALSE;
            }
  		} 
        elseif (is_array($this->listing_ids)) 
        {
            switch ($output_format) 
            {
		        case $output_format = 'jpg':
		            if ($this->_processJPGs($this->listing_ids, $output_quality)) 
		            {
		                $this->messages[] = 'Put down your cocktail -- phRETSimg is finished.<br />';
		                return array('run_status' => '1', 'messages' => $this->messages);
		            } 
		            else 
		            {
		                $this->messages[] = 'Something went horribly wrong and phRETSimg failed to finish.<br />';
		                return FALSE;
		            }
		        case $output_format = 'test': // TODO Build support for other output types.
		            $this->messages[] = 'Second output type would come from here!';
		            return FALSE;
            }

        } 
        elseif (empty($this->listing_ids)) 
        {
            $this->messages[] = 'No mls_listing_id numbers found for date range.';
            die('No mls_listing_id numbers found for date range.');
                    
        } 
        else 
        {
            $this->messages[] = 'The data type of the passed-in variable is unsupported.';
            die('The data type of the passed-in variable is unsupported.');
        }
    }
    
    /**
     * Get images, process with GD methods native to PHP, and save as JPG.
     * 
     * Loops through MLS numbers and generates images using GD, which provides 
     * the capability to manipulate images before writing to disc.
     *  
     * @param $listing_ids  array
     * @param $rets_handle  object
     * @param $img_path     string
     * 
     * @return bool
     * 
     * TODO Refactor this like whoa. Lots of duplication; pull out into another method or help function.
     *  
     */ 
    private function _processJPGs($listing_ids = '', $output_quality = '100') 
    {
        
            if (empty($listing_ids)) 
            {
                // TODO SUPER-IMPORTANT!!! This is a hack so we could get just new records right now but
                // really, really needs to reworked to handle an empty mls_listing_id param.
                $this->messages[] = 'No listing ids were passed into phRETSimg.';
                return TRUE; // TODO This is the offending part.
            } 
            else 
            {
                $rets = new phRetsModel($this->rets_url, $this->rets_user, $this->rets_pass, $this->rets_version, $this->rets_profile);
                $rets = $rets->connect();
                //$completed_count = 1; // TODO Get this working or get rid of it.

                // If single id (string or int), then this.
                if (!is_array($listing_ids)) 
           		{
            		$listing_id = $listing_ids; // Just correcting semantics of variable name.
                    
               		// Process single mls_listing_id.
                	$photos = $rets->GetObject("Property", "Photo", $listing_id); // TODO Fix this. (??? Not sure what needs fixing.)
                    $return = "MLS Listings Completed:<br/>"; // TODO Get working or get rid of this.
                    foreach ($photos as $photo) 
                    {
                    	if(!empty($photo['Content-ID']))
                    	{
                            $listing = $photo['Content-ID'];
                            $number = $photo['Object-ID'];
                            if (1 == $number) 
                            {
                                $filename = $this->img_path . $listing . ".jpg";
                            } 
                            else 
                            {
                                $filename = $this->img_path . "{$listing}_{$number}.jpg";
                            }

                            // Resize or adjust quality, if needed, and generate .jpg.
                            if ($photo['Success'] == true) 
                            { 
                                $jpg = imagecreatefromstring($photo['Data']);
                                if ( imagejpeg($jpg, $filename, $output_quality) ) 
                                {
                                    // Free up memory
                                    imagedestroy($jpg);
                                    $this->messages[] = "Image created: {$filename}."; // Just for debugging purposes.
                                } 
                            }
                            else 
                            {
                            	$this->createSymLink($filename);
                            }
                        }
                        else 
                        {
                    		$filename = $this->img_path . $listing_id . ".jpg";
                        	$this->createSymLink($filename);
                        }
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$filename.'<br/>'."\n"; // TODO Get working or get rid of this.
                    }         
                } 
                else
                {
                	$completed_count = 0;
                    // If array of multiple ids, then this.
                    foreach ($listing_ids as $listing_id) 
                    {
                        echo "MLS Listings Completed: " . $completed_count++ . ':<br/>'; // TODO Get working or get rid of this.
                        $photos = $rets->GetObject("Property", "Photo", $listing_id); 
                        foreach ($photos as $photo) 
                        {
	                    	if(!empty($photo['Content-ID']))
	                    	{
	                        	//echo '<pre>'.print_r($photo,true).'<pre>';
	                            $listing = $photo['Content-ID'];
	                            $number = $photo['Object-ID'];
	                            if (1 == $number) 
	                            {
	                                $filename = $this->img_path . $listing . ".jpg";
	                            } 
	                            else 
	                            {
	                                $filename = $this->img_path . "{$listing}_{$number}.jpg";
	                            }

	                            // Resize or adjust quality, if needed, and generate .jpg.
	                            if ($photo['Success'] == true) 
	                            { 
	                                $jpg = imagecreatefromstring($photo['Data']);
	                                if ( imagejpeg($jpg, $filename, $output_quality) ) 
	                                {
	                                    // Free up memory
	                                    imagedestroy($jpg);
	                                    $this->messages[] = "Image created: {$filename}."; // Just for debugging purposes.
	                                } 
	                                else 
	                                {
                            			$this->createSymLink($filename);
	                                }
	                            } 
                           	}
                            else 
                            {
                            	$this->createSymLink($filename);
                            }
                        	echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$filename.'<br/>'."\n"; // TODO Get working or get rid of this.
                        }
                    }
                }
            //$this->_logSuccess("Full update of image archive completely successfully."); // TODO Fix this busted logging.
            return TRUE;
            }
    }
    
    /**
     * Log error message.
     * 
     * Simple error logger.
     * 
     * @param string $error_msg Text of error message to log.
     * TODO Flesh this out.
     * TODO Setup switch to allow use of one method for multiple error types.
     * 
     */
    private function _logError($error_msg) 
    {
        include_once(GSPLUGINPATH.'mls_management/inc/KLogger.php' );
        $KLogger = new KLogger($this->error_log_path, 3);
        $KLogger->logError($error_msg);
    }

    public function createSymLink($filename)
    {
	    // Write entry to log file.
	    $this->_logError("Failed to create image: {$filename}. Symlink to placeholder created.");

	    // Create symbolic link to placeholder image.
	    symlink($this->img_path . 'placeholders/placeholder640x480.jpg', $filename);
	    $this->messages[] = "Failed to create image: {$filename}.";
    }
}