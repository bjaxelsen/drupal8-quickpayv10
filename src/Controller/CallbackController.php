<?php
/**
 * @file
 * Contains \Drupal\quickpay\Controller\CallbackController.
 */

namespace Drupal\quickpay\Controller;

use Drupal\quickpay\Entity\Quickpay;
use Drupal\quickpay\QuickpayTransaction;
use Drupal\Core\Logger\RfcLogLevel;
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
//    die(print_r($request->get('test')));
    $response = new Response();
    $response->setStatusCode(500);
    $quickpay = new Quickpay();
    $request_body = file_get_contents("php://input");
    $request = json_decode($request_body);
    try {
      $transaction = new QuickpayTransaction($request);
      $transaction->save();
      // Invoke hook_quickpay_callback.
      \Drupal::service('module_handler')->invokeAll('quickpay_callback', array($order_id, $transaction));
      $response->setStatusCode(200);
    }
    catch (Exception $e) {
      \Drupal::logger('quickpay')->log(RfcLogLevel::WARNING, 'Could not create transaction from request: !request.', array('!request' => print_r($request, TRUE)));
    }
    $response->send();
    exit;
  }

}
