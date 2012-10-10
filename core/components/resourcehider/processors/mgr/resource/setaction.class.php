<?php
class ResourceHiderSetAction extends modObjectGetProcessor {
    public $classKey = 'modResource';
    public $languageTopics = array('resourcehider:default');

    public $action;
    public $error;

    public function initialize() {
        $this->action = $this->getProperty('perform');
        return parent::initialize();
    }

    public function process() {
        $result = $this->_perform();
        if ($result) {
            return parent::process();
        }

        $msg = $this->error;
        if (!$msg) {
            $msg = $this->modx->lexicon('resourcehider.error_msg_default');
        }
        return $this->failure($msg);
    }

    /**
     * Execute what should be done
     *
     * @return bool If the operation went bad or fine :)
     */
    private function _perform() {
        switch ($this->action) {
            case 'show_in_tree':
                $this->object->set('show_in_tree', true);
                break;

            case 'hide_in_tree':
                $this->object->set('show_in_tree', false);
                break;

            case 'show_children_in_tree':
                $this->object->set('hide_children_in_tree', false);
                break;

            case 'hide_children_in_tree':
                $this->object->set('hide_children_in_tree', true);
                break;

            default:
                $this->error = $this->modx->lexicon('resourcehider.error_msg_noaction');
                return false;
        }

        if ($this->object->save() === false) {
            $this->error = $this->modx->lexicon('resourcehider.error_msg_save');
            return false;
        }

        return true;
    }
}

return 'ResourceHiderSetAction';
