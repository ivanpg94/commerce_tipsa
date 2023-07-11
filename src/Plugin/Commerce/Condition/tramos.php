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
     *   id = "tramos",
     *   label = @Translation("Tramos Tipsa"),
     *   category = @Translation("Shipment"),
     *   entity_type = "commerce_shipment",
     * )
     */

    class tramos extends ConditionBase implements ContainerFactoryPluginInterface {

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
            'condition' => [
              'price' => NULL,
              'maxweight' => NULL,
              'weight' => NULL,
              'enabled' => TRUE,
            ],
          ] + parent::defaultConfiguration();
      }

      /**
       * {@inheritdoc}
       */
      public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildConfigurationForm($form, $form_state);
        // Preparing condition fieldsets.
          $maxweight = $this->configuration['condition']['maxweight'];
          $weight = $this->configuration['condition']['weight'];
          $price = $this->configuration['condition']['price'];
          $enabled = $this->configuration['condition']['enabled'];

          $form['condition'] = [
            '#type' => 'fieldset',
            '#title' => 'Condition',
          ];

          $form['condition']['enabled'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enabled?'),
            '#default_value' => $enabled,
          ];
          // Check 'Enabled' checkbox if condition group should be enabled.
          if ($enabled) {
            $form['condition']['enabled']['#attributes'] = ['checked' => 'checked'];
          }
        $form['condition']['maxweight'] = [
          '#type' => 'physical_measurement',
          '#measurement_type' => MeasurementType::WEIGHT,
          '#title' => $this->t('Max Weight'),
          '#default_value' => $maxweight,
          '#required' => FALSE,
          '#states' => [
            'invisible' => [
              ':input[name="conditions[form][shipment][tramos][configuration][form][condition][enabled]"]' => ['checked' => FALSE],
            ],
            'required' => [
              ':input[name="conditions[form][shipment][tramos][configuration][form][condition][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];
          // Show condition group fields only when 'Enabled' checkbox is checked.
          $form['condition']['price'] = [
            '#type' => 'commerce_price',
            '#title' => t('Price'),
            '#default_value' => $price,
            '#required' => FALSE,
            '#states' => [
              'invisible' => [
                ':input[name="conditions[form][shipment][tramos][configuration][form][condition][enabled]"]' => ['checked' => FALSE],
              ],
              'required' => [
                ':input[name="conditions[form][shipment][tramos][configuration][form][condition][enabled]"]' => ['checked' => TRUE],
              ],
            ],
          ];
          $form['condition']['weight'] = [
            '#type' => 'physical_measurement',
            '#measurement_type' => MeasurementType::WEIGHT,
            '#title' => $this->t('Weight'),
            '#default_value' => $weight,
            '#required' => FALSE,
            '#states' => [
              'invisible' => [
                ':input[name="conditions[form][shipment][tramos][configuration][form][condition][enabled]"]' => ['checked' => FALSE],
              ],
              'required' => [
                ':input[name="conditions[form][shipment][tramos][configuration][form][condition][enabled]"]' => ['checked' => TRUE],
              ],
            ],
          ];


        return $form;
      }

      /**
       * {@inheritdoc}
       */
      public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
        parent::submitConfigurationForm($form, $form_state);
        // Saving configuration values.
        $values = $form_state->getValue($form['#parents']);
          $this->configuration['condition']['enabled'] = $values['condition']['enabled'];
          $this->configuration['condition']['price'] = $values['condition']['price'];
          $this->configuration['condition']['weight'] = $values['condition']['weight'];
        $this->configuration['condition']['maxweight'] = $values['condition']['maxweight'];
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
          $enabled = $values['condition']['enabled'];
          $price = $values['condition']['price'];
          $weight = $values['condition']['weight'];
          $maxweight = $values['condition']['maxweight'];
          if (empty($price['number']) && !empty($enabled)) {
            $element = $form['condition']['price'];
            $form_state->setError($element, 'You must enter correct price value!');
          }
        if (empty($maxweight['number']) && !empty($enabled)) {
          $element = $form['condition']['maxweight'];
          $form_state->setError($element, 'You must enter correct max weight value!');
        }
          if (empty($weight['number']) && !empty($enabled)) {
            $element = $form['condition']['weight'];
            $form_state->setError($element, 'You must enter correct weight value!');
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

        return FALSE;
      }

    }

