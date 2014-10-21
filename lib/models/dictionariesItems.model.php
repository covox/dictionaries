<?php

class dictionariesItemsModel extends waModel
{
    protected $table = 'dictionaries_items';

    public function getByDictionaryId($dictionary_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE dictionary_id=i:dictionary_id ORDER BY sort";
        return $this->query($sql, array(
            'dictionary_id' => $dictionary_id,
        ))->fetchAll('id');
    }

    /** Increase `sort` by 1 where `sort` <= $sort and `dictionary_id` = $dictionary_id */
    public function moveApart($dictionary_id, $sort)
    {
        $sql = "UPDATE {$this->table} SET sort=sort+1 WHERE dictionary_id=i:dictionary_id AND sort >= i:sort";
        return $this->exec($sql, array(
            'dictionary_id' => $dictionary_id,
            'sort' => $sort,
        ));
    }
}

