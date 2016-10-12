<?php
/**
 * Connector to handle ajax requests (mostly for the manager) to processors
 */
$config = dirname(dirname(dirname(__DIR__))) . '/config.core.php';
if (file_exists($config)) {
    // Dev config
    require_once $config;
} else {
    // Manager config
    require_once dirname(dirname(__DIR__)) . '/config.core.php';
}

require_once MODX_CORE_PATH .'config/'. MODX_CONFIG_KEY .'.inc.php';
require_once MODX_CONNECTORS_PATH .'index.php';

/**
 * @var modX $modx A modX instance
 * @var string $ctx The context key
 * @var string $ml The manager language
 * @var string $connectorRequestClass The connector request class name used to handle the current request
 */


$service = 'resourcehider';
$path = $modx->getOption(
    "{$service}.core_path",
    null,
    $modx->getOption('core_path') . "components/{$service}/"
);
$modx->getService($service, "model.{$service}.ResourceHider", $path);

// Handle request
$modx->request->handleRequest(array(
    'processors_path' => $modx->getOption(
        'processors_path',
        $modx->{$service}->config,
        $path . 'processors/'
    ),
    'location' => $ctx === 'mgr' ? 'mgr' : '',
));
