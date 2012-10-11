<?php
class ResourceHiderGetList extends modObjectGetListProcessor {
    public $classKey = 'modResource';
    public $defaultSortField = 'pagetitle';

    public $allowed = array('modDocument', 'modResource');

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->where(array(
            'class_key:IN' => $this->allowed,
            'show_in_tree' => false,
        ));

        return $c;
    }
}

return 'ResourceHiderGetList';
