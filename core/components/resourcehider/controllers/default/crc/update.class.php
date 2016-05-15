<?php
/**
 * Update controller
 */
class HiddenChildrenUpdateManagerController extends ResourceUpdateManagerController
{
    /**
     * @var ResourceHider $resourcehider
     */
    public $resourcehider;

    public function initialize()
    {
        $path = $this->modx->getOption(
            'resourcehider.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/resourcehider/'
        );
        $this->resourcehider = $this->modx->getService('resourcehider', 'model.resourcehider.ResourceHider', $path);

        parent::initialize();
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->resourcehider->config['mgr_js_url'] . 'resourcehidercrc.js');
        $this->addCss($this->resourcehider->config['mgr_css_url'] . 'resourcehider-crc.css');

        // Load default form
        parent::loadCustomCssJs();

        // Add our stuff
        $this->addHtml(
<<<HTML
<script>
    Ext.onReady(function() {
        ResourceHider.config = {$this->modx->toJSON($this->resourcehider->config)};
        ResourceHider.load({$this->resource->id});
    });
</script>
HTML
        );
    }

    public function getLanguageTopics()
    {
        return array_merge(
            parent::getLanguageTopics(),
            array('resourcehider:default')
        );
    }
}
