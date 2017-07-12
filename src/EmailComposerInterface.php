<?php

namespace Drupal\kifimail;

interface EmailComposerInterface {
  public function compose($template_id, array $variables = []);
}
