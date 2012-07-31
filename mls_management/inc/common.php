<?php

/**
 * Pull in GetSimple's common.php for access to constants.
 * 
 * TODO Document this. 
 */
$path = dirname(__FILE__).'/../../../';
$rootPath =  realpath($path);

if(!function_exists('getXML'))
{
	require_once($rootPath.'/admin/inc/common.php');
}

if(!isset($thisfile)) { $plugin_file = GSPLUGINPATH.'MLSdata.php'; } else { $plugin_file =  $thisfile; }

define('MLSFILE', $plugin_file);
define('MLSMANAGEMENTFILE', GSDATAOTHERPATH.'mls_management.xml');
define('MLSFOLDER', GSDATAPATH.'mls/');
define('UPDATELOGPATH', GSDATAPATH  . 'logs/mlsPropertyUpdate.txt');
define('MLSCOLUMNSFOLDER', GSDATAPATH.'mls/columns/');

require_once(GSPLUGINPATH.'mls_management/class/mlsData.php');
require_once(GSPLUGINPATH.'mls_management/class/extractData.php');
require_once(GSPLUGINPATH.'mls_management/class/mlsImg.php');
require_once(GSPLUGINPATH.'mls_management/class/phRetsModel.php');
require_once(GSPLUGINPATH.'mls_management/class/phRets.php');

function initiateClass($mls)
{	
	require_once(GSPLUGINPATH.'mls_management/class/mls/'.$mls['mlsclass'].'.php');
    $initiateClass = '$mlsRetsClass = new ' . $mls['mlsclass'] . '("'.$mls['mlsdatabase'].'");'; 
    $mlsRetsClass = ''; 
    eval($initiateClass); 
    return $mlsRetsClass;
}

/**
 * Incrementally updates image archive by numbers of days into the past.
 * 
 * @return mixed
 */
function MLSimgIncrementalUpdate() 
{
    require_once(GSPLUGINPATH.'mls_management/class/mlsImgModel.php');
    
    // TODO Modify this to work with multiple passed-in MLS's? Use hidden field on form to specify which MLS entry in actions form it came from?
    $mlsData = new mlsData();
    $mls = $mlsData->getAllMLS();
	echo '<pre>'.print_r($mls, true).'</pre>';
	$db_host = $mlsData->getData('dbhost');
    $db_user = $mlsData->getData('dbuser');
    $db_pass = $mlsData->getData('dbpass');
    foreach($mls as $ind_mls)
    {
	    $db_name =$ind_mls['mlsdatabase'];
	    $prop_types = explode(",", $ind_mls['mlsptypes']);
	    foreach($prop_types as $prop_type)
	    {
	        // Update this to extended model once base model is done.
	        $mlsImgModel = new MLSimgModel($db_host, $db_user, $db_pass, $db_name, $prop_type);
	        $listing_ids = $mlsImgModel->getMlsListingIdsIncrementalByDaysAgo($mlsData->getData('mlsImg_increm_update_days_ago')); // TODO Add setting for this.
	        //$listing_ids = $mlsImgModel->getMlsListingIdsIncrementalByDaysAgo(4);
			echo '<pre>'.print_r($listing_ids, true).'</pre>';
	        $mlsImg = new MLSimg($listing_ids, $ind_mls);
	        $data = $mlsImg->generateImagesByID();
	        //return $data;
	    }
    }
}

function MLSimgSingleUpdate($mls_listing_id, $mls_name)
{
    $mlsData = new mlsData();
    $mls = $mlsData->getAllMLS($mls_name);
    //print_r($mls);
    $mlsImg = new MLSimg($mls_listing_id, $mls);
    $data = $mlsImg->generateImagesByID();
    if($data != false)
    {
    	echo '<div class="updated">Property Images Updated</div>';
    }
}

/**
 * (Re)Generates the entire image archive via MLSimg using all mls_listing_ids in the database. 
 * 
 * @return mixed 
 */
function MLSimgGenerateArchive($mls) 
{
    require_once(GSPLUGINPATH.'mls_management/class/mlsImgModel.php');
    $mlsData = new mlsData();
    $mls = $mlsData->getAllMLS($mls);
	echo '<pre>'.print_r($mls, true).'</pre>';
	    $db_host = $mlsData->getData('dbhost');
    $db_user = $mlsData->getData('dbuser');
    $db_pass = $mlsData->getData('dbpass');
    $db_name = $mls['mlsdatabase'];
    $prop_types = explode(",", $mls['mlsptypes']);
    foreach($prop_types as $prop_type)
    {
        //TODO Update this to extended model once base model is done.
        $mlsImgModel = new MLSimgModel($db_host, $db_user, $db_pass, $db_name, $prop_type);
        $listing_ids = $mlsImgModel->getAllMlsListingIds();
        //echo '<pre>'.print_r($listing_ids,true).'</pre>';
        $mlsImg = new MLSimg($listing_ids, $mls);
        $data = $mlsImg->generateImagesByID();
        return $data;
	}
}
