<?php

class ResourceHiderGetList extends modObjectGetListProcessor
{
    /** @var ResourceHider */
    public $rh;
    public $classKey = 'modResource';
    public $defaultSortField = 'pagetitle';

    public $allowed = array();

    public function __construct(modX & $modx, array $properties = array())
    {
        parent::__construct($modx, $properties);
        $this->rh =& $this->modx->resourcehider;
        if (!$this->rh or !($this->rh instanceof ResourceHider)) {
            $this->rh = $this->modx->getService('resourcehider', 'ResourceHider', $this->modx->getOption('resourcehider.core_path', null, $this->modx->getOption('core_path') . 'components/resourcehider/') . 'model/resourcehider/');
        }
        $this->allowed = $this->rh->config['allowed_classes'];
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->leftJoin('modContext', 'Context');
        $c->select(array('id', 'context_key', 'hide_children_in_tree', 'show_in_tree', 'pagetitle'));

        $parent = $this->getProperty('resource');
        if (isset($parent) && !empty($parent)) {
            // Assume we are in a CRC (or TV)
            $c->where(array(
                'parent' => $parent,
            ));
        } else {
            $c->where(array(
                'class_key:IN' => $this->allowed,
            ));
            // Assume we are in the CMP
            $type = $this->getProperty('type');
            if (!empty($type)) {
                switch ($type) {
                    case 'children';
                        $c->where(array(
                            'hide_children_in_tree' => true,
                        ));
                        break;

                    case 'hidden';
                        $c->where(array(
                            'show_in_tree' => false,
                        ));
                        break;

                    default:
                        $c->where(array(
                            array(
                                'AND:show_in_tree:=' => false,
                                'OR:hide_children_in_tree:=' => true,
                            ),
                        ));
                        break;
                }
            }

            $ctx = $this->getProperty('context_key');
            if (!empty($ctx) && $ctx != $this->modx->lexicon('resourcehider.all')) {
                $c->where(array(
                    'context_key' => $ctx,
                ));
            }
        }

        $query = $this->getProperty('query');
        if (!empty($query)) {
            $query = '%'. $query .'%';
            $c->where(array(
                'pagetitle:LIKE' => $query,
                'OR:longtitle:LIKE' => $query,
            ));
        }

        return $c;
    }

    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->sortby('Context.rank', 'ASC');
        $c->sortby('parent', 'ASC');
        $c->sortby('menuindex', 'ASC');

        return parent::prepareQueryAfterCount($c);
    }
}

return 'ResourceHiderGetList';
