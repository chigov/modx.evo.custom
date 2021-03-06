<?php
$installMode = intval($_POST['installmode']);

if ($installMode == 0 || $installMode == 2) {
    $database_collation = isset($_POST['database_collation']) ? $_POST['database_collation'] : 'utf8_general_ci';
    $database_charset = substr($database_collation, 0, strpos($database_collation, '_'));
    $_POST['database_connection_charset'] = $database_charset;
    if(empty($_SESSION['databaseloginpassword']))
        $_SESSION['databaseloginpassword'] = $_POST['databaseloginpassword'];
    if(empty($_SESSION['databaseloginname']))
        $_SESSION['databaseloginname'] = $_POST['databaseloginname'];
}
elseif ($installMode == 1) {
    include $base_path . MGR_DIR . '/includes/config.inc.php';
    if (@ $conn = mysqli_connect($database_server, $database_user, $database_password)) {
        if (@ mysqli_query($conn, "USE {$dbase}")) {
            if (!$rs = mysqli_query($conn, "show session variables like 'collation_database'")) {
                $rs = mysqli_query($conn, "show session variables like 'collation_server'");
            }
            if ($rs && $collation = mysqli_fetch_row($rs)) {
                $database_collation = trim($collation[1]);
            }
        }
    }
    if (empty ($database_collation)) $database_collation = 'utf8_general_ci';
    
    $database_charset = substr($database_collation, 0, strpos($database_collation, '_'));
    if (!isset ($database_connection_charset) || empty ($database_connection_charset)) {
        $database_connection_charset = $database_charset;
    }

    if (!isset ($database_connection_method) || empty ($database_connection_method)) {
        $database_connection_method = 'SET CHARACTER SET';
        if (function_exists('mysqli_set_charset')) mysqli_set_charset($conn, $database_connection_charset);
    }
    if ($database_connection_method != 'SET NAMES' && $database_connection_charset != $database_charset) {
        $database_connection_method = 'SET NAMES';
    }

    $_POST['database_name'] = $dbase;
    $_POST['tableprefix'] = $table_prefix;
    $_POST['database_connection_charset'] = $database_connection_charset;
    $_POST['database_connection_method'] = $database_connection_method;
    $_POST['databasehost'] = $database_server;
    $_SESSION['databaseloginname'] = $database_user;
    $_SESSION['databaseloginpassword'] = $database_password;
}

$ph['install_language'] = $install_language;
$ph['manager_language'] = $manager_language;
$ph['installMode'] = $installMode;
$ph['database_name'] = trim($_POST['database_name'], '`');
$ph['tableprefix'] = $_POST['tableprefix'];
$ph['database_collation'] = $_POST['database_collation'];
$ph['database_connection_charset'] = $_POST['database_connection_charset'];
$ph['database_connection_method'] = $_POST['database_connection_method'];
$ph['databasehost'] = $_POST['databasehost'];
$ph['cmsadmin'] = trim($_POST['cmsadmin']);
$ph['cmsadminemail'] = trim($_POST['cmsadminemail']);
$ph['cmspassword'] = trim($_POST['cmspassword']);
$ph['cmspasswordconfirm'] = trim($_POST['cmspasswordconfirm']);

$ph['checked'] = isset ($_POST['installdata']) && $_POST['installdata'] == "1" ? 'checked' : '';

# load setup information file
include($base_path . 'install/setup.info.php');
$ph['templates'] = getTemplates($moduleTemplates);
$ph['tvs']       = getTVs($moduleTVs);
$ph['chunks']    = getChunks($moduleChunks);
$ph['modules']   = getModules($moduleModules);
$ph['plugins']   = getPlugins($modulePlugins);
$ph['snippets']  = getSnippets($moduleSnippets);

$ph['action'] = ($installMode == 1) ? 'mode' : 'connection';

$tpl = file_get_contents($base_path . 'install/actions/tpl_options.html');
$content = parse($tpl,$ph);
echo parse($content,$_lang,'[%','%]');



function getTemplates($presets=array()) {
	if(!count($presets)) return '';
    $selectedTemplates = isset ($_POST['template']) ? $_POST['template'] : array ();
    $tpl = '<label><input type="checkbox" name="template[]" value="[+i+]" class="[+class+]" [+checked+] />[%install_update%] <span class="comname">[+name+]</span> - [+desc+]</label><hr />';
    $_ = array();
    $i = 0;
    foreach ($presets as $preset) {
        $ph['i']     = $i;
        $ph['name']  = $preset[0];
        $ph['desc']  = $preset[1];
        $ph['class'] = !in_array('sample', $preset[6]) ? 'toggle' : 'toggle demo';
        $ph['checked']   = in_array($i, $selectedTemplates) || (!isset($_POST['options_selected'])) ? 'checked' : '';
        $_[] = parse($tpl,$ph);
        $i++;
    }
    if(0<count($_)) return '<h3>[%templates%]</h3>' . join("\n", $_);
}

function getTVs($presets=array()) {
    if(!count($presets)) return '';
    $selectedTvs = isset ($_POST['tv']) ? $_POST['tv'] : array ();
    $tpl = '<label><input type="checkbox" name="tv[]" value="[+i+]" class="[+class+]" [+checked+] />[%install_update%] <span class="comname">[+name+]</span> - [+alterName+] <span class="description">([+desc+])</span></label><hr />';
    $_ = array();
    $i = 0;
    foreach ($presets as $preset) {
        $ph['i']         = $i;
        $ph['name']      = $preset[0];
        $ph['alterName'] = $preset[1];
        $ph['desc']      = $preset[2];
        $ph['class']     = !in_array('sample', $preset[12]) ? 'toggle' : 'toggle demo';
        $ph['checked']       = in_array($i, $selectedTvs) || (!isset($_POST['options_selected'])) ? 'checked' : '';
        $_[] = parse($tpl,$ph);
        $i++;
    }
    if(0<count($_)) return '<h3>[%tvs%]</h3>' . join("\n", $_);
}

// display chunks
function getChunks($presets=array()) {
	if(!count($presets)) return '';
    $selected = isset ($_POST['chunk']) ? $_POST['chunk'] : array ();
    $tpl = '<label><input type="checkbox" name="chunk[]" value="[+i+]" class="[+class+]" [+checked+] />[%install_update%] <span class="comname">[+name+]</span> - [+desc+]</label><hr />';
    $_ = array();
    $i = 0;
    foreach ($presets as $preset) {
        $ph['i']     = $i;
        $ph['name']  = $preset[0];
        $ph['desc']  = $preset[1];
        $ph['class'] = !in_array('sample', $preset[5]) ? 'toggle' : 'toggle demo';
        $ph['checked']   = in_array($i, $selected) || (!isset($_POST['options_selected'])) ? 'checked' : '';
        $_[] = parse($tpl,$ph);
        $i++;
    }
    if(0<count($_)) return '<h3>[%chunks%]</h3>' . join("\n", $_);
}

// display modules
function getModules($presets=array()) {
	if(!count($presets)) return '';
    $selected = isset ($_POST['module']) ? $_POST['module'] : array ();
    $tpl = '<label><input type="checkbox" name="module[]" value="[+i+]" class="[+class+]" [+checked+] />[%install_update%] <span class="comname">[+name+]</span> - [+desc+]</label><hr />';
    $_ = array();
    $i = 0;
    foreach ($presets as $preset) {
        $ph['i']     = $i;
        $ph['name']  = $preset[0];
        $ph['desc']  = $preset[1];
        $ph['class'] = !in_array('sample', $preset[7]) ? 'toggle' : 'toggle demo';
        $ph['checked']   = in_array($i, $selected) || (!isset($_POST['options_selected'])) ? 'checked' : '';
        $_[] = parse($tpl,$ph);
        $i++;
    }
    if(0<count($_)) return '<h3>[%modules%]</h3>' . join("\n", $_);
}

// display plugins
function getPlugins($presets=array()) {
	if(!count($presets)) return '';
    $selected = isset ($_POST['plugin']) ? $_POST['plugin'] : array ();
    $tpl = '<label><input type="checkbox" name="plugin[]" value="[+i+]" class="[+class+]" [+checked+] />[%install_update%] <span class="comname">[+name+]</span> - [+desc+]</label><hr />';
    $_ = array();
    $i = 0;
    foreach ($presets as $preset) {
        $ph['i']     = $i;
        $ph['name']  = $preset[0];
        $ph['desc']  = $preset[1];
        $ph['class'] = !in_array('sample', $preset[8]) ? 'toggle' : 'toggle demo';
        $ph['checked']   = in_array($i, $selected) || (!isset($_POST['options_selected'])) ? 'checked' : '';
        $_[] = parse($tpl,$ph);
        $i++;
    }
    if(0<count($_)) return '<h3>[%plugins%]</h3>' . join("\n", $_);
}

// display snippets
function getSnippets($presets=array()) {
	if(!count($presets)) return '';
    $selected = isset ($_POST['snippet']) ? $_POST['snippet'] : array ();
    $tpl = '<label><input type="checkbox" name="snippet[]" value="[+i+]" class="[+class+]" [+checked+] />[%install_update%] <span class="comname">[+name+]</span> - [+desc+]</label><hr />';
    $_ = array();
    $i = 0;
    foreach ($presets as $preset) {
        $ph['i']     = $i;
        $ph['name']  = $preset[0];
        $ph['desc']  = $preset[1];
        $ph['class'] = !in_array('sample', $preset[5]) ? 'toggle' : 'toggle demo';
        $ph['checked'] = in_array($i, $selected) || (!isset($_POST['options_selected'])) ? 'checked' : '';
        $_[] = parse($tpl,$ph);
        $i++;
    }
    if(0<count($_)) return '<h3>[%snippets%]</h3>' . join("\n", $_);
}
