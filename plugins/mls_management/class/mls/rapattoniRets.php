<?php
require_once(GSPLUGINPATH.'mls_management/class/phRets.php');

class rapattoniRets extends extractData 
{
	public $dbh;

	public function __construct($db_name)
	{
		$this->db_name = $db_name;
		//Connect to database
		$this->dbh = $this->connectDB();
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

	public function getRetsData($rets_url, $rets_user, $rets_pass, $rets_offset=300, $offset, $prop_type, $mls_num=null)
	{
		if($prop_type == 'sfr')
		{
			$getRets = $this->getSfrData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset, $mls_num);
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


	public function getSfrData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset, $mls_num)
	{
		try
		{
			$date = date_create();
			$truncate = "-*-*- ".date_format($date, 'Y-m-d H:i:s')." Starting `sfr` download in the `'.$this->db_name.'` database -*-*-\n";
			echo $truncate.'<br/>';
			$this->writeLogs(UPDATELOGPATH, $truncate);
			//UPDATELOGPATH
		}
		catch(PDOException $e)
		{
			$updateError = 'Error: '.$e->getMessage()."\n";
			$this->writeLogs(UPDATELOGPATH, $updateError);
		}

		//Connect To RETS Server
		$rets = new phRETS; 
		$rets->AddHeader("RETS-Version", "RETS/1.7.2");
		$rets->AddHeader("User-Agent", "profile/1.7");
		$connect = $rets->Connect($rets_url, $rets_user, $rets_pass);

		//Get the "Update Increment" from mls management settings file & Query RETS server for properties that have been modified in the past x days
		$select_date = (int) $this->getData('mlsImg_increm_update_days_ago');
        $query_date = date ('Y-m-d', strtotime('-'.$select_date.' day', strtotime(date("Y-m-j"))));
        echo $query_date;
		if(is_null($mls_num))
		{
       		$query = "(MLNumber=0+)";
       		//$query = "(MLNumber=0+),(LastModifiedDateTime={$query_date}T03:28:04+)";
       	}
		else
		{
       		$query = "(MLNumber={$mls_num})";
       	}

		$search = $rets->SearchQuery("Property", "RESI", $query, array("Select" => "State,MLNumber,ListingRid,ListingOfficeMLSID,ListingOfficeName,ListingOfficeNumber,ListingAgentMLSID,ListingAgentFullName,ListingAgentNumber,EntryDate,PropertyType,RESICOMT,Style,RESIREST,RESIPETS,RESIPARK,RESILOTD,RESICONS,RESIBWAC,MarketingRemarks,Status,RESIFREO,RESISSLE,ListingPrice,RESIZONE,RESIWTRD,RESIWATR,RESIVIEW,StreetNumber,StreetName,StreetSuffix,StreetPostDirection,Unit,Longitude,Latitude,City,ZipCode,Region,County,Subdivision,Area,YearBuilt,Acres,SquareFootage,PricePerSquareFoot,Bedrooms,Bathrooms,RESIHOAF,RESIHOD,LegalDescription,APN,RESITXYR,Section,Township,MasterBedroomDim,SecondBedroomDim,ThirdBedroomDim,FourthBedroomDim,FifthBedroomDim,KitchenDim,DenDim,DiningRoomDim,FamilyRoomDim,LivingRoomDim,GreatRoomDim,RESIINTF,VirtualTourURL,PictureCount,PictureModifiedDateTime,VOWAutomatedValuationDisplay,VOWConsumerComment,RESIHOFA,RESIFINI,ShowAddressToPublic,RESIPVSP,RESINGAR,RESIGARA,LastModifiedDateTime,RESITOTF,RESIKITC,RESIPVPL,RESIPOLD,RESIFLOR,RESIROOF,RESIAMEN,RESIINTF,RESIEXTF,RESIHEAT,RESICOOL,RESIDENN,RESIWIND,EntryDate,HalfBathrooms,PropertySubtype1", "Limit" => $rets_offset, "Offset" => $offset));
		if($search)
		{
			$count = 0;
			while ($listing = $rets->FetchRow($search)) 
			{
				$count++;
			 	$db_mapping = array('mls_id' => '116', 
			 		'p_type' => 'sfr',
					'mls_state_id' => $listing['State'], 
					'mls_listing_id' => $listing['MLNumber'], 
					'listing_rid' => $listing['ListingRid'], 
					'tln_firm_id' => $listing['ListingOfficeMLSID'], 
					'mls_office_name' => $listing['ListingOfficeName'], 
					'mls_office_phone' => $listing['ListingOfficeNumber'], 
					'tln_realtor_id' => $listing['ListingAgentMLSID'], 
					'mls_agent_name' => $listing['ListingAgentFullName'], 
					'mls_agent_phone' => $listing['ListingAgentNumber'], 
					'mls_agent_email' => '', 
					'showing_phone' => '', 
					'showing_appt' => '', 
					'showing_instr' => '', 
					'listing_date' => $listing['EntryDate'], 
					'property_type_code' => $listing['PropertyType'], 
					'community_type' => $listing['RESICOMT'], 
					'building_design' => $listing['PropertySubtype1'], 
					'building_desc' => $listing['Style'], 
					'security' => '', 
					'restrictions' => $listing['RESIREST'], 
					'pets' => $listing['RESIPETS'], 
					'parking' => $listing['RESIPARK'], 
					'lot_desc' => $listing['RESILOTD'], 
					'construction' => $listing['RESICONS'], 
					'boat_access' => $listing['RESIBWAC'], 
					'remarks' => $listing['MarketingRemarks'], 
					'status_code' => "A-Active", 
					'foreclosure' => $listing['RESIFREO'], 
					'short_sale' => $listing['RESISSLE'], 
					'short_sale_comp' => '', 
					'sale_price' => $listing['ListingPrice'], 
					'original_price' => '', 
					'sold_price' => '', 
					'days_on_market' => $listing['EntryDate'], 
					'zoning_code' => $listing['RESIZONE'], 
					'water_front_desc' => $listing['RESIWTRD'], 
					'water' => $listing['RESIWATR'], 
					'view' => $listing['RESIVIEW'], 
					'full_address' => $listing['StreetNumber'].' '.$listing['StreetPostDirection'].' '.$listing['StreetName'].' '.$listing['StreetSuffix'].', '.$listing['City'].', '.$listing['State'], 
					'street_number' => $listing['StreetNumber'], 
					'street_name' => $listing['StreetName'], 
					'street_type' => $listing['StreetSuffix'], 
					'street_direction' => $listing['StreetPostDirection'], 
					'unit_number' => $listing['Unit'], 
					'longitude' => $listing['Longitude'], 
					'latitude' => $listing['Latitude'], 
					'city' => $listing['City'], 
					'zip_code' => $listing['ZipCode'], 
					'mls_area' => $listing['Region'], 
					'county' => $listing['County'], 
					'subdivision' => $listing['Subdivision'], 
					'community_name' => $listing['Area'], 
					'year_built' => $listing['YearBuilt'], 
					'acres' => $listing['Acres'], 
					'building_square_footage' => '', 
					'living_square_footage' => $listing['SquareFootage'], 
					'price_per_sqft' => $listing['PricePerSquareFoot'], 
					'bedrooms' => $listing['Bedrooms'], 
					'baths_full' => $listing['Bathrooms'], 
					'baths_half' => $listing['HalfBathrooms'], 
					'total_rooms' => '', 
					'school_elementary' => '', 
					'school_middle' => '', 
					'school_junior_high' => '', 
					'school_high' => '', 
					'hoa_fees' => $listing['RESIHOAF'], 
					'hoa_desc' => $listing['RESIHOD'], 
					'owners_name' => '', 
					'legal' => $listing['LegalDescription'], 
					'apn' => $listing['APN'], 
					'taxes' => '', 
					'tax_year' => $listing['RESITXYR'], 
					'section' => $listing['Section'], 
					'range' => '', 
					'township' => $listing['Township'], 
					'master_bed' => $listing['MasterBedroomDim'], 
					'bed2' => $listing['SecondBedroomDim'], 
					'bed3' => $listing['ThirdBedroomDim'], 
					'bed4' => $listing['FourthBedroomDim'], 
					'bed5' => $listing['FifthBedroomDim'], 
					'kitchen' => $listing['KitchenDim'], 
					'laundry' => '', 
					'den' => $listing['DenDim'], 
					'dining' => $listing['DiningRoomDim'], 
					'family' => $listing['FamilyRoomDim'], 
					'living' => $listing['LivingRoomDim'], 
					'great' => $listing['GreatRoomDim'], 
					'feature_codes' => $listing['RESIINTF'], 
					'virtual_tour_url' => $listing['VirtualTourURL'], 
					'photo_quantity' => $listing['PictureCount'], 
					'photo_url' => 'http://profileidx.com/photos/116/'.$listing['MLNumber'].'.jpg', 
					'photo_most_recent_date' => $listing['PictureModifiedDateTime'], 
					'avm' => $listing['VOWAutomatedValuationDisplay'], 
					'hoa_frequency' => $listing['RESIHOFA'], 
					'syndication' => '',
					'furnished' => $listing['RESIFINI'],
					'display_address' => $listing['ShowAddressToPublic'],
					'spa_included' => $listing['RESIPVSP'],
					'garage_desc' => $listing['RESIGARA'],
					'garage_spaces' => $listing['RESINGAR'],
					'windows' => $listing['RESIWIND'],
					'date_last_transaction' => $listing['LastModifiedDateTime'],
					'total_floors' => $listing['RESITOTF'],
					'kitchen_desc' => $listing['RESIKITC'],
					'pool' => $listing['RESIPVPL'],
					'pool_desc' => $listing['RESIPOLD'],
					'flooring' => $listing['RESIFLOR'],
					'roof' => $listing['RESIROOF'],
					'cooling' => $listing['RESICOOL'],
					'heat' => $listing['RESIHEAT'],
					'interior_features' => $listing['RESIINTF'],
					'exterior_features' => $listing['RESIEXTF'],
					'amenities' => $listing['RESIAMEN'],
					'den_included' => $listing['RESIDENN']);
       				$is_array = true;
       				//echo 'DATA<pre>'.print_r($db_mapping,true).'</pre>';
	                try
	                {
	                	$current_date = date("Y-m-d H:i:s");
	                	$db_mapping['days_on_market'] = $this->checkDateDifferance($db_mapping['days_on_market'], $current_date);

	                	if($db_mapping['property_type_code'] == 'Residential')
	                	{
	                		$db_mapping['property_type_code'] = 'Single Family';
	                	}
	                	if($db_mapping['building_design'] == 'High Rise (8+)')
	                	{
	                		$db_mapping['building_design'] = 'High Rise (8 or more)';
	                		$db_mapping['property_type_code'] = 'Condo';
	                	}
	                	elseif($db_mapping['building_design'] == 'Low Rise (1-3)')
	                	{
	                		$db_mapping['building_design'] = 'Low Rise (1-3 Floors)';
	                		$db_mapping['property_type_code'] = 'Condo';
	                	}
	                	elseif($db_mapping['building_design'] == 'Mid Rise (4-7)')
	                	{
	                		$db_mapping['building_design'] = 'Mid Rise (4-7 Floors)';
	                		$db_mapping['property_type_code'] = 'Condo';
	                	}
	                	elseif($db_mapping['building_design'] == 'Villa Attch/Half Dup')
	                	{
	                		$db_mapping['building_design'] = 'Villa Attached';
	                	}

                       $db_mapping['listing_date'] = $this->RETSdatetimeToMySQL($db_mapping['listing_date']);
	                   if((strpos($db_mapping['zip_code'], '-') !== false))
	                   {
	                   		$db_mapping['zip_code'] = array_shift(explode('-', $db_mapping['zip_code']));
	                   }
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
	                   echo '<div style="text-align:left">';
	                   $checkProperty = $this->checkProperty('sfr', $db_mapping['mls_listing_id']);
	                   if(empty($checkProperty))
	                   {
	                   		$questionMarks = $this->getQuestionMarks($db_mapping);
	                   		$columnNames = $this->getPDOColumnNames($db_mapping);
	                    	$sql = "INSERT INTO `sfr` (".$columnNames.") VALUES (".$questionMarks.")";							
			                $q = $this->dbh->prepare($sql);
			                $attemt_addition = $q->execute(array_values($db_mapping));


						    //Initiate coordinates update
					    	$coord_sql = "UPDATE `sfr` SET `coordinates` = GEOMFROMTEXT('POINT(".$db_mapping['longitude']." ".$db_mapping['latitude'].")') WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";	
					        $coord_q = $this->dbh->prepare($coord_sql);
					        $attemt_coord_addition = $coord_q->execute();

	                    	//Logs
	                		$checkPropertyResult =  $count. ':SFR Property INSERT Success: '.$db_mapping['mls_listing_id']."\n";
							$this->writeLogs(UPDATELOGPATH, $checkPropertyResult);
							echo $checkPropertyResult.'<br/></pre>';
	            			$count++;
	                   }
	                   else
	                   {
	                		$sql = "UPDATE `sfr` SET ".$sql. " WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";
			                $q = $this->dbh->prepare($sql);
			                $attemt_addition = $q->execute();

						    //Initiate coordinates update
					    	$coord_sql = "UPDATE `sfr` SET `coordinates` = GEOMFROMTEXT('POINT(".$db_mapping['longitude']." ".$db_mapping['latitude'].")') WHERE `mls_listing_id`='{$db_mapping['mls_listing_id']}'";	
					        $coord_q = $this->dbh->prepare($coord_sql);
					        $attemt_coord_addition = $coord_q->execute();

			                //Logs
	                		$checkPropertyResult = $count. ':SFR Property UPDATE Success: '.$db_mapping['mls_listing_id']."\n";
							$this->writeLogs(UPDATELOGPATH, $checkPropertyResult);
							echo $checkPropertyResult.'<br/></pre>';
	            			$count++;
	                   }
	                   echo '</div>';
	                }
	                catch(PDOException $e)
	                {
	                    echo '<div class="error">'.$e->getMessage().'</div>';
	                }
		        }
			$rets->FreeResult($search);
			}
		else
		{
			print_r($rets->Error());
		}
   		if(isset($is_array) && $is_array == true)
   		{
   			return true;	
   		}
   		else
   		{
   			return false;
   		}
	}
}