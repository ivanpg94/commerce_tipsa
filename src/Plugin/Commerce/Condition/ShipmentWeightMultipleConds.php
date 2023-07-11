<?php

namespace Drupal\commerce_tipsa\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\physical\MeasurementType;
use Drupal\physical\Weight;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the multiple weight condition for shipments.
 *
 * @CommerceCondition(
 *   id = "shipment_weight_multiple_conds_tipsa",
 *   label = @Translation("Shipment weight - Multiple Conditions Tipsa"),
 *   category = @Translation("Shipment"),
 *   entity_type = "commerce_shipment",
 * )
 */
class ShipmentWeightMultipleConds extends ConditionBase implements ContainerFactoryPluginInterface {

  protected $stateService;

  /**
   * Constructs a new ShipmentWeightMultipleConds object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $stateService
   *   The Drupal State Service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $stateService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stateService = $stateService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'condition_1' => [
        'price' => NULL,
        'operator' => '<',
        'weight' => NULL,
        'enabled' => TRUE,
      ],
      'condition_2' => [
        'price' => NULL,
        'operator' => '<',
        'weight' => NULL,
        'enabled' => TRUE,
      ],
      'condition_3' => [
        'price' => NULL,
        'operator' => '<',
        'weight' => NULL,
        'enabled' => TRUE,
      ],
      'condition_4' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => TRUE,
      ],
      'condition_5' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => FALSE,
      ],
      'condition_6' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => FALSE,
      ],
      'condition_7' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => FALSE,
      ],
      'condition_8' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => FALSE,
      ],
      'condition_9' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => FALSE,
      ],
      'condition_10' => [
        'price' => NULL,
        'operator' => '>',
        'weight' => NULL,
        'enabled' => FALSE,
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // Preparing condition fieldsets.
    for ($i = 1; $i <= 10; $i++) {
      $weight = $this->configuration['condition_' . $i]['weight'];
      $price = $this->configuration['condition_' . $i]['price'];
      $enabled = $this->configuration['condition_' . $i]['enabled'];
      $operator = $this->configuration['condition_' . $i]['operator'];

      $form['condition_' . $i] = [
        '#type' => 'fieldset',
        '#title' => 'Condition group #' . $i,
      ];

      $form['condition_' . $i]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enabled?'),
        '#default_value' => $enabled,
      ];
      // Check 'Enabled' checkbox if condition group should be enabled.
      if ($enabled) {
        $form['condition_' . $i]['enabled']['#attributes'] = ['checked' => 'checked'];
      }
      // Show condition group fields only when 'Enabled' checkbox is checked.
      $form['condition_' . $i]['price'] = [
        '#type' => 'commerce_price',
        '#title' => t('Price'),
        '#default_value' => $price,
        '#required' => FALSE,
        '#states' => [
          'invisible' => [
            ':input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][configuration][form][condition_' . $i . '][enabled]"]' => ['checked' => FALSE],
          ],
          'required' => [
            ':input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][configuration][form][condition_' . $i . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['condition_' . $i]['operator'] = [
        '#type' => 'select',
        '#title' => $this->t('Operator'),
        '#options' => $this->getComparisonOperators(),
        '#default_value' => $operator,
        '#required' => FALSE,
        '#states' => [
          'invisible' => [
            ':input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][configuration][form][condition_' . $i . '][enabled]"]' => ['checked' => FALSE],
          ],
          'required' => [
            ':input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][configuration][form][condition_' . $i . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['condition_' . $i]['weight'] = [
        '#type' => 'physical_measurement',
        '#measurement_type' => MeasurementType::WEIGHT,
        '#title' => $this->t('Weight'),
        '#default_value' => $weight,
        '#required' => FALSE,
        '#states' => [
          'invisible' => [
            ':input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][configuration][form][condition_' . $i . '][enabled]"]' => ['checked' => FALSE],
          ],
          'required' => [
            ':input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][configuration][form][condition_' . $i . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    // Saving configuration values.
    $values = $form_state->getValue($form['#parents']);
    for ($i = 1; $i <= 10; $i++) {
      $this->configuration['condition_' . $i]['enabled'] = $values['condition_' . $i]['enabled'];
      $this->configuration['condition_' . $i]['price'] = $values['condition_' . $i]['price'];
      $this->configuration['condition_' . $i]['operator'] = $values['condition_' . $i]['operator'];
      $this->configuration['condition_' . $i]['weight'] = $values['condition_' . $i]['weight'];
    }
  }

  /**
   * Validates filling of price and weight fields in enabled fieldsets.
   *
   * @param array $form
   *   Configuration form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    for ($i = 1; $i <= 10; $i++) {
      $enabled = $values['condition_' . $i]['enabled'];
      $price = $values['condition_' . $i]['price'];
      $weight = $values['condition_' . $i]['weight'];
      if (empty($price['number']) && !empty($enabled)) {
        $element = $form['condition_' . $i]['price'];
        $form_state->setError($element, 'You must enter correct price value!');
      }
      if (empty($weight['number']) && !empty($enabled)) {
        $element = $form['condition_' . $i]['weight'];
        $form_state->setError($element, 'You must enter correct weight value!');
      }
    }
  }

  /**
   * Evaluates shipment weight conditions.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Shipment entity object.
   *
   * @return bool
   *   Returns entity evaluation result.
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $entity;
    $weight = $shipment->getWeight();
    if (!$weight) {
      // The conditions can't be applied until the weight is known.
      return FALSE;
    }
    // Get enabled conditions list.
    $enabled_conditions = [];
    foreach ($this->getConfiguration() as $config) {
      if (!empty($config['enabled'])) {
        $enabled_conditions[] = $config;
      }
    }
    // Evaluate matching conditions.
    if (count($enabled_conditions) === 1) {
      $condition_unit = $enabled_conditions['0']['weight']['unit'];

      /** @var \Drupal\physical\Weight $weight */
      $weight = $weight->convert($condition_unit);
      $condition_weight = new Weight($enabled_conditions['0']['weight']['number'], $condition_unit);
      // Saving condition info to states.
      $this->stateService->set('order_' . $shipment->getOrderId() . '_weight_condition', $enabled_conditions['0']);

      // Return evaluation results.
      switch ($enabled_conditions['0']['operator']) {
        case '>=':
          return $weight->greaterThanOrEqual($condition_weight);

        case '>':
          return $weight->greaterThan($condition_weight);

        case '<=':
          return $weight->lessThanOrEqual($condition_weight);

        case '<':
          return $weight->lessThan($condition_weight);

        case '==':
          return $weight->equals($condition_weight);

        default:
          throw new \InvalidArgumentException("Invalid operator {$this->configuration['operator']}");
      }
    }
    else {
      // Get matched conditions.
      $matched_conditions = [];
      foreach ($enabled_conditions as $key => $condition) {
        $condition_unit = $condition['weight']['unit'];

        /** @var \Drupal\physical\Weight $weight */
        $weight = $weight->convert($condition_unit);
        $condition_weight = new Weight($condition['weight']['number'], $condition_unit);

        switch ($condition['operator']) {
          case '>=':
            $matched_conditions[$key]['condition_type'] = 'greaterThanOrEqual';
            $matched_conditions[$key]['condition_result'] = $weight->greaterThanOrEqual($condition_weight);
            $matched_conditions[$key]['condition_weight'] = $condition['weight']['number'];
            $matched_conditions[$key]['condition_price'] = $condition['price']['number'];
            $matched_conditions[$key]['condition_operator'] = $condition['operator'];
            break;

          case '>':
            $matched_conditions[$key]['condition_type'] = 'greaterThan';
            $matched_conditions[$key]['condition_result'] = $weight->greaterThan($condition_weight);
            $matched_conditions[$key]['condition_weight'] = $condition['weight']['number'];
            $matched_conditions[$key]['condition_price'] = $condition['price']['number'];
            $matched_conditions[$key]['condition_operator'] = $condition['operator'];
            break;

          case '<=':
            $matched_conditions[$key]['condition_type'] = 'lessThanOrEqual';
            $matched_conditions[$key]['condition_result'] = $weight->lessThanOrEqual($condition_weight);
            $matched_conditions[$key]['condition_weight'] = $condition['weight']['number'];
            $matched_conditions[$key]['condition_price'] = $condition['price']['number'];
            $matched_conditions[$key]['condition_operator'] = $condition['operator'];
            break;

          case '<':
            $matched_conditions[$key]['condition_type'] = 'lessThan';
            $matched_conditions[$key]['condition_result'] = $weight->lessThan($condition_weight);
            $matched_conditions[$key]['condition_weight'] = $condition['weight']['number'];
            $matched_conditions[$key]['condition_price'] = $condition['price']['number'];
            $matched_conditions[$key]['condition_operator'] = $condition['operator'];
            break;

          case '==':
            $matched_conditions[$key]['condition_type'] = 'equals';
            $matched_conditions[$key]['condition_result'] = $weight->equals($condition_weight);
            $matched_conditions[$key]['condition_weight'] = $condition['weight']['number'];
            $matched_conditions[$key]['condition_price'] = $condition['price']['number'];
            $matched_conditions[$key]['condition_operator'] = $condition['operator'];
            break;

          default:
            throw new \InvalidArgumentException("Invalid operator {$this->configuration['operator']}");
        }
      }
      foreach ($matched_conditions as $match) {
        if ($match['condition_result'] === TRUE) {
          // Saving condition info to states.
          $this->stateService->set('order_' . $shipment->getOrderId() . '_weight_condition', $match);
          // Return evaluation results.
          return $weight->{$match['condition_type']}($condition_weight);
        }
      }
    }

    return FALSE;
  }

}
