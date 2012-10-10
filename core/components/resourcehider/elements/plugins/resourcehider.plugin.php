<?php
/**
 * ResourceHider Plugin
 *
 * @var modX $modx
 * @event OnDocFormPrerender
 * @package resourcehider
 */
if ($modx->context->key != 'mgr') return;
$params = $modx->event->params;
if ($params['mode'] !== modSystemEvent::MODE_UPD) return;

$rh = $modx->getService('resourcehider', 'ResourceHider', $modx->getOption('resourcehider.core_path', null, $modx->getOption('core_path') . 'components/resourcehider/') . 'model/resourcehider/');
if (!($rh instanceof ResourceHider)) return;

$objectArray = $params['resource']->toArray();
$modx->regClientStartupScript($rh->config['js_url'] . 'mgr/resourcehider.js');
$modx->regClientStartupScript('<script type="text/javascript">
    Ext.onReady(function() {
        ResourceHider.config = '. $modx->toJSON($rh->config) .';
        Ext.applyIf(MODx.lang, '. $modx->toJSON($modx->lexicon->loadCache('resourcehider')) .');

        var modAB = Ext.getCmp("modx-action-buttons");
        if (modAB) {
            modAB.insert(0, new ResourceHider.Menu({
                record: '. $modx->toJSON($objectArray) .'
            }));
            // Keep the spacing between buttons
            modAB.insert(1, "-");
            modAB.doLayout();
        }
    });
</script>');

return;
