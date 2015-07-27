<?php
/**
 * @file
 * Contains \Drupal\quickpay_example\Controller\QuickpayTestController.
 */

namespace Drupal\quickpay_example\Controller;

use Drupal\node\NodeInterface;
use Drupal\quickpay\Entity\Quickpay;
use Drupal\quickpay\QuickpayTransaction;

/**
 * Endpoints for the routes defined in the test module.
 */
class QuickpayExampleController {
  /**
   * Content for the confirmation page.
   *
   * @return array
   *   A renderable array.
   */
  public function success() {
    return array(
      '#type' => 'markup',
      '#markup' => '<h1>Congratulations</h1><p>You just completed a purchase.</p>',
    );
  }

  /**
   * Display the status for the transaction.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The order node to check for.
   *
   * @return array
   *   A renderable array.
   */
  public function status(NodeInterface $node) {
    $quickpay = Quickpay::load('example');
    $transaction = new QuickpayTransaction($quickpay, $node->field_example_transaction_id->value);
    return array(
      '#type' => 'markup',
      '#markup' => '<p>Approved: ' . ($transaction->approved ? 'TRUE' : 'FALSE') . '</p><p>Message: ' . $transaction->qp_status_msg . '</p>',
    );
  }

}
