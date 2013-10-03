<?php
/**
 * ResourceHider Plugin
 *
 * @var modX $modx
 * @event OnDocFormPrerender
 * @package resourcehider
 */
// Make sure we are in the manager
if ($modx->context->key != 'mgr') {
    return;
}
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
// Make sure the current resource used an allowed class key
if (!in_array($resource->get('class_key'), $rh->config['allowed_classes'])) {
    return;
}
// Make sure the resource is in a context where Resource Hider is allowed
$allowedContexts = $rh->config['allowed_contexts'];
if (!empty($allowedContexts)) {
    if (!in_array($resource->get('context_key'), $allowedContexts)) {
        return;
    }
}

// Make sure the parent is not a container with hidden children
$parent = $resource->getOne('Parent');
if ($parent && $parent->get('class_key') === 'HiddenChildren') {
    // @todo: back to container button ?
    return;
}

// Seems like we are good to display the button
$objectArray = $resource->toArray();
$modx->regClientStartupScript($rh->config['mgr_js_url'] . 'resourcehider.js');
$modx->regClientStartupScript('<script type="text/javascript">
    Ext.onReady(function() {
        ResourceHider.config = '. $modx->toJSON($rh->config) .';
        Ext.applyIf(MODx.lang, '. $modx->toJSON($modx->lexicon->loadCache('resourcehider')) .');
        ResourceHider.load('. $modx->toJSON($objectArray) .');
    });
</script>');

return;
