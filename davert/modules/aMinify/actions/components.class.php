<?php

/**
 * a components.
 *
 * @package    apostrophe
 * @subpackage a
 * @author     P'unk Ave
 */
class aMinifyComponents extends BaseaComponents {

  public function executeArea() {
    $this->page = aTools::getCurrentPage();
    $this->pageid = $this->page->id;
    $this->slots = $this->page->getArea($this->name, $this->addSlot, sfConfig::get('app_a_new_slots_top', true));
    $this->editable = false;
    $user = $this->getUser();

    $this->infinite = $this->getOption('infinite');
    if (!$this->infinite) {
      // Watch out for existing slots of the wrong type, which might contain data
      // that is incompatible with the singleton slot's type. That can happen if you
      // switch slot types in the template, or change from an area to a singleton slot.
      // Also ignore anything after the first slot (again, that can happen if you
      // switch from an area to a singleton slot)
      if (count($this->slots) > 1) {
        // Get the first one without being tripped up by the fact that it's a hash
        foreach ($this->slots as $key => $slot) {
          break;
        }
        $this->slots = array($key => $slot);
      }
      if (count($this->slots)) {
        // Get the first one without being tripped up by the fact that it's a hash
        foreach ($this->slots as $key => $slot) {
          break;
        }
        if ($slot->type !== $this->options['type']) {
          $this->slots = array();
        }
      }
      if (!count($this->slots)) {
        if (!isset($this->options['type'])) {
          throw new sfException('Must specify type when embedding a singleton slot');
        }
        $this->slots[1] = $this->page->createSlot($this->options['type']);
        $this->slots[1]->setEditDefault(false);
      }
    }
  }



}
