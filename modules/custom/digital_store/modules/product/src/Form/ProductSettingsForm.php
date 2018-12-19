<?php

namespace Drupal\digital_store_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Product settings.
 */
class ProductSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_store_product_product_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['digital_store_product.settings.prices'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('digital_store_product.settings.prices');

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Price Options title.'),
      '#required' => TRUE,
      '#default_value' => $config->get('title'),
    ];

    $form['quick_buy'] = [
      '#type' => 'radios',
      '#title' => $this->t('Do you want to activate Quick Buy in the product detail?'),
      '#options' => [
        TRUE => $this->t('Yes'),
        FALSE => $this->t('No'),
      ],
      '#default_value' => (int) $config->get('quick_buy'),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Price Options description.'),
      '#default_value' => $config->get('description'),
      '#rows' => 4,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('digital_store_product.settings.prices');
    if (isset($values['title'])) {
      $config->set('title', $values['title']);
    }
    if (isset($values['description'])) {
      $config->set('description', $values['description']);
    }
    if (isset($values['quick_buy'])) {
      $config->set('quick_buy', $values['quick_buy']);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
