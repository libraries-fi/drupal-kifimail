<?php

namespace Drupal\kifimail\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the contact form entity.
 *
 * @ConfigEntityType(
 *   id = "kifimail",
 *   label = @Translation("Kifimail"),
 *   handlers = {
 *     "list_builder" = "Drupal\kifimail\TemplateListBuilder",
 *     "form" = {
 *       "add" = "Drupal\kifimail\Form\TemplateEditForm",
 *       "edit" = "Drupal\kifimail\Form\TemplateEditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "template",
 *   admin_permission = "administer kifimail",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/kifimail/{kifimail}/preview",
 *     "collection" = "/admin/kifimail",
 *     "delete-form" = "/admin/kifimail/{kifimail}/delete",
 *     "edit-form" = "/admin/kifimail/{kifimail}",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "theme",
 *     "subject",
 *     "body",
 *     "with_signature",
 *   }
 * )
 */
class Template extends ConfigEntityBase {
  protected $id;
  protected $label;
  protected $theme;
  protected $subject;
  protected $body;
  protected $with_signature;

  public function getTheme() {
    return $this->theme;
  }

  public function setTheme($theme) {
    $this->theme = $theme;
  }

  public function getSubject() {
    return $this->subject;
  }

  public function setSubject($subject) {
    $this->subject = $subject;
  }

  public function getBody() {
    return $this->body;
  }

  public function setBody($body) {
    $this->body = $body;
  }

  public function isWithSignature() {
    return (bool)$this->with_signature;
  }

  public function setWithSignature($state) {
    $this->with_signature = (bool)$state;
  }
}
