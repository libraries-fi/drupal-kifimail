<?php

namespace Drupal\kifimail;

use Drupal\Core\Entity\ContentEntityInterface;

interface LoggedEmailInterface extends ContentEntityInterface {
  public function getEmailAddress();
  public function getSubject();
  public function getBody();
  public function getSentTime();
  public function getModuleId();
  public function getMessageId();
}
