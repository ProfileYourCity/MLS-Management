<?php

/* Include admin/inc/common.php if it is not loaded
 * - Check if global $rootPath var is set. If not set root path from actual file location. Include admin/inc/common.php if not loaded. 
 *
 */
$rootPath = $_SERVER['DOCUMENT_ROOT'];
if(!function_exists('getXML'))
{
    require_once($rootPath.'/admin/inc/common.php');
    function add_action(){}
    function add_filter(){}
}

/* Define Constants
 * 
 */
define('MLSPLUGINFOLDER', GSPLUGINPATH.'mls_management');
define('MLSPLUGINNAME', 'MLS Management');
define('MLSPLUGINID', 'mls_management');
/*
if(!isset($thisfile)) { $plugin_file = GSPLUGINPATH.MLSPLUGINID.'php'; } else { $plugin_file =  $thisfile; }
define('MLSFILE', $plugin_file);
*/
define('MLSMANAGEMENTFILE', GSDATAOTHERPATH.'mls_management.xml');
define('MLSFOLDER', GSDATAPATH.'mls/');
define('UPDATELOGPATH', GSDATAPATH  . 'logs/mlsPropertyUpdate.txt');
define('MLSCOLUMNSFOLDER', GSDATAPATH.'mls/columns/');

/* Include all the primary class files
 *
 */
$primaryClassFiles = glob(MLSPLUGINFOLDER.'/class/primary/*.php');
foreach($primaryClassFiles as $primaryClassFile)
{
	require_once($primaryClassFile);
}

/* Include all secondary class files
 *
 */
$classFiles = glob(MLSPLUGINFOLDER.'/class/*.php');
foreach($classFiles as $classFile)
{
	require_once($classFile);
}

/* Include all inc files in the inc folder 
 *
 */
$incFiles = glob(MLSPLUGINFOLDER.'/inc/*.php');
foreach($incFiles as $incFile)
{
	require_once($incFile);
}

/* Add Hooks & Filters. Register Scripts & Styles
 *
 * add_action('settings-sidebar','createSideMenu',array($thisfile, MLSPLUGINNAME)); 
 * add_action('nav-tab','makeNavTab');
 * add_filter('content','content_filter_function');
*/
global $SITEURL;
if(function_exists('register_script'))
{
    register_script(MLSPLUGINNAME.'_js', $SITEURL.'/plugins/'.MLSPLUGINID.'/js/admin_js.js', '1.0', TRUE);
    queue_script(MLSPLUGINNAME.'_js', GSBACK);
    register_style(MLSPLUGINNAME.'_css', $SITEURL.'/plugins/'.MLSPLUGINID.'/css/admin_styles.css', '1.0', 'screen');
    queue_style(MLSPLUGINNAME.'_css', GSBACK);  
}
/* Cross Compatibility */ 
else
{
    add_action('header', 'addStyleP', array($SITEURL.'/plugins/'.MLSPLUGINID.'/css/admin_styles.css'));
    add_action('header', 'addScriptP', array($SITEURL.'/plugins/'.MLSPLUGINID.'/js/admin_js.js'));

    function addStyleP($stylesheet)
    {
        $css = '<link href="'.$stylesheet.'" rel="stylesheet" type="text/css" />';
        echo $css;
    }
    
    function addScriptP($script)
    {
        $script = '<script type="text/javascript" src="'.$script.'"></script>';
        echo $script;
    }
}

/** 
* Displays admin navigation
*
* @return void
*/
function mls_management_admin_navigation()
{
    $action = isset($_GET['action']) ? $_GET['action'] : 'manage';
    ?>
    <script type="text/javascript">
        $('body').addClass('<?php echo MLSPLUGINID; ?>');
        function decision(message, url){
            if(confirm(message)) location.href = url;
        }
    </script>
    <div style="width:100%;margin:0 -15px -15px -10px;padding:0px;">
        <h3  class="floated"><?php echo MLSPLUGINNAME; ?></h3>
        <div class="edit-nav clearfix">
            <p>
				<?php exec_action('mls_management-addtab'); ?>
                <a href="load.php?id=<?php echo MLSPLUGINID; ?>&action=settings" class="<?php echo ($action == 'settings') ? 'current' : ''; ?>">Settings</a>
                <a href="load.php?id=<?php echo MLSPLUGINID; ?>&action=query" class="<?php echo ($action == 'query') ? 'current' : ''; ?>">Query RETS Server</a>
                <a href="load.php?id=<?php echo MLSPLUGINID; ?>&action=manage" class="<?php echo ($action == 'manage') ? 'current' : ''; ?>">Manage RETS Server</a>
            </p>
        </div>
    </div>
    </div>
    <div class="main" style="margin-top:-10px;">
    <?php
}

function initiateClass($mls)
{   
    require_once(GSPLUGINPATH.'mls_management/class/mls/'.$mls['mlsclass'].'.php');
    $initiateClass = '$mlsRetsClass = new ' . $mls['mlsclass'] . '("'.$mls['mlsdatabase'].'");'; 
    $mlsRetsClass = ''; 
    eval($initiateClass); 
    return $mlsRetsClass;
}

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
?>