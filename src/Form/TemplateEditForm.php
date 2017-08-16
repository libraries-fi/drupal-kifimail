<?php

namespace Drupal\kifimail\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TemplateEditForm extends EntityForm {
  private $config;

  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }

  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config;
  }

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['#attached']['library'][] = 'kifimail/template_preview';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Template name'),
      '#required' => TRUE,
      '#default_value' => $this->entity->label(),
    ];

    $form['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier'),
      '#description' => $this->t('Identifier has to match that used in application code.'),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#default_value' => $this->entity->id(),
    ];

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#description' => $this->t('Base layout used for messages'),
      '#options' => $this->getMailThemes(),
      '#empty_option' => '',
      '#default_value' => $this->entity->getTheme(),
      '#required' => TRUE,
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#description' => $this->t('Email message subject'),
      '#default_value' => $this->entity->getSubject(),
      '#required' => TRUE,
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#rows' => 15,
      '#required' => TRUE,
      '#default_value' => $this->entity->getBody(),
      '#description' => htmlspecialchars($this->t('Contents of <body> tag.')),
      '#attributes' => [
        'class' => ['code-editor'],
        'data-editor-mode' => 'xml'
      ]
    ];

    $form['with_signature'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Attach user signature to messages'),
      '#default_value' => $this->entity->isWithSignature(),
    ];
    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    $template = $this->entity;
    $status = $template->save();
    $form_state->setRedirectUrl($template->urlInfo('collection'));

    if ($status = SAVED_UPDATED) {
      drupal_set_message($this->t('Email template %template has been updated.', [
        '%template' => $template->label(),
      ]));
    } else {
      drupal_set_message($this->t('Email template %template has been added.', [
        '%template' => $template->label(),
      ]));
    }
  }

  private function getMailThemes() {
    $themes = $this->config->get('kifimail.settings')->get('themes');
    return array_combine($themes, $themes);
  }
}
