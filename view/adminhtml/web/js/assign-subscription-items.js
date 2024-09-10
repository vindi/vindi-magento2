/**
 * Created by : Vindi Payment
 */

/* global $, $H */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var selectedItems = config.selectedItems,
            subscriptionItems = $H(selectedItems),
            gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000;
        /**
         * Show selected subscription items when edit form in associated subscription items grid
         */
        $('vindi_subscription_items').value = Object.toJSON(subscriptionItems);
        /**
         * Register Subscription Item
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerSubscriptionItem(grid, element, checked) {
            if (checked) {
                if (element.positionElement) {
                    element.positionElement.disabled = false;
                    subscriptionItems.set(element.value, element.positionElement.value);
                }
            } else {
                if (element.positionElement) {
                    element.positionElement.disabled = true;
                }
                subscriptionItems.unset(element.value);
            }
            $('vindi_subscription_items').value = Object.toJSON(subscriptionItems);
            grid.reloadParams = {
                'selected_subscription_items[]': subscriptionItems.keys()
            };
        }

        /**
         * Click on subscription item row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function subscriptionItemRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Change subscription item position
         *
         * @param {String} event
         */
        function positionChange(event) {
            var element = Event.element(event);

            if (element && element.checkboxElement && element.checkboxElement.checked) {
                subscriptionItems.set(element.checkboxElement.value, element.value);
                $('vindi_subscription_items').value = Object.toJSON(subscriptionItems);
            }
        }

        /**
         * Initialize subscription item row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function subscriptionItemRowInit(grid, row) {
            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                position = $(row).getElementsByClassName('input-text')[0];

            if (checkbox && position) {
                checkbox.positionElement = position;
                position.checkboxElement = checkbox;
                position.disabled = !checkbox.checked;
                position.tabIndex = tabIndex++;
                Event.observe(position, 'keyup', positionChange);
            }
        }

        gridJsObject.rowClickCallback = subscriptionItemRowClick;
        gridJsObject.initRowCallback = subscriptionItemRowInit;
        gridJsObject.checkboxCheckCallback = registerSubscriptionItem;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                subscriptionItemRowInit(gridJsObject, row);
            });
        }
    };
});
