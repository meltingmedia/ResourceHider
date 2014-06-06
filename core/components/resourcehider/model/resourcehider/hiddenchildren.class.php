<?php

class HiddenChildren extends modResource
{
    public $showInContextMenu = true;

    public function __construct(xPDO &$xpdo)
    {
        parent::__construct($xpdo);
        $this->fromArray(array(
            'class_key' => 'HiddenChildren',
            'hide_children_in_tree' => true,
            'isfolder' => true,
        ));

        $this->xpdo->lexicon->load('resourcehider:default');
    }

    public function getContextMenuText()
    {
        return array(
            'text_create' => $this->xpdo->lexicon('resourcehider.container'),
            'text_create_here' => $this->xpdo->lexicon('resourcehider.container_create_here'),
        );
    }

    public function getResourceTypeName()
    {
        return $this->xpdo->lexicon('resourcehider.container');
    }

    public static function getControllerPath(xPDO &$modx)
    {
        $base = $modx->getOption(
            'resourcehider.core_path',
            null,
            $modx->getOption('core_path') . 'components/resourcehider/'
        );
        $theme = $modx->getOption('manager_theme', null, 'default');

        $path = $base . "controllers/{$theme}/crc/";
        if (!file_exists($path) && $theme !== 'default') {
            $path = $base . "controllers/default/crc/";
        }

        return $path;
    }

//    public function prepareTreeNode(array $node = array())
//    {
//        $this->xpdo->log(modX::LOG_LEVEL_INFO, print_r($node, true));
//
//        return $node;
//    }

    public function save($cacheFlag = null)
    {
        if ($this->class_key == 'HiddenChildren') {
            $hide = true;
        } else {
            $hide = false;
        }
        $this->set('hide_children_in_tree', $hide);

        return parent::save($cacheFlag);
    }

    public function prepareTreeNode(array $node = array())
    {
        $cls = str_replace('tree-folder ', '', $node['cls']) . ' icon icon-plus-square-o';
        $node['iconCls'] = $cls;

        return $node;
    }
}
