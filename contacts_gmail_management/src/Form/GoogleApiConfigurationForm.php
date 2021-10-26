<?php
/**
 * @file
 * Contains \Drupal\contacts_gmail_management\Form\GoogleApiConfigurationForm.
 */

namespace Drupal\contacts_gmail_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a google_api_configuration_form form.
 */
class GoogleApiConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'google_api.settings',
    ];
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'google_api_settings_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_api.settings');
    global $base_url;
    $form['google_api'] = array(
      '#type' => 'details',
      '#title' => t('Google API OAuth Credentials'),
      '#open' => TRUE,
      '#weight' => 0,
    );
    $form['google_api']['client_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Client id'),
      '#required' => TRUE,
      '#default_value' => $config->get('client_id'),
    );
    $form['google_api']['client_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Client secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('client_secret'),
    );
    $form['google_api']['redirect_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Redirect Url'),
      '#default_value' => $config->get('redirect_url'),
      '#description' => $base_url,
    );
    $form['google_api']['source'] = array(
      '#type' => 'radios',
      '#title' => t('Source where contacts will be imported'),
      '#description' => t('Check address book if you want to get contacts from Gmail address book or "other_contacts" if you want to get contacts that are not saved in your address book, but you have interacted with'),
      '#options' =>[
        '0' => 'Address book',
        '1' => 'Other contacts'
      ],
      '#default_value' => $config->get('source'),
    );

   $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage(t('The values are being saved correctly'), 'status');
    $values = $form_state->getValues();
    foreach ($values as $key => $value) {
      \Drupal::getContainer()->get('config.factory')
        ->getEditable('google_api.settings')->set($key, $value)
        ->save();
    }
  }
}


