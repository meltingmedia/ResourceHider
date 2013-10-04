<?php

use meltingmedia\migration\Helper;

if ($object->xpdo) {
    /** @var $modx modX */
    $modx =& $object->xpdo;

    $rootPath = $modx->getOption('resourcehider.core_path', null, $modx->getOption('core_path') . 'components/resourcehider/');
    $modelPath = $rootPath . 'model/';
    // Make sure the service class is available
    if (file_exists($modelPath . 'resourcehider/resourcehider.class.php')) {
        /** @var $service ResourceHider */
        $service = $modx->getService('resourcehider', 'ResourceHider', $modelPath .'resourcehider/');
    }

    $migration = new Helper($modx, array(
        'component_name' => 'ResourceHider',
        'package_name' => 'resourcehider',
        'namespace' => $options['namespace'],
        'migrations_path' => $service ? $service->config['migrations_path'] : $rootPath . 'migrations/',
    ));

    $haveCustomTables = $service ? $service->config['add_package'] : false;

    // Get signature
    $tmpVersion = $migration->getVersion($options);

    if ($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_INSTALL or
        $options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_UPGRADE
    ) {
        $name = $options['namespace'];
        $thisVersion = str_replace($name . '-', '', $tmpVersion);
        // Add package to manipulate tables
        if ($haveCustomTables) {
            $modx->addPackage('resourcehider', $modelPath);
        }
        $m = $modx->getManager();

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
        $version = $migration->getPreviousFromDB($tmpVersion);
        $modx->log(modX::LOG_LEVEL_INFO, 'Uninstalling' . print_r($options, true));
        $modx->log(modX::LOG_LEVEL_INFO, 'previous installed version : ' . $version);
    }

    // Update the version setting
    if ($version) {
        $migration->setVersion($version);
    } else {
        $migration->removeVersionSetting();
    }
}
