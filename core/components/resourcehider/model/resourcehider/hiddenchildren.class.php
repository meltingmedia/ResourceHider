<?php

class HiddenChildren extends modResource
{
    public $showInContextMenu = true;

    function __construct(xPDO &$xpdo)
    {
        parent::__construct($xpdo);
        $this->fromArray(array(
            'class_key' => 'HiddenChildren',
            'hide_children_in_tree' => true,
            'isfolder' => true,
        ));
    }

    public function getContextMenuText()
    {
        return array(
            'text_create' => 'Hidden container',
            'text_create_here' => 'Create hidden container here',
        );
    }

    public function getResourceTypeName()
    {
        return 'Hidden container';

        $className = $this->_class;
        if ($className == 'modDocument') $className = 'document';

        return $this->xpdo->lexicon($className);
    }

    public static function getControllerPath(xPDO &$modx)
    {
        $default = $modx->getOption('core_path') . 'components/resourcehider/';

        return $modx->getOption('resourcehider.core_path', null, $default) . 'controllers/crc/';
    }

//    public function prepareTreeNode(array $node = array())
//    {
//        $this->xpdo->log(modX::LOG_LEVEL_INFO, print_r($node, true));
//
//        return $node;
//    }
}
