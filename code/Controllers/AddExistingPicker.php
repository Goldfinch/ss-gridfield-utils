<?php namespace Milkyway\SS\GridFieldUtils\Controllers;
/**
 * Milkyway Multimedia
 * AddExistingPicker.php
 *
 * @package milkyway-multimedia/ss-gridfield-utils
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

if(!class_exists('GridFieldAddExistingSearchButton')) {
    return;
}

use Controller;
use PaginatedList;

class AddExistingPicker extends \GridFieldAddExistingSearchHandler
{
    private static $allowed_actions = [
        'index',
        'add',
        'undo',
    ];

    public function index() {
        return $this->renderWith([
            'GridField_Controllers_AddExistingPicker',
            'GridFieldAddExistingSearchHandler',
        ]);
    }

    public function add($request) {
        $items = $this->checkAccess(array_unique((array)$request->postVar('ids')));

        if($handler = $this->button->getAddHandler()) {
            return call_user_func($handler, $request, $this->grid, $this->button, $items);
        }

        if($items === false) {
            return;
        }

        $list = $this->grid->getList();

        foreach($items as $item) {
            $list->add($item);
        }
    }

    public function undo($request) {
        $items = $this->checkAccess(array_unique((array)$request->postVar('ids')));

        if($handler = $this->button->getUndoHandler()) {
            return call_user_func($handler, $request, $this->grid, $this->button, $items);
        }

        if($items === false) {
            return;
        }

        $list = $this->grid->getList();

        foreach($items as $item) {
            $list->remove($item);
        }
    }

    public function Items() {
        $list = $this->getSearchList();

        if($list->dataClass() == $this->grid->getModelClass()) {
            $list = $list->subtract($this->grid->getList());
        }

        $list = new PaginatedList($list, $this->request);

        return $list;
    }

    public function isAsync() {
        return $this->button->isAsync();
    }

    protected function checkAccess($ids) {
        if(!$ids || !count($ids)) {
            $this->httpError(400);
            return false;
        }

        $items = [];

        foreach($ids as $id) {
            $item = $this->getSearchList()->byID($id);

            if(!$item) {
                $this->httpError(400);
                return false;
            }

            $items[] = $item;
        }

        return count($items) ? $items : false;
    }

    public function Link($action = null) {
        return Controller::join_links($this->grid->Link(), ($this->button->getUrlSegment() ?: 'add-existing-search'), $action);
    }
}