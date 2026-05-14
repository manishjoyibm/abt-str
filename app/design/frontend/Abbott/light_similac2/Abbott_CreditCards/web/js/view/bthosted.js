  define([
      'jquery',
      'ko',
      'uiComponent',
      'braintreeClient',
      'braintreeHostedFields1',
      'mage/storage',
      'Magento_Customer/js/customer-data',
      'PayPal_Braintree/js/validator'
    ],
    function($, ko, Component, braintreeClient, hostedFields, storage, customerData,validator) {
      'use strict';
      var self;
      return Component.extend({
        selectedCardType: ko.observable(),

        initialize: function() {
          self = this;
          this._super();
        },

        getCcAvailableTypes: function () {
            var availableTypes = self.btConfig.availableCardTypes;
            return availableTypes;
        },

        initFormValidationEvents: function (hostedFieldsInstance) {
            hostedFieldsInstance.on('empty', function (event) {
                if (event.emittedBy === 'number') {
                    self.selectedCardType(null);
                }
            });
            hostedFieldsInstance.on('cardTypeChange', function (event) {
                if (event.cards.length === 1) {
                    self.selectedCardType(
                        self.getMageCardType(event.cards[0].type, self.getCcAvailableTypes())
                    );
                }
            });
        },
        getMageCardType: function (type, availableTypes) {
            var storedCardType = null,
                mapper = self.btConfig.ccTypesMapper;

            if (type && typeof mapper[type] !== 'undefined') {
                storedCardType = mapper[type];

                if (_.indexOf(availableTypes, storedCardType) !== -1) {
                    return storedCardType;
                }
            }

            return null;
        },

        getIcons: function (type) {
            return self.icons.hasOwnProperty(type) ?
                self.icons[type]
                : false;
        },
        initHostedFields: function() {
          var form = document.querySelector('#hosted-fields-form');
          var submit = document.querySelector('#save-card');
          $("#address-or-both").change(function() {
            var selectedOption = $('option:selected', this).val();
            if (selectedOption == 'update') {
              $("#credit-card-details").removeClass("d-none");
            } else if (selectedOption == 'continue') {
              $("#credit-card-details").addClass("d-none");
            }
          });
          braintreeClient.create({
            authorization: self.at
          }, function(clientErr, clientInstance) {
            if (clientErr) {
              return;
            }
            hostedFields.create({
              client: clientInstance,
              styles: {
                'input': {
                  'font-size': '14px'
                },
                'input.invalid': {
                  'color': 'red'
                },
                'input.valid': {
                  'color': 'green'
                }
              },
              fields: {
                number: {
                  selector: '#card-number'
                },
                cvv: {
                  selector: '#cvv'
                },
                expirationDate: {
                  selector: '#expiration-date',
                  placeholder: 'MM/YYYY'
                }
              }
            }, function(hostedFieldsErr, instance) {
              if (hostedFieldsErr) {
                return;
              }
              self.initFormValidationEvents(instance);
              submit.removeAttribute('disabled');
              form.addEventListener('submit', function(event) {
                event.preventDefault();
                if (!($("#hosted-fields-form").valid())) {
                  return;
                }
                var postData = {};
                var elements = form.elements;
                for (var i = 0; i < elements.length; i++) {
                  var item = elements.item(i);
                  postData[item.name] = item.value;
                }
                var street = [];
                street[0] = postData['street']
                postData['street'] = street;
                postData['isEdit'] = self.isEdit;
                postData['paymentToken'] = self.paymentToken;
                var selectedOption = $('#address-or-both').val();
                if (!(selectedOption == 'continue' && self.isEdit)) {
                  $("#card-number-error").text('');
                  $("#expiration-date-error").text('');
                  $("#cvv-error").text('');
                  $("#card-number").removeClass('card-error-input');
                  $("#expiration-date").removeClass('card-error-input');
                  $("#cvv").removeClass('card-error-input');
                  var state = instance.getState();
                  var formValid = Object.keys(state.fields).every(function(key) {
                    return state.fields[key].isValid;
                  });
                  if (formValid) {
		                $(submit, this).attr('disabled', 'disabled');
                    instance.tokenize(function(tokenizeErr, payload) {
                      if (tokenizeErr) {
                        return;
                      }
                      postData['nonce'] = payload['nonce'];
                      self.saveDetails(postData);
                    });
                  } else {
                    Object.keys(state.fields).forEach(function(key) {
                      var divId = state.fields[key].container.id;
                      var divIdError = state.fields[key].container.id + "-error";
                      if (state.fields[key].isEmpty) {
                        $("#" + divId).addClass("card-error-input");
                        $("#" + divIdError).text("This is required field");
                      } else if (!(state.fields[key].isValid)) {
                        $("#" + divId).addClass("card-error-input");
                        $("#" + divIdError).text("Please enter valid " + key);
                      }
                    });
                  }
                } else {
                  self.saveDetails(postData);
                }
              });
            });
          });
        },
        saveDetails: function(postData) {
          var gatag;
          
          if(postData['isEdit'] == 1) {
          gatag = "edit-payment"; }
          else{ 
          gatag = "add-new-payment";
          }
          window.dataLayer = window.dataLayer || [];
          
          window.dataLayer.push({event: "ga-custom-events",eventCategory: gatag+"-method",eventAction: "click",eventLabel: gatag+"-method_submit"});
          
          storage.post(
          "card/add/savecard", JSON.stringify(postData)
          ).fail(
          function(response) {
          window.location = self.getSuccessUrl;
          }
          ).done(
          function(response) {
          window.dataLayer.push({
          event: "ga-custom-events",
          eventCategory: gatag+"-method",
          eventAction: "submit",
          eventLabel: gatag+"-method_submit"
          });
          window.location = self.getSuccessUrl;
          }
          );
          }
      });
    });
