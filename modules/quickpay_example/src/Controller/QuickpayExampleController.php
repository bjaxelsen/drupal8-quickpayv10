<?php
/**
 * @file
 * Contains \Drupal\quickpay_example\Controller\QuickpayTestController.
 */

namespace Drupal\quickpay_example\Controller;

/**
 * Endpoints for the routes defined in the test module.
 */
class QuickpayExampleController {
  /**
   * Content for the confirmation page.
   */
  public function success() {
    return array(
      '#type' => 'markup',
      '#markup' => '<h1>Congratulations</h1><p>You just completed a purchase.</p><p>Now lets make millions $$$</p>',
    );
  }

}
