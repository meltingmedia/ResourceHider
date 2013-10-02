<?php
/**
 * @package resourcehider
 * @subpackage controllers
 */
require_once dirname(__FILE__) . '/model/resourcehider/resourcehider.class.php';

class IndexManagerController extends modExtraManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}

abstract class ResourceHiderManagerController extends modManagerController
{
    /** @var ResourceHider $resourcehider */
    public $resourcehider;

    public function initialize()
    {
        $this->resourcehider = new ResourceHider($this->modx);

        $this->addJavascript($this->resourcehider->config['js_url'] . 'mgr/resourcehidercmp.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            ResourceHider.config = '. $this->modx->toJSON($this->resourcehider->config) .';
        });
        </script>');
        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return array('resourcehider:default');
    }

    public function checkPermissions()
    {
        return true;
    }
}
