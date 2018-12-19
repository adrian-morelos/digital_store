/**
 * @file
 * Javascript to generate Stripe token in PCI-compliant way.
 */
(function ($, Drupal, drupalSettings) {

    'use strict';

    /**
     * Attaches the digitalStoreStripeForm behavior.
     *
     * @type {Drupal~behavior}
     *
     * @prop object cardNumber
     *   Stripe card number element.
     * @prop object cardExpiry
     *   Stripe card expiry element.
     * @prop object cardCvc
     *   Stripe card cvc element.
     * @prop {Drupal~behaviorAttach} attach
     *   Attaches the digitalStoreStripeForm behavior.
     * @prop {Drupal~behaviorDetach} detach
     *   Detaches the digitalStoreStripeForm behavior.
     *
     * @see Drupal.digitalStoreStripe
     */
    Drupal.behaviors.digitalStoreStripeForm = {
        cardNumber: null,
        cardExpiry: null,
        cardCvc: null,
        attach: function (context) {
            var self = this;
            if (!drupalSettings.digitalStoreStripe || !drupalSettings.digitalStoreStripe.publishableKey) {
                return;
            }
            $('.stripe-form', context).once('stripe-processed').each(function () {
                var $form = $(this).closest('form');
                // Clear the token every time the payment form is loaded. We only need the token
                // one time, as it is submitted to Stripe after a card is validated. If this
                // form reloads it's due to an error; received tokens are stored in the checkout pane.
                $('#stripe_token', $form).val('');
                // Create a Stripe client.
                /* global Stripe */
                var stripe = Stripe(drupalSettings.digitalStoreStripe.publishableKey);
                // Create an instance of Stripe Elements.
                var elements = stripe.elements();
                var classes = {
                    base: 'form-text',
                    invalid: 'error'
                };
                var style = {
                    base: {
                        color: '#303238',
                        fontSize: '17.6px',
                        fontSmoothing: 'antialiased',
                        '::placeholder': {
                            color: '#CFD7DF'
                        }
                    },
                    invalid: {
                        color: '#e5424d',
                        ':focus': {
                            color: '#303238'
                        }
                    }
                };
                // Create instances of the card elements.
                self.cardNumber = elements.create('cardNumber', {
                    classes: classes,
                    style: style
                });
                self.cardExpiry = elements.create('cardExpiry', {
                    classes: classes,
                    style: style
                });
                self.cardCvc = elements.create('cardCvc', {
                    classes: classes,
                    style: style
                });
                // Add an instance of the card UI components into the "scard-element" element <div>
                self.cardNumber.mount('#card-number-element');
                self.cardExpiry.mount('#expiration-element');
                self.cardCvc.mount('#security-code-element');
                // Input validation.
                self.cardNumber.on('change', function (event) {
                    stripeErrorHandler(event);
                });
                self.cardExpiry.on('change', function (event) {
                    stripeErrorHandler(event);
                });
                self.cardCvc.on('change', function (event) {
                    stripeErrorHandler(event);
                });

                // Insert the token ID into the form so it gets submitted to the server
                var stripeTokenHandler = function (token) {
                    // Set the Stripe token value.
                    $('#stripe_token', $form).val(token.id);
                    // Submit the form.
                    $form.get(0).submit();
                };
                // Helper to handle the Stripe responses with errors.
                var stripeErrorHandler = function (result) {
                    if (result.error) {
                        // Inform the user if there was an error.
                        stripeErrorDisplay(result.error.message);
                    } else {
                        // Clean up error messages.
                        $form.find('#payment-errors').html('');
                    }
                };
                // Helper for displaying the error messages within the form.
                var stripeErrorDisplay = function (error_message) {
                    // Display the message error in the payment form.
                    $form.find('#payment-errors').html(Drupal.theme('digitalStoreStripeError', error_message));
                    // Allow the customer to re-submit the form.
                    $form.find('input.btn-complete-order').prop('disabled', false);
                };
                // Create a Stripe token and submit the form or display an error.
                var stripeCreateToken = function () {
                    stripe.createToken(self.cardNumber).then(function (result) {
                        if (result.error) {
                            // Inform the user if there was an error.
                            stripeErrorDisplay(result.error.message);
                        } else {
                            // Send the token to your server.
                            stripeTokenHandler(result.token);
                        }
                    });
                };
                // Form submit.
                $form.on('submit.digital_store_stripe', function (e) {
                    // Disable the submit button to prevent repeated clicks.
                    $form.find('input.btn-complete-order').prop('disabled', true);
                    // Try to create the Stripe token and submit the form.
                    stripeCreateToken();
                    // Prevent the form from submitting with the default action.
                    if ($('#card-number-element', $form).length) {
                        return false;
                    }
                });
            });
        },

        detach: function (context, settings, trigger) {
            if (trigger !== 'unload') {
                return;
            }
            var self = this;
            ['cardNumber', 'cardExpiry', 'cardCvc'].forEach(function (i) {
                if (self[i] && self[i].length > 0) {
                    self[i].unmount();
                    self[i] = null;
                }
            });
            var $form = $('.stripe-form', context).closest('form');
            if ($form.length === 0) {
                return;
            }
            $form.off('submit.digital_store_stripe');
        }
    };

    $.extend(Drupal.theme, /** @lends Drupal.theme */{
        digitalStoreStripeError: function (message) {
            return $('<div class="messages messages--error"></div>').html(message);
        }
    });

})(jQuery, Drupal, drupalSettings);
