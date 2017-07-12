<?php

namespace Drupal\kifimail;

use Exception;
use Twig_Environment as Twig;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\Markup;

/**
 * @deprecated Use Drupal\kifimail\Plugin\Mail\Mailer::format instead.
 */
class EmailComposer implements EmailComposerInterface {
  private $entity_manager;
  private $twig;

  public function __construct(EntityManagerInterface $entity_manager, Twig $twig) {
    $this->entity_manager = $entity_manager;
    $this->renderer = $twig;
  }

  /**
   * Render template into a message body.
   */
  public function composeBody($template_id, array $variables = [], $process = true) {
    if (is_object($template_id)) {
      $template = $template_id;
    } else {
      $template = $this->loadTemplate($template_id);
      if (!$template) {
        throw new Exception(sprintf('Template \'%s\' does not exist', $template_id));
      }
    }
    if ($process) {
      $variables = $this->processVariables($variables);
    }
    // Prefix required by Drupal's string loader.
    $body = '{# inline_template_start #}' . $template->getBody();
    $markup = $this->renderer->render($body, $variables);
    return Markup::create($markup);
  }

  /**
   * Compose a Drupal mail() compatible message.
   */
  public function compose($template_id, array $variables = []) {
    if (is_object($template_id)) {
      $template = $template_id;
    } else {
      $template = $this->loadTemplate($template_id);
      if (!$template) {
        throw new Exception(sprintf('Template \'%s\' does not exist', $template_id));
      }
    }
    return [
      'body' => $this->composeBody($template, $variables),
      'subject' => $template->getSubject(),
      'style' => $template->getStyle(),
      'theme' => $template->getTheme(),
    ];
  }

  public function loadTemplate($template_id) {
    return $this->entity_manager->getStorage('kifimail')->load($template_id);
  }

  public function processVariables(array $variables) {
    foreach ($variables as $key => $value) {
      if (is_object($value)) {
        foreach ($this->processors as $type => $processor) {
          if (is_a($value, $type)) {
            $processor->process($value, $variables);
            break;
          }
        }
      }
    }
    return $variables;
  }

  public function test() {
    var_dump($this->processors);
  }

  public function addProcessor(VariablesProcessorInterface $processor, $type) {
    $this->processors[$type] = $processor;
  }
}
