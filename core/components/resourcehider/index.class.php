<?php

abstract class ResourceHiderManagerController extends modExtraManagerController
{
    /**
     * @var ResourceHider $service
     */
    public $service;
    /**
     * @var string $jsURL The URL for the JS assets for the manager
     */
    public $jsURL;
    /**
     * @var string $cssURL The URL for the CSS assets for the manager
     */
    public $cssURL;

    /**
     * Get the current modX version
     *
     * @return array
     */
    public static function getModxVersion()
    {
        return @include_once MODX_CORE_PATH . "docs/version.inc.php";
    }

    public function initialize()
    {
        $path = $this->modx->getOption(
            'resourcehider.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/resourcehider/'
        );
        $this->service =& $this->modx->getService('resourcehider', 'model.resourcehider.ResourceHider', $path);
        $this->jsURL = $this->service->config['mgr_js_url'];
        $this->cssURL = $this->service->config['mgr_css_url'];

        $this->addJavascript($this->jsURL . 'resourcehidercmp.js');
        $this->addHtml(
<<<HTML
<script>
    Ext.onReady(function() {
        ResourceHider.config = {$this->modx->toJSON($this->service->config)};
    });
</script>
HTML
        );
        parent::initialize();
    }

    /**
     * Get the appropriate action for the current MODX version
     *
     * @return string The action
     */
    public function getAction()
    {
        $version = $this->modx->getVersionData();
        if (version_compare($version['full_version'], '2.3.0-dev') >= 0) {
            return '\'?namespace=resourcehider\'';
        }

        return 'MODx.action["resourcehider:index"]';
    }

    public function getLanguageTopics()
    {
        return array('resourcehider:default');
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('resourcehider');
    }
}


class IndexManagerController extends ResourceHiderManagerController
{
    public static function getDefaultController()
    {
        $version = self::getModxVersion();
        if (version_compare($version['full_version'], '2.3.0') >= 0) {
            return 'home';
        }

        return 'default/home';
    }
}
