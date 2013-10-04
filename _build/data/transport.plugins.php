<?php
/**
 * Package in the plugins
 *
 * @var modX $modx
 * @var array $sources
 * @package resourcehider
 * @subpackage build
 */
$plugins = array();

/** @var modPlugin $ResourceHider */
$ResourceHider = $modx->newObject('modPlugin');
$ResourceHider->set('id', 1);
$ResourceHider->set('name', 'ResourceHider');
$ResourceHider->set('description', 'Helps you hide/show resources in the resource tree.');
$ResourceHider->set('plugincode', Helper::getPHPContent($sources['elements'] . 'plugins/resourcehider.plugin.php'));
//$ResourceHider->set('category', 0);

$events = include $sources['data'] . 'events/events.resourcehider.php';
if (is_array($events) && !empty($events)) {
    $ResourceHider->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in '.count($events).' Plugin Events for ResourceHider.');
    flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events for ResourceHider!');
}
unset($events);

$plugins[] = $ResourceHider;

return $plugins;
