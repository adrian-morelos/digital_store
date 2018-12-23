<?php

namespace Drupal\digital_store;

/**
 * Digital Store Asset Libraries Helper class.
 */
class DigitalStoreAssetLibraries {

  /**
   * The currently path.
   *
   * @var string
   */
  protected $currentPath = NULL;

  /**
   * Attach Stylesheets based on the page context.
   *
   * @param array $variables
   *   The array with the page variables.
   */
  public function attachStylesheets(array &$variables = []) {
    $items = [];
    if ($variables['is_front']) {
      // Attach Stylesheets on front page.
      $items[] = 'front';
    }
    elseif (isset($variables['node'])) {
      // Attach Stylesheets on node page.
      /* @var \Drupal\node\NodeInterface $node */
      $node = $variables['node'];
      $bundle = $node->bundle();
      $items[] = $bundle;
    }
    else {
      // Attach Stylesheets on normal page based on current path.
      $current_path = $this->getCurrentPath();
      $css_name = $this->currentPathToCssName($current_path);
      $items[] = $css_name;
    }
    // Attach Styles based on roles.
    $current_user = $variables['user'] ?? NULL;
    if ($current_user && ($current_user->isAuthenticated())) {
      // Attach Admin Roles.
      $roles = $current_user->getRoles();
      $bundle = 'shop_manager';
      if (in_array($bundle, $roles)) {
        $items[] = $bundle;
      }
    }
    // Attached relevant stylesheets.
    if (empty($items)) {
      // Stop Here.
      return;
    }
    $theme_path = $variables['directory'] ?? NULL;
    if (empty($theme_path)) {
      // Stop Here.
      return;
    }
    foreach ($items as $delta => $bundle) {
      // Get stylesheet content.
      $styles = $this->getCssFilePath($bundle, $theme_path, TRUE);
      // Attach styles.
      $this->attachStyleTag($variables, $styles, $bundle);
    }
  }

  /**
   * Attach Style Tag to the head of the Html structure.
   *
   * @param array $element
   *   The array with the page variables.
   * @param string $value
   *   The stylesheet content.
   * @param string $context
   *   The tag context.
   */
  public function attachStyleTag(array &$element = [], $value = NULL, $context = NULL) {
    if (empty($value) || empty($value)) {
      // Nothing to do, stop here.
      return;
    }
    $identifier = !empty($context) ? $context . '-css' : NULL;
    $element['#attached']['html_head'][] = [
      [
        '#tag' => 'style',
        '#value' => $value,
        '#attributes' => [
          'context' => $context,
        ],
      ],
      $identifier
    ];
  }

  /**
   * Converts Current Path to a valid CSS name.
   *
   * @param string $current_path
   *   The current path.
   *
   * @return string
   *   The CSS name, otherwise NULL.
   */
  public function currentPathToCssName($current_path = NULL) {
    if (empty($current_path)) {
      return NULL;
    }
    $current_path = trim($current_path, '/');
    $items = explode('/', $current_path);
    $file_path_items = [];
    // Remove numeric string from the path.
    foreach ($items as $delta => $item) {
      if (!is_numeric($item)) {
        $file_path_items[] = $item;
      }
    }
    return implode('-', $file_path_items);
  }

  /**
   * Gets css file path.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The currently active request object.
   */
  public function getCurrentPath() {
    if (is_null($this->currentPath)) {
      $this->currentPath = \Drupal::service('path.current')->getPath();
    }
    return $this->currentPath;
  }

  /**
   * Gets css file path.
   *
   * @param string $name
   *   The file name.
   * @param string $theme_path
   *   The theme path.
   * @param bool $load
   *   The flag that determines if the file's content should be loaded.
   *
   * @return string|FALSE
   *   The string path if the file exits, Otherwise FALSE.
   *   If the flag load is sent in TRUE then will to return the file's content.
   */
  public function getCssFilePath($name = NULL, $theme_path = NULL, $load = FALSE) {
    if (empty($name) || empty($theme_path)) {
      return FALSE;
    }
    $path = $theme_path . '/css/' . $name . '.css';
    if (!file_exists($path)) {
      return FALSE;
    }
    if ($load) {
      return file_get_contents($path);
    }
    return $path;
  }

}
