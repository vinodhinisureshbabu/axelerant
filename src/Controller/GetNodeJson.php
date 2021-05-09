<?php

namespace Drupal\axelerant_task\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Controller for display node data in json format.
 */
class GetNodeJson extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory interface.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory  = $config_factory;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }
  /**
   * Display the json representation of node.
   * @param $nid
   * Node ID from URL.
   */
  public function data($nid) {
    /*Load node by ID */
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    /* Format the json from node values. */
    $json_array = array(
      'data' => array()
    );
    $json_array['data'][] = array(
        'type' => $node->get('type')->target_id,
        'id' => $node->get('nid')->value,
        'attributes' => array(
          'title' =>  $node->get('title')->value,
          'content' => $node->get('body')->value,
        ),
    );
    /*return a json response*/
    return new JsonResponse($json_array);
  }
  /*
   *  Access check based on URL parameters.
   *
   * @param $siteapikey
   *  API key from URL.
   *
   * @param $nid
   * node id from URL.
   *
   * @return boolean
   *   A Drupal\Core\Access\AccessResult.
  */
  public function access($siteapikey, $nid) {
    // check node id exists in page content type 
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'page')
    ->condition('nid', $nid);
    $nids = $query->execute();
    /*get the saved site api key from config */
    $stored_siteapikey = $this->config('axelerant_task.configuration')->get('siteapikey');
    /*check given node id exists and compare the site api key*/
    if (!empty($nids) && isset($stored_siteapikey) && $stored_siteapikey == $siteapikey) {
      return AccessResult::allowed(); //allow access
    }
    return AccessResult::forbidden(); // restrict access
  }
}