<?php
require_once(GSPLUGINPATH.'mls_management/class/phRets.php');

class rapattoniRets extends extractData 
{
	public $dbh;

  	public $columns_db = "mls_id,p_type,mls_state_id,mls_listing_id,tln_firm_id,mls_office_name,mls_office_phone,tln_realtor_id,mls_agent_name,mls_agent_phone,mls_agent_email,showing_phone,showing_appt,showing_instr,listing_date,property_type_code,community_type,building_design,building_desc,security,restrictions,pets,parking,lot_desc,construction,boat_access,remarks,status_code,foreclosure,short_sale,short_sale_comp,sale_price,original_price,sold_price,days_on_market,zoning_code,water_front_desc,water,view,full_address,street_number,street_name,street_type,street_direction,unit_number,longitude,latitude,city,zip_code,mls_area,county,subdivision,community_name,year_built,acres,building_square_footage,living_square_footage,price_per_sqft,bedrooms,baths_full,baths_half,total_rooms,school_elementary,school_middle,school_junior_high,school_high,hoa_fees,hoa_desc,owners_name,legal,apn,taxes,tax_year,section,`range`,township,master_bed,bed2,bed3,bed4,bed5,kitchen,laundry,den,dining,family,living,great,feature_codes,virtual_tour_url,photo_quantity,photo_url,photo_most_recent_date,avm,blogging,hoa_frequency,syndication";

  	public $columns_rets = "State,ListingRid,ListingOfficeMLSID,ListingOfficeName,ListingOfficeNumber,ListingAgentMLSID,ListingAgentFullName,ListingAgentNumber,EntryDate,PropertyType,RESICOMT,Style,RESIREST,RESIPETS,RESIPARK,RESILOTD,RESICONS,RESIBWAC,MarketingRemarks,Status,RESIFREO,RESISSLE,ListingPrice,RESIZONE,RESIWTRD,RESIWATR,RESIVIEW,StreetNumber,StreetName,StreetSuffix,StreetPostDirection,Unit,Longitude,Latitude,City,ZipCode,Region,County,Subdivision,Area,YearBuilt,Acres,SquareFootage,PricePerSquareFoot,Bedrooms,Bathrooms,RESIHOAF,RESIHOD,LegalDescription,APN,RESITXYR,Section,Township,MasterBedroomDim,SecondBedroomDim,ThirdBedroomDim,FourthBedroomDim,FifthBedroomDim,KitchenDim,DenDim,DiningRoomDim,FamilyRoomDim,LivingRoomDim,GreatRoomDim,RESIINTF,VirtualTourURL,PictureCount,PictureModifiedDateTime,VOWAutomatedValuationDisplay,VOWConsumerComment,RESIHOFA";

	public function __construct($db_name)
	{
		$this->db_name = $db_name;
		//Connect to database
		$this->dbh = $this->connectDB($db_name);
	}
	
	//Function to allow conection to database
	public function connectDB($db_name)
	{

		try 
		{
			$this->dbh = new PDO("mysql:host={$this->getData('dbhost')};dbname={$db_name}", $this->getData('dbuser'), $this->getData('dbpass'));
			$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
			/*** return database handle ***/
			return $this->dbh;
		}
		catch(PDOException $e)
		{
			echo '<div class="error">'.$e->getMessage().'</div>';
		}
	}

	public function getRetsData($rets_url, $rets_user, $rets_pass, $rets_offset=300, $offset, $prop_type)
	{
		if($prop_type == 'sfr')
		{
			$getRets = $this->getSfrData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset);
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

	public function getSfrData($rets_url, $rets_user, $rets_pass, $rets_offset, $offset)
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

		$rets = new phRETS; 
		$rets->AddHeader("RETS-Version", "RETS/1.7.2");
		$rets->AddHeader("User-Agent", "profile/1.7");
		$connect = $rets->Connect($rets_url, $rets_user, $rets_pass);
        $query = "(MLNumber=0+)";
		$search = $rets->SearchQuery("Property", "RESI", $query, array("Select" => "State,ListingRid,ListingOfficeMLSID,ListingOfficeName,ListingOfficeNumber,ListingAgentMLSID,ListingAgentFullName,ListingAgentNumber,EntryDate,PropertyType,RESICOMT,Style,RESIREST,RESIPETS,RESIPARK,RESILOTD,RESICONS,RESIBWAC,MarketingRemarks,Status,RESIFREO,RESISSLE,ListingPrice,RESIZONE,RESIWTRD,RESIWATR,RESIVIEW,StreetNumber,StreetName,StreetSuffix,StreetPostDirection,Unit,Longitude,Latitude,City,ZipCode,Region,County,Subdivision,Area,YearBuilt,Acres,SquareFootage,PricePerSquareFoot,Bedrooms,Bathrooms,RESIHOAF,RESIHOD,LegalDescription,APN,RESITXYR,Section,Township,MasterBedroomDim,SecondBedroomDim,ThirdBedroomDim,FourthBedroomDim,FifthBedroomDim,KitchenDim,DenDim,DiningRoomDim,FamilyRoomDim,LivingRoomDim,GreatRoomDim,RESIINTF,VirtualTourURL,PictureCount,PictureModifiedDateTime ,VOWAutomatedValuationDisplay,VOWConsumerComment,RESIHOFA", "Limit" => $rets_offset, "Offset" => $offset));
		if($search)
		{
			$count = 0;
			while ($listing = $rets->FetchRow($search)) 
			{
				$count++;
			 	$db_mapping = array('mls_id' => '116', 
			 		'p_type' => 'sfr',
					'mls_state_id' => $listing['State'], 
					'mls_listing_id' => $listing['ListingRid'], 
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
					'building_design' => '', 
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
					'days_on_market' => '', 
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
					'baths_half' => '', 
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
					'photo_url' => 'http://profileidx.com/photos/116/'.$listing['ListingRid'].'.jpg', 
					'photo_most_recent_date' => $listing['PictureModifiedDateTime'], 
					'avm' => $listing['VOWAutomatedValuationDisplay'], 
					'blogging' => $listing['VOWConsumerComment'], 
					'hoa_frequency' => $listing['RESIHOFA'], 
					'syndication' => '');
       				$is_array = true;

	                try
	                {
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