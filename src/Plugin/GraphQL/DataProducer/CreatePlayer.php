<?php

namespace Drupal\drupov_apollo_react_hooks\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates a new player entity.
 *
 * @DataProducer(
 *   id = "create_player",
 *   name = @Translation("Create Player"),
 *   description = @Translation("Creates a new player."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Player")
 *   ),
 *   consumes = {
 *     "data" = @ContextDefinition("any",
 *       label = @Translation("Player data")
 *     )
 *   }
 * )
 */
class CreatePlayer extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * CreatePlayer constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * Creates a player.
   *
   * @param array $data
   *   The title of the job.
   *
   * @return \Drupal\node\NodeInterface
   *   The newly created player.
   *
   * @throws \Exception
   */
  public function resolve(array $data) {
    if ($this->currentUser->hasPermission('create player content')) {
      $first_name = $data['first_name'];
      $last_name = $data['last_name'];

      $values = [
        'type' => 'player',
        'title' => $first_name . ' ' . $last_name,
        'field_first_name' => $first_name,
        'field_last_name' => $last_name,
      ];
      $node = Node::create($values);
      $node->save();

      return $node;
    }

    return NULL;
  }

}
