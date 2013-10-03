<?php
/**
 * Toggle the published status of the given resource
 */
class TogglePublish extends modObjectUpdateProcessor
{
    public $classKey = 'modResource';

    /**
     * @inherit
     */
    public function beforeSet()
    {
        $this->properties = array();
        $this->object->set('published', !$this->object->get('published'));

        return parent::beforeSet();
    }
}

return 'TogglePublish';
