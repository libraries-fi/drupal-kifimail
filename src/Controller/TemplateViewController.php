<?php

namespace Drupal\kifimail\Controller;

use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateViewController {
  public function title(EntityInterface $template) {
    return $template->label();
  }

  public function view(EntityInterface $kifimail, $view_mode = 'full', $langcode = NULL) {

  }

  public function ajaxPreview(Request $request, $theme) {
    $root = ['#theme' => $theme];
    $markup = \Drupal::service('renderer')->renderRoot($root);
    return new Response($markup);
  }
}
