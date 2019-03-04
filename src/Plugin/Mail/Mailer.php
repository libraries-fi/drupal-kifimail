<?php

namespace Drupal\kifimail\Plugin\Mail;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Utility\Token;
use Drupal\swiftmailer\Plugin\Mail\SwiftMailer;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  protected $configFactory;
  protected $twig;
  protected $languageManager;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')->getStorage('kifimail'),
      $container->get('token'),
      $container->get('plugin.manager.mail')->createInstance('swiftmailer'),
      $container->get('config.factory'),
      $container->get('twig'),
      $container->get('language_manager')
    );
  }

  public function __construct(EntityStorageInterface $user_storage, EntityStorageInterface $template_storage, Token $token, SwiftMailer $mailer, ConfigFactoryInterface $config_factory, TwigEnvironment $twig, LanguageManagerInterface $language_manager) {
    $this->userStorage = $user_storage;
    $this->mailTemplateStorage = $template_storage;
    $this->token = $token;
    $this->mailer = $mailer;
    $this->configFactory = $config_factory;
    $this->twig = $twig;
    $this->languageManager = $language_manager;
  }

  /**
   * Format the message.
   *
   * For convenience, this mailer allows one to pass recipient, sender etc. mail addresses as
   * user entities or account proxies. Mailer will then extract the email address from these objects
   * and also use the person's name.
   */
  public function format(array $message) {
    $message['to'] = $this->extractMailAddress($message['to']);
    $reply_to = $message['reply-to'];
    $config = $this->configFactory->get('system.site');

    if (isset($message['params']['from'])) {
      $from = $this->extractMailAddress($message['params']['from']);
      $message['from'] = $from;
      $message['headers']['Sender'] = $message['from'];
    }

    // Format with site name when using site email as sender.
    if ($message['from'] == $config->get('mail')) {
      $message['from'] = $this->extractMailAddress([
        'name' => $config->get('name'),
        'email' => $config->get('mail'),
      ]);
      $message['headers']['Sender'] = $message['from'];
    }

    if ($reply_to instanceof UserInterface || $reply_to instanceof AccountInterface) {
      $message['reply-to'] = $this->extractMailAddress($reply_to);
      $message['headers']['Reply-to'] = $message['reply-to'];
    }

    $template = $this->loadTemplate($message['id'], $message['langcode']);
    $subject = $this->token->replace($template->getSubject(), $message['params']);
    $body = $this->token->replace($template->getBody(), $message['params']);

    $markup = $this->twig->renderInline($body, $message['params']);

    $message['subject'] = $subject;
    $message['body'] = [$markup];
    $message['params']['theme'] = $template->getTheme();
    $message['params']['with_signature'] = $template->isWithSignature();

    $message['headers']['Content-Type'] = 'text/html; charset=UTF-8';

    return $this->mailer->format($message);
  }

  public function mail(array $message) {
    $status = true;

    foreach ($this->recipientChunks($message['to']) as $recipients) {
      print($recipients . PHP_EOL . PHP_EOL);
      $message['to'] = $recipients;
      $status &= $this->mailer->mail($message);
    }

    return $status;
  }

  protected function extractMailAddress($info) {
    if (is_array($info)) {
      $name = empty($info['name']) ? NULL : $info['name'];
      $email = empty($info['email']) ? NULL : $info['email'];
      return $name ? sprintf('%s <%s>', $name, $email) : $email;
    } elseif ($info instanceof AccountInterface) {
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

  protected function loadTemplate($template_id, $langcode = NULL) {
    if ($langcode) {
      $tmp = $this->languageManager->getConfigOverrideLanguage();
      $this->languageManager->setConfigOverrideLanguage($this->languageManager->getLanguage($langcode));
      $template = $this->mailTemplateStorage->load($template_id);
      $this->languageManager->setConfigOverrideLanguage($tmp);
    } else {
      $template = $this->mailTemplateStorage->load($template_id);
    }

    if (!$template) {
      throw new \Error(sprintf("Template '{$template_id}' does not exist."));
    }

    return $template;
  }

  /**
   * Splits recipients to chunks to avoid too long headers.
   */
  private function recipientChunks($recipients) {
    $recipients = explode(',', $recipients);
    $max_length = 998;
    $chunk = '';

    foreach ($recipients as $recipient) {
      $new_chunk = ltrim(sprintf('%s, %s', $chunk, trim($recipient)), ', ');
      if (strlen($new_chunk) < $max_length) {
        $chunk = $new_chunk;
      } else {
        yield $chunk;
        $chunk = $recipient;
      }
    }

    yield $chunk;
  }
}
