<?php

/**
 * Interface to Contacts application to set up user access rights.
 */
class checkdictionariesRightConfig extends waRightConfig
{
    public function init()
    {
        $this->addItem('add_dictionary', _w('Can create new dictionaries'), 'checkbox');

        $dm = new dictionariesModel();
        $dictionaries = array();
        foreach($dm->getAll() as $dictionary) {
            $dictionaries[$dictionary['id']] = $dictionary['name'];
        }
        $this->addItem('dictionary', _w('Available dictionaries'), 'selectdictionary', array('items' => $dictionaries, 'options' => array(
            0 => _w('No access'),
            1 => _w('Check items only'),
            2 => _w('Full access'),
        )));
    }
}
