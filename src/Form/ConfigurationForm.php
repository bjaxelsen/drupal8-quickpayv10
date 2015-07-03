<?php
/**
 * @file
 * Contains \Drupal\quickpay\Form\ConfigurationForm.
 */

namespace Drupal\quickpay\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Form controller for the Quickpay configuration entity edit forms.
 */
class ConfigurationForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\quickpay\Entity\Quickpay $entity */
    $entity = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Configuration name'),
      '#default_value' => $entity->label(),
      '#size' => 30,
      '#required' => TRUE,
      '#maxlength' => 64,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
      '#size' => 30,
      '#maxlength' => 64,
      '#machine_name' => array(
        'exists' => ['\Drupal\quickpay\Entity\Quickpay', 'load'],
      ),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Configuration description'),
      '#description' => $this->t('Where the configuration is used, usecases, etc.'),
      '#default_value' => $entity->get('description'),
      '#required' => TRUE,
    );

    $form['merchant_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Merchant ID'),
      '#description' => t('This is the Merchant ID from the Quickpay manager.'),
      '#default_value' => $entity->get('merchant_id'),
      '#required' => TRUE,
    );

    $form['agreement_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Agreement ID'),
      '#description' => t('This is the Agreement ID from the Quickpay manager.'),
      '#default_value' => $entity->get('agreement_id'),
      '#required' => TRUE,
    );

    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('API'),
      '#description' => t('This is the API key from the Quickpay manager.'),
      '#default_value' => $entity->get('api_key'),
      '#required' => TRUE,
    );

    $form['private_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Private'),
      '#description' => t('This is the private key from the Quickpay manager.'),
      '#default_value' => $entity->get('private_key'),
      '#required' => TRUE,
    );

    $form['order_prefix'] = array(
      '#type' => 'textfield',
      '#title' => t('Order ID prefix'),
      '#description' => t('Prefix for order IDs. Order IDs must be uniqe when sent to QuickPay, use this to resolve clashes.'),
      '#default_value' => $entity->get('order_prefix'),
    );

    $languages = $this->getLanguages() + array(LanguageInterface::LANGCODE_NOT_SPECIFIED => t('Language of the user'));
    $form['language'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#description' => t('The language for the credit card form.'),
      '#options' => $languages,
      '#default_value' => $entity->get('language'),
    );

    $form['method'] = array(
      '#type' => 'radios',
      '#id' => 'quickpay-method',
      '#title' => t('Accepted payment methods'),
      '#description' => t('Which payment methods to accept. NOTE: Some require special agreements.'),
      '#default_value' => $entity->get('payment_method'),
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
    foreach ($this->getQuickpayCards() as $key => $card) {
      $options[$key] = empty($card['image']) ? $card['name'] : '<img src="/' . $card['image'] . '" />' . $card['name'];
    }

    $form['accepted_cards'] = array(
      '#type' => 'checkboxes',
      '#id' => 'quickpay-cards',
      '#title' => t('Select accepted cards'),
      '#default_value' => $entity->get('accepted_cards'),
      '#options' => $options,
    );

    $form['autofee'] = array(
      '#type' => 'checkbox',
      '#title' => t('Autofee'),
      '#description' => t('If set, the fee charged by the acquirer will be calculated and added to the transaction amount.'),
      '#default_value' => $entity->get('autofee'),
    );

    $form['autocapture'] = array(
      '#type' => 'checkbox',
      '#title' => t('Autocapture'),
      '#description' => t('If set, the transactions will be automatically captured.'),
      '#default_value' => $entity->get('autocapture'),
    );

    $form['debug'] = array(
      '#type' => 'checkbox',
      '#title' => t('Debug log'),
      '#description' => t('Log all request and responses to QuickPay in the Watchdog.'),
      '#default_value' => $entity->get('debug'),
    );

    return parent::form($form, $form_state, $this->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\quickpay\Entity\Quickpay $entity */
    $entity = $this->entity;

    // Prevent leading and trailing spaces.
    $entity->set('label', trim($entity->label()));
    $entity->set('description', $form_state->getValue('description'));
    $entity->set('merchant_id', $form_state->getValue('merchant_id'));
    $entity->set('agreement_id', $form_state->getValue('agreement_id'));
    $entity->set('api_key', $form_state->getValue('api_key'));
    $entity->set('private_key', $form_state->getValue('private_key'));
    $entity->set('order_prefix', $form_state->getValue('order_prefix'));
    $entity->set('language', $form_state->getValue('language'));
    $entity->set('method', $form_state->getValue('method'));
    if ($form_state->getValue('method') === 'selected') {
      $entity->set('accepted_cards', $form_state->getValue('accepted_cards'));
    }
    else {
      $entity->set('accepted_cards', array($form_state->getValue('method')));
    }
    $entity->set('autofee', $form_state->getValue('autofee'));
    $entity->set('debug', $form_state->getValue('debug'));
    $status = $entity->save();

    $edit_link = $entity->link($this->t('Edit'));
    $action = $status == SAVED_UPDATED ? 'updated' : 'added';

    drupal_set_message($this->t('Quickpay configuration %label has been %action.', ['%label' => $entity->label(), '%action' => $action]));
    $this->logger('quickpay')->notice('Quickpay configuration %label has been %action.', array('%label' => $entity->label(), 'link' => $edit_link));

    $form_state->setRedirect('quickpay.configuration_list');
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = ($this->entity->isNew()) ? $this->t('Add configuration') : $this->t('Update configuration');
    return $actions;
  }

  /**
   * Returns an array of languages supported by Quickpay.
   *
   * @return array
   *   Array with key being language codes, and value being names.
   */
  protected function getLanguages() {
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
   * @return array
   *   Array with card name and image.
   */
  protected function getQuickpayCards() {
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
