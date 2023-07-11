<?php

namespace Drupal\commerce_tipsa\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class urltipsa extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function completado() {
    $completaEnvio = \Drupal::service('commerce_tipsa.completaEnvio')->completa_Envio();
  }

}
