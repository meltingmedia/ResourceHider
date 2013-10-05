<?php
/**
 * Package in the plugins
 *
 * @var modX $modx
 * @var array $sources
 */
$plugins = array();

$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->set('id', 1);
$plugins[0]->set('name', 'ResourceHider');
$plugins[0]->set('description', 'Helps you hide/show resources in the resource tree.');
$plugins[0]->set('plugincode', Helper::getPHPContent($sources['elements'] . 'plugins/resourcehider.plugin.php'));
//$ResourceHider->set('category', 0);

$events = include $sources['data'] . 'events/events.resourcehider.php';
if (is_array($events) && !empty($events)) {
    $plugins[0]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in '.count($events).' Plugin Events for ResourceHider.');
    flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events for ResourceHider!');
}
unset($events);

return $plugins;
