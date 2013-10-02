<?php

class getContextList extends modObjectGetListProcessor
{
    public $classKey = 'modContext';
    public $permission = 'view_context';
    public $languageTopics = array('context');
    public $defaultSortField = 'key';

    public function initialize()
    {
        $initialized = parent::initialize();
        $this->setDefaultProperties(array(
            'search' => '',
            'exclude' => '',
        ));

        return $initialized;
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $search = $this->getProperty('search');
        if (!empty($search)) {
            $c->where(array(
                'key:LIKE' => '%'.$search.'%',
                'OR:description:LIKE' => '%'.$search.'%',
            ));
        }

        $exclude = $this->getProperty('exclude');
        if (!empty($exclude)) {
            $c->where(array(
                'key:NOT IN' => is_string($exclude) ? explode(',', $exclude) : $exclude,
            ));
        }

        $sortBy = $this->getProperty('sortBy');
        $dir = $this->getProperty('sortDir', $this->defaultSortDirection);
        if (!empty($sortBy)) {
            $c->sortby($sortBy, $dir);
        }

        return $c;
    }

    public function beforeIteration(array $list)
    {
        array_unshift($list, array('key' => $this->modx->lexicon('resourcehider.all')));

        return parent::beforeIteration($list);
    }
}

return 'getContextList';
