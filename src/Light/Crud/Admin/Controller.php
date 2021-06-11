<?php

declare(strict_types=1);

namespace Light\Crud\Admin;

use Light\Crud;
use Light\Form\Element\Text;
use Light\Form\Generator;

/**
 * Class Controller
 * @package Light\Crud\Admin
 *
 * @crud-title Администраторы
 * @crud-manageable true
 *
 * @crud-header Имя [text, name, true]
 * @crud-header Логин [text, login, true]
 * @crud-header Активность [bool, enabled, true]
 */
class Controller extends Crud
{
  /**
   * @return string
   */
  public function getModelClassName()
  {
    return Model::class;
  }

  /**
   * @param Model $model
   * @return Generator
   */
  public function getForm($model = null)
  {
    return new Generator(['data' => $model], [
      'Данные авторизации' => [
        new Text('name', [
          'value' => $model->name,
          'label' => 'Имя администратора'
        ]),
        new Text('login', [
          'value' => $model->login,
          'label' => 'Логин'
        ]),
        new Text('password', [
          'value' => '',
          'label' => 'Пароль',
          'allowNull' => true,
        ]),
      ]
    ]);
  }

  /**
   * @throws \Light\Exception\DomainMustBeProvided
   * @throws \Light\Exception\RouterVarMustBeProvided
   */
  public function manage()
  {
    $model = Model::fetchObject([
      'id' => $this->getRequest()->getParam('id')
    ]);

    /** @var Form $form */
    $form = $this->getForm($model);

    if ($this->getRequest()->isPost()) {
      if ($form->isValid($this->getRequest()->getPostAll())) {

        $formData = $form->getValues();

        if (strlen($formData['password'])) {
          $formData['password'] = md5($formData['password']);
        } else {
          unset($formData['password']);
        }

        // var_dump($formData); die();

        $model->populate($formData);
        $model->save();

        die('ok:' . $this->getRequest()->getPost('return-url'));
      }
    }

    $form->setReturnUrl(
      $this->getRouter()->assemble([
        'controller' => $this->getRouter()->getController(),
        'action' => 'index'
      ])
    );

    $this->getView()->setVars([
      'title' => $this->getTitle(),
      'form' => $form,
    ]);

    $this->getView()->setScript('form/default');
  }
}
