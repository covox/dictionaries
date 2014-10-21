<?php

/**
 * Collection of backend actions that show HTML pages
 */
class dictionariesBackendActions extends waViewActions
{
    public function __construct(waSystem $system = null)
    {
        parent::__construct($system);
        $this->setLayout(new dictionariesDefaultLayout());
    }

    /** Default action when no other action is specified. */
    public function defaultAction()
    {
        $dm = new dictionariesModel();
        $dictionaries = $dm->getAllowed();
        if (!$dictionaries) {
            if ($this->getRights('add_dictionary')) {
                $this->execute('editor');
                return;
            }
            return;
        }

        $id = waRequest::cookie('last_dictionary_id', 0, 'int');
        if ($id && isset($dictionaries[$id])) {
            $this->execute('dictionary', $dictionaries[$id]);
            return;
        }

        $dictionaries = array_values($dictionaries);
        $this->execute('dictionary', $dictionaries[0]);
    }

    public function DictionaryAction($dictionary = null)
    {
        if (!$dictionary) {
            if (! ( $id = waRequest::request('id', 0, 'int'))) {
                throw new waException('No id specified.');
            }
            $dm = new dictionariesModel();
            if (! ( $dictionary = $dm->getById($id))) {
                throw new waException('Dictionary does not exist.');
            }
        }

        $access = $this->getRights('dictionary.'.$dictionary['id']);
        if (!$access) {
            throw new waRightsException('Access denied.');
        }
        $this->view->assign('can_edit', $access > 1);
        $this->view->assign('dictionary', $dictionary);
//        $this->view->assign('dictionary_id', $id);

        $dim = new dictionariesItemsModel();
        $items = dictionariesItem::prepareItems($dim->getByDictionaryId($dictionary['id']));

        $this->view->assign('items', array_values($items));

        wa()->getResponse()->setCookie('last_dictionary_id', $dictionary['id']);
        $this->layout->setTitle($dictionary['name']);
    }

    /** Create new or edit existing dictionary. */
    public function EditorAction()
    {
        $id = waRequest::request('id', 0, 'int');
        if ($id) {
            if($this->getRights('dictionary.'.$id) <= 1) {
                throw new waRightsException('Access denied.');
            }
            $lm = new dictionariesModel();
            if (! ( $dictionary = $lm->getById($id))) {
                throw new waException('Dictionary does not exist.');
            }
            $this->layout->setTitle($dictionary['name']);
        } else {
            if(!$this->getRights('add_dictionary')) {
                throw new waRightsException('Access denied.');
            }
            $dictionary = array(
                'id' => '',
                'name' => '',
                'color_class' => 'c-white',
                'icon' => 'notebook',
                'count' => 0,
            );
        }
        $this->view->assign('dictionary', $dictionary);

        $this->view->assign('icons', array(
            'notebook',
            'lock',
            'lock-unlocked',
            'broom',
            'star',
            'livejournal',
            'contact',
            'lightning',
            'light-bulb',
            'pictures',
            'reports',
            'books',
            'marker',
            'lens',
            'alarm-clock',
            'animal-monkey',
            'anchor',
            'bean',
            'car',
            'disk',
            'cookie',
            'burn',
            'clapperboard',
            'bug',
            'clock',
            'cup',
            'home',
            'fruit',
            'luggage',
            'guitar',
            'smiley',
            'sport-soccer',
            'target',
            'medal',
            'phone',
            'store',
        ));

        $this->view->assign('colors', array(
            'c-white',
            'c-gray',
            'c-yellow',
            'c-green',
            'c-blue',
            'c-red',
            'c-purple',
        ));
    }
}

