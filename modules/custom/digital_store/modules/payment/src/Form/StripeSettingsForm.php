<?php

namespace Drupal\digital_store_payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\digital_store_payment\PaymentGatewayMode;

/**
 * Stripe Connection settings.
 */
class StripeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_store_admin_stripe_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['digital_store.settings.stripe'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('digital_store.settings.stripe');

    $form['stripe_api_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stripe API Endpoint'),
      '#required' => TRUE,
      '#default_value' => $config->get('stripe_api_endpoint'),
    ];

    $form['publishable_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Publishable Key'),
      '#default_value' => $config->get('publishable_key'),
      '#attributes' => [
        'autocomplete' => 'off',
        'placeholder' => 'xxxxxxxxxxxxxxxxxxx',
      ],
    ];

    $form['secret_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret Key'),
      '#default_value' => $config->get('secret_key'),
      '#attributes' => [
        'autocomplete' => 'off',
        'placeholder' => 'xxxxxxxxxxxxxxxxxxx',
      ],
    ];

    $form['connection_environment'] = [
      '#type' => 'select',
      '#options' => PaymentGatewayMode::getModeLabels(),
      '#required' => TRUE,
      '#weight' => 3,
      '#title' => t('Connection Environment'),
      '#description' => t('Please select Connection Environment'),
      '#default_value' => $config->get('connection_environment'),
    ];

    $form['capture'] = [
      '#type' => 'radios',
      '#title' => $this->t('Transaction mode'),
      '#description' => $this->t('This setting is only respected if the chosen payment gateway supports authorizations.'),
      '#options' => [
        TRUE => $this->t('Authorize and capture'),
        FALSE => $this->t('Authorize only (requires manual capture after checkout)'),
      ],
      '#default_value' => (int) $config->get('capture'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('digital_store.settings.stripe');
    if (isset($values['publishable_key']) && !empty($values['publishable_key'])) {
      $config->set('publishable_key', $values['publishable_key']);
    }
    if (isset($values['secret_key']) && !empty($values['secret_key'])) {
      $config->set('secret_key', $values['secret_key']);
    }
    if (isset($values['connection_environment'])) {
      $config->set('connection_environment', $values['connection_environment']);
    }
    if (isset($values['stripe_api_endpoint'])) {
      $config->set('stripe_api_endpoint', $values['stripe_api_endpoint']);
    }
    if (isset($values['capture'])) {
      $config->set('capture', $values['capture']);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
