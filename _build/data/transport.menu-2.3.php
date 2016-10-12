<?php
/**
 * Adds modMenu into package
 *
 * @var modX $modx
 */

$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'resourcehider',
    'parent' => 'components',
    'description' => 'resourcehider.menu_desc',
    'namespace' => 'resourcehider',
    'action' => 'home',
//    'icon' => '',
//    'menuindex' => 0,
//    'params' => '',
//    'handler' => '',
//    'permissions' => '',
), '', true, true);

return $menu;
