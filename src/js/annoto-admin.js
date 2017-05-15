jQuery(document).ready( function ( $ ) {

    'use strict';

    var nameToOutputTextMapper = {
        mappingData: {
            'widget-position': {
                right: 'Right',
                bottom: 'Bottom',
                left: 'Left'
            },
            'player-type': {
                youtube: 'YouTube',
                vimeo: 'Vimeo'
            },
            'rtl-support': {
                0: 'En',
                1: 'He'
            }
        },
        getOutputText: function(fieldName, valueName) {
            return this.mappingData[fieldName][valueName];
        }
    };

    var toggleDisabledInputs = {
        ssoSupport: function () {
            var ssoSecretInput = $('#sso-secret');
            this.isSsoSecretEnabled() ? ssoSecretInput.prop('disabled', false) : ssoSecretInput.prop('disabled', true);
        },
        ssoSecret: function () {
            var apiKeyInput = $('#api-key'),
                ssoSecretInput = $('#sso-secret');

            $('#demo-mode')[0].checked ? apiKeyInput.prop('disabled', false) : apiKeyInput.prop('disabled', true);
            this.isSsoSecretEnabled() ? ssoSecretInput.prop('disabled', false) : ssoSecretInput.prop('disabled', true);
        },
        isSsoSecretEnabled: function () {
            return $('#demo-mode')[0].checked && $('#sso-support')[0].checked;
        },
        all: function () {
            this.ssoSupport();
            this.ssoSecret();
        }
    };

    var settingsFromServer = JSON.parse($('#settingsFromServer').val());

    var settingForm = {
        formId: '#settingForm',
        gatheredData: function () {
            var settingData = {};

            $( this.formId ).find( 'input.setting-data' ).each( function () {

                settingData[this.name] = $( this ).val();

                if ( this.type === 'checkbox' ) {
                    settingData[this.name] = String( Number( $( this )[0].checked ) );
                }
            });

            return settingData;
        },
        isDataChanged: function () {
             return JSON.stringify(this.gatheredData()) !== JSON.stringify(settingsFromServer);
        },
        sendToServer: function () {
            if (this.isDataChanged()) {
                $('#submitSettings').addClass('disabled');

                $.post(
                    '',
                    {
                        action: 'save-settings',
                        data: this.gatheredData()
                    }
                )
                    .done( function ( response ) {
                        var response = JSON.parse(response);

                        if (response.status === 'success') {
                            $("#successMessage").slideDown();

                            settingsFromServer = response.data;

                            setTimeout(function() {
                                $("#successMessage").slideUp();
                            }, 3000);
                        }

                        if (response.status === 'failed') {
                            $('#failMessage').slideDown();

                            setTimeout(function() {
                                $('#failMessage').slideUp();
                            }, 3000);
                        }

                        $('#submitSettings').removeClass('disabled');

                    } );
            }
        }
    };

    (function () {
        $( '#settingForm' ).find( ':input.setting-data' ).each( function () {

            $( this ).val(settingsFromServer[this.name]);

            if ( this.type === 'checkbox' ) {
                $( this ).prop( 'checked', Boolean( Number( settingsFromServer[this.name] ) ) );
            }

            if ( $( this ).hasClass( 'is-dropdown' )) {
                $( '#' + this.name )
                    .text(nameToOutputTextMapper.getOutputText( this.name, settingsFromServer[this.name] ) );
            }
        });

        toggleDisabledInputs.all();
    }) ();

    $( '#settingForm' ).submit( function( event ) {
        event.preventDefault();

        settingForm.sendToServer();
    });

    $('#submitSettings').on('click', function ( event ) {
        event.preventDefault();

        $( '#settingForm' ).submit();
    });

    $('#demo-mode').change(function () {
        toggleDisabledInputs.ssoSecret();
    });

    $('#sso-support').change(function () {
        toggleDisabledInputs.ssoSupport();
    });

    $('.dropdown-menu a').click(function () {
        var buttonId = $(this).closest('ul').data('btnId');
        $( '#' + buttonId ).text( this.innerText );
        $( 'input.setting-data[name=' + buttonId + ']').val( this.name );
    });
});
