<?php
/**
 * @see xPDOVehicle::validate
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
 */

$success = false;
if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;

    $modx->getVersionData();
    if (version_compare($modx->version['full_version'], '2.3', '>=')) {
        $success = true;
    } else {
        $modx->log(modX::LOG_LEVEL_WARN, 'Skipping menu for MODX Revolution 2.3');
    }
}

return $success;
