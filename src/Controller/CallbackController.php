<?php
/**
 * @file
 * Contains \Drupal\quickpay\Controller\CallbackController.
 */

namespace Drupal\quickpay\Controller;

use Drupal\quickpay\Quickpay;
use Drupal\quickpay\QuickpayTransaction;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class CallbackController {
  /**
   * Callback from Quickpay.
   *
   * @param string $order_id.
   */
  public function callback($order_id) {
    $response = new Response;
    $response->setStatusCode(500);
    $quickpay = new Quickpay;
    // Get the
    $requestBody = file_get_contents("php://input");
    $request = json_decode($requestBody);
    try {
      $transaction = new QuickpayTransaction($request);
      $transaction->save();
      // Invoke hook_quickpay_callback.
      \Drupal::service('module_handler')->invokeAll('quickpay_callback', array($order_id, $transaction));
      $response->setStatusCode(200);
    }
    catch (Exception $e) {
      \Drupal::logger('quickpay')->log(RfcLogLevel::WARNING, 'Could not create transaction from request: !request.', array('!request' => print_r($request, true)));
    }
    $response->send();
    exit;
  }

  /**
   * Access callback to check that the url parameters hasn't been tampered with.
   *
   * @param string $order_id
   */
  public function access($order_id) {
    $quickpay = new Quickpay;
    $requestBody = file_get_contents("php://input");
    $checksum = $quickpay->getChecksumFromRequest($requestBody);
    if (isset($_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256']) && strcmp($checksum, $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256']) === 0) {
      return AccessResult::allowed();
    }
    \Drupal::logger('quickpay')->log(RfcLogLevel::WARNING, 'Computed checksum does not match header checksum.');
    return AccessResult::forbidden();
  }
}
