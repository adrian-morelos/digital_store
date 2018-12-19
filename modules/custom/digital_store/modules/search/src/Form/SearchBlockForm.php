<?php

namespace Drupal\digital_store_search\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the search form for the search page block.
 */
class SearchBlockForm extends FormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new SearchBlockForm.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(LanguageManagerInterface $language_manager, RendererInterface $renderer) {
    $this->languageManager = $language_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_store_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $args = []) {
    $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->getId();
    $default_value = '';
    if (isset($args['keys'])) {
      $default_value = $args['keys'];
    }
    elseif ($search_value = $this->getRequest()->get('keys')) {
      $default_value = $search_value;
    }
    $keys_title = $this->t(
      'Enter the terms you wish to search for.',
      [],
      ['langcode' => $langcode]
    );
    $form['keys'] = [
      '#type' => 'search',
      '#title' => $this->t('Search', [], ['langcode' => $langcode]),
      '#title_display' => 'invisible',
      '#size' => 15,
      '#default_value' => $default_value,
      '#attributes' => [
        'title' => $keys_title,
        'placeholder' => 'Type here to search'
      ],
    ];
    $form['submit'] = [
      '#type' => 'markup',
      '#markup' => '<button type="submit" class="btn s-btn">' . $this->getSearchLabel() . '</button>',
      '#allowed_tags' => ['button', 'svg', 'path'],
    ];
    $route = 'digital_store_search.search';
    $form['#action'] = $this->getUrlGenerator()->generateFromRoute($route);
    $form['#method'] = 'get';
    // Dependency on search api config entity.
    $this->renderer->addCacheableDependency($form, $langcode);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route = 'digital_store_search.search';
    // This form submits to the search page, so processing happens there.
    $keys = $form_state->getValue('keys');
    $form_state->setRedirectUrl(Url::fromRoute($route, ['keys' => $keys]));
  }

  /**
   * Get the Search label.
   *
   * @return string
   *   The search label.
   */
  public function getSearchLabel() {
    return $this->t("<svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='14px' height='14px'> <path fill-rule='evenodd'  fill='rgb(255, 255, 255)' d='M13.608,13.607 C13.097,14.119 12.267,14.119 11.755,13.607 L8.547,10.400 C9.290,9.922 9.922,9.290 10.400,8.546 L13.608,11.754 C14.120,12.266 14.120,13.096 13.608,13.607 ZM5.254,10.496 C2.358,10.496 0.011,8.149 0.011,5.253 C0.011,2.358 2.358,0.010 5.254,0.010 C8.149,0.010 10.497,2.358 10.497,5.253 C10.497,8.149 8.149,10.496 5.254,10.496 ZM5.254,1.321 C3.085,1.321 1.322,3.085 1.322,5.253 C1.322,7.422 3.085,9.186 5.254,9.186 C7.422,9.186 9.186,7.422 9.186,5.253 C9.186,3.085 7.422,1.321 5.254,1.321 ZM3.069,5.253 L2.195,5.253 C2.195,3.567 3.568,2.195 5.254,2.195 L5.254,3.069 C4.049,3.069 3.069,4.049 3.069,5.253 Z'/></svg>");
  }

}
