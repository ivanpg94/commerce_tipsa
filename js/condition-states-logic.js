/**
 * @file
 * Shipment Method configuration UI logic.
 */

(function ($, window, Drupal) {

  'use strict';

  /**
   * Provides a logic for shipment conditions checkboxes.
   */
  Drupal.behaviors.shippingCheckboxes = {
    attach: function (context) {
      var $plugin = $('input[name="plugin[0][target_plugin_id]"]', context);

      // Hide not needed checkboxes.
      var changeStatusShipment = function (plugin_id, rebuild = false) {
        $('#edit-conditions-form-shipment .details-wrapper > div').show();

        if (plugin_id === 'commerce_tipsa') {
          var $checkbox = $('input[name="conditions[form][shipment][shipment_weight][enable]"]');
        }
        else {
          var $checkbox = $('input[name="conditions[form][shipment][shipment_weight_multiple_conds_tipsa][enable]"]');
        }

        // Uncheck checkbox if radio button was clicked.
        if (rebuild) {
          $checkbox.prop('checked', false).change();
        }

        $checkbox.parent().parent().hide();
      };

      $plugin.once('change-radio').change(function () {
        changeStatusShipment(this.value, true);
      });

      var plugin_id = $('input[name="plugin[0][target_plugin_id]"]:checked', context).val();
      if (plugin_id !== undefined) {
        changeStatusShipment(plugin_id);
      }
    }
  };

})(jQuery, window, Drupal);
