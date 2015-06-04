<?php
/**
 * @file
 * Contains \Drupal\quickpay_test\Controller\QuickpayTestController.
 */

namespace Drupal\quickpay_test\Controller;

/**
 * Endpoints for the routes defined in the test module.
 */
class QuickpayTestController {
  /**
   * Get the checkout form.
   *
   * The reason we have to load it this way, and not using the _form in the
   * route definition, is we need to unset form_id and form_build_id.
   * Read the comment further below.
   *
   * @param string $order_id
   *   The order ID to send to quickpay.
   */
  public function checkout($order_id) {
    // Make the order ID available for the form.
    $_SESSION['order_id'] = $order_id;
    $form = \Drupal::formBuilder()->getForm('Drupal\quickpay_test\Form\CheckoutForm');
    // The form_id and the form_build_id which is part of the form definition
    // will be a part of the generated checksum at Quickpay. But since they
    // are not a part of our generated checksum, then they should be removed
    // from the form. They are not doing anything anyways, since we are
    // requesting quickpay directly.
    // @TODO - Find a better solution.
    unset($form['form_id']);
    unset($form['form_build_id']);
    return $form;
  }

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
