<?php

namespace Drupal\kifimail;

use Traversable;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class VariablesProcessorManager extends DefaultPluginManager {
  public function __construct(Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/kifimail/Email', $namespaces, $module_handler, VariablesProcessorInterface::class);

    $this->alterInfo('kifimail_variables');
    $this->setCacheBackend($cache_backend, 'kifimail_variables_plugins');
  }
}
