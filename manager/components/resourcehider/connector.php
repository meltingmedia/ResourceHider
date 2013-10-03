<?php
/**
 * ResourceHider Connector
 *
 * @var modX $modx
 * @package resourcehider
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('resourcehider.core_path', null, $modx->getOption('core_path') . 'components/resourcehider/');
require_once $corePath . 'model/resourcehider/resourcehider.class.php';

$modx->resourcehider = new ResourceHider($modx);

$modx->lexicon->load('resourcehider:default');

// handle request
$path = $modx->getOption('processors_path', $modx->resourcehider->config, $corePath . 'processors/');
$location = $modx->context->get('key') == 'mgr' ? 'mgr' : '';
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => $location,
));
