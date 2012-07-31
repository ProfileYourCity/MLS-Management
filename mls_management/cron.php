<?php

require_once("inc/common.php");

/** 
* This conditional is used to download the mls data and update the database. It needs to be replaced with a more secure method. For now it exists so the cronjob can update the database every day.
**/
if(empty($_SERVER['argv'][1]))
{
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
				echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL=http://www.profileidx.com/index.php?id=mls_management&mls_name='.$_GET['mls_name'].'&extract&rets&password='.$_GET['password'].'&offset='.$offset.'">';
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

?>