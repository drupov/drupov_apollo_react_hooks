<?php

namespace Drupal\drupov_apollo_react_hooks\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\drupov_apollo_react_hooks\Wrappers\PlayerConnection;

/**
 * @Schema(
 *   id = "players",
 *   name = "Players schema"
 * )
 */
class PlayersSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $registry = new ResolverRegistry();
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);
    $this->addMutationFields($registry, $builder);
    $this->addPlayerFields($registry, $builder);

    // Re-usable connection type fields.
    $this->addConnectionFields('PlayerConnection', $registry, $builder);

    return $registry;
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'player',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['player']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'players',
      $builder->produce('query_players')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addMutationFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Create player mutation.
    $registry->addFieldResolver('Mutation', 'createPlayer',
      $builder->produce('create_player')
        ->map('data', $builder->fromArgument('data'))
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addPlayerFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Player', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Player', 'first_name',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_first_name.value'))
    );

    $registry->addFieldResolver('Player', 'last_name',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_last_name.value'))
    );
  }

  /**
   * @param string $type
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addConnectionFields($type, ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (PlayerConnection $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (PlayerConnection $connection) {
        return $connection->items();
      })
    );
  }

}
