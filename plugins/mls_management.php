<?php
ini_set("memory_limit","1812M");
# get correct id for plugin
$thisfile=basename(__FILE__, ".php");
require_once('mls_management/inc/common.php');

# register plugin
register_plugin(
	$thisfile, 
	MLSPLUGINNAME, 
	'2.0', 			
	'Mike Henken',	
	'http://profileyourcity.com', 
	'MLS Management', 
	'settings', 
	'mls_management_admin' 
);

/** 
* Handle admin area pages and conditionals
*
* @return void
*/
function mls_management_admin()
{
	//Display Admin Navigation
	mls_management_admin_navigation();

	//Get current action - If action is not set, action is manage
    $action = isset($_GET['action']) ? $_GET['action'] : 'manage';

    if($action == 'edit_mls')
    {
    	//Process Edit MLS
		if(isset($_POST['edit_mls']))
		{
			saveEditMLS();
		}
		//Edit MLS Columns
    	elseif(isset($_GET['edit_columns']))
		{
			if(isset($_POST['mls_id']))
			{
				save_edit_columns();
			}
			edit_columns($_GET['edit_columns']);
		}
		//Delete MLS
		elseif(isset($_GET['delete_mls']))
		{
			$deleteMLS = $mlsManagement->deleteMLS($_GET['delete_mls']);
		} 
		//Edit MLS Area
		editMLS(urldecode($_GET['mls_code']));
	}
    elseif($action == 'settings')
    {
    	//Manage Settings Area
		manageRETSsettings();	
    }
    else
    {
    	//Edit MLS
		if(isset($_POST['add_mls']) || isset($_POST['edit_mls']))
		{
			saveEditMLS();
		}
    	//Update/Insert single property in database
		if(isset($_POST['update_single_property']))
		{
			update_single_property($_POST['mls_num'], $_POST['mls_code'], $_POST['p_type']);
		}
    	//Download image for single property
	    elseif(isset($_GET['updateImgSingleProperty']))
	    {
	    	update_single_property_image($_POST['mls_num'], $_POST['mls_code'], $_POST['mls_table']);
	    }

		// Display MLS-specific settings and controls.
        manageRETS();
        
        // Display MLS image-specific controls.
        manageImages();
    }
	
	exec_action('mls_management-controller');
}

function frontend_controller()
{
	global $GET;
	$mlsManagement = new mlsManagement;
    $action = isset($GET['action']) ? $GET['action'] : '';
    if(isset($GET['password']) && urldecode($GET['password']) == $mlsManagement->getData('password'))
    {
	    if($action == 'updateProperties')
	    {
	    	if(isset($GET['mls_code']))
	    	{
	    		$offset = isset($GET['offset']) ? $GET['offset'] : 0;
	    		$cron = isset($GET['cron']) ? true : false;
	    		updateAllProperties($GET['mls_code'], $GET['offset'], $GET['password'], $cron);
	    	}
	    	else
	    	{
	    		echo 'You Must Supply The MLS Code';
	    	}
	    }
	    elseif($action == 'downloadImages')
	    {
			$mlsManagement = new mlsManagement;
			echo "Preparing to download images\n";
			if(isset($GET['password']) && urldecode($GET['password']) == $mlsManagement->getData('password'))
			{
				$imageDownloader = new imageDownloader;

				$offset = (isset($GET['offset'])) ? $GET['offset'] : null;
				$limit = (isset($GET['limit'])) ? $GET['limit'] : null;
				$cron = (isset($GET['cron'])) ? $GET['cron'] : null;

				downloadAllImages($GET['mls_code'], $GET['table'], $limit, $offset, $cron, $GET['password']);
			}
	    }
	}
    else
    {
    	die('You Must Supply Password');
    }
}
if(isset($GET['mls_management']))
{
	frontend_controller();
}