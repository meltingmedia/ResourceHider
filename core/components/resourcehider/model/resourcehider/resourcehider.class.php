<?php
/**
 * Resource Hider service class
 */
class ResourceHider
{
    /** @var modX $modx */
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

            'use_autoloader' => true,
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
            'target' => $this->getOption('resourcehider.crc_target', null, 'panel'),
            'content_action' => $this->getOption('resourcehider.crc_content_action', null, 'none'),
            'insert_idx' => $this->getOption('resourcehider.crc_insert_idx', null, 'last'),
            'set_active_tab' => $this->getOption('resourcehider.crc_set_active_tab', null, false),
        ), $config);

        $this->modx->lexicon->load($this->prefix . ':default');

        if ($this->config['use_autoloader']) {
            $this->autoLoad();
        }
        if ($this->config['add_package']) {
            $this->modx->addPackage($this->prefix, $this->config['model_path']);
        }
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

    /**
     * Initialize the auto-loader if found
     *
     * @return void
     */
    private function autoLoad()
    {
        $loader = $this->config['vendor_path'] . 'autoload.php';
        if (file_exists($loader)) {
            require_once $loader;
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Autoloader file not found');
        }
    }
}
