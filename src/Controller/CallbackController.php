<?php
/**
 * @file
 * Contains \Drupal\quickpay\Controller\CallbackController.
 */

namespace Drupal\quickpay\Controller;

use Drupal\quickpay\Entity\Quickpay;
use Drupal\quickpay\QuickpayTransaction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * Endpoints for the routes defined.
 */
class CallbackController {
  /**
   * Callback from Quickpay.
   *
   * @param string $order_id
   *   The order ID from Quickpay.
   */
  public function callback($order_id, Request $request) {
    $response = new Response();
    $response->setStatusCode(500);
    try {
      $response = $arguments[1]->getContent();
      // TODO REPLACE WITH DATA FORM REQUEST.
      $quickpay = Quickpay::load('example');
      $transaction = new QuickpayTransaction($quickpay);
      $transaction->loadFromResponse($response);
      // Invoke hook_quickpay_callback.
      \Drupal::service('module_handler')->invokeAll('quickpay_callback', array($order_id, $transaction));
      $response->setStatusCode(200);
    }
    catch (Exception $e) {
      \Drupal::logger('quickpay')->error('Could not create transaction from request: !request.', array('!request' => print_r($request, TRUE)));
    }
    $response->send();
    exit;
  }

}
