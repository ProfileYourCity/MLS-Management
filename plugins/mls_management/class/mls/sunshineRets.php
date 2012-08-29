<?php
require_once(GSPLUGINPATH.'mls_management/class/phRets.php');

class sunshineRets extends extractData 
{
	public $dbh;
	public $update_length;

	public function __construct($db_name='proidx_188')
	{
		$this->db_name = $db_name;
		//Connect to database
		$this->dbh = $this->connectDB();
		$this->update_length = $this->getData('mlsImg_increm_update_days_ago');

	}
	
	//Function to allow conection to database
	public function connectDB()
	{
		try 
		{
			$this->dbh = new PDO("mysql:host={$this->getData('dbhost')};dbname={$this->db_name}", $this->getData('dbuser'), $this->getData('dbpass'));
			$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
			/*** return database handle ***/
			return $this->dbh;
		}
		catch(PDOException $e)
		{
			echo '<div class="error">'.$e->getMessage().'</div>';
		}
	}

	public function getRetsData($rets_url, $rets_user, $rets_pass, $rets_offset=offset, $offset, $prop_type, $mls_num=null)
	{
		if($prop_type == 'sfr')
		{
			$getRets = $this->getSfrData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset, false, $mls_num);
		}
		elseif($prop_type == 'lots')
		{
			$getRets = $this->getLotsData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset, false, $mls_num);
		}

		if($getRets == true)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getSfrData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset, $update=false, $mls_num=null)
	{
		$date = date_create();
		$truncate = "-*-*- ".date_format($date, 'Y-m-d H:i:s')." Starting `sfr` download in the `'.$this->db_name.'` database -*-*-\n";
		echo $truncate.'<br/>';
		$this->writeLogs(UPDATELOGPATH, $truncate);
		
		//Connect To RETS Server
		$rets = new phRETS; 
		$rets->AddHeader("RETS-Version", "RETS/1.7.2");
		$connect = $rets->Connect($rets_url, $rets_user, $rets_pass);

		//Get the "Update Increment" from mls management settings file & Query RETS server for properties that have been modified in the past x days
		$select_date = (int) $this->getData('mlsImg_increm_update_days_ago');
        $query_date = date ('Y-m-j', strtotime('-'.$select_date.' day', strtotime(date("Y-m-j"))));
		if(is_null($mls_num))
		{
        	$query = "(datelasttransaction={$query_date}+)";
        	echo $query;
	        //$query = "(developmentname=WILDCAT+RUN)";
		}
		else
		{
        	$query = "(mlnum=$mls_num)";
		}

	  	$search = $rets->SearchQuery("Property","RES",$query,
	    array("Select" => "boardcode,stateid,mlnum,listbrokercode,listbrokername,listbrokerphone,listagentid,listagentname,listagentphone,listagentemail,showingphonenumber,showingapptrequiredyn,showinginfomvo,datelisting,ownershipdescsvo,communitytypemvo,buildingdesignsvo,buildingdescmvo,securitymvo,restrictionsmvo,petssvo,parkingmvo,lotdescmvo,constructionmvo,boataccessmvo,remarksbuyer,status,foreclosedyn,shortsaleyn,shortsalecomp,pricelist,priceoriginal,pricesold,dom,zoningcode,waterfrontdescmvo,watermvo,viewmvo,fulladdress,streetnumber,streetname,streettype,streetdirectionid,suiteaptnum,longitude,latitude,city,zip,mlsarea,countyid,subcondoname,developmentname,yearbuilt,acres,areatotal,arealiving,pricepersqft,bedroomssvo,totalfullbaths,totalhalfbaths,roomsmvo,schoolelementary,schoolmiddle,colistagentfax,schoolhigh,hoafeeamt,hoadescsvo,ownername,legaldesc,suiteaptnum,taxamount,taxyear,legalsection,township,masterbrdim,secondbrdim,thirdbrdim,fourthbrdim,fifthbrdim,kitchendim,floorplantype,dendim,diningrmdim,famrmdim,livingrmdim,greatrmdim,amenitiesmvo,virtualtoururl,numimages,variabledualrate,datelasttransactionphoto,avmyn,addressonnetyn,hoafeepmtsvo,internetsitesmvo,dateclosed,exteriorfeaturesmvo,interiorfeaturesmvo,amenitiesmvo,equipmentmvo,heatmvo,coolmvo,roofmvo,flooringmvo,privatepoolyn,kitchenmvo,garagedescsvo,garagespaces,windowsmvo,pooldescmvo,datependingsale,numoffloors,furnisheddescsvo,datelasttransaction,privatespayn","Limit" => $rets_offset, "Offset" => $offset));
			$count = 0;
            while ($listing = $rets->FetchRow($search)) 
       		{
       			$this->updateSfr($listing, $count);
       			$count++;
       			$is_array = true;
	    	}
	   		$rets->FreeResult($search);
	   		if(isset($is_array) && $is_array == true)
	   		{
	   			return true;	
	   		}
	   		else
	   		{
	   			$sfrFinished = "-*-*- Finished Downloading -*-*- \n";
				$this->writeLogs(UPDATELOGPATH, $sfrFinished);
	   			return false;
	   		}
	}

	public function updateSfr($listing, $count)
	{
		$db_mapping = array('mls_id' => '188',
		'p_type'	    => 'sfr',
		'mls_state_id' => $listing['stateid'],
		'mls_listing_id' => $listing['mlnum'],
		'tln_firm_id' => $listing['listbrokercode'],
		'mls_office_name' => $listing['listbrokername'],
		'mls_office_phone' => $listing['listbrokerphone'],
		'tln_realtor_id' => $listing['listagentid'],
		'mls_agent_name' => $listing['listagentname'],
		'mls_agent_phone' => $listing['listagentphone'],
		'mls_agent_email' => $listing['listagentemail'],
		'showing_phone' => $listing['showingphonenumber'],
		'showing_appt' => $listing['showingapptrequiredyn'],
		'showing_instr' => $listing['showinginfomvo'],
		'listing_date' => $listing['datelisting'],
		'property_type_code' => $listing['ownershipdescsvo'],
		'community_type' => $listing['communitytypemvo'],
		'building_design' => $listing['buildingdesignsvo'],
		'building_desc' => $listing['buildingdescmvo'],
		'security' => $listing['securitymvo'],
		'restrictions' => $listing['restrictionsmvo'],
		'pets' => $listing['petssvo'],
		'parking' => $listing['parkingmvo'],
		'lot_desc' => $listing['lotdescmvo'],
		'construction' => $listing['constructionmvo'],
		'boat_access' => $listing['boataccessmvo'],
		'remarks' => $listing['remarksbuyer'],
		'status_code' => $listing['status'],
		'foreclosure' => $listing['foreclosedyn'],
		'short_sale' => $listing['shortsaleyn'],
		'short_sale_comp' => $listing['shortsalecomp'],
		'sale_price' => $listing['pricelist'],
		'original_price' => $listing['priceoriginal'],
		'sold_price' => $listing['pricesold'],
		'days_on_market' => $listing['dom'],
		'zoning_code' => $listing['zoningcode'],
		'water_front_desc' => $listing['waterfrontdescmvo'],
		'water' => $listing['watermvo'],
		'view' => $listing['viewmvo'],
		'full_address' => $listing['fulladdress'],
		'street_number' => $listing['streetnumber'],
		'street_name' => $listing['streetname'],
		'street_type' => $listing['streettype'],
		'street_direction' => $listing['streetdirectionid'],
		'unit_number' => $listing['suiteaptnum'],
		'longitude' => $listing['longitude'],
		'latitude' => $listing['latitude'],
		'city' => $listing['city'],
		'zip_code' => $listing['zip'],
		'mls_area' => $listing['mlsarea'],
		'county' => $listing['countyid'],
		'subdivision' => $listing['subcondoname'],
		'community_name' => $listing['developmentname'],
		'year_built' => $listing['yearbuilt'],
		'acres' => $listing['acres'],
		'building_square_footage' => $listing['areatotal'],
		'living_square_footage' => $listing['arealiving'],
		'price_per_sqft' => $listing['pricepersqft'],
		'bedrooms' => $listing['bedroomssvo'],
		'baths_full' => $listing['totalfullbaths'],
		'baths_half' => $listing['totalhalfbaths'],
		'total_rooms' => $listing['roomsmvo'],
		'school_elementary' => $listing['schoolelementary'],
		'school_middle' => $listing['schoolmiddle'],
		'school_high' => $listing['schoolhigh'],
		'hoa_fees' => $listing['hoafeeamt'],
		'hoa_desc' => $listing['hoadescsvo'],
		'owners_name' => $listing['ownername'],
		'legal' => $listing['legaldesc'],
		'apn' => $listing['suiteaptnum'],
		'taxes' => $listing['taxamount'],
		'tax_year' => $listing['taxyear'],
		'section' => $listing['legalsection'],
		'township' => $listing['township'],
		'master_bed' => $listing['masterbrdim'],
		'bed2' => $listing['secondbrdim'],
		'bed3' => $listing['thirdbrdim'],
		'bed4' => $listing['fourthbrdim'],
		'bed5' => $listing['fifthbrdim'],
		'kitchen' => $listing['kitchendim'],
		'laundry' => $listing['floorplantype'],
		'den' => $listing['dendim'],
		'dining' => $listing['diningrmdim'],
		'family' => $listing['famrmdim'],
		'living' => $listing['livingrmdim'],
		'great' => $listing['greatrmdim'],
		'feature_codes' => $listing['amenitiesmvo'],
		'virtual_tour_url' => $listing['virtualtoururl'],
		'photo_quantity' => $listing['numimages'],
		'photo_url' => $listing[91] = 'http://photos.profileidx.com/188/'.$listing['mlnum'].'.jpg',
		'photo_most_recent_date' => $listing['datelasttransactionphoto'],
		'avm' => $listing['avmyn'],
		'display_address' => $listing['addressonnetyn'],
		'hoa_frequency' => $listing['hoafeepmtsvo'],
		'syndication' => $listing['internetsitesmvo'],
		'date_closed' => $listing['dateclosed'],
		'pending_sale_date' => $listing['datependingsale'],
		'exterior_features' => $listing['exteriorfeaturesmvo'],
		'interior_features' => $listing['interiorfeaturesmvo'],
		'amenities' => $listing['amenitiesmvo'],
		'equipment' => $listing['equipmentmvo'],
		'heat' => $listing['heatmvo'],
		'cooling' => $listing['coolmvo'],
		'roof' => $listing['roofmvo'],
		'flooring' => $listing['flooringmvo'],
		'pool' => $listing['privatepoolyn'],
		'kitchen_desc' => $listing['kitchenmvo'],
		'garage_desc' => $listing['garagedescsvo'],
		'garage_spaces' => $listing['garagespaces'],
		'windows' => $listing['windowsmvo'],
		'pool_desc' => $listing['pooldescmvo'],
		'total_floors' => $listing['numoffloors'],
		'furnished' => $listing['furnisheddescsvo'],
		'date_last_transaction' => $listing['datelasttransaction'],
		'den_included' => $listing['bedroomssvo'],
		'spa_included' => $listing['privatespayn']);
		$is_array = true;
        try
        {
			$db_mapping['listing_date'] = $this->RETSdatetimeToMySQL($db_mapping['listing_date']);
            $db_mapping['date_last_transaction'] = $this->RETSdatetimeToMySQL($db_mapping['date_last_transaction']);
			if((strpos($db_mapping['zip_code'], '-') !== false))
			{
					$db_mapping['zip_code'] = array_shift(explode('-', $db_mapping['zip_code']));
			}
			if($db_mapping['property_type_code'] == 'High Rise (8 or more)' || $db_mapping['property_type_code'] == 'Low Rise (1-3 Floors)' || $db_mapping['property_type_code'] == 'Mid Rise (4-7 Floors)' || $db_mapping['property_type_code'] == 'Villa Detached')
			{
				$db_mapping['property_type_code'] == 'Condo';
			}
			else
			{
				$db_mapping['property_type_code'] == 'Single Family';
			}
			if(strpos($db_mapping['den_included'], 'den'))
			{
				$db_mapping['den_included'] = 'Yes';
			}
			else
			{
				$db_mapping['den_included'] = 'No';
			}
			if($db_mapping['photo_quantity'] == '0' || $db_mapping['photo_quantity'] < 1)
			{
				$db_mapping['photo_url'] = 'http://photos.profileidx.com/placeholders/placeholder640x480.jpg';
			}
			echo '<div style="text-align:left">';
			$checkProperty = $this->checkProperty('sfr', $db_mapping['mls_listing_id']);
			if(empty($checkProperty))
			{
				//Generate appropriate number of question marks and comlumn names list
			   		$questionMarks = $this->getQuestionMarks($db_mapping);
			   		$columnNames = $this->getPDOColumnNames($db_mapping);
			   	//Initiate basic insert statement
			    	$sql = "INSERT INTO `sfr` (".$columnNames.") VALUES (".$questionMarks.")";	
			        $q = $this->dbh->prepare($sql);
			        $attemt_addition = $q->execute(array_values($db_mapping));

			    //Initiate coordinates update
			    	$coord_sql = "UPDATE sfr SET  `coordinates` = GEOMFROMTEXT('POINT(".$db_mapping['longitude']." ".$db_mapping['latitude'].")') WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";	
			        $coord_q = $this->dbh->prepare($coord_sql);
			        $attemt_coord_addition = $coord_q->execute();

				//Logs
					$checkPropertyResult =  $count. ':SFR Property INSERT Success: '.$db_mapping['mls_listing_id']."\n";
					$this->writeLogs(UPDATELOGPATH, $checkPropertyResult);
					echo $db_mapping['mls_listing_id'].': '.$checkPropertyResult.'<br/></pre>';
			}
			else
			{
					//Calculate # of columns/values - split into 2 (or 3 if odd number) arrays
			   		$listing_column_count = count($db_mapping);
			   		$listing_column_count = $listing_column_count / 2;
			   		$db_map_chunk = array_chunk($db_mapping, $listing_column_count, true);
						//echo '2<pre>'.print_r($db_map_chunk,true).'</pre>';

					//Update the records with each of the data 2 or 3 data chunks
			   		foreach($db_map_chunk as $db_map)
			   		{
			       		$sql_stmt = $this->createSQL($db_mapping);
			    		$sql = "UPDATE `sfr` SET ".$sql_stmt. " WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";
			    		//echo $sql;
			            $q = $this->dbh->prepare($sql);
			            $attemt_addition = $q->execute();

			            //Logs
			    		$checkPropertyResult = $count. ':SFR Property UPDATE Success: '.$db_mapping['mls_listing_id']."\n";
						$this->writeLogs(UPDATELOGPATH, $checkPropertyResult);
						echo $db_mapping['mls_listing_id'].': '.$checkPropertyResult.'<br/></pre>';
			   		}
			}
			echo '</div>';
        }
        catch(PDOException $e)
        {
            $error_pdo = $db_mapping['mls_listing_id'].': '.$e->getMessage();
			$this->writeLogs(UPDATELOGPATH, $error_pdo);
        }
	}

	public function getLotsData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset, $update=false, $mls_num=null)
	{
		//$this->dbh->exec("TRUNCATE TABLE `lots`");
		$date = date_create();
		$startLots = "-*-*-  ".date_format($date, 'Y-m-d H:i:s')." Starting `lots` download in the `'.$this->db_name.'` database -*-*-\n";
		$this->writeLogs(UPDATELOGPATH, $startLots);
		echo $startLots;

		$rets = new phRETS; 
		$rets->AddHeader("RETS-Version", "RETS/1.7.2");
		$connect = $rets->Connect($rets_url, $rets_user, $rets_pass);

		//Get the "Update Increment" from mls management settings file & Query RETS server for properties that have been modified in the past x days
		$select_date = (int) $this->getData('mlsImg_increm_update_days_ago');
        $query_date = date ('Y-m-j', strtotime('-'.$select_date.' day', strtotime(date("Y-m-j"))));
		if(is_null($mls_num))
		{
        	$query = "(datelasttransaction={$query_date}+)";
        	echo $query;
	        //$query = "(developmentname=WILDCAT+RUN)";
		}
		else
		{
        	$query = "(mlnum=$mls_num)";
		}

	  	$search = $rets->SearchQuery("Property","LOT",$query,
	    array("Select" => "lotdescmvo,listbrokername,stateid,mlnum,pricelist,pricesold,sourcemeasurements,viewmvo,watermvo,waterfrontdescmvo,zoningcode,remarksagent,remarksbuyer,legaldesc,landusecodesvo,numberofparcels,otherpid,specialassessmentsvo,colistbrokercode,showingapptrequiredyn,showingphonenumber,hoafeeamt,pricepersqft,sellpricepersqft,priceoriginal,hoadescsvo,hoafeepmtsvo,internetsitesmvo,irrigationmvo,landimprovementsmvo,landleasepaymentsvo,status,listingtypesvo,lotdescmvo,lottypesvo,maintenancemvo,mandclubfeepmtsvo,masterhoafeepmtsvo,mgmtsvo,mlsarea,possessionmvo,rearexposuresvo,propertylocationsvo,restrictionsmvo,roadmvo,sewermvo,showinginfomvo,soldfinancingtypesvo,specialinfomvo,streetdirectionid,streettype,streetname,streetnumber,zip,developmentname,fulladdress,listagentid,listbrokercode,lotunit,ownername,pid,subdivisionnum,datelisting,datependingsale,datewithdrawn,dateterminated,dateclosed,datecreated,datelasttransaction,condofee,legalunit,landleasefee,landsqft,lotfrontage,range,township,city,virtualtoururl,acres,longitude,latitude,plannedusemvo,taxamount,taxyear,countyid,datelasttransactionphoto,numimages,landleasefeeonetime,listagentname,listagentphone,listagentemail,subdivisioninfomvo,utilitiesavailmvo,shortsaleyn,shortsalecomp,foreclosedyn,addressonnetyn","Limit" => $rets_offset, "Offset" => $offset));
			$count = 0;
            while ($listing = $rets->FetchRow($search)) 
       		{
       			$this->updateLots($listing, $count);
       			$count++;
       			$is_array = true;
	    	}
	   		$rets->FreeResult($search);
	   		if(isset($is_array) && $is_array == true)
	   		{
	   			return true;	
	   		}
	   		else
	   		{
	   			$sfrFinished = "-*-*- Finished Downloading -*-*- \n";
	   			return false;
	   		}
	}

	public function updateLots($listing, $count)
	{
		$db_mapping = array(
		'mls_id' 				=> '188',
		'p_type'				=> 'lots',
		'property_type_code' 	=> $listing['lotdescmvo'],
		'mls_office_name' 		=> $listing['listbrokername'],
		'mls_state_id' 			=> $listing['stateid'],
		'mls_listing_id' 		=> $listing['mlnum'],
		'sale_price' 			=> $listing['pricelist'],
		'sold_price' 			=> $listing['pricesold'],
		'source_measurements' 	=> $listing['sourcemeasurements'],
		'view' 					=> $listing['viewmvo'],
		'water' 				=> $listing['watermvo'],
		'waterfront_desc' 		=> $listing['waterfrontdescmvo'],
		'zoning_code' 			=> $listing['zoningcode'],
		'agent_remarks' 		=> $listing['remarksagent'],
		'remarks' 				=> $listing['remarksbuyer'],
		'legal_desc'			=> $listing['legaldesc'],
		'land_use_code' 		=> $listing['landusecodesvo'],
		'num_of_parcels' 		=> $listing['numberofparcels'],
		'other_pids' 			=> $listing['otherpid'],
		'special_assessment_fee' => $listing['specialassessmentsvo'],
		'colist_broker_code'	=> $listing['colistbrokercode'],
		'showing_phone_num' 	=> $listing['showingphonenumber'],
		'hoa_fees'			 	=> $listing['hoafeeamt'],
		'price_per_sqft' 		=> $listing['pricepersqft'],
		'sell_price_per_sqft' 	=> $listing['sellpricepersqft'],
		'orginal_price' 		=> $listing['priceoriginal'],
		'hoa_desc' 				=> $listing['hoadescsvo'],
		'hoa_frequency'			=> $listing['hoafeepmtsvo'],
		'syndication' 			=> $listing['internetsitesmvo'],
		'irrigation' 			=> $listing['irrigationmvo'],
		'land_improvements' 	=> $listing['landimprovementsmvo'],
		'land_lease_payments'   => $listing['landleasepaymentsvo'],
		'status_code' 			=> $listing['status'],
		'listing_type' 			=> $listing['listingtypesvo'],
		'lot_desc' 				=> $listing['lotdescmvo'],
		'lot_type' 				=> $listing['lottypesvo'],
		'maintenance' 			=> $listing['maintenancemvo'],
		'mand_club_fee' 		=> $listing['mandclubfeepmtsvo'],
		'master_hoa_fee_freq' 	=> $listing['masterhoafeepmtsvo'],
		'management' 			=> $listing['mgmtsvo'],
		'mls_area' 				=> $listing['mlsarea'],
		'possession' 			=> $listing['possessionmvo'],
		'rear_exposure' 		=> $listing['rearexposuresvo'],
		'property_location' 	=> $listing['propertylocationsvo'],
		'restrictions' 			=> $listing['restrictionsmvo'],
		'road' 					=> $listing['roadmvo'],
		'sewer' 				=> $listing['sewermvo'],
		'showing_info' 			=> $listing['showinginfomvo'],
		'sold_financing_type' 	=> $listing['soldfinancingtypesvo'],
		'special_info' 			=> $listing['specialinfomvo'],
		'street_direction' 		=> $listing['streetdirectionid'],
		'street_type' 			=> $listing['streettype'],
		'street_name' 			=> $listing['streetname'],
		'street_number' 		=> $listing['streetnumber'],
		'zip_code'			    => $listing['zip'],
		'community_name' 		=> $listing['developmentname'],
		'fulladdress' 			=> $listing['fulladdress'],
		'tln_realtor_id' 		=> $listing['listagentid'],
		'list_broker_code' 		=> $listing['listbrokercode'],
		'lot_unit' 				=> $listing['lotunit'],
		'owner_name' 			=> $listing['ownername'],
		'pid' 					=> $listing['pid'],
		'subdivision' 			=> $listing['subdivisionnum'],
		'listing_date' 			=> $listing['datelisting'],
		'pending_sale_date' 	=> $listing['datependingsale'],
		'date_withdrawn' 		=> $listing['datewithdrawn'],
		'date_terminated' 		=> $listing['dateterminated'],
		'date_closed' 			=> $listing['dateclosed'],
		'date_created' 			=> $listing['datecreated'],
		'date_last_transaction' => $listing['datelasttransaction'],
		'condo_fee' 			=> $listing['condofee'],
		'legal_unit' 			=> $listing['legalunit'],
		'land_lease_fee' 		=> $listing['landleasefee'],
		'land_sqft' 			=> $listing['landsqft'],
		'lot_frontage' 			=> $listing['lotfrontage'],
		'range' 				=> $listing['range'],
		'town_ship' 			=> $listing['township'],
		'city' 					=> $listing['city'],
		'virtual_tour_url' 		=> $listing['virtualtoururl'],
		'acres' 				=> $listing['acres'],
		'longitude' 			=> $listing['longitude'],
		'latitude' 				=> $listing['latitude'],
		'planned_ise' 			=> $listing['plannedusemvo'],
		'taxes' 				=> $listing['taxamount'],
		'tax_year' 				=> $listing['taxyear'],
		'county' 				=> $listing['countyid'],
		'photo_most_recent_date' => $listing['datelasttransactionphoto'],
		'photo_quantity' 		=> $listing['numimages'],
		'photo_url' 			=> 'http://photos.profileidx.com/188/'.$listing['mlnum'].'.jpg',
		'mls_agent_name' 		=> $listing['listagentname'],
		'mls_agent_phone' 		=> $listing['listagentphone'],
		'mls_agent_email' 		=> $listing['listagentemail'],
		'subdivision_info' 		=> $listing['subdivisioninfomvo'],
		'avail_utilities' 		=> $listing['utilitiesavailmvo'],
		'short_sale' 			=> $listing['shortsaleyn'],
		'short_sale_comp' 		=> $listing['shortsalecomp'],
		'foreclosure' 			=> $listing['foreclosedyn'],
		'display_address'       => $listing['addressonnetyn']);
        try
        {
           $db_mapping['listing_date'] = $this->RETSdatetimeToMySQL($db_mapping['listing_date']);
           $db_mapping['date_last_transaction'] = $this->RETSdatetimeToMySQL($db_mapping['date_last_transaction']);
           if((strpos($db_mapping['zip_code'], '-') !== false))
           {
           		$db_mapping['zip_code'] = array_shift(explode('-', $db_mapping['zip_code']));
           }
           echo '<div style="text-align:left">';
           $checkProperty = $this->checkProperty('lots', $db_mapping['mls_listing_id']);
           if(empty($checkProperty))
           {
				//Generate appropriate number of question marks and comlumn names list
           		$questionMarks = $this->getQuestionMarks($db_mapping);
           		$columnNames = $this->getPDOColumnNames($db_mapping);

			   	//Initiate basic insert statement
            	$sql = "INSERT INTO `lots` (".$columnNames.") VALUES (".$questionMarks.")";
                $q = $this->dbh->prepare($sql);
                $attemt_addition = $q->execute(array_values($db_mapping));

			    //Initiate coordinates update
			    	$coord_sql = "UPDATE lots SET `coordinates` = GEOMFROMTEXT('POINT(".$db_mapping['longitude']." ".$db_mapping['latitude'].")') WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";	
			        $coord_q = $this->dbh->prepare($coord_sql);
			        $attemt_coord_addition = $coord_q->execute();

                //Logs
	        		$checkPropertyResult = $count. ':LOTS Property INSERT Success: '.$db_mapping['mls_listing_id']."\n";
					$this->writeLogs(UPDATELOGPATH, $checkPropertyResult);
					echo $checkPropertyResult.'<br/></pre>';
           }
           else
           {
			   	//Initiate basic update statement
           		$sql_stmt = $this->createSQL($db_mapping);
        		$sql = "UPDATE `lots` SET ".$sql_stmt. " WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";
                $q = $this->dbh->prepare($sql);
                $attemt_addition = $q->execute();

			    //Initiate coordinates update
			    	$coord_sql = "UPDATE lots SET  `coordinates` = GEOMFROMTEXT('POINT(".$db_mapping['longitude']." ".$db_mapping['latitude'].")') WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";	
			        $coord_q = $this->dbh->prepare($coord_sql);
			        $attemt_coord_addition = $coord_q->execute();

                //Logs
	        		$checkPropertyResult = $count. ':LOTS Property UPDATE Success: '.$db_mapping['mls_listing_id']."\n";
					$this->writeLogs(UPDATELOGPATH, $checkPropertyResult);
					echo $checkPropertyResult.'<br/></pre>';
           }
           echo '</div>';
        }
        catch(PDOException $e)
        {
            $error = 'Error'.$e->getMessage()."\n";
			$this->writeLogs(UPDATELOGPATH, $error);
        }
	}

	public function createSQL($db_mapping)
	{
       $count_sql = 0;
       $sql = '';
	   foreach($db_mapping as $key => $value)
	   {
	   		$nvalue = str_replace("'", "&#39;", $value);
	   		$sql .= "`{$key}`='{$nvalue}'";
	        $count_sql++;	 
	   		if($count_sql < count($db_mapping))
	   		{
	   			$sql .= ', ';
	   		}
	   }
	   return $sql;
	}
}