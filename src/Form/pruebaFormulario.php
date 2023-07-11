<?php

namespace Drupal\commerce_tipsa\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AddMoreForm.
 *
 * @package Drupal\form_test\Form
 */
class pruebaFormulario extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_more_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $tipsa = \Drupal::moduleHandler()->moduleExists('commerce_tipsa');
    $envialia = \Drupal::moduleHandler()->moduleExists('commerce_envialia');
    $name_field = $form_state->get('num_names');
    $form['#tree'] = TRUE;

    $form['tags'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Commerce Tipsa'),
      '#prefix' => "<div id='names-fieldset-wrapper'>",
      '#suffix' => '</div>',
    ];
    if($tipsa==true){
    if (empty($name_field)) {
      $name_field = $form_state->set('num_names', 1);
    }

    for ($i = 0; $i < $form_state->get('num_names'); $i++) {
      $form['tags'][$i]['peso'] = [
        '#type' => 'number',
      ];
      $form['tags'][$i]['select'] = [
        '#type' => 'select',
        '#title' => t('Select element'),
        //       '#default_value' => \Drupal::config('commerce_delivery.settings')->get('selecta2'),
        '#options' => [
          'mayorigual' => t('≥'),
          'menorigual' => t('≤'),
          'mayor' => t('>'),
          'menor' => t('<'),
          'menor' => t('<'),
        ],
      ];
      $form['tags'][$i]['peso2'] = [
        '#type' => 'number',
      ];
      $form['tags'][$i]['select2'] = [
        '#type' => 'select',
        '#title' => t('Select element'),
        //       '#default_value' => \Drupal::config('commerce_delivery.settings')->get('selecta2'),
        '#options' => [
          'mayorigual' => t('≥'),
          'menorigual' => t('≤'),
          'mayor' => t('>'),
          'menor' => t('<'),
          'menor' => t('<'),
        ],
      ];
    }
    $form['tags']['actions'] = [
      '#type' => 'actions',
    ];
    $form['tags']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => array('::addOne'),
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => "names-fieldset-wrapper",
      ],
    ];
    if ($form_state->get('num_names') > 1) {
      $form['tags']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => t('Remove one'),
        '#submit' => array('::removeCallback'),
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => "names-fieldset-wrapper",
        ],
      ];
    }
    $form_state->setCached(FALSE);
    }else{
      $form['tags']['activartipsa'] = array(
        '#type' => 'checkbox',
        '#title' => t('Activar'),
        '#default_value' => \Drupal::config('commerce_delivery.settings')->get('comerce_delivery_activartipsa'),
      );
      $form['tags']['tipsa'] = array(
        '#type' => 'markup',
        '#markup' => 'El modulo Commerce Tipsa no esta habilitado',
        '#tree' => true,
      );
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    return $form['tags'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    $add_button = $name_field + 1;
    $form_state->set('num_names', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_names', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $form_state->setRebuild(TRUE);
    \Drupal::configFactory()->getEditable('commerce_delivery.settings')->set('comerce_delivery_activartipsa', $form_state->getValue(['activartipsa']))->save();
    \Drupal::configFactory()->getEditable('commerce_delivery.settings')->set('comerce_delivery_activarenvialia', $form_state->getValue(['activarenvialia']))->save();


    // Display result.
    dsm($form_state->getValue(array('tags')));
    dsm(count($form_state->getValue(array('tags'))));
    foreach ($form_state->getValue(array('tags')) as $key => $value) {
      //drupal_set_message($key . ': ' . $value);
      if(is_numeric($key))
        dsm($form_state->getValue(array('tags', $key, 'first_name')));


    }
  }

}
