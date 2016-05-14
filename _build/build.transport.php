<?php
/**
 * Build script to create the transport package
 */
$tstart = microtime(true);
set_time_limit(0);

// Define package names
define('PKG_NAME', 'ResourceHider');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '0.2.1');
define('PKG_RELEASE', 'dev');

// Define build paths
$root = dirname(dirname(__FILE__)) . '/';
$sources = array(
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'resolvers' => $root . '_build/resolvers/',
    'docs' => $root.'core/components/'. PKG_NAME_LOWER .'/docs/',
    'chunks' => $root . 'core/components/'. PKG_NAME_LOWER .'/chunks/',
    'lexicon' => $root . 'core/components/'. PKG_NAME_LOWER .'/lexicon/',
    'elements' => $root.'core/components/'. PKG_NAME_LOWER .'/elements/',
    'source_assets' => $root.'assets/components/'. PKG_NAME_LOWER,
    'manager_assets' => $root.'manager/components/'. PKG_NAME_LOWER,
    'source_core' => $root.'core/components/'. PKG_NAME_LOWER,
);
unset($root);

// Override with your own defines here (see build.config.sample.php)
require_once $sources['build'] . 'build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/helper.php';

$modx = new modX();
$modx->initialize('mgr');
// Used for nice formatting of log messages
if (!XPDO_CLI_MODE) {
    echo '<pre>';
}
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/'. PKG_NAME_LOWER .'/');

// Create category
/** @var $category modCategory */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

// Add plugins
$plugins = include $sources['data'].'transport.plugins.php';
if (!is_array($plugins)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding plugins failed.');
}
$category->addMany($plugins);
$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($plugins).' plugins.');
flush();
unset($plugins);

// Create category vehicle
$attr = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Plugins' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
                'PluginEvents' => array(
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => false,
                    xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
                ),
            ),
        ),
    ),
);
$vehicle = $builder->createVehicle($category, $attr);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to category...');
$vehicle->resolve('file', array(
    'source' => $sources['manager_assets'],
    'target' => "return MODX_MANAGER_PATH . 'components/';",
));
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$builder->putVehicle($vehicle);

// Load menu
$modx->log(modX::LOG_LEVEL_INFO, 'Packaging in menu...');
$menu = include $sources['data'] . 'transport.menu.php';
if (empty($menu)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu.');
}
$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace', 'controller'),
        ),
    ),
));
$modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP resolvers...');
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'] . 'master.php',
));
$builder->putVehicle($vehicle);
unset($vehicle, $menu);

// Now pack in the license file, readme and setup options
$modx->log(modX::LOG_LEVEL_INFO, 'Adding package attributes and setup options...');
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    /*'setup-options' => array(
        'source' => $sources['build'] . 'setup.options.php',
    ),*/
));

// Zip up package
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
$builder->pack();

$tend = microtime(true);
$totalTime = sprintf("%2.4f s", ($tend - $tstart));
$modx->log(modX::LOG_LEVEL_INFO, "\n\nPackage Built. \nExecution time: {$totalTime}\n");
if (!XPDO_CLI_MODE) {
    echo '</pre>';
}
exit();
