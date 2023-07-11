<?php

namespace Drupal\commerce_tipsa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CommerceTipsaForm extends FormBase

{
  public function getFormId()
  {
    return 'commerce_tipsa_form';
  }
  protected function getEditableConfigNames()
  {
    return [
      'commerce_tipsa.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_tipsa.settings');
//Title
    $form['overview'] = [
      '#markup' => t('Tipsa API Credentials'),
      '#prefix' => '<p><strong>',
      '#suffix' => '</strong></p>',
    ];
//tipsa API URL contenedor

    $form['api_url_settings'] = [
      '#title' => t('Tipsa Datos'),
      '#description' => t(''),
      '#type' => 'fieldset',
      '#collapsable' => TRUE,
      '#collapsed' => FALSE,
    ];
//tipsa API URL login
    $form['api_url_settings']['api_url'] = [
      '#title' => t('Tipsa API URL login'),
      '#description' => t('Url to conect with tipsa login webservice.'),
      '#type' => 'textfield',
      '#size' => '30',
      '#placeholder' => 'http://example.com',
      '#required' => FALSE,
      '#default_value' => \Drupal::config('commerce_tipsa.settings')->get('commerce_tipsa_api_url'),
    ];
//tipsa API URL Accion
    $form['api_url_settings']['api_url_action'] = [
      '#title' => t('Tipsa API URL Action'),
      '#description' => t('Url to connect with tipsa action webservice.'),
      '#type' => 'textfield',
      '#size' => '30',
      '#placeholder' => 'http://example.com',
      '#required' => FALSE,
      '#default_value' => \Drupal::config('commerce_tipsa.settings')->get('commerce_tipsa_api_url_action'),
    ];
//tipsa API Agency
    $form['api_url_settings']['api_agency'] = [
      '#title' => t('Tipsa API Agency'),
      '#description' => t('Agency to login to the tipsa webservice.'),
      '#type' => 'textfield',
      '#size' => '30',
      '#placeholder' => '00000',
      '#required' => FALSE,
      '#default_value' => \Drupal::config('commerce_tipsa.settings')->get('commerce_tipsa_api_agency'),
    ];
//tipsa API User
    $form['api_url_settings']['api_user'] = [
      '#title' => t('Tipsa API User'),
      '#description' => t('User to login to the tipsa webservice.'),
      '#type' => 'textfield',
      '#size' => '30',
      '#placeholder' => '0000',
      '#required' => FALSE,
      '#default_value' => \Drupal::config('commerce_tipsa.settings')->get('commerce_tipsa_api_user'),
    ];
//tipsa API Password
    $form['api_url_settings']['api_password'] = [
      '#title' => t('Tipsa API Password'),
      '#description' => t('Password to login to the tipsa webservice.'),
      '#type' => 'textfield',
      '#size' => '30',
      '#placeholder' => '0000',
      '#required' => FALSE,
      '#default_value' => \Drupal::config('commerce_tipsa.settings')->get('commerce_tipsa_api_password'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];
    return $form;
  }


  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $api_url = $form_state->getValue('api_url');


    if (empty($api_url)) {
      $form_state->setErrorByName('api_url', $this->t('this field is required '));
    }
    if (empty($api_url)) {
      $form_state->setErrorByName('api_url_action', $this->t('this field is required '));
    }
    if (empty($api_url)) {
      $form_state->setErrorByName('api_agency', $this->t('this field is required '));
    }
    if (empty($api_url)) {
      $form_state->setErrorByName('api_user', $this->t('this field is required '));
    }
    if (empty($api_url)) {
      $form_state->setErrorByName('api_password', $this->t('this field is required '));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);

    // Save Commerce envialia variables.
    \Drupal::configFactory()->getEditable('commerce_tipsa.settings')->set('commerce_tipsa_api_url', $form_state->getValue(['api_url']))->save();
    \Drupal::configFactory()->getEditable('commerce_tipsa.settings')->set('commerce_tipsa_api_url_action', $form_state->getValue(['api_url_action']))->save();
    \Drupal::configFactory()->getEditable('commerce_tipsa.settings')->set('commerce_tipsa_api_agency', $form_state->getValue(['api_agency']))->save();
    \Drupal::configFactory()->getEditable('commerce_tipsa.settings')->set('commerce_tipsa_api_user', $form_state->getValue(['api_user']))->save();
    \Drupal::configFactory()->getEditable('commerce_tipsa.settings')->set('commerce_tipsa_api_password', $form_state->getValue(['api_password']))->save();

  }

}
