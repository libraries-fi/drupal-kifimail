<?php

namespace Drupal\kifimail;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class TemplateListBuilder extends EntityListBuilder {
  public function buildHeader() {
    $header = [
      'id' => [
        'data' => $this->t('ID'),
        'style' => 'width: 150px',
      ],
      'label' => $this->t('Nimi'),
    ];
    return $header + parent::buildHeader();
  }

  public function buildRow(EntityInterface $template) {
    $row = [
      'id' => $template->id(),
      'label' => $template->label(),
    ];
    return $row + parent::buildRow($template);
  }

  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort('label')
      ->sort('id');

    $query->accessCheck(FALSE);

    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }
}
