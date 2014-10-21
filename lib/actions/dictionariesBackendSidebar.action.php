<?php

/**
 * Application sidebar. Used as a part of the default layout.
 */
class dictionariesBackendSidebarAction extends waViewAction
{
    public function execute()
    {
        $dm = new dictionariesModel();
        $dictionaries = $dm->getAllowed();
        foreach($dictionaries as $id => &$dictionary) {
            if (strtolower(substr($dictionary['icon'], 0, 7)) == 'http://') {
                $dictionary['icon'] = '<i class="icon16" style="background-image:url('.htmlspecialchars($dictionary['icon']).')"></i>';
            } else {
                $dictionary['icon'] = '<i class="icon16 '.$dictionary['icon'].'"></i>';
            }
        }

        if ( ( $id = waRequest::request('id')) && isset($dictionaries[$id])) {
            $dictionaries[$id]['current'] = true;
        }

        $this->view->assign('dictionaries', $dictionaries);
        $this->view->assign('can_add_dictionaries', $this->getRights('add_dictionary'));
    }
}

