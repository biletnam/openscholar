<?php

/**
 * @file
 * Contains \OsRestfulLayout
 */

class OsRestfulLayout extends OsRestfulSpaces {

  protected $validateHandler = 'layouts';
  protected $objectType = 'context';

  /**
   * Verify the user have access to the manage layout.
   */
  public function checkGroupAccess() {
    parent::checkGroupAccess();
    $account = $this->getAccount();

    if (!spaces_access_admin($account, $this->space)) {
      // The current user can't manage boxes.
      $this->throwException("You can't manage layout in this vsite.");
    }
  }

  /**
   * Updating a given space override.
   */
  public function updateSpace() {
    // Check group access.
    $this->checkGroupAccess();

    // Validate the object from the request.
    $this->validate();

    $controller = $this->space->controllers->{$this->objectType};
    $settings = $controller->get($this->object->object_id);

    // Merge blocks.
    foreach ($settings['blocks'] as $delta => &$block) {
      if (empty($this->object->blocks[$delta])) {
        continue;
      }

      $block = array_merge($settings['blocks'][$delta], $this->object->blocks[$delta]);
    }

    $controller->set($this->object->object_id, $settings);

    return $settings;
  }

  /**
   * Creating a space override.
   */
  public function createSpace() {
    // Check group access.
    $this->checkGroupAccess();

    // Validate the object from the request.
    $this->validate();

    $space = spaces_load('og', $this->object->vsite);

    // Set up the blocks layout.
    ctools_include('layout', 'os');
    $contexts = array(
      $this->object->context,
      'os_public',
    );
    $blocks = os_layout_get_multiple($contexts, FALSE, TRUE);

    // todo: get the box.

    // Create the box the current vsite.
//    $box = boxes_box::factory($this->object->widget, $options);
//    $space->controllers->boxes->set($box->delta, $box);

    // Add the block to the region.
//    $blocks['boxes-' . $box->delta]['region'] = $this->object->region;

    if (!array_key_exists($blocks['boxes-' . $box->delta], array('module', 'delta'))) {
      $blocks['boxes-' . $box->delta]['delta'] = $box->delta;
      $blocks['boxes-' . $box->delta]['module'] = 'boxes';
      $blocks['boxes-' . $box->delta]['weight'] = 0;
    }

    $space->controllers->context->set($this->object->context . ":reaction:block", array(
      'blocks' => $blocks,
    ));
  }

  public function deleteSpace() {

  }
}
