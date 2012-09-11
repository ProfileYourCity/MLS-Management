<?php

function manageRETS()
{
	$mlsManagement = new mlsManagement;
	$all_mls = $mlsManagement->getAllMLS();
	?>
	<h3 class="floated">Current MLS(s) Available</h3>
	<div class="edit-nav clearfix" style="">
		<a href="#" class="add_mls_button">Add MLS</a>
    	<a href="#" class="updateProp">Update Single Property</a>
	</div>
	<?php 
		//Include ADD MLS Form
		echo addMLS(); 
	?>
	<?php 
		//Include Update MLS Property Form
		echo updateSinglePropertyDataForm($all_mls); 
	?>
	<div style="clear:both">&nbsp;</div>
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
						<a href="load.php?id=mls_management&extract&rets&mls_name=<?php echo $mls['mlsname']; ?>&password=<?php echo $mlsManagement->getData('password'); ?>&offset=0" title="Delete MLS: <?php echo $mls['mlsname']; ?>" ><img src="../plugins/mls_management/images/sync_small.png" /></a>
					</td>
					<td>
						<input type="hidden" name="old_mls_name" value="<?php echo $mls['mlscode']; ?>" />
						<input type="hidden" name="mls_database" value="<?php echo $mls['mlsdatabase']; ?>" />
						<input type="hidden" name="mls_property_types" value="<?php echo $mls['mlsptypes']; ?>" />
						<input class="mls<?php echo $mls_count; ?> " style="display:none" name="edit_mls" type="submit" value="Edit MLS" />
						<a href="load.php?id=mls_management&action=edit_mls&mls_code=<?php echo $mls['mlscode']; ?>">Edit More</a>
					</td>
				</tr>
			</form>
			<?php
			$mls_count++;
		}
		echo '</table>';
}

function addMLS()
{
	?>
	<div class="add_mls hidden_box">
		<h3>Add MLS</h3>  
		<form method="post" accept-charset="utf-8">
			<p>
				<label>MLS Name (Accurate MLSID, ex: naples)</label>
				<input type="text" class="text" name="mls_name" value="" />
			</p>
			<p>
				<label>MLS Code (3 digit code assigned to MLS): </label>
				<input type="text" class="text" name="mls_code" value="" />
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
				<a href="load.php?id=<?php echo MLSPLUGINID; ?>&cancel" class="cancel">Cancel</a>
			</p>
		</form>		
		<div style="clear:both">&nbsp;</div>
	</div>
	<?php
}

function updateSinglePropertyDataForm($all_mls)
{
	?>
    <div class="update_prop hidden_box" style="width:600px;padding-top:25px;">
		<form action="load.php?id=mls_management" method="post" accept-charset="utf-8">
			<div class="leftsec" style="width:160px;">
				<p>
					<label>MLS Number:</label>
					<input type="text" class="text" name="mls_num">
				</p>
			</div>
			<div class="leftsec" style="width:120px;margin-left:25px;">
				<p>
					<label>Property Type:</label>
					<select class="text" name="p_type">
						<option value="sfr">SFR</option>		
						<option value="lots">Lots</option>		
					</select>
				</p>
			</div>
			<div class="leftsec" style="width:120px;margin-left:25px;">
				<p>
					<label>MLS Name:</label>
					<select class="text" name="mls_code">
					<?php
					foreach($all_mls as $mls)
					{
						echo '<option value="'.$mls['mlscode'].'">'.$mls['mlsname'].'</option>';
					}
					?>				
					</select>
				</p>
			</div>
			<div class="leftsec" style="width:200px;margin-left:25px;">
				<p>
					<br/>
					<input type="submit" class="submit" name="update_single_property" value="Update Property">
				</p>
			</div>
			<div style="clear:both"></div>
		</form>
    </div>
    <?php
}

function editMLS($mls)
{
	$mlsManagement = new mlsManagement;
	$mls = $mlsManagement->getAllMLS($mls);
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
				<label>MLS Name:</label>
				<input type="text" class="text" name="mls_code" value="<?php echo $mls['mlscode']; ?>">
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
				<input type="hidden" name="old_mls_name" value="<?php echo $mls['mlscode']; ?>" />
				<input type="submit" class="submit" name="edit_mls" value="Edit MLS">
			</p>
		</div>
		<div style="clear:both"></div>
	</form>
	<?php
}

function edit_columns($mls_id)
{
	$mlsManagement = new mlsManagement;
	$columns = $mlsManagement->getMLSColumns($mls_id);
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
							<select class="text" name="<?php echo $table['name']; ?>_column_type_<?php echo $count; ?>" style="width:110px !important;">
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
							<select class="text" name="<?php echo $table['name']; ?>_column_enabled_<?php echo $count; ?>" style="width:55px !important;">
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
	</div>
	<div class="main" style="margin-top:-10px;">
	<?php
}


/**
 * Displays admin panel with settings and actions for managing MLS-derived images. 
 */
function manageImages()
{
    $mlsManagement = new mlsManagement;
	$all_mls = $mlsManagement->getAllMLS();
	?>
	</div>
	<div class="main" style="margin-top:-10px;">
	<h3 class="floated">MLS Image Actions</h3>
    <div class="edit-nav">
    	<a href="#" class="updatePropImg">Update Single Property</a>
    </div>
	<div style="clear:both">&nbsp;</div>
	<?php 
		//Include Update MLS Property Images Form
		echo updateSinglePropertyImagesForm($all_mls); 
	?>
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
	<?php
}



function manageRETSsettings()
{
	$mlsManagement = new mlsManagement;
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
            
		$saveSettingsFile = $mlsManagement->processSettings($post_data);
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
	<form action="load.php?id=<?php echo MLSPLUGINID; ?>&action=settings" method="post" class="mls_settings">
        <h4>Database Settings</h4>
            <p>
                    <label>Host</label>
                    <input type="text" class="text" name="db_host" value="<?php $mlsManagement->getData('dbhost', true); ?>" />
            </p>
            <p>
                    <label>Database User</label>
                    <input type="text" class="text" name="db_user" value="<?php $mlsManagement->getData('dbuser', true); ?>" />
            </p>
            <p>
                    <label>Database Password</label>
                    <input type="text" class="text" name="db_pass" value="<?php $mlsManagement->getData('dbpass', true); ?>" />
            </p>
            <p>
                    <label>RETS Download Password</label>
                    <input type="text" class="text" name="password" value="<?php $mlsManagement->getData('password', true); ?>" />
            </p>

        <h4>Image Storage & Processing Settings</h4>
            <p>
                    <label>Image Archive Path</label>
                    <input type="text" class="text" name="image_archive_path" value="<?php GSROOTPATH . $mlsManagement->getData('image_archive_path', true); ?>" />
            </p>
            <p>
                    <label>Placeholder Image</label>
                    <input type="text" class="text" name="placeholder_image" value="<?php GSROOTPATH . $mlsManagement->getData('placeholder_image', true); ?>" />
            </p>
            <p>
                    <label>Activity Log Path</label>
                    <input type="text" class="text" name="activity_log_path" value="<?php GSROOTPATH . $mlsManagement->getData('activity_log_path', true); ?>" />    
            </p>
            <p>
                    <label>Error Log Path</label>
                    <input type="text" class="text" name="error_log_path" value="<?php GSROOTPATH . $mlsManagement->getData('error_log_path', true); ?>" />    
            </p>
            <p>
                    <label>PHP Memory Limit During Processing of Images</label>
                    <input type="text" class="text" name="phretsimg_mem_limit" value="<?php $mlsManagement->getData('phretsimg_mem_limit', true); ?>" />
            </p>
            <p>
                    <label>Incremental Update Frequency (in Days)</label>
                    <input type="text" class="text" name="mlsImg_increm_update_days_ago" value="<?php $mlsManagement->getData('mlsImg_increm_update_days_ago', true); ?>" />
            <p>
                <input type="submit" class="submit" name="submit_database_settings" value="Submit" />
            </p>
	</form>
	<?php
}

function updateSinglePropertyImagesForm($all_mls)
{
	?>
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
					<label>Property Type:</label>
					<select class="text" name="mls_table">
						<option value="sfr">SFR</option>		
						<option value="lots">Lots</option>		
					</select>
				</p>
			</div>
			<div class="leftsec" style="width:120px;margin-left:25px;">
				<p>
					<label>MLS Name:</label>
					<select class="text" name="mls_code">
					<?php
					foreach($all_mls as $mls)
					{
						echo '<option value="'.$mls['mlscode'].'">'.$mls['mlsname'].'</option>';
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
    <?php
}

function saveEditMLS()
{
	$mlsManagement = new mlsManagement;
	//echo '<pre>'.print_r($_POST, true).'</pre>';
	$mls_post_array = array('mlsname' => $_POST['mls_name'],
							'mlscode' => $_POST['mls_code'],
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
	$addMLS = $mlsManagement->addMLS($mls_post_array,$edit);
}

function save_edit_columns()
{
	$mlsManagement = new mlsManagement;
	//echo 'Post: <pre>'.print_r($_POST, true).'</pre>';
	$count_tables = 0;
	$mls = $mlsManagement->getAllMLS($_GET['edit_columns']);
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
	$addMLS = $mlsManagement->editColumns($_GET['edit_columns'], $columns);
}

function update_single_property($mls_num, $mls_id, $table)
{
	$mlsManagement = new mlsManagement;
	$extractData = new extractData;
	$mls = $mlsManagement->getAllMLS($mls_id);
	$mlsRetsClass = initiateClass($mls);
	$updateProperty = $mlsRetsClass->getRetsData($mls['retsurl'], $mls['retsuser'], $mls['retspass'], $mls['retsoffset'], 0, $table, $mls_num);
	if($updateProperty)
	{
		echo '<div class="updated">Property Updated</div>';
	}
	else
	{
		echo '<div class="error">Property NOT Updated</div>';
	}
}

function update_single_property_image($mls_num, $mls_id, $table)
{
	$mlsManagement = new mlsManagement;
	$imageDownloader = new imageDownloader;
	$mlsInfo = $mlsManagement->getAllMLS($mls_id);
	$listing_id = $mls_num;

	$mlsManagement->dbh = $mlsManagement->connectDB($mlsManagement->getData("dbhost"), $mlsInfo['mlsdatabase'], $mlsManagement->getData("dbuser"), $mlsManagement->getData("dbpass"));
	$sql = "SELECT mls_listing_id, listing_rid FROM {$table} WHERE `mls_listing_id` = {$mls_num}";
	$stmt = $mlsManagement->dbh->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetch();
	$listing_ids = array(0 => array('mls_listing_id' => $results['mls_listing_id'], 'listing_rid' => $results['listing_rid']));

	$images = $imageDownloader->getImageData($mlsInfo,$listing_ids);
	foreach($images as $image)
	{
		foreach($image as $ind_image)
		{
			$imageResult[] = $imageDownloader->saveImageData($ind_image, $mls_id, $table, $listing_id);
		}
	}
	if($imageResult != false)
	{
		echo '<div class="updated"><strong>Property Images Updated</strong><br/>';
		foreach($imageResult as $image)
		{
			echo $image.' created'.'<br/>';
		}
		echo '</div>';
	}
}

function updateAllProperties($mls_code, $offset, $password, $cron=false, $mls_num=null)
{
	$ExtractData = new extractData;
	$mlsManagement = new mlsManagement;
	$AllMLS = $mlsManagement->getAllMLS();
	$mls = $mlsManagement->getAllMLS($mls_code);
	echo '<pre>'.print_r($mls, true).'</pre>';
	$ptypes = explode(',', $mls['mlsptypes']);
	$extract = array();

	$mlsRetsClass = initiateClass($mls);
	if($cron == false)
	{
		foreach($ptypes as $ptype)
		{
			$extract[] = $mlsRetsClass->getRetsData($mls['retsurl'], $mls['retsuser'], $mls['retspass'], $mls['retsoffset'], $offset, $ptype, $mls_num);
		}
		if(in_array('1', $extract))
		{
			$offset = $offset + $mls['retsoffset'];
			echo '<META HTTP-EQUIV="refresh" CONTENT="2;URL=http://www.profileidx.com/index.php?mls_management&password='.$password.'&action=updateProperties&mls_code='.$mls_code.'&offset='.$offset.'">';
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


function downloadAllImages($mls_id, $table, $limit, $offset, $cron, $password)
{
	$mlsManagement = new mlsManagement;
	$imageDownloader = new imageDownloader;

	//Get Individual MLS Information
	$mlsInfo = $mlsManagement->getAllMLS($mls_id);

	//Log & Echo MLS Information
	$mlsInfoLog = 'MLS Info ( '.date('m/d/Y h:i:s a', time()).' ): '.print_r($mlsInfo, true)."\n";
	$imageDownloader->_logError($mlsInfoLog);
	echo $mlsInfoLog;

	//Get Properties
	$properties = $imageDownloader->getPropertyIds($mlsInfo, $table, $mlsManagement->getData('mlsImg_increm_update_days_ago'), $limit, $offset);	

	//Log & Echo Properties
	$propertiesLog = 'Properties: '.print_r($properties, true)."\n";
	$imageDownloader->_logError($propertiesLog);
	echo $propertiesLog;

	//Get Image Data For Each Property
	$images = $imageDownloader->getImageData($mlsInfo, $properties);
	foreach($images as $property_id => $property_images)
	{
		foreach($property_images as $ind_image)
		{
			$imageResult[] = $imageDownloader->saveImageData($ind_image, $mls_id, $table, $property_id);
		}
	}

	//Log & Echo Properties
	$propertiesLog = 'Properties: '.print_r($properties, true)."\n";
	$imageDownloader->_logError($propertiesLog);
	echo $propertiesLog;
	print_r($imageResult);
	if($offset != null && $imageResult != false && !isset($GET['cron']))
	{
		echo 'uhh... redirect?';
		$offset = $offset + $limit;
		echo '<META HTTP-EQUIV="refresh" CONTENT="2;URL=http://profileidx.com/index.php?mls_management&action=downloadImages&password='.$password.'&mls_code='.$mls_id.'&table='.$table.'&limit='.$limit.'&offset='.$offset.'">';
	}
	else
	{
		echo 'Array Done';
	}
}
?>