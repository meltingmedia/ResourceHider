<?php
/**
 * ResourceHider Plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @event OnDocFormPrerender
 * @event OnBeforeManagerPageInit
 */

$path = $modx->getOption('resourcehider.core_path', null, $modx->getOption('core_path') . 'components/resourcehider/');
/** @var ResourceHider $service */
$service = $modx->getService('resourcehider', 'model.resourcehider.ResourceHider', $path);

if (method_exists($service, $modx->event->name)) {
    $service->{$modx->event->name}();
}
