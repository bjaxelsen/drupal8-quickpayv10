<?php
/**
 * @file
 * Contains \Drupal\quickpay\Form\SettingsForm.
 */

namespace Drupal\quickpay\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Settings form.
 */
class SettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quickpay_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['quickpay.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('quickpay.settings');
    $form['#tree'] = TRUE;

    $form['settings']['merchant'] = array(
      '#type' => 'textfield',
      '#title' => t('Merchant ID'),
      '#description' => t('This is the Merchant ID from the Quickpay manager.'),
      '#default_value' => $config->get('merchant'),
      '#required' => TRUE,
    );

    $form['settings']['agreement'] = array(
      '#type' => 'textfield',
      '#title' => t('Agreement ID'),
      '#description' => t('This is the Agreement ID from the Quickpay manager.'),
      '#default_value' => $config->get('agreement'),
      '#required' => TRUE,
    );

    $form['settings']['api'] = array(
      '#type' => 'textfield',
      '#title' => t('API'),
      '#description' => t('This is the api key from the Quickpay manager.'),
      '#default_value' => $config->get('api'),
      '#required' => TRUE,
    );


    $form['settings']['private'] = array(
      '#type' => 'textfield',
      '#title' => t('Private'),
      '#description' => t('This is the private key from the Quickpay manager.'),
      '#default_value' => $config->get('private'),
      '#required' => TRUE,
    );

    $form['settings']['order_prefix'] = array(
      '#type' => 'textfield',
      '#title' => t('Order id prefix'),
      '#description' => t('Prefix for order ids. Order ids must be uniqe when sent to QuickPay, use this to resolve clashes.'),
      '#default_value' => $config->get('order_prefix'),
    );

    $languages = $this->get_languages() + array(LanguageInterface::LANGCODE_NOT_SPECIFIED => t('Language of the user'));

    $form['settings']['language'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#description' => t('The language for the credit card form.'),
      '#options' => $languages,
      '#default_value' => $config->get('language'),
    );

    $form['settings']['method'] = array(
      '#type' => 'radios',
      '#id' => 'quickpay-method',
      '#title' => t('Accepted payment methods'),
      '#description' => t('Which payment methods to accept. NOTE: Some require special agreements.'),
      '#default_value' => $config->get('method'),
      '#options' => array(
        'creditcard' => t('Creditcard'),
        '3d-creditcard' => t('3D-Secure Creditcard'),
        'selected' => t('Selected payment methods'),
      ),
      '#attached' => array(
        'library' => array('quickpay/settings'),
      ),
    );

    $options = array();
    // Add image to the cards where defined.
    foreach ($this->get_quickpay_cards() as $key => $card) {
      $options[$key] = empty($card['image']) ? $card['name'] : '<img src="/' . $card['image'] . '" />' . $card['name'];
    }

    $form['settings']['cards'] = array(
      '#type' => 'checkboxes',
      '#id' => 'quickpay-cards',
      '#title' => t('Selected payment methods'),
      '#default_value' => $config->get('cards'),
      '#options' => $options,
    );

    $form['settings']['splitpayment'] = array(
      '#type' => 'checkbox',
      '#title' => t('Split payments'),
      '#description' => t('Allows for capturing payments in parts.'),
      '#default_value' => $config->get('splitpayment'),
    );

    $form['settings']['autofee'] = array(
      '#type' => 'checkbox',
      '#title' => t('Autofee'),
      '#description' => t('If set, the fee charged by the acquirer will be calculated and added to the transaction amount.'),
      '#default_value' => $config->get('autofee'),
    );

    $form['settings']['debug'] = array(
      '#type' => 'checkbox',
      '#title' => t('Debug log'),
      '#description' => t('Log all request and responses to QuickPay in watchdog.'),
      '#default_value' => $config->get('debug'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the order_prefix setting.
    if (!preg_match('/^[a-zA-Z0-9]{0,15}$/', $form_state->getValues()['settings']['order_prefix'])) {
      $form_state->setErrorByName('order_prefix', $this->t('Order prefix must only contain alphanumerics and be no longer than 15 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('quickpay.settings');
    foreach ($form_state->getValues()['settings'] as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
    drupal_set_message(t('Settings saved.'), 'status');
  }

  /**
  * Returns an array of languages supported by Quickpay.
  *
  * @return array.
  */
  protected function get_languages() {
    return array(
      'da' => t('Danish'),
      'de' => t('German'),
      'en' => t('English'),
      'fo' => t('Faeroese'),
      'fr' => t('French'),
      'gl' => t('Greenlandish'),
      'it' => t('Italian'),
      'no' => t('Norwegian'),
      'nl' => t('Dutch'),
      'pl' => t('Polish'),
      'se' => t('Swedish'),
    );
  }

  /**
   * Information about all supported cards.
   *
   * @return array.
   */
  protected function get_quickpay_cards() {
    $images_path = drupal_get_path('module', 'quickpay') . '/images/';
    return array(
      'dankort' => array(
        'name' => t('Dankort'),
        'image' => $images_path . 'dan.jpg',
      ),
      'edankort' => array(
        'name' => t('eDankort'),
        'image' => $images_path . 'edan.jpg',
      ),
      'visa' => array(
        'name' => t('Visa'),
        'image' => $images_path . 'visa.jpg',
      ),
      'visa-dk' => array(
        'name' => t('Visa, issued in Denmark'),
        'image' => $images_path . 'visa.jpg',
      ),
      '3d-visa' => array(
        'name' => t('Visa, using 3D-Secure'),
        'image' => $images_path . '3d-visa.gif',
      ),
      '3d-visa-dk' => array(
        'name' => t('Visa, issued in Denmark, using 3D-Secure'),
        'image' => $images_path . '3d-visa.gif',
      ),
      'visa-electron' => array(
        'name' => t('Visa Electron'),
        'image' => $images_path . 'visaelectron.jpg',
      ),
      'visa-electron-dk' => array(
        'name' => t('Visa Electron, issued in Denmark'),
        'image' => $images_path . 'visaelectron.jpg',
      ),
      '3d-visa-electron' => array(
        'name' => t('Visa Electron, using 3D-Secure'),
      ),
      '3d-visa-electron-dk' => array(
        'name' => t('Visa Electron, issued in Denmark, using 3D-Secure'),
      ),
      'mastercard' => array(
        'name' => t('Mastercard'),
        'image' => $images_path . 'mastercard.jpg',
      ),
      'mastercard-dk' => array(
        'name' => t('Mastercard, issued in Denmark'),
        'image' => $images_path . 'mastercard.jpg',
      ),
      'mastercard-debet-dk' => array(
        'name' => t('Mastercard debet card, issued in Denmark'),
        'image' => $images_path . 'mastercard.jpg',
      ),
      '3d-mastercard' => array(
        'name' => t('Mastercard, using 3D-Secure'),
      ),
      '3d-mastercard-dk' => array(
        'name' => t('Mastercard, issued in Denmark, using 3D-Secure'),
      ),
      '3d-mastercard-debet-dk' => array(
        'name' => t('Mastercard debet, issued in Denmark, using 3D-Secure'),
      ),
      '3d-maestro' => array(
        'name' => t('Maestro'),
        'image' => $images_path . '3d-maestro.gif',
      ),
      '3d-maestro-dk' => array(
        'name' => t('Maestro, issued in Denmark'),
        'image' => $images_path . '3d-maestro.gif',
      ),
      'jcb' => array(
        'name' => t('JCB'),
        'image' => $images_path . 'jcb.jpg',
      ),
      '3d-jcb' => array(
        'name' => t('JCB, using 3D-Secure'),
        'image' => $images_path . '3d-jcb.gif',
      ),
      'diners' => array(
        'name' => t('Diners'),
        'image' => $images_path . 'diners.jpg',
      ),
      'diners-dk' => array(
        'name' => t('Diners, issued in Denmark'),
        'image' => $images_path . 'diners.jpg',
      ),
      'american-express' => array(
        'name' => t('American Express'),
        'image' => $images_path . 'amexpress.jpg',
      ),
      'american-express-dk' => array(
        'name' => t('American Express, issued in Denmark'),
        'image' => $images_path . 'amexpress.jpg',
      ),
      'danske-dk' => array(
        'name' => t('Danske Netbetaling'),
        'image' => $images_path . 'danskebank.jpg',
      ),
      'nordea-dk' => array(
        'name' => t('Nordea Netbetaling'),
        'image' => $images_path . 'nordea.jpg',
      ),
      'fbg1886' => array(
        'name' => t('Forbrugsforeningen'),
        'image' => $images_path . 'forbrugsforeningen.gif',
      ),
      'ikano' => array(
        'name' => t('Ikano'),
        'image' => $images_path . 'ikano.jpg',
      ),
      'paypal' => array(
        'name' => t('PayPal'),
        'image' => $images_path . 'paypal.jpg',
      ),
      'sofort' => array(
        'name' => t('Sofort'),
        'image' => $images_path . 'sofort.png',
      ),
      'viabill' => array(
        'name' => t('ViaBill'),
        'image' => $images_path . 'viabill.png',
      ),
    );
  }

}
?>
