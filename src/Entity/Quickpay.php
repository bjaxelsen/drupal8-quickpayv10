<?php
/**
 * @file
 * Contains \Drupal\quickpay\Entity\Quickpay.
 */

namespace Drupal\quickpay\Entity;

use Drupal\quickpay\QuickpayInterface;
use Drupal\quickpay\QuickpayException;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the Quickpay entity.
 *
 * @ConfigEntityType(
 *   id = "configuration",
 *   label = @Translation("Quickpay configuration"),
 *   module = "quickpay",
 *   config_prefix = "configuration",
 *   admin_permission = "administer site configuration",
 *   handlers = {
 *     "list_builder" = "Drupal\quickpay\QuickpayListBuilder",
 *     "form" = {
 *       "default" = "Drupal\quickpay\Form\ConfigurationForm",
 *       "delete" = "Drupal\quickpay\Form\ConfigurationDeleteForm"
 *     },
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/quickpay/manage/{configuration}",
 *     "delete-form" = "/admin/config/quickpay/manage/{configuration}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   }
 * )
 */
class Quickpay extends ConfigEntityBase implements QuickpayInterface {
  /**
   * The unique ID of the Quickpay configuration.
   */
  public $id;

  /**
   * The label of the Quickpay configuration.
   */
  public $label;

  /**
   * The description of the Quickpay configuration.
   */
  public $description;

  /**
   * The merchant ID from Quickpay.
   */
  public $merchant_id;

  /**
   * The agreement ID from Quickpay.
   */
  public $agreement_id;

  /**
   * The API key from Quickpay.
   */
  public $api_key;

  /**
   * The private key from Quickpay.
   */
  public $private_key;

  /**
   * Prefix to prepend to the order ID.
   */
  public $orderPrefix = '';

  /**
   * Language for the card information form.
   */
  public $language = LanguageInterface::LANGCODE_NOT_SPECIFIED;

  /**
   * The payment method to use.
   */
  public $payment_method = 'creditcard';

  /**
   * Array of accepted cards.
   */
  public $accepted_cards = array('creditcard');

  /**
   * Whether autofee should be enabled.
   */
  public $autofee = FALSE;

  /**
   * Whether auto capture should be enabled.
   */
  public $autocapture = FALSE;

  /**
   * Whether to activate debug mode, logging all interations.
   */
  public $debug = FALSE;

  /**
   * A map of currencies know by the module.
   */
  const BASE_CURRENCIES = array(
    'DKK' => array('code' => 'DKK', 'multiplier' => 100),
    'USD' => array('code' => 'USD', 'multiplier' => 100),
    'EUR' => array('code' => 'EUR', 'multiplier' => 100),
    'GBP' => array('code' => 'GBP', 'multiplier' => 100),
    'SEK' => array('code' => 'SEK', 'multiplier' => 100),
    'NOK' => array('code' => 'NOK', 'multiplier' => 100),
    'ISK' => array('code' => 'ISK', 'multiplier' => 100),
  );

  /**
   * Get the language of the user.
   *
   * @inheritdoc
   *
   * @TODO: If LanguageInterface::LANGCODE_NOT_SPECIFIED the current users
   * language should be used and not 'en'.
   */
  public function getLanguage() {
    $language_code = LanguageInterface::LANGCODE_NOT_SPECIFIED ? 'en' : $this->language;
    $language_codes = array(
      'da' => 'da',
      'de' => 'de',
      'en' => 'en',
      'fo' => 'fo',
      'fr' => 'fr',
      'kl' => 'gl',
      'it' => 'it',
      'nb' => 'no',
      'nn' => 'no',
      'nl' => 'nl',
      'pl' => 'pl',
      'sv' => 'se',
    );
    return isset($language_codes[$language_code]) ? $language_codes[$language_code] : 'en';
  }

  /**
   * Get information about an currency.
   *
   * @inheritdoc
   */
  public function currencyInfo($code) {
    // If the currency is not known, throw an exception.
    if (!array_key_exists($code, self::BASE_CURRENCIES)) {
      throw new QuickpayException(t('Unknown currency code %currency', array('%currency' => $code)));
    }
    $base_currencies = self::BASE_CURRENCIES;
    return $base_currencies[$code];
  }

  /**
   * Returns the amount adjusted by the multiplier for the currency.
   *
   * @inheritdoc
   */
  public function wireAmount($amount, array $currency_info) {
    return (function_exists('bcmul') ? bcmul($amount, $currency_info['multiplier']) : $amount * $currency_info['multiplier']);
  }

  /**
   * Reverses wireAmount().
   *
   * @inheritdoc
   */
  public function unwireAmount($amount, array $currency_info) {
    return (function_exists('bcdiv') ?
      bcdiv($amount, $currency_info['multiplier'], log10($currency_info['multiplier'])) :
      $amount / $currency_info['multiplier']);
  }

  /**
   * Return the proper cardtypelock for the accepted cards.
   *
   * @inheritdoc
   */
  public function getPaymentMethods() {
    if (is_array($this->accepted_cards)) {
      $cards = $this->accepted_cards;
      // Aren't supported in cardtypelock.
      unset($cards['ikano']);
      return implode(',', $cards);
    }
    // Already set to the proper string.
    return $this->accepted_cards;
  }

  /**
   * Calculate the md5checksum for the request.
   *
   * @see http://tech.quickpay.net/payments/hosted/#checksum
   *
   * @inheritdoc
   */
  public function getChecksum(array $data) {
    ksort($data);
    $base = implode(" ", $data);
    return hash_hmac("sha256", $base, $this->api_key);
  }

  /**
   * Build the checksum from the request callback from quickpay.
   *
   * @inheritdoc
   */
  public function getChecksumFromRequest($request) {
    return hash_hmac("sha256", $request, $this->private_key);
  }

  /**
   * Request a QuickPay service.
   *
   * @see http://tech.quickpay.net/api/services/?scope=merchant
   *
   * @inheritdoc
   */
  public function request($url) {
    $client = \Drupal::httpClient();
    try {
      $response = $client->get($url, [
          'auth' => ['', $this->get('api_key')],
          'headers' => [
            'Accept-Version' => QUICKPAY_VERSION,
          ],
        ]
      );
      return $response->json();
    }
    catch (\Exception $e) {
      throw new QuickpayException($e->getMessage());
    }
  }

  /**
   * Initialize a Quickpay instance from a request.
   *
   * @inheritdoc
   */
  public static function loadFromRequest($request) {
    if (isset($request->variables->quickpay_configuration_id)) {
      $configuration_id = $request->variables->quickpay_configuration_id;
      return self::load($configuration_id);
    }
    return FALSE;
  }

}
