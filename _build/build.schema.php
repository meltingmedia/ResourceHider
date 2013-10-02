<?php
/**
 * Build Schema script
 *
 * @package cmpstarter
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

// define package name
define('PKG_NAME', 'ResourceHider');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
// define sources
$root = dirname(dirname(__FILE__)) . '/';
$db = 'mysql';
$sources = array(
    'root' => $root,
    'core' => $root . 'core/components/'. PKG_NAME_LOWER .'/',
    'model' => $root . 'core/components/'. PKG_NAME_LOWER .'/model/',
    'schema' => $root . 'core/components/'. PKG_NAME_LOWER .'/model/schema/',
    'schema_file' => dirname(__FILE__) . '/schema/' . PKG_NAME_LOWER . ".{$db}.schema.xml",
    'assets' => $root . 'assets/components/'. PKG_NAME_LOWER .'/',
);

require_once dirname(__FILE__) . '/build.config.php';
include_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->loadClass('transport.modPackageBuilder', '', false, true);
echo '<pre>'; // used for nice formatting of log messages
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$manager = $modx->getManager();
$generator = $manager->getGenerator();

if (!is_dir($sources['model'])) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Model directory not found!');
    die();
}
if (!file_exists($sources['schema_file'])) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Schema file not found!');
    die();
}


$generator->classTemplate = <<<EOD
<?php

class [+class+] extends [+extends+]
{

}
EOD;
$generator->platformTemplate = <<<EOD
<?php

require_once (dirname(dirname(__FILE__)) . '/[+class-lowercase+].class.php');

class [+class+]_[+platform+] extends [+class+]
{

}
EOD;
$generator->mapHeader = <<<EOD
<?php

EOD;
$generator->metaTemplate = <<<EOD
<?php

\$xpdo_meta_map = [+map+];
EOD;

$generator->parseSchema($sources['schema_file'], $sources['model']);

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

echo "\nExecution time: {$totalTime}\n";

exit ();
