<?php

/**
 * Collection of backend actions that output JSON data
 */
class dictionariesJsonActions extends waJsonActions
{
    /** Default action when no other action is specified. */
    public function defaultAction()
    {
        throw new waException('Unknown action.');
    }

    /** Save dictionary item using POST data from dictionary page */
    public function ItemsaveAction()
    {
        $item = array();
        foreach(array('dictionary_id', 'name', 'sort') as $k) {
            $v = waRequest::post($k, null);
            if ($v !== null) {
                $item[$k] = $v;
            }
        }

        if (waRequest::post('check')) {
            $item['contact_id'] = wa()->getUser()->getId();
            $item['done'] = date('Y-m-d H:i:s');
        } else if (waRequest::post('uncheck')) {
            $item['contact_id'] = null;
            $item['done'] = null;
        }

        $id = waRequest::post('id', 0, 'int');


        $lim = new dictionariesItemsModel();
        if ($id > 0) {
            unset($item['dictionary_id']);
            if (isset($item['name']) && strlen($item['name']) <= 0) {
                unset($item['name']);
            }

            $i = $lim->getById($id);

            // check access
            $access = $this->getRights('dictionary.'.$i['dictionary_id']);
            if (!$access || ($access <= 1 && isset($item['name']))) {
                throw new waRightsException('Access denied.');
            }

            $lim->moveApart($i['dictionary_id'], isset($item['sort']) ? $item['sort'] : $i['sort']);
            $lim->updateById($id, $item);

            // update log
            if (isset($item['name'])) {
                $this->logAction('item_edit', 1);
            }
            if (array_key_exists('done', $item)) {
                if ($item['done']) {
                    $this->logAction('item_check', 1);
                } else {
                    $this->logAction('item_uncheck', 1);
                }
            }
        } else {
            if(!isset($item['name']) || strlen($item['name']) <= 0 || empty($item['dictionary_id'])) {
                print "<pre>";
                var_dump($item);
                print "</pre>";
                throw new waException('Not enough parameters.');
            }

            // check access
            $access = $this->getRights('dictionary.'.$item['dictionary_id']);
            if ($access <= 1) {
                throw new waRightsException('Access denied.');
            }

            $lim->moveApart($item['dictionary_id'], isset($item['sort']) ? $item['sort'] : 0);
            $id = $lim->insert($item);
            $this->logAction('item_create', 1);
        }

        $this->response = dictionariesItem::prepareItem($lim->getById($id));

//        $lm = new dictionariesModel();
//        $lm->updateCount($this->response['dictionary_id']);
    }

    /** Move dictionary in sidebar */
    public function DictionarymoveAction()
    {
        if (! ( $id = waRequest::post('id', 0, 'int'))) {
            throw new waException('No id specified.');
        }
        if (null === ( $sort = waRequest::post('sort', null, 'int'))) {
            throw new waException('No sort specified.');
        }
        if(!$this->getRights('add_dictionary')) {
            throw new waRightsException('Access denied.');
        }

        $lm = new dictionariesModel();
        $lm->moveApart($sort);
        $lm->updateById($id, array('sort' => $sort));
    }

    /** Save dictionary using POST data from dictionary settings form */
    public function DictionarysaveAction()
    {
        $dictionary = array(
            'name' => waRequest::post('name', ''),
            'color_class' => waRequest::post('color_class', 'c-yellow'),
            'icon' => waRequest::post('icon', 'notebook'),
        );

        if(strlen($dictionary['name']) <= 0) {
            throw new waException('No name specified.');
        }

        $id = waRequest::post('id', 0, 'int');
        $lm = new dictionariesModel();
        if ($id) {
            if($this->getRights('dictionary.'.$id) <= 1) {
                throw new waRightsException('Access denied.');
            }
            $lm->updateById($id, $dictionary);
        } else {
            if(!$this->getRights('add_dictionary')) {
                throw new waRightsException('Access denied.');
            }
            $lm->moveApart(0);
            $id = $lm->insert($dictionary);

            // if user is not an admin then grant him full access on newly created dictionary
            $admin = wa()->getUser()->getRights('dictionaries', 'backend') > 1;
            $rm = new waContactRightsModel();
            if (!$admin) {
                $rm->save(wa()->getUser()->getId(), 'dictionaries', 'dictionary.'.$id, 2);
            }
            $this->logAction('dictionary_create', 1);
        }
        $this->response = $id;
    }

    /** Delete item */
    public function DeleteitemAction()
    {
        if (! ( $id = waRequest::post('id', 0, 'int'))) {
            throw new waException('No id given.');
        }

        $lim = new dictionariesItemsModel();
        if (! ( $item = $lim->getById($id))) {
            return;
        }

        // check access
        if($this->getRights('dictionary.'.$item['dictionary_id']) <= 1) {
            throw new waRightsException('Access denied.');
        }

        $lim->deleteById($id);
//        $lm = new dictionariesModel();
//        $lm->updateCount($item['dictionary_id']);
        $this->response = 'done';
        $this->log('item_delete', 1);
    }

    /** Start over by unchecking all dictionary items */
    public function StartoverAction()
    {
        if (! ( $id = waRequest::post('id', 0, 'int'))) {
            throw new waException('No id given.');
        }
        if(!$this->getRights('dictionary.'.$id)) {
            throw new waRightsException('Access denied.');
        }

        $dim = new dictionariesItemsModel();
        $dim->updateByField('dictionary_id', $id, array('done' => null));

//        $lm = new dictionariesModel();
//        $lm->updateCount($id);

        $this->response = dictionariesItem::prepareItems(array_values($dim->getByDictionaryId($id)));
        $this->logAction('dictionary_startover', 1);
    }

    /** Delete dictionary */
    public function DeletedictionaryAction()
    {
        if (! ( $id = waRequest::post('id', 0, 'int'))) {
            throw new waException('No id given.');
        }

        // check access
        if($this->getRights('dictionary.'.$id) <= 1) {
            throw new waRightsException('Access denied.');
        }

        $lm = new dictionariesModel();
        $lm->deleteById($id);

        $lim = new dictionariesItemsModel();
        $lim->deleteByField('dictionary_id', $id);

        $this->logAction('dictionary_delete', 1);
        $this->response = 'done';
    }
}

