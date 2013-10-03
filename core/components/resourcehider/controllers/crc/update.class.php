<?php
/**
 * Update controller
 */
class HiddenChildrenUpdateManagerController extends ResourceUpdateManagerController
{
    /** @var ResourceHider $resourcehider */
    public $resourcehider;

    public function loadCustomCssJs()
    {
        // Get the ResourceHider service + load required assets
        $path = $this->modx->getOption(
            'resourcehider.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/resourcehider/'
        ). 'model/resourcehider/';
        $this->resourcehider = $this->modx->resourcehider = $this->modx->getService('resourcehider', 'ResourceHider', $path);
        $this->addJavascript($this->resourcehider->config['mgr_js_url'] . 'resourcehidercrc.js');

        // Load default form
        parent::loadCustomCssJs();

        // Add our stuff
        $this->addHtml('<script type="text/javascript">
            Ext.onReady(function() {
                ResourceHider.config = '. $this->modx->toJSON($this->resourcehider->config) .';
                Ext.applyIf(MODx.lang, '. $this->modx->toJSON($this->modx->lexicon->loadCache('resourcehider')) .');
                ResourceHider.load('. $this->resource->id .');
            });
        </script>');
    }
}
