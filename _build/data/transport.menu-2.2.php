<?php
/**
 * Adds modActions and modMenus into package
 *
 * @var modX $modx
 * @var modAction $action
 * @var modMenu $menu
 * @package resourcehider
 * @subpackage build
 */
$action = $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'resourcehider',
    'parent' => 0,
    'controller' => 'index',
    'haslayout' => true,
    'lang_topics' => 'resourcehider:default',
    'assets' => '',
), '', true, true);

$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'resourcehider',
    'parent' => 'components',
    'description' => 'resourcehider.menu_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
), '', true, true);
$menu->addOne($action);
unset($menus);

return $menu;
