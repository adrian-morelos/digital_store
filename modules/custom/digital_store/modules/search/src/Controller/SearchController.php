<?php

namespace Drupal\digital_store_search\Controller;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\SearchApiException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Search Controller.
 */
class SearchController extends ControllerBase {

  /**
   * The search ID.
   *
   * The search ID is a freely-chosen machine name identifying this search query
   * for purposes of identifying the query later in the page request. It will be
   * used, amongst other things, to identify the query in the search results
   * cache service.
   */
  const SEARCH_ID = 'global_search';

  /**
   * Return the limit per page.
   *
   *  The page limit.
   */
  const PAGE_LIMIT = 21;

  /**
   * Return the search api index.
   *
   *  The index.
   */
  const SEARCH_API_INDEX = 'global_search';

  /**
   * Return the search api index.
   *
   * @return string
   *   The index.
   */
  public function getIndex() {
    return self::SEARCH_API_INDEX;
  }

  /**
   * Return the limit per page.
   *
   * @return int
   *   The page limit.
   */
  public function getLimit() {
    return self::PAGE_LIMIT;
  }

  /**
   * Get the search offset.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return int
   *   The offset value.
   */
  public function getOffSet(Request $request = NULL) {
    if (!$request) {
      return 0;
    }
    $page = $request->get('page');
    if (is_null($page)) {
      return 0;
    }
    return ($page * $this->getLimit());
  }

  /**
   * Page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   The page build.
   */
  public function resultPage(Request $request) {
    $build = [];
    // Keys can be in the query.
    $keys = $request->get('keys');
    $perform_search = !empty($keys);
    $result_count = 0;
    $results = [];
    $search_response = ($perform_search) ? $this->doSearch($request, $keys) : [];
    if (!empty($search_response)) {
      $result_count = $search_response['result_count'];
      $results = $search_response['results'];
    }
    // Handle the result.
    if (!empty($results)) {

      $build['#search_title'] = [
        '#markup' => $this->t('Search results'),
      ];

      $build['#no_of_results'] = [
        '#markup' => $this->formatPlural($result_count, '1 result found', '@count results found'),
      ];

      $build['#results'] = $results;

      // Build pager.
      pager_default_initialize($result_count, $this->getLimit());
      $build['#pager'] = [
        '#type' => 'pager',
      ];
    }
    elseif ($perform_search) {
      $build['#no_results_found'] = [
        '#markup' => $this->t('Your search yielded no results.'),
      ];

      $build['#search_help'] = [
        '#markup' => $this->t('<ul>
<li>Check if your spelling is correct.</li>
<li>Remove quotes around phrases to search for each word individually. <em>bike shed</em> will often show more results than <em>&quot;bike shed&quot;</em>.</li>
<li>Consider loosening your query with <em>OR</em>. <em>bike OR shed</em> will often show more results than <em>bike shed</em>.</li>
</ul>'),
      ];
    }
    return $build;
  }

  /**
   * Perform a search.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $keys
   *   The search word.
   *
   * @return array
   *   The search response.
   */
  public function doSearch(Request $request, $keys) {
    if (empty($keys)) {
      return [];
    }
    /* @var $search_api_index \Drupal\search_api\IndexInterface */
    $search_api_index = Index::load($this->getIndex());
    // Create the query.
    $query = $search_api_index->query([
      'limit' => $this->getLimit(),
      'offset' => $this->getOffSet($request),
    ]);
    // Set the Search ID.
    $query->setSearchID(self::SEARCH_ID);
    // Set the parse mode: Direct.
    $parse_mode = \Drupal::getContainer()
      ->get('plugin.manager.search_api.parse_mode')
      ->createInstance('direct');
    $query->setParseMode($parse_mode);
    // Search for keys.
    if (!empty($keys)) {
      $query->keys($keys);
    }
    // Add filter for current language.
    $langcode = \Drupal::service('language_manager')
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->getId();
    $query->addCondition('search_api_language', $langcode);

    $result = $query->execute();
    $items = $result->getResultItems();
    if (empty($items)) {
      return [];
    }
    /* @var $item \Drupal\search_api\Item\ItemInterface */
    $results = [];
    foreach ($items as $item) {
      try {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $item->getOriginalObject()->getValue();
      } catch (SearchApiException $e) {
        continue;
      }
      if (!$entity) {
        continue;
      }
      // Render as view modes.
      $view_mode = 'default';
      $results[] = $this->entityTypeManager()
        ->getViewBuilder($entity->getEntityTypeId())
        ->view($entity, $view_mode);
    }
    return [
      'results' => $results,
      'result_count' => $result->getResultCount(),
    ];
  }

}
