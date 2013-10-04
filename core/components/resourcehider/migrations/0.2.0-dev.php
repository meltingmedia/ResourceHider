<?php
/**
 * @var modX $modx
 * @var xPDOManager $m
 * @var string $for
 * @var meltingmedia\migration\Helper $migration
 */

$modx->log(modX::LOG_LEVEL_INFO, "Performing maintenance for version {$for}");

$assets = $modx->getOption('resourcehider.assets_path', null, $modx->getOption('assets_path') . 'components/resourcehider/');
if (file_exists($assets)) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Removing previous assets files...');
    $modx->cacheManager->deleteTree($assets, array('deleteTop' => true, 'extensions' => array('.js', '.php')));
}
