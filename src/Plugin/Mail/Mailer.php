<?php

namespace Drupal\kifimail\Plugin\Mail;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\swiftmailer\Plugin\Mail\SwiftMailer;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Render\Markup;

/**
 * @Mail(
 *   id = "kifimail",
 *   label = @Translation("Kifimail"),
 *   description = @Translation("Kifimailer mail plugin.")
 * )
 */
class Mailer implements MailInterface, ContainerFactoryPluginInterface {
  protected $userStorage;
  protected $mailTemplateStorage;
  protected $token;
  protected $mailer;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')->getStorage('kifimail'),
      $container->get('token'),
      $container->get('plugin.manager.mail')->createInstance('swiftmailer')
    );
  }

  public function __construct(EntityStorageInterface $user_storage, EntityStorageInterface $template_storage, Token $token, SwiftMailer $mailer) {
    $this->userStorage = $user_storage;
    $this->mailTemplateStorage = $template_storage;
    $this->token = $token;
    $this->mailer = $mailer;
  }

  /**
   * Format the message.
   *
   * For convenience, this mailer allows one to pass recipient, sender etc. mail addresses as
   * user entities or account proxies. Mailer will then extract the email address from these objects
   * and also use the person's name.
   */
  public function format(array $message) {
    $message['to'] = $this->extractMailName($message['to']);
    $reply_to = $message['reply-to'];

    if (isset($message['params']['from'])) {
      $from = $this->extractMailName($message['params']['from']);
      $message['headers']['From'] = $from;
      $message['headers']['Sender'] = $from;
    }

    if ($reply_to instanceof UserInterface || $reply_to instanceof AccountInterface) {
      $message['reply-to'] = $this->extractMailName($reply_to);
      $message['headers']['Reply-to'] = $message['reply-to'];
    }

    $template = $this->loadTemplate($message['id']);
    $subject = $this->token->replace($template->getSubject(), $message['params']);
    $body = $this->token->replace($template->getBody(), $message['params']);

    $message['subject'] = $subject;
    $message['body'] = [Markup::create($body)];
    $message['params']['theme'] = $template->getTheme();
    $message['params']['with_signature'] = $template->isWithSignature();

    return $this->mailer->format($message);
  }

  public function mail(array $message) {
    return $this->mailer->mail($message);
  }

  protected function extractMailName($info) {
    if ($info instanceof AccountInterface) {
      $info = $this->userStorage->load($info->id());
    }
    if ($info instanceof UserInterface) {
      if ($name = $info->get('field_real_name')->value) {
        return sprintf('%s <%s>', $name, $info->getEmail());
      } else {
        return $info->getEmail();
      }
    }
    return $info;
  }

  protected function loadTemplate($template_id) {
    if ($template = $this->mailTemplateStorage->load($template_id)) {
      return $template;
    }
    throw new \Error(sprintf("Template '{$template_id}' does not exist."));
  }
}
