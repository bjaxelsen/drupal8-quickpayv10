<?php
/**
 * @file
 * Contains \Drupal\quickpay\Access\QuickpayCallbackAccessCheck.
 */

namespace Drupal\quickpay\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\AccessResult;

/**
 * Checks access for displaying configuration translation page.
 */
class QuickpayCallbackAccessCheck implements AccessInterface {

  /**
   * Access callback to check that the url parameters hasn't been tampered with.
   *
   * @param string $order_id
   *   The order ID from Quickpay.
   */
  public function access($order_id, Request $request) {
    \Drupal::logger('quickpay')->notice(print_r($request->request, true));
    return AccessResult::forbidden();
/*
    $quickpay = new Quickpay();

    checksum = $quickpay->getChecksumFromRequest($request)
    if (isset($_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256']) && strcmp($checksum, $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256']) === 0) {
      return AccessResult::allowed();
    }
    \Drupal::logger('quickpay')->log(RfcLogLevel::WARNING, 'Computed checksum does not match header checksum.');
    return AccessResult::forbidden();*/
  }
}
?>
