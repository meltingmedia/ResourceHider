<?php

class HiddenChildrenUpdateManagerController extends ResourceUpdateManagerController
{
    /** @var ResourceHider $resourcehider */
    public $resourcehider;

    public function loadCustomCssJs()
    {
        $path = $this->modx->getOption(
                'resourcehider.core_path',
                null,
                $this->modx->getOption('core_path') . 'components/resourcehider/'
        ). 'model/resourcehider/';
        $this->resourcehider = $this->modx->resourcehider = $this->modx->getService('resourcehider', 'ResourceHider', $path);
        $this->addJavascript($this->resourcehider->config['js_url'] . 'mgr/resourcehidercmp.js');

        parent::loadCustomCssJs();
        $this->addHtml('<script type="text/javascript">
            Ext.onReady(function() {
                ResourceHider.config = '. $this->modx->toJSON($this->resourcehider->config) .';
                Ext.applyIf(MODx.lang, '. $this->modx->toJSON($this->modx->lexicon->loadCache('resourcehider')) .');

                Ext.getCmp("modx-panel-resource").add({
                    title: "test"
                    ,style: "margin-top: 10px"
                    ,layout: "form"
                    ,bodyCssClass: "main-wrapper"
                    ,autoHeight: true
                    ,collapsible: true
                    ,animCollapse: false
                    ,hideMode: "offsets"
                    ,items: [{
                        xtype: "resourcehider-grid"
                        ,resource: '. $this->resource->id .'
                    }]
                });
            });
        </script>');
    }
}
