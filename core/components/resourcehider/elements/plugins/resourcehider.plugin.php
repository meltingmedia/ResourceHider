<?php
/**
 * ResourceHider Plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @event OnDocFormPrerender
 */

$params = $modx->event->params;
// Only when updating a resource
if ($params['mode'] !== modSystemEvent::MODE_UPD) {
    return;
}
/** @var modResource $resource */
$resource =& $params['resource'];
// Get the service
$path = $modx->getOption('resourcehider.core_path', null, $modx->getOption('core_path') . 'components/resourcehider/'). 'model/resourcehider/';
$rh = $modx->getService('resourcehider', 'ResourceHider', $path);
if (!($rh instanceof ResourceHider)) {
    return;
}
// Make sure the resource is in a context where Resource Hider is allowed
$allowedContexts = $rh->config['allowed_contexts'];
if (!empty($allowedContexts)) {
    if (!in_array($resource->get('context_key'), $allowedContexts)) {
        return;
    }
}
// Make sure the current resource uses an allowed class key
if (!in_array($resource->get('class_key'), $rh->config['allowed_classes'])) {
    return;
}

// Define the appropriate action according to the parent being a container with hidden children or not
$parent = $resource->getOne('Parent');
if ($parent && $parent->get('class_key') === 'HiddenChildren') {
    // Back to container button
    $load = 'ResourceHider.loadBack();';
} else {
    // Normal split button
    $objectArray = $resource->toArray();
    $load = 'ResourceHider.load('. $modx->toJSON($objectArray) .');';
}

$modx->controller->addLexiconTopic('resourcehider:default');
// Seems like we are good to display the button
$modx->regClientStartupScript($rh->config['mgr_js_url'] . 'resourcehider.js');
$modx->regClientStartupScript(
<<<HTML
<script>
    ResourceHider.config = {$modx->toJSON($rh->config)};
    Ext.onReady(function() {
        {$load}
    });
</script>
HTML
);

return;
