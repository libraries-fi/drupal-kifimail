<?php

namespace Drupal\kifimail;

use Drupal\Core\Entity\EntityInterface;

interface VariablesProcessorInterface {
  public function process(EntityInterface $entity, array &$variables);
}
