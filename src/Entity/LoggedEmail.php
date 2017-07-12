<?php

namespace Drupal\kifimail\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\kifimail\LoggedEmailInterface;

/**
 * @ContentEntityType(
 *   id = "kifimail_log",
 *   label = @Translation("Email log entry"),
 *   handlers = {
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *   },
 *   base_table = "kifimail_log",
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "user",
 *     "uuid" = "uuid",
 *     "label" = "subject",
 *     "langcode" = "langcode",
 *   },
 * )
 */
class LoggedEmail extends ContentEntityBase implements LoggedEmailInterface {
  public function getEmailAddress() {
    return $this->get('email')->value;
  }

  public function getSubject() {
    return $this->get('subject')->value;
  }

  public function getBody() {
    return $this->get('body')->value;
  }

  public function getSentTime() {
    return $this->get('sent')->value;
  }

  public function getModuleId() {
    return $this->get('module')->value;
  }

  public function getMessageId() {
    return $this->get('message_id')->value;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('Log entry ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the log entry.'))
      ->setReadOnly(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('Language of the message.'))
      ->setRequired(TRUE);

    $fields['module'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Module'))
      ->setDescription(t('Module from which the message was sent.'))
      ->setSetting('max_length', 100);

    $fields['message_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Message ID'))
      ->setDescription(t('Custom message identifier.'))
      ->setSetting('max_length', 100);

    $fields['sent'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the message was sent.'));

    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Email address'))
      ->setDescription(t('Recipient email address.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setDescription(t('Message subject.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['body'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Message body'))
      ->setDescription(t('Content of the message.'))
      ->setRequired(TRUE);

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Sender'))
      ->setDescription(t('User who sent the email.'))
      ->setSettings(['target_type' => 'user']);

    return $fields;
  }
}
