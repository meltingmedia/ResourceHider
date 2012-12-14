<?php
/**
 * @package resourcehider
 */
class ResourceHider {
    /* For debugging purpose */
    public $bench = array();
    /** @var modX $modx */
    public $modx;

    /**
     * Constructs the ResourceHider object
     *
     * @param modX &$modx A reference to the modX object
     * @param array $config An array of configuration options
     */
    function __construct(modX &$modx, array $config = array()) {
        $this->modx =& $modx;

        $basePath = $this->modx->getOption('resourcehider.core_path', $config, $this->modx->getOption('core_path') . 'components/resourcehider/');
        $assetsUrl = $this->modx->getOption('resourcehider.assets_url' , $config, $this->modx->getOption('assets_url') . 'components/resourcehider/');
        $classes = $this->modx->getOption('resourcehider.allowed_classes', $config, 'modDocument, modResource, modSymLink, modWebLink, modStaticResource');
        $allowedClasses = array_map('trim', explode(',', $classes));

        $this->config = array_merge(array(
            'base_path' => $basePath,
            'core_path' => $basePath,
            'model_path' => $basePath . 'model/',
            'processors_path' => $basePath . 'processors/',
            'js_url' => $assetsUrl . 'js/',
            'css_url' => $assetsUrl . 'css/',
            'assets_url' => $assetsUrl,
            'connector_url' => $assetsUrl . 'connector.php',
            'allowed_classes' => $allowedClasses,
            'debug' => false,
            'debug_user' => '',
        ), $config);

        // Debug settings
        if ($this->modx->getOption('debug', $this->config, false)) {
            error_reporting(E_ALL); ini_set('display_errors', true);
            $this->modx->setLogTarget('FILE');
            $this->modx->setLogLevel(4);

            $debugUser = $this->config['debug_user'] == '' ? $this->modx->user->get('username') : 'anonymous';
            $user = $this->modx->getObject('modUser', array('username' => $debugUser));
            if ($user == null) {
                $this->modx->user->set('id', $this->modx->getOption('debugUserId', $this->config, 1));
                $this->modx->user->set('username', $debugUser);
            } else {
                $this->modx->user = $user;
            }
        }

        $this->modx->addPackage('resourcehider', $this->config['model_path']);
    }

    /**
     * @return mixed
     */
    public function getMicrotime() {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];

        return $mtime;
    }

    /**
     * Starts a bench timer
     *
     * @param string $name The bench name
     */
    public function startBench($name) {
        $this->bench[$name] = $this->getMicrotime();
    }

    /**
     * Stops the given bench
     *
     * @param string $name The bench name
     * @return string The bench result
     */
    public function endBench($name) {
        $tend = $this->getMicrotime();
        $totalTime = ($tend - $this->bench[$name]);
        $result = sprintf("Exec time for %s * %2.4f s", $name, $totalTime);
        $result .= " - Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\n";

        return $result;
    }
}
