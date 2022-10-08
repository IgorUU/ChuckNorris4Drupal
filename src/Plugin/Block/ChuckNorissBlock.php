<?php

namespace Drupal\chuck_norris\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "chuck_norris_example",
 *   admin_label = @Translation("Chuck Norris"),
 *   category = @Translation("Chuck Norris module")
 * )
 */
class ChuckNorissBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = \Drupal::formBuilder()->getForm('Drupal\chuck_norris\Form\ChuckNorrisForm');
    return $build;
  }
}
