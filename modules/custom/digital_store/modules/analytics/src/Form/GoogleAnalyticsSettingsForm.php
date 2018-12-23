<?php

namespace Drupal\digital_store_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Google Analytics Measurement Protocol settings.
 */
class GoogleAnalyticsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_store_analytics_google_analytics_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['digital_store_analytics.settings.google_analytics'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('digital_store_analytics.settings.google_analytics');

    $form['protocol_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Protocol Version'),
      '#required' => TRUE,
      '#default_value' => $config->get('protocol_version'),
    ];

    $form['tid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tracking ID / Web Property ID'),
      '#default_value' => $config->get('tid'),
      '#required' => TRUE,
      '#description' => $this->t('The tracking ID / web property ID. The format is UA-XXXX-Y. All collected data is associated by this ID.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('digital_store_analytics.settings.google_analytics');
    if (isset($values['tid']) && !empty($values['tid'])) {
      $config->set('tid', $values['tid']);
    }
    if (isset($values['secret_key']) && !empty($values['secret_key'])) {
      $config->set('secret_key', $values['secret_key']);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
