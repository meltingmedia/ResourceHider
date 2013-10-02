<?php
/**
 * @package resourcehider
 * @subpackage controllers
 */
class ResourceHiderHomeManagerController extends ResourceHiderManagerController
{
    public function process(array $scriptProperties = array())
    {

    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('resourcehider');
    }

    public function loadCustomCssJs()
    {
        $this->addHtml('<script type="text/javascript">
            Ext.onReady(function() {
                MODx.add("resourcehider-cmp");
            });
        </script>');
    }

    public function getTemplateFile() {
        return '';
    }
}
