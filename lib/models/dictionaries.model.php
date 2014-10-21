<?php

class dictionariesModel extends waModel
{
    protected $table = 'dictionaries';
    protected $items_table = 'dictionaries_items';

    public function getAll($key = null, $normalize = false)
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY sort, name";
        return $this->query($sql)->fetchAll($key, $normalize);
    }

    /** Get dictionaries available for current user.
      * @return array id => db row */
    public function getAllowed()
    {
        $dictionaries = $this->getAll('id');

        $admin = wa()->getUser()->getRights('dictionaries', 'backend') > 1;
        if (!$admin) {
            $available = wa()->getUser()->getRights('dictionaries', 'dictionary.%');
        }

        foreach($dictionaries as $id => &$dictionary) {
            if (!$admin && (!isset($available[$id]) || !$available[$id])) {
                unset($dictionaries[$id]);
                continue;
            }
        }

        return $dictionaries;
    }

    /** Increase `sort` by 1 where `sort` <= $sort */
    public function moveApart($sort)
    {
        $sql = "UPDATE {$this->table} SET sort=sort+1 WHERE sort >= i:sort";
        return $this->exec($sql, array(
            'sort' => $sort,
        ));
    }
}

