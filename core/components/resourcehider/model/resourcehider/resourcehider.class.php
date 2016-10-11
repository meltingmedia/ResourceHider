<?php

//require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Resource Hider service class
 */
class ResourceHider
{
    /**
     * @var modX $modx
     */
    public $modx;

    /**
     * Constructs the ResourceHider object
     *
     * @param modX $modx A reference to the modX object
     * @param array $config An array of configuration options
     */
    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;
        $this->prefix = $prefix = strtolower(get_class($this));

        $basePath = $this->modx->getOption("{$prefix}.core_path", $config, $this->modx->getOption('core_path') . "components/{$prefix}/");
        $assetsUrl = $this->modx->getOption("{$prefix}.assets_url" , $config, $this->modx->getOption('assets_url') . "components/{$prefix}/");
        $managerUrl = $this->modx->getOption("{$prefix}.manager_url" , $config, $this->modx->getOption('manager_url') . "components/{$prefix}/");

        $classes = $this->modx->getOption('resourcehider.allowed_classes', $config, 'modDocument, modResource, modSymLink, modWebLink, modStaticResource');
        $allowedClasses = array_map('trim', explode(',', $classes));

        $allowedContexts = array();
        $ctxs = $this->modx->getOption('resourcehider.allowed_contexts', $config);
        if ($ctxs) {
            $allowedContexts = array_map('trim', explode(',', $ctxs));
        }

        $this->config = array_merge(array(
            'base_path' => $basePath,
            'core_path' => $basePath,
            'model_path' => $basePath . 'model/',
            'processors_path' => $basePath . 'processors/',

            'add_package' => true,

            'migrations_path' => $basePath . 'migrations/',

            'vendor_path' => $basePath . 'vendor/',

            'js_url' => $assetsUrl . 'js/',
            'css_url' => $assetsUrl . 'css/',
            'assets_url' => $assetsUrl,

            'connector_url' => $managerUrl . 'connector.php',
            'mgr_js_url' => $managerUrl . 'js/',
            'mgr_css_url' => $managerUrl . 'css/',

            'allowed_classes' => $allowedClasses,
            'allowed_contexts' => $allowedContexts,
            'show_status' => $this->modx->getOption('resourcehider.show_status', null, false),

            // CRC config
            //'target' => $this->getOption('resourcehider.crc_target', null, 'panel'),
            'target' => $this->getOption('resourcehider.crc_target', null, 'tabs'),
            'content_action' => $this->getOption('resourcehider.crc_content_action', null, 'none'),
            'insert_idx' => $this->getOption('resourcehider.crc_insert_idx', null, 'last'),
            'set_active_tab' => $this->getOption('resourcehider.crc_set_active_tab', null, false),
        ), $config);

        $this->modx->lexicon->load($this->prefix . ':default');

        if ($this->config['add_package']) {
            $this->modx->addPackage($this->prefix, $this->config['model_path']);
        }
    }

    /**
     * @see ResourceManagerController::firePreRenderEvents()
     */
    public function OnDocFormPrerender()
    {
        $params = $this->modx->event->params;
        // Allow the ability not to display the modAB option
        if (!$this->shouldShow() || $params['mode'] !== modSystemEvent::MODE_UPD) {
            return;
        }

        /** @var modResource $resource */
        $resource = $params['resource'];
//        if (!$resource instanceof modResource) {
//            return;
//        }

        // Make sure the resource is in a context where Resource Hider is allowed
        $allowedContexts = $this->config['allowed_contexts'];
        if (!empty($allowedContexts)) {
            if (!in_array($resource->get('context_key'), $allowedContexts)) {
                return;
            }
        }

        // Make sure the current resource uses an allowed class key
        if (!in_array($resource->get('class_key'), $this->config['allowed_classes'])) {
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
            $load = 'ResourceHider.load('. $this->modx->toJSON($objectArray) .');';
        }

        $this->modx->controller->addLexiconTopic('resourcehider:default');
        // Seems like we are good to display the button i modAB
        $this->modx->controller->addJavascript($this->config['mgr_js_url'] . 'resourcehider.js');
        $this->modx->controller->addHtml(<<<HTML
<script>
    ResourceHider.config = {$this->modx->toJSON($this->config)};
    Ext.onReady(function() {
        {$load}
    });
</script>
HTML
        );
    }

    /**
     * @TODO
     *
     * @see modManagerController::render()
     */
    public function OnBeforeManagerPageInit()
    {
        // Load tree override if any
        if (!$this->shouldShow()) {
            return;
        }
    }

    /**
     * Check whether or not to display ResourceHider extra data for the current user
     *
     * @return bool
     */
    public function shouldShow()
    {
        return (bool) ($this->modx->user->sudo || $this->modx->getOption('resourcehider.show', null, false));
    }

    /**
     * @param string $key
     * @param null|array $options
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getOption($key, $options = null, $default = null)
    {
        // System wide setting
        $value = $this->modx->getOption($key, $options, $default);
        if ($this->isValidController()) {
            // Context override
            $contextKey = $this->modx->controller->resource->context_key;
            $ctx = $this->modx->getContext($contextKey);
            if ($ctx && $ctx instanceof modContext) {
                $value = $ctx->getOption($key, ($default ? $default : $value), $options);
            }
        }

        return $value;
    }

    /**
     * Check whether or not we are using a controller with a reference to a modResource
     *
     * @return bool
     */
    public function isValidController()
    {
        return $this->modx->controller &&
            property_exists($this->modx->controller, 'resource') &&
            $this->modx->controller->resource instanceof modResource;
    }
}
