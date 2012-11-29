<?php

class dictionariesDefaultLayout extends waLayout
{
    public function setTitle($title)
    {
        $this->assign('title', $title);
    }

    public function execute()
    {
        if (!isset($this->blocks['title']) || strlen($this->blocks['title']) <= 0) {
            $this->setTitle(_w('Dictionaries'));
        }

        $this->executeAction('sidebar', new dictionariesBackendSidebarAction());
    }
}

