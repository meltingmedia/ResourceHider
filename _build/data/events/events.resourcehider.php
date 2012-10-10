<?php
/**
 * Adds events to ResourceHider plugin
 *
 * @var modX $modx
 * @package resourcehider
 * @subpackage build
 */
$events = array();

$events['OnDocFormPrerender'] = $modx->newObject('modPluginEvent');
$events['OnDocFormPrerender']->fromArray(array(
    'event' => 'OnDocFormPrerender',
    'priority' => 0,
    'propertyset' => 0,
), '', true, true);

return $events;
