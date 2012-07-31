<?php

/**
 * CRUD controller for MLS data sources.
 * 
 * TODO Needs documentation. 
 */
class mlsData
{
	/** 
	* A public variable 
	* 
	* @var object stores database connection
	*/  
	public $dbh;
	
	/** 
	* 
	* 
	* @return void 
	*/  
	public function __construct()
	{
		//If MLS management file does not exist, create
		if(!file_exists(MLSMANAGEMENTFILE))
		{
			$xml = new SimpleXMLExtended('<item></item>');
			XMLsave($xml, MLSMANAGEMENTFILE);
		}
		if(!file_exists(MLSFOLDER))
		{
			mkdir(MLSFOLDER);
		}
	}

	/** 
    * Process settings form. Saves to xml file
    * s
    * @return void
    */  
	//public function processSettings($db_host, $db_user, $db_pass, $password, $image_archive_path, $placeholder_image)
        public function processSettings($post_data)
	{
                $mlsData = new extractData;

		# create xml file
		if (file_exists(MLSMANAGEMENTFILE)) 
		{ 
			unlink(MLSMANAGEMENTFILE); 
		}
                $xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		foreach($post_data as $key => $value)
		{
			$parent_nodes_node = $xml->addChild($key);
				$parent_nodes_node->addCData($value);
		}
		if(XMLsave($xml, MLSMANAGEMENTFILE))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function addMLS($post_data, $edit=false)
	{
		if($edit != false)
		{
			$fileName = to7bit($edit, "UTF-8");
			$fileName = MLSFOLDER . clean_url($fileName) . '.xml';
			unlink($fileName);
		}
		$xml = new SimpleXMLExtended('<item></item>');
		foreach($post_data as $key => $value)
		{
			$parent_nodes_node = $xml->addChild($key);
				$parent_nodes_node->addCData($value);
		}
		$fileName = to7bit($post_data['mlsname'], "UTF-8");
		$fileName = MLSFOLDER . clean_url($fileName) . '.xml';
		if(XMLsave($xml, $fileName))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function editColumns($mls_id, $columns)
	{
		$xml = new SimpleXMLExtended('<item></item>');
		$tables_parent = $xml->addChild('tables');
		foreach($columns as $table => $table_data)
		{
			$tables_node = $tables_parent->addChild('table');
				$table_name = $tables_node->addChild('name');
					$table_name->addCData($table_data['name']);
				$tables_columns = $tables_node->addChild('columns');
				foreach($table_data['columns'] as $column_ar)
				{
					foreach($column_ar as $column)
					{	
						//echo '<pre>'.print_r($column,true).'</pre>';
						$column_node = $tables_columns->addChild('column');
							$column_label = $column_node->addChild('label');
								$column_label->addCData($column['label']);
							$column_options = $column_node->addChild('options');
								$column_options->addCData($column['options']);
							$column_name = $column_node->addChild('name');
								$column_name->addCData($column['name']);
							$column_type = $column_node->addChild('type');
								$column_type->addCData($column['type']);
							$column_enabled = $column_node->addChild('enabled');
								$column_enabled->addCData($column['enabled']);
					}
				}
		}
		$fileName = to7bit($mls_id, "UTF-8");
		$fileName = MLSCOLUMNSFOLDER . clean_url($fileName) . '.xml';
		if(XMLsave($xml, $fileName))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getMLSColumns($mls_id, $show_table=null, $exclude_empty=false)
	{
		$mls = $this->getAllMLS($mls_id);
		$mls_types = explode(',', $mls['mlsptypes']);
		$data = getXML(MLSCOLUMNSFOLDER . $mls_id . '.xml');
		$count_tables = 0;
		foreach($data->tables->table as $table)
		{
			$count_columns = 0;
			$table_name = (string) $table->name;
			if($show_table != null && $show_table != $table_name)
			{
				
			}
			else
			{
				$columns[$count_tables]['name'] = $table_name;
				foreach($table->columns->column as $column)
				{
					if($exclude_empty != false && empty($column->label))
					{

					}
					else
					{
						$columns[$count_tables]['columns'][$count_columns]['label'] = (string) $column->label;
						$columns[$count_tables]['columns'][$count_columns]['name'] = (string) $column->name;
						$columns[$count_tables]['columns'][$count_columns]['options'] = (string) $column->options;
						$columns[$count_tables]['columns'][$count_columns]['type'] = (string) $column->type;
						$columns[$count_tables]['columns'][$count_columns]['enabled'] = (string) $column->enabled;
						$count_columns++;
					}
				}
				$count_tables++;
			}
		}
		return $columns;
	}

	public function deleteMLS($mls_id)
	{
		$fileName = to7bit($mls_id, "UTF-8");
		$fileName = MLSFOLDER . clean_url($fileName) . '.xml';
		$delete_mls = unlink($fileName);
		if($delete_mls)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getAllMLS($mls_name=null)
	{
		$all_mls = glob(MLSFOLDER . "/*.xml");
		$mls_info = array();
		if($all_mls != false && ( count($all_mls) > 0 ) ) 
		{
			$count = 0;
			$count_columns = 0;
			foreach($all_mls as $mls)
			{
				$data = getXML($mls);
				if($mls_name != null && $mls_name == $data->mlsname)
				{
					$mls_info['mlsname'] = (string) $data->mlsname;
					$mls_info['retsurl'] = (string) $data->retsurl;
					$mls_info['retsuser'] = (string) $data->retsuser;
					$mls_info['retspass'] = (string) $data->retspass;
					$mls_info['mlsclass'] = (string) $data->mlsclass;
					$mls_info['mlsdatabase'] = (string) $data->mlsdatabase;
					$mls_info['mlsptypes'] = (string) $data->mlsptypes;
					$mls_info['retsoffset'] = (string) $data->retsoffset;
					$mls_info['retsprofile'] = (string) $data->retsprofile;
				}
				elseif($mls_name == null)
				{
					$mls_info[$count]['mlsname'] = (string) $data->mlsname;
					$mls_info[$count]['retsurl'] = (string) $data->retsurl;
					$mls_info[$count]['retsuser'] = (string) $data->retsuser;
					$mls_info[$count]['retspass'] = (string) $data->retspass;
					$mls_info[$count]['mlsclass'] = (string) $data->mlsclass;
					$mls_info[$count]['mlsdatabase'] = (string) $data->mlsdatabase;
					$mls_info[$count]['mlsptypes'] = (string) $data->mlsptypes;
					$mls_info[$count]['retsoffset'] = (string) $data->retsoffset;
					$mls_info[$count]['retsprofile'] = (string) $data->retsprofile;
					$count++;
				}
			}
		}
		return $mls_info;
	}
	
	/** 
    * Sets Gets data from xml file
    * 
	* @param string $field the xml node which will be returned
	* @param bool $echo if true echo data instead of return
    * @return string the result of node request
    */  
	public function getData($field, $echo=false)
	{
		$mls_data = getXML(MLSMANAGEMENTFILE);
		if(is_object($mls_data->field))
		{	
			if($echo != false)
			{
				echo $mls_data->$field;
			}
			else
			{
				return $mls_data->$field;
			}
		}
		else
		{
			return false;
		}
	}

	public function writeLogs($filename, $logs)
	{
		$fh = fopen($filename, 'a');
		fwrite($fh, $logs);
		fclose($fh);
	}
}
?>