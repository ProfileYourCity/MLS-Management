<?php
ini_set("memory_limit","1812M");
set_time_limit(660);
ini_set("auto_detect_line_endings", true);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 1);
error_reporting(E_ALL);
# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 
	'MLS Data', 
	'1.0', 			
	'Mike Henken',	
	'http://michaelhenken.com/',
	'Manage MLS Databases', 
	'pages', 
	'mls_management_admin' 
);

add_action('settings-sidebar','createSideMenu',array($thisfile, 'MLS Management'));
require_once(GSPLUGINPATH.'mls_management/inc/common.php');

function mls_management_admin()
{
	?>
	<style type="text/css">
		.hidden_box {
			display:none;
			padding:10px;
			background-color:#f6f6f6;
			margin:10px;
		}
		.mls_span {
			color:inherit;
			font-size:inherit;
			line-height:inherit;
		}
		.mls_input {
			display:none;
			width:150px;
		}
		#maincontent {
			width:100% !important;
		}
		#sidebar {
			display:none;
		}
		table {
			width:100% !important;
		}
	</style>
	<div style="width:100%;margin:0 -15px -15px -10px;padding:0px;">
			<h3  class="floated">MLS Management</h3>
			<div class="edit-nav clearfix">
    			<p>
					<a href="load.php?id=mls_management&settings">Settings</a>
					<a href="load.php?id=mls_management&query">Query RETS Server</a>
					<a href="load.php?id=mls_management">Manage RETS Server</a>
				</p>
			</div>
		</div>
		</div>
		<div class="main" style="margin-top:-10px;">
	<?php
	displayAdminContent();
}

function displayAdminContent()
{
	$mlsData = new mlsData;

	//Handle additons, deletions, and edits to MLS(s)
	if(isset($_POST['add_mls']) || isset($_POST['edit_mls']))
	{
		//echo '<pre>'.print_r($_POST, true).'</pre>';
		$mls_post_array = array('mlsname' => $_POST['mls_name'],
								'retsurl' => $_POST['rets_url'],
								'retsuser' => $_POST['rets_user'],
								'retspass' => $_POST['rets_pass'],
								'mlsclass' => $_POST['mls_class'],
								'mlsdatabase' => $_POST['mls_database'],
								'mlsptypes' => $_POST['mls_property_types'],
								'retsoffset' => $_POST['rets_offset'],
								'retsprofile' => $_POST['rets_profile']);
		if(isset($_POST['edit_mls']))
		{
			$edit = $_POST['old_mls_name'];
		}
		else
		{
			$edit = false;
		}
		$addMLS = $mlsData->addMLS($mls_post_array,$edit);
	}
	elseif(isset($_GET['edit_columns']))
	{
		$mls = $mlsData->getAllMLS($_GET['edit_columns']);
		if(isset($_POST['mls_id']))
		{
			//echo 'Post: <pre>'.print_r($_POST, true).'</pre>';
			$count_tables = 0;
			$mls_types = explode(',', $mls['mlsptypes']);
			foreach($mls_types as $mls_type)
			{
				$columns[$count_tables] = array('name' => $mls_type);
				for ($count=0; isset($_POST[$mls_type .'_column_name_'.$count]); $count++) 
				{
					$columns[$count_tables]['columns'][$count]['column']['name'] = $_POST[$mls_type .'_column_name_'.$count];
					$columns[$count_tables]['columns'][$count]['column']['label'] = $_POST[$mls_type .'_column_label_'.$count];
					$columns[$count_tables]['columns'][$count]['column']['options'] = $_POST[$mls_type .'_column_options_'.$count];
					$columns[$count_tables]['columns'][$count]['column']['type'] = $_POST[$mls_type .'_column_type_'.$count];
					$columns[$count_tables]['columns'][$count]['column']['enabled'] = $_POST[$mls_type .'_column_enabled_'.$count];
				}
				$count_tables++;
			}
			//echo '<pre>'.print_r($columns, true).'</pre>';
			$addMLS = $mlsData->editColumns($_GET['edit_columns'], $columns);
		}
		edit_columns($_GET['edit_columns']);
	}
	elseif(isset($_GET['delete_mls']))
	{
		$deleteMLS = $mlsData->deleteMLS($_GET['delete_mls']);
	}
        
        // Trigger incremental update of image archive.
        elseif ( isset( $_GET['MLSimg_incrementalUpdate'] ) )
        {
            MLSimgIncrementalUpdate(urldecode($_GET['MLSimg_incrementalUpdate']));
        }

        elseif(isset($_GET['updateImgSingleProperty']))
        {
        	MLSimgSingleUpdate($_POST['mls_num'], $_POST['mls_name']);
        }
        
        // Trigger full (re)generation of image archive.
        elseif ( isset( $_GET['MLSimg_generateArchive'] ) )
        {
            MLSimgGenerateArchive(urldecode($_GET['MLSimg_generateArchive']));
        }
        
        // Display log-viewer page.
        elseif ( isset( $_GET['MLSimg_viewLogs'] ) )
        {
            MLSimgViewLogs();
        }
        
	//Display mangage mls screen
	elseif(isset($_GET['mange_mls']) || !isset($_GET['settings']) && !isset($_GET['query']) && !isset($_GET['view_mls']) && !isset($_GET['edit_mls']))
	{
		// Display MLS-specific settings and controls.
                manageRETS();
                
                // Display MLS image-specific controls.
                manageImages();
	}

	//Display edit specific mls screen
	elseif(isset($_GET['edit_mls']))
	{
		editMLS(urldecode($_GET['edit_mls']));
	}

	//Display settings screen
	elseif(isset($_GET['settings']))
	{
		manageRETSsettings();	
	}
        
}


function manageRETS()
{
	$mlsData = new mlsData;
	$all_mls = $mlsData->getAllMLS();
	?>
	<h3 class="floated">Current MLS(s) Available</h3>
	<div class="edit-nav clearfix" style="">
		<a href="#" class="add_mls_button">Add MLS</a>
	</div>
	<div class="add_mls hidden_box">
		<h3>Add MLS</h3>  
		<form method="post" accept-charset="utf-8">
			<p>
				<label>MLS Name (Accurate MLSID, ex: naples)</label>
				<input type="text" class="text" name="mls_name" value="" />
			</p>
			<p>
				<label>Rets Login URL (Full URL)</label>
				<input type="text" class="text" name="rets_url" value="" />
			</p>
			<p>
				<label>Rets Username</label>
				<input type="text" class="text" name="rets_user" value="" />
			</p>
			<p>
				<label>Rets Password</label>
				<input type="text" class="text" name="rets_pass" value="" />
			</p>
			<p>
				<label>MLS Class</label>
				<input type="text" class="text" name="mls_class" value="" />
			</p>
			<p>
				<input type="submit" name="add_mls" class="submit" value="Add MLS"/>
				&nbsp;&nbsp;OR&nbsp;&nbsp;
				<a href="load.php?id=mls_management&cancel" class="cancel">Cancel</a>
			</p>
		</form>		
		<div style="clear:both">&nbsp;</div>
	</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$('.add_mls_button').click(function() {
					$('.add_mls').show();
					$('.add_mls_button').hide();
				})
				$('.cancel').click(function() {
					$('.add_mls_button').show();
					$('.add_mls').hide();
				})
			})
		</script>
	<table class="highlight">
		<tr>
			<th>MLS Name</th>
			<th>MLS URL</th>
			<th>MLS Username</th>
			<th>MLS Password</th>
			<th>MLS Class</th>
			<th>Delete</th>
			<th>Sync</th>
			<th></th>
		</tr>
		<?php
		$mls_count = 0;
		foreach($all_mls as $mls)
		{
			?>
			<form action="" method="post">
				<tr>
					<td>
						<input class="mls<?php echo $mls_count; ?> mls_input" name="mls_name" value="<?php echo $mls['mlsname']; ?>" />
						<span class="mls<?php echo $mls_count; ?>" ONCLICK="showinput('mls<?php echo $mls_count; ?>')" class="mls_span"><?php echo $mls['mlsname']; ?></span>
					</td>
					<td>
						<input class="mls<?php echo $mls_count; ?> mls_input" name="rets_url" value="<?php echo $mls['retsurl']; ?>" />
						<span class="mls<?php echo $mls_count; ?>" ONCLICK="showinput('mls<?php echo $mls_count; ?>')" class="mls_span"><?php echo $mls['retsurl']; ?></span>
					</td>
					<td>
						<input class="mls<?php echo $mls_count; ?> mls_input" name="rets_user" value="<?php echo $mls['retsuser']; ?>" />
						<span class="mls<?php echo $mls_count; ?>" ONCLICK="showinput('mls<?php echo $mls_count; ?>')" class="mls_span"><?php echo $mls['retsuser']; ?></span>
					</td>
					<td>
						<input class="mls<?php echo $mls_count; ?> mls_input" name="rets_pass" value="<?php echo $mls['retspass']; ?>" />
						<span class="mls<?php echo $mls_count; ?>" ONCLICK="showinput('mls<?php echo $mls_count; ?>')" class="mls_span"><?php echo $mls['retspass']; ?></span>
					</td>
					<td>
						<input class="mls<?php echo $mls_count; ?> mls_input" name="mls_class" value="<?php echo $mls['mlsclass']; ?>" />
						<span class="mls<?php echo $mls_count; ?>" ONCLICK="showinput('mls<?php echo $mls_count; ?>')" class="mls_span"><?php echo $mls['mlsclass']; ?></span>
					</td>
					<td class="delete" >
						<a class="delconfirm" href="load.php?id=mls_management&delete_mls=<?php echo $mls['mlsname']; ?>" title="Delete MLS: <?php echo $mls['mlsname']; ?>" >X</a>
					</td>
					<td>
						<a href="load.php?id=mls_management&extract&rets&mls_name=<?php echo $mls['mlsname']; ?>&password=<?php echo $mlsData->getData('password'); ?>&offset=0" title="Delete MLS: <?php echo $mls['mlsname']; ?>" ><img src="../plugins/mls_management/images/sync_small.png" /></a>
					</td>
					<td>
						<input type="hidden" name="old_mls_name" value="<?php echo $mls['mlsname']; ?>" />
						<input type="hidden" name="mls_database" value="<?php echo $mls['mlsdatabase']; ?>" />
						<input type="hidden" name="mls_property_types" value="<?php echo $mls['mlsptypes']; ?>" />
						<input class="mls<?php echo $mls_count; ?> " style="display:none" name="edit_mls" type="submit" value="Edit MLS" />
						<a href="load.php?id=mls_management&edit_mls=<?php echo $mls['mlsname']; ?>">Edit More</a>
					</td>
				</tr>
			</form>
			<?php
			$mls_count++;
		}
		?>
			</table>
			<script type="text/javascript">
				function showinput(a_type){
					$('input.'+[a_type]).show();
					$('span.'+[a_type]).hide();
					$('a.'+[a_type]).hide();
				}
				function showdate(a_type){
					$('input#'+[a_type]).datepicker();
					$('span.'+[a_type]).hide();
					$('input#'+[a_type]).show();
				}
				$(function() {
					$( "#datepicker" ).datepicker();
				});
			</script>
		<?php
	//include GSPLUGINPATH.'mls_management/inc/rets.php';
}

function editMLS($mls)
{
	$mlsData = new mlsData;
	$mls = $mlsData->getAllMLS($mls);
	//echo '<pre>'.print_r($mls).'</pre>';
	?>
	<h3 class="floated">MLS Information</h3>
	<div class="edit-nav clearfix" style="">
		<a href="load.php?id=mls_management&edit_columns=<?php echo $_GET['edit_mls']; ?>">Edit Column Labels</a>
	</div>
	<form method="post" accept-charset="utf-8">
		<div class="leftsec">
			<p>
				<label>MLS Name:</label>
				<input type="text" class="text" name="mls_name" value="<?php echo $mls['mlsname']; ?>">
			</p>
			<p>
				<label>RETS Url:</label>
				<input type="text" class="text" name="rets_url" value="<?php echo $mls['retsurl']; ?>">
			</p>
			<p>
				<label>RETS Username:</label>
				<input type="text" class="text" name="rets_user" value="<?php echo $mls['retsuser']; ?>">
			</p>		
			<p>
				<label>Rets Password:</label>
				<input type="text" class="text" name="rets_pass" value="<?php echo $mls['retspass']; ?>">
			</p>	
			<p>
				<label>Rets Profile (if custom):</label>
				<input type="text" class="text" name="rets_profile" value="<?php echo $mls['retsprofile']; ?>">
			</p>
		</div>
		<div class="leftsec">
			<p>
				<label>MLS Extended Class:</label>
				<input type="text" class="text" name="mls_class" value="<?php echo $mls['mlsclass']; ?>">
			</p>
			<p>
				<label>MLS Database Name:</label>
				<input type="text" class="text" name="mls_database" value="<?php echo $mls['mlsdatabase']; ?>">
			</p>
			<p>
				<label>Property Types (comma seperated):</label>
				<input type="text" class="text" name="mls_property_types" value="<?php echo $mls['mlsptypes']; ?>">
			</p>
			<p>
				<label>Rets Offset: </label>
				<input type="text" class="text" name="rets_offset" value="<?php echo $mls['retsoffset']; ?>">
			</p>
			<p>
				<label>Possible Views: </label>
				<input type="text" class="text" name="rets_offset" value="<?php echo $mls['retsoffset']; ?>">
			</p>
			<p>
				<label>Possible Boat Access: </label>
				<input type="text" class="text" name="rets_offset" value="<?php echo $mls['retsoffset']; ?>">
			</p>
			<p>
				<label>Possible Waterfront Descriptions: </label>
				<input type="text" class="text" name="rets_offset" value="<?php echo $mls['retsoffset']; ?>">
			</p>
			<p>
				<input type="hidden" name="old_mls_name" value="<?php echo $mls['mlsname']; ?>" />
				<input type="submit" class="submit" name="edit_mls" value="Edit MLS">
			</p>
		</div>
		<div style="clear:both"></div>
	</form>
	<?php
}

function edit_columns($mls_id)
{
	$mlsData = new mlsData;
	$columns = $mlsData->getMLSColumns($mls_id);
	?>
	<h3>Labels</h3>
	<form method="post" accept-charset="utf-8">
		<?php 
		foreach($columns as $table) 
		{ 
			 //echo 'Table ARray <pre>'.print_r($table, true).'</pre>';
			?>
			<span style="font-size:16px;font-weight:bold;"><?php echo $table['name']; ?></span>	<br/>
			<table>
				<tr>
					<th>Column Name</th>
					<th>Label</th>
					<th>Options</th>
					<th>Type</th>
					<th>Enabled</th>
				</tr>
				<?php
				$count = 0;
				foreach($table['columns'] as $column)
				{
					?>
					<tr>
						<td><?php echo $column['name']; ?><input type="hidden" name="<?php echo $table['name']; ?>_column_name_<?php echo $count; ?>" value="<?php echo $column['name']; ?>" /></td>
						<td>
							<input type="text" class="text" name="<?php echo $table['name']; ?>_column_label_<?php echo $count; ?>" value="<?php echo $column['label']; ?>" style="width:170px;" />
						</td>
						<td>
							<input type="text" class="text" name="<?php echo $table['name']; ?>_column_options_<?php echo $count; ?>" value="<?php echo $column['options']; ?>" style="width:170px;" />
						</td>
						<td>
							<select class="text" name="<?php echo $table['name']; ?>_column_type_<?php echo $count; ?>" style="width:190px;">
								<?php
								$types = array('text', 'sortext', 'select', 'shortselect', 'multiselect', 'radio', 'checkbox', 'min-to-max');
								foreach($types as $type)
								{
									$selected = '';
									if($type == $column['type'])
									{
										$selected = 'selected';
									}
									echo '<option value="'.$type.'" '.$selected.'>'.$type.'</option>';
								}
								?>
							</select>
						</td>
						<td>
							<select class="text" name="<?php echo $table['name']; ?>_column_enabled_<?php echo $count; ?>" style="width:90px;">
								<?php
								$types = array('yes', 'no');
								foreach($types as $type)
								{
									$selected = '';
									if($type == $column['enabled'])
									{
										$selected = 'selected';
									}
									echo '<option value="'.$type.'" '.$selected.'>'.$type.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<?php
					$count++;
				}
				?>
			</table>
			<?php
		}
		?>
		<p>
			<input type="hidden" name="mls_id" value="<?php echo $_GET['edit_columns']; ?>" />
			<input type="submit" value="submit" class="submit" />
		</p>
	</form>
	<?php
}

/**
 * Displays admin panel with settings and actions for managing MLS-derived images. 
 */
function manageImages()
{
        $mlsData = new mlsData;
	$all_mls = $mlsData->getAllMLS();
	?>
	<h3 class="floated">MLS Image Actions</h3>
    <div class="edit-nav">
    	<a href="#" class="updatePropImg">Update Single Property</a>
    </div>
		<div style="clear:both">&nbsp;</div>
    <div class="update_prop_img hidden_box" style="width:600px;padding-top:25px;">
		<form action="load.php?id=mls_management&updateImgSingleProperty" method="post" accept-charset="utf-8">
			<div class="leftsec" style="width:160px;">
				<p>
					<label>MLS Number:</label>
					<input type="text" class="text" name="mls_num">
				</p>
			</div>
			<div class="leftsec" style="width:120px;margin-left:25px;">
				<p>
					<label>MLS Name:</label>
					<select class="text" name="mls_name">
					<?php
					foreach($all_mls as $mls)
					{
						echo '<option value="'.$mls['mlsname'].'">'.$mls['mlsname'].'</option>';
					}
					?>				
					</select>
				</p>
			</div>
			<div class="leftsec" style="width:200px;margin-left:25px;">
				<p>
					<br/>
					<input type="submit" class="submit" value="Update Property Image">
				</p>
			</div>
			<div style="clear:both"></div>
		</form>
    </div>
		<div style="clear:both">&nbsp;</div>
        <table class="highlight">
		<tr>
			<th>MLS NAME</th>
			<th>ACTIONS AVAILABLE</th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
		<?php
        $mls_count = 0;
		foreach($all_mls as $mls)
		{
			?>
            <tr>
                <td>
                        <?php echo $mls['mlsname']; ?>
                </td>
                <td>
                    <a href="load.php?id=mls_management&MLSimg_incrementalUpdate=<?php echo $mls['mlsname']; ?>" class="">Incremental Update</a>
                </td>
                <td>
                    <a href="load.php?id=mls_management&MLSimg_generateArchive=<?php echo $mls['mlsname']; ?>" class="">Generate Archive</a>
                </td>
                <td>
                    <a href="load.php?id=mls_management&MLSimg_viewLogs" class="">View Logs</a>
                </td>
                <td>
                </td>
                <td>
                        <!-- <input type="hidden" name="old_mls_name" value="<?php // echo $mls['mlsname']; ?>" />
                        <input type="hidden" name="mls_database" value="<?php // echo $mls['mlsdatabase']; ?>" />
                        <input class="mls<?php // echo $mls_count; ?> " style="display:none" name="edit_mls" type="submit" value="Edit MLS" />
                        <a href="load.php?id=MLSdata&edit_mls=<?php // echo $mls['mlsname']; ?>">Edit More</a> -->
                </td>
            </tr>
			<?php
		}
		?> 
		</table> 
		<script type="text/javascript">
			$(".updatePropImg").click(function(){
				$('.update_prop_img').show();
				$('.updatePropImg').hide();
			});
		</script>
	<?php
}

function MLSimgViewLogs() 
{
        $mlsData = new mlsData();
        
        $error_log_path = GSROOTPATH . $mlsData->getData('error_log_path');
        
        // Get log files and assemble into array (daily logs are created if anything is logged).
        if ( $DH = opendir($error_log_path) ) {
            
                while (FALSE !== ($entry = readdir($DH) ) )
                {
                        if ( $entry != "." && $entry != ".." )
                        {
                                $error_logs[] = file_get_contents($error_log_path . $entry);
                        }
                }
                closedir($DH);
                
                foreach ($error_logs as $key => $value) {
                    
                    ?>
        
                        <div id="error-log-<?php echo $key ?>" class="log">
                            
                            <?php echo $value ?>

                        </div>
        
                    <?php
                    
                }
                
        } else {
            echo 'Failed to open error logs or directory.';
        }
        
        
        
       
}

function manageRETSsettings()
{
	$mlsData = new mlsData;
	if(isset($_POST['submit_mls_settings']) || isset($_POST['submit_database_settings']))
	{
                $post_data = array('dbhost' => $_POST['db_host'],
                                    'dbuser' => $_POST['db_user'],
                                    'dbpass' => $_POST['db_pass'],
                                  'password' => $_POST['password'],
                        'image_archive_path' => $_POST['image_archive_path'],
                         'placeholder_image' => $_POST['placeholder_image'],
                         'activity_log_path' => $_POST['activity_log_path'],
                            'error_log_path' => $_POST['error_log_path'],
                       'phretsimg_mem_limit' => $_POST['phretsimg_mem_limit'],
             'mlsImg_increm_update_days_ago' => $_POST['mlsImg_increm_update_days_ago'],
                                    );
            
		$saveSettingsFile = $mlsData->processSettings($post_data);
		if($saveSettingsFile == true)
		{
			echo '<div class="updated">Settings Successfully Saved</div>';
		}
		else
		{
			echo '<div class="updated">Settings Could Not Be Saved!!</div>';
		}
	}
	?>
	<style>
		h4 {
			width:918px !important;
			font-size: 16px;
			font-weight: normal;
			font-family: Georgia, Times, Times New Roman, serif;
			color: #CF3805;
			margin-bottom:10px !important;
		}
	</style>
	<form action="load.php?id=mls_management&settings" method="post">
                <h4>Database Settings</h4>
                    <p>
                            <label>Host</label>
                            <input type="text" class="text" name="db_host" value="<?php $mlsData->getData('dbhost', true); ?>" />
                    </p>
                    <p>
                            <label>Database User</label>
                            <input type="text" class="text" name="db_user" value="<?php $mlsData->getData('dbuser', true); ?>" />
                    </p>
                    <p>
                            <label>Database Password</label>
                            <input type="text" class="text" name="db_pass" value="<?php $mlsData->getData('dbpass', true); ?>" />
                    </p>
                    <p>
                            <label>RETS Download Password</label>
                            <input type="text" class="text" name="password" value="<?php $mlsData->getData('password', true); ?>" />
                    </p>
        
                <h4>Image Storage & Processing Settings</h4>
                    <p>
                            <label>Image Archive Path</label>
                            <input type="text" class="text" name="image_archive_path" value="<?php GSROOTPATH . $mlsData->getData('image_archive_path', true); ?>" />
                    </p>
                    <p>
                            <label>Placeholder Image</label>
                            <input type="text" class="text" name="placeholder_image" value="<?php GSROOTPATH . $mlsData->getData('placeholder_image', true); ?>" />
                    </p>
                    <p>
                            <label>Activity Log Path</label>
                            <input type="text" class="text" name="activity_log_path" value="<?php GSROOTPATH . $mlsData->getData('activity_log_path', true); ?>" />    
                    </p>
                    <p>
                            <label>Error Log Path</label>
                            <input type="text" class="text" name="error_log_path" value="<?php GSROOTPATH . $mlsData->getData('error_log_path', true); ?>" />    
                    </p>
                    <p>
                            <label>PHP Memory Limit During Processing of Images</label>
                            <input type="text" class="text" name="phretsimg_mem_limit" value="<?php $mlsData->getData('phretsimg_mem_limit', true); ?>" />
                    </p>
                    <p>
                            <label>Incremental Update Frequency (in Days)</label>
                            <input type="text" class="text" name="mlsImg_increm_update_days_ago" value="<?php $mlsData->getData('mlsImg_increm_update_days_ago', true); ?>" />
                    <p>
                        <input type="submit" class="submit" name="submit_database_settings" value="Submit" />
                    </p>
	</form>
		<script type="text/javascript">
			$(document).ready(function() {
				$('.add_mls_button').click(function() {
					$('.add_mls').show();
					$('.add_mls_button').hide();
				})
			})
		</script>
	<?php
}



/** 
* This conditional is used to download the mls data and update the database. It needs to be replaced with a more secure method. For now it exists so the cronjob can update the database every day.
**/
if(empty($_SERVER['argv'][1]))
{
	if(!empty($_SERVER['argv'][0]))
	parse_str($_SERVER['argv'][0], $GET);
}
else
{
	$gets = '';
	$count = 0;
	foreach($_SERVER['argv'] as $arguement)
	{
		if($count != 0)
		{
			$gets .= '&'.$arguement;
		}
		$count++;
	}
	parse_str($gets, $GET);
}
if(isset($GET['extract']))
{
	$ExtractData = new extractData;
	$mlsData = new mlsData;
	$AllMLS = $mlsData->getAllMLS();
	if(isset($GET['rets']) && isset($GET['password']) && urldecode($GET['password']) == $mlsData->getData('password'))
	{
		$mls = $mlsData->getAllMLS($GET['mls_name']);
		echo '<pre>'.print_r($mls, true).'</pre>';
		$ptypes = explode(',', $mls['mlsptypes']);
		$extract = array();

		$mlsRetsClass = initiateClass($mls);
		if(!isset($GET['cron']))
		{
			echo 'Cron not Set';
			if(!isset($GET['offset']) || $GET['offset'] == 0)
			{
				$offset = 0;
			}
			else
			{
				$offset = $GET['offset'];
			}
			foreach($ptypes as $ptype)
			{
				$extract[] = $mlsRetsClass->getRetsData($mls['retsurl'], $mls['retsuser'], $mls['retspass'], $mls['retsoffset'], $offset, $ptype);
			}
			if(in_array('1', $extract))
			{
				$offset = $offset + $mls['retsoffset'];
				echo '<META HTTP-EQUIV="refresh" CONTENT="2;URL=http://www.profileidx.com/index.php?mls_name='.$_GET['mls_name'].'&extract&rets&password='.$_GET['password'].'&offset='.$offset.'">';
			}
			else
			{
				echo '<h1>Update Complete</h1>';
			}
		}
		else
		{
			foreach($ptypes as $ptype)
			{
				$offset = 0;
				$extract = true;
				while($extract != false)
				{
					$extract = $mlsRetsClass->getRetsData($mls['retsurl'], $mls['retsuser'], $mls['retspass'], $mls['retsoffset'], $offset, $ptype);
					$offset = $offset + 1000;
				}
				if($extract == false)
				{
					echo 'PTYPE: '.$ptype.' Is Finished';
				}
			}
		}
	}
}

elseif(isset($GET['updateImgProperties']))
{
	$mlsData = new mlsData;
	echo 'Update Images Is Set';
	if(isset($GET['password']) && urldecode($GET['password']) == $mlsData->getData('password'))
	{
		echo 'Password Matches';
		MLSimgIncrementalUpdate();
	}
}

elseif(isset($GET['MLSimg_generateArchive']))
{
	MLSimgGenerateArchive(urldecode($GET['MLSimg_generateArchive']));
}
