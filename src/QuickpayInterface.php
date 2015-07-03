<?php

/**
 * @file
 * Contains \Drupal\quickpay\QuickpayInterface.
 */

namespace Drupal\quickpay;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a quickpay entity.
 */
interface QuickpayInterface extends ConfigEntityInterface {
  public function currencyInfo($code);
  public function wireAmount($amount, array $currency_info);
  public function unwireAmount($amount, array $currency_info);
  public function getLanguage();
  public function getPaymentMethods();
  public function getChecksum(array $data);
  public function getChecksumFromRequest($request);
  public static function loadFromRequest($request);
}
