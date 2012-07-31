<?php

$rets_login_url = "http://rets2.sunshinemls.com:6103/rets/login";
$rets_username = "smlsFHLD00ley2012";
$rets_password = "sd3445lkjsndfg7";
$rets_modtimestamp_field = "datelisting";

$property_classes = array("RES");

// DateTime which is used to determine how far back to retrieve records.
$previous_start_time = "1980-01-01T00:00:00";

//////////////////////////////

require_once(GSPLUGINPATH.'MLSdata/class/phRets.php');

// start rets connection
$rets = new phRETS;

$rets->AddHeader("RETS-Version", "RETS/1.7.2");

$connect = $rets->Connect($rets_login_url, $rets_username, $rets_password);

function getAvailableMLSFields($resource, $class, $rets)
{
    echo '<div class="updated"><strong>Listing Available Fields:</strong><br/><strong>Resource:</strong> '.$resource.'<br/> <strong>Class:</strong> '.$class.'</div>';
    $fields = $rets->GetMetadataTable($resource, $class);

    echo '<table><th>Field Name</th><th>Field Description</th><th>Field Type</th>';
    foreach ($fields as $field) 
    {
        echo "<tr><td>{$field['SystemName']}</td><td>{$field['LongName']}</td><td>{$field['DataType']}</td></tr>";
    }
    echo '</table>';
}

if(isset($_GET['query']))
{   
    $ExtractData = new extractData;
    ?>
    <h3  class="floated">MLS Management</h3>
    <div class="edit-nav clearfix">
        <p>
            <a href="load.php?id=MLSdata&query&getFields">Get MLS Fields</a>
        </p>
    </div>
    <?php
    if(isset($_GET['getFields']))
    {
        $resources = $rets->GetMetadataResources();
        ?>
        <form action="load.php?id=MLSdata&amp;query&amp;getFields" method="post" id="metadata_window">
        <div class="leftopt">
            <p>
                <label>Resource: </label>
                <select class="text short disabledQuery" name="fieldResource">
                    <?php
                    foreach($resources as $resource)
                    {
                        if(isset($_POST['fieldResource']) && $_POST['fieldResource'] == $resource['ResourceID'])
                        {
                            echo '<option value="'.$resource['ResourceID'].'" selected>'.$resource['Description'].' ('.$resource['ResourceID'].')</option>';
                        }
                        else
                        {
                            echo '<option value="'.$resource['ResourceID'].'">'.$resource['Description'].' ('.$resource['ResourceID'].')</option>';
                        }
                    }
                    ?>  
                </select>
            </p>
            <p>
                <input type="submit" class="submit submitQuery" value="Start Query" style="width:auto;float:left" />
                <?php if(isset($_POST['fieldResource'])){ echo '<a href="load.php?id=MLSdata&query&getFields" style="display:inline-block;float:left;margin-left:5px;padding-top:2px;">Reset Query</a>'; } ?>
            </p>
        </div>
        <?php if(isset($_POST['fieldResource'])) {  ?>
        <div class="rightopt">
            <p>
                <label>Class: </label>
                <select class="text short" name="fieldClass">
                    <?php
                    $classes = $rets->GetMetadataClasses($_POST['fieldResource']);
                    foreach($classes as $class)
                    {
                        if(isset($_POST['fieldClass']) && $_POST['fieldClass'] == $class['ClassName'])
                        {
                            echo '<option value="'.$class['ClassName'].'">'.$class['Description'].' ('.$class['ClassName'].')</option>';
                        }
                        else
                        {
                            echo '<option value="'.$class['ClassName'].'">'.$class['Description'].' ('.$class['ClassName'].')</option>';
                        }
                    }
                    ?>
                </select>
            </p>
        </div>
        <?php } ?>
        </form>
        <div style="clear:both"></div>
        <?php
        if(isset($_POST['fieldResource']) && isset($_POST['fieldClass']))
        {
            getAvailableMLSFields($_POST['fieldResource'],$_POST['fieldClass'], $rets); 
        }
    }

}

//For future use
function getAgentsCSVformat()
{
    $query = "(status=|871)";
    $search_agent = $rets->SearchQuery("Agent","AGT","agentid=1+");
    $count = 0;
    while ($listing = $rets->FetchRow($search_agent)) 
    {
        $count++;
        echo "{$listing['fname']},{$listing['lname']},{$listing['email']},{$listing['agentphone']}\n";
    }
}


if(isset($_GET['extract_rets']))
{
    $mlsData = getRetsMLSdata();
    $ExtractData->insertRetsData($update=true,$table, $mlsData);
    //sendEmailUpdates();
}


function getRetsMLSdata($rets)
{
    $search = $rets->SearchQuery("Property","RES","(datelisting=2012-01-01+)",
    array("Limit" => "1", "Offset" => "1500")
    );
    while ($listing = $rets->FetchRow($search)) {
        echo '<pre>';
        print_r($listing);
        echo '</pre>';
    }
    $rets->FreeResult($search);
}
?>