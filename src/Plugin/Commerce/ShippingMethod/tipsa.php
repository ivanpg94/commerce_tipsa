<?php

namespace Drupal\commerce_tipsa\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\state_machine\WorkflowManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\physical\MeasurementType;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

/**
 * Provides the FlatRatePerItem shipping method.
 *
 * @CommerceShippingMethod(
 *   id = "tipsa",
 *   label = @Translation("Tipsa"),
 * )
 */
class tipsa extends ShippingMethodBase {

  /**
   * Constructs a new FlatRate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $package_type_manager
   *   The package type manager.
   * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
   *   The workflow manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PackageTypeManagerInterface $package_type_manager, WorkflowManagerInterface $workflow_manager, StateInterface $stateService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $package_type_manager, $workflow_manager);

    $this->services['default'] = new ShippingService('default', $this->configuration['rate_label']);
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
      $container->get('plugin.manager.commerce_package_type'),
      $container->get('plugin.manager.workflow'),
      $container->get('state')
    );
  }

  public function defaultConfiguration() {
    return [
        'rate_label' => '',
        'rate_description' => '',
        'minimum_weight' => NULL,
        'extra_weight_rate' => NULL,
        'services' => ['default'],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    //  $amount = $this->configuration['rate_amount'];
    // A bug in the plugin_select form element causes $amount to be incomplete.
    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }
    // A bug in the plugin_select form element causes $base_amount to be undefined.
    if (!empty($this->configuration['base_amount'])) {
      $base_amount = $this->configuration['base_amount'];
    }
    else {
      $base_amount = NULL;
    }
    $minimum_weight = $this->configuration['minimum_weight'];
    if (!isset($minimum_weight) || !isset($minimum_weight['number'], $minimum_weight['unit'])) {
      $minimum_weight = ['number' => 0, 'unit' => WeightUnit::KILOGRAM];
    }
    else {
      $minimum_weight = [
        'number' => $this->configuration['minimum_weight']['number'],
        'unit' => $this->configuration['minimum_weight']['unit'],
      ];
    }

    $extra_weight_rate = $this->configuration['extra_weight_rate'];
    // A bug in the plugin_select form element causes $amount to be incomplete.
    if (isset($extra_weight_rate) && !isset($extra_weight_rate['number'], $extra_weight_rate['currency_code'])) {
      $extra_weight_rate = ['number' => 0, 'currency_code' => 'EUR'];
    }
    else {
      $extra_weight_rate = $this->configuration['extra_weight_rate'];
    }

    $form['rate_label'] = [
      '#type' => 'textfield',
      '#title' => t('Rate label'),
      '#description' => t('Shown to customers when selecting the rate.'),
      '#default_value' => $this->configuration['rate_label'],
      '#required' => TRUE,
    ];
    $form['rate_description'] = [
      '#type' => 'textfield',
      '#title' => t('Rate description'),
      '#description' => t('Provides additional details about the rate to the customer.'),
      '#default_value' => $this->configuration['rate_description'],
    ];
    $form['base_amount'] = [
      '#type' => 'commerce_price',
      '#title' => t('Base amount'),
      '#default_value' => $base_amount,
      '#required' => TRUE,
      '#description' => t('Charged once.'),
    ];
    $form['minimum_weight'] = [
      '#type' => 'physical_measurement',
      '#measurement_type' => MeasurementType::WEIGHT,
      '#title' => $this->t('Minimum weight'),
      '#default_value' => $minimum_weight,
      '#required' => TRUE,
    ];
    $form['extra_weight_rate'] = [
      '#type' => 'commerce_price',
      '#title' => t('Extra weight rate'),
      '#description' => t('Rate per unit weight above minimum weight'),
      '#default_value' => $extra_weight_rate,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['rate_label'] = $values['rate_label'];
      $this->configuration['rate_description'] = $values['rate_description'];
      $this->configuration['base_amount'] = $values['base_amount'];
      $this->configuration['minimum_weight'] = $values['minimum_weight'];
      $this->configuration['extra_weight_rate'] = $values['extra_weight_rate'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateRates(ShipmentInterface $shipment) {
    $conf = $this->configuration;

    $weight = $shipment->getWeight();
    if (!$weight) {
      return FALSE;
    }

    $base_amount = number_format($this->configuration['base_amount']['number'], 2, '.', '');
    $shipment_weight = number_format($weight->getNumber(), 2, '.', '');
    $minimum_weight = number_format($conf['minimum_weight']['number'], 2, '.', '');
    $extra_weight_rate = number_format($conf['extra_weight_rate']['number'], 2, '.', '');

    if ($shipment_weight <= $minimum_weight) {
      $shipping_price = $base_amount;
    } else {
      $shipping_price = $base_amount + (($shipment_weight - $minimum_weight) * $extra_weight_rate);
    }

    $amount = new Price((string) $shipping_price, $conf['base_amount']['currency_code']);

    $rates = [];
    $rates[] = new ShippingRate([
      'shipping_method_id' => $this->parentEntity->id(),
      'service' => $this->services['default'],
      'amount' => $amount,
    ]);

    return $rates;
  }

}
