<?php
/**
 * @file
 * Contains \Drupal\quickpay\Access\QuickpayCallbackAccessCheck.
 */

namespace Drupal\quickpay\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\AccessResult;
use Drupal\quickpay\Entity\Quickpay;

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
    $content = json_decode($request->getContent());
    $quickpay = Quickpay::loadFromRequest($content);
    if ($quickpay) {
      $checksum_calculated = $quickpay->getChecksumFromRequest($request->getContent());
      $checksum_requested = $request->server->get('HTTP_QUICKPAY_CHECKSUM_SHA256');
      if (!empty($checksum_requested) && strcmp($checksum_calculated, $checksum_requested) === 0) {
        return AccessResult::allowed();
      }
      \Drupal::logger('quickpay')->error('Computed checksum does not match header checksum.');
    }
    else {
      \Drupal::logger('quickpay')->error('Could not load a Quickpay configuration from request.');
    }
    return AccessResult::forbidden();
  }
}
?>
