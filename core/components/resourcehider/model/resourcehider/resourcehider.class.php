<?php
/**
 * @package resourcehider
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
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;
        $this->prefix = $prefix = strtolower(get_class($this));

        $basePath = $this->modx->getOption("{$prefix}.core_path", $config, $this->modx->getOption('core_path') . "components/{$prefix}/");
        $assetsPath = $this->modx->getOption("{$prefix}.assets_path" , $config, $this->modx->getOption('assets_path') . "components/{$prefix}/");
        $assetsUrl = $this->modx->getOption("{$prefix}.assets_url" , $config, $this->modx->getOption('assets_url') . "components/{$prefix}/");
        $managerPath = $this->modx->getOption("{$prefix}.manager_path" , $config, $this->modx->getOption('manager_path') . "components/{$prefix}/");
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

            'use_autoloader' => false,
            'vendor_path' => $basePath . 'vendor/',
            'use_rte' => false,

            'js_url' => $assetsUrl . 'js/',
            'css_url' => $assetsUrl . 'css/',
            'assets_url' => $assetsUrl,
            'connector_url' => $assetsUrl . 'connector.php',

            'allowed_classes' => $allowedClasses,
            'allowed_contexts' => $allowedContexts,

            //'debug' => $this->modx->getOption("{$prefix}.debug", null, false),
            'debug' => $this->modx->getOption("{$prefix}.debug", null, true),
            'debug_user' => null,
            'debug_user_id' => null,
        ), $config);

        $this->modx->lexicon->load($this->prefix . ':default');

        if ($this->modx->getOption('debug', $this->config)) {
            $this->initDebug();
        }
        if ($this->config['use_autoloader']) {
            $this->autoLoad();
        }
        if ($this->config['add_package']) {
            $this->modx->addPackage($this->prefix, $this->config['model_path']);
        }
    }

    /**
     * Initialize the debug properties, to get more verbose errors
     *
     * @return void
     */
    private function initDebug()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', true);
        //$this->modx->setLogTarget('FILE');
        $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
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
