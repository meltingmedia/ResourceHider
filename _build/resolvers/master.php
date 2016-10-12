<?php
/**
 * Master resolver to handle migrations & upgrades
 *
 * @see xPDOVehicle::resolve
 *
 * @var xPDOVehicle $this
 * @var xPDOTransport $transport
 * @var xPDOObject|mixed $object
 * @var array $options
 *
 * @var array $fileMeta
 * @var string $fileName
 * @var string $fileSource
 *
 * @var array $r
 * @var string $type (file/php), obviously php :)
 * @var string $body (json)
 * @var integer $preExistingMode
 */
if ($object->xpdo) {
    /** @var $modx modX */
    $modx =& $object->xpdo;

    $rootPath = $modx->getOption(
        'resourcehider.core_path',
        null,
        $modx->getOption('core_path') . 'components/resourcehider/'
    );
    $loader = "{$rootPath}vendor/autoload.php";
    if (file_exists($loader)) {
        require_once $loader;
    }
    $modelPath = $rootPath . 'model/';
    // Make sure the service class is available
    if (file_exists($modelPath . 'resourcehider/resourcehider.class.php')) {
        /** @var $service ResourceHider */
        $service = $modx->getService('resourcehider', 'ResourceHider', $modelPath .'resourcehider/');
    }

    $migration = new meltingmedia\migration\Helper($modx, array(
        'component_name' => 'ResourceHider',
        'package_name' => 'resourcehider',
        'namespace' => $options['namespace'],
        'migrations_path' => $service ? $service->config['migrations_path'] : $rootPath . 'migrations/',
    ));

    $haveCustomTables = $service ? $service->config['add_package'] : false;

    // Get signature
    $tmpVersion = $migration->getVersion($options);

    if ($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_INSTALL ||
        $options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_UPGRADE
    ) {
        // Common stuff for install/upgrade
        $name = $options['namespace'];
        $thisVersion = str_replace($name . '-', '', $tmpVersion);
        // Add package to manipulate tables
        if ($haveCustomTables) {
            $modx->addPackage('resourcehider', $modelPath);
        }
        $m = $modx->getManager();

        // Extension package
        $extPack = $modx->getOption('resourcehider.core_path');
        if (empty($extPack)) {
            $extPack = '[[++core_path]]components/resourcehider/model/';
        }
        if ($modx instanceof modX) {
            $modx->addExtensionPackage('resourcehider', $extPack);
        }

        switch ($options[xPDOTransport::PACKAGE_ACTION]) {
            case xPDOTransport::ACTION_INSTALL:
                $modx->log(modX::LOG_LEVEL_INFO, 'Installing version : '. $thisVersion);

//                if ($haveCustomTables) {
//                    $objects = array('HiddenChildren');
//                    foreach ($objects as $object) {
//                        $m->createObjectContainer($object);
//                    }
//                }
                break;
            case xPDOTransport::ACTION_UPGRADE:
                // Get previously installed version
                $previousVersion = $migration->getPreviousVersion();
                $modx->log(modX::LOG_LEVEL_INFO, 'Previous version : '. $previousVersion);

                $upgrades = array('0.2.0-dev');
                // Lets trigger appropriate "migrations"
                foreach ($upgrades as $for) {
                    if (version_compare($previousVersion, $for) <= 0) {
                        include_once $migration->getMigration($for);
                    }
                }

                break;
        }
        $version = $thisVersion;

    } else {
        // Uninstall
        $version = $migration->getPreviousFromDB($tmpVersion);
        $modx->log(modX::LOG_LEVEL_INFO, 'Uninstalling' . print_r($options, true));
        $modx->log(modX::LOG_LEVEL_INFO, 'previous installed version : ' . $version);

        // Remove extension package
        if ($modx instanceof modX) {
            $modx->removeExtensionPackage('resourcehider');
        }
    }

    // Update the version setting
    if ($version) {
        $migration->setVersion($version);
    } else {
        $migration->removeVersionSetting();
    }
}
