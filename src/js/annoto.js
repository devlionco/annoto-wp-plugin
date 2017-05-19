jQuery( function ( $ ) {

    'use strict';

    var getSettingsDeferred = $.Deferred();

    $.post(
        '',
        {
            action: 'get-settings',
            data: {}
        }
    ).done(function(serverResponse) {
        var response = JSON.parse(serverResponse);

        if (response.status === 'success') {
            getSettingsDeferred.resolve(response.data);
        } else {
            getSettingsDeferred.reject('WP Server: Fetching of the Annoto settings has been failed!');
        }
    });

    $.when( getSettingsDeferred )
        .then(
            function( data ) {
                var playerId = '';

                $('article iframe').each(function () {
                    if (this.src.indexOf(data['player-type']) !== -1 && typeof this.id !== 'undefined') {
                        playerId = this.id;
                        return false;
                    }
                });

                return {
                    playerId: playerId,
                    settings: data
                };
            },
            function (errText){
                console.error(errText)
            })
        .then(
            function (configData) {
                console.log(configData.settings);

                if ( configData.playerId.length === 0 ) {
                    return;
                }

                var config = {
                    clientId: configData.settings['api-key'],
                    position: configData.settings['widget-position'],
                    widgets: [
                        {
                            player: {
                                type: configData.settings['player-type'],
                                element: configData.playerId
                            },
                            timeline: {
                                embedded: false
                            }
                        }
                    ],
                    simple: true,
                    rtl: Boolean( configData.settings['rtl-support'] ),
                    demoMode: Boolean( configData.settings['demo-mode'] )
                };


                if ( ! window.Annoto) {
                    console && console.error('Annoto: not loaded');
                    return;
                }

                var annotoApi;
                window.Annoto.on('ready', function (api) {
                    annotoApi = api;
                });
                // window.Annoto.auth(configData.settings.token);
                var annotoBoot = window.Annoto.boot(config);

                $.when( annotoBoot ).then(
                    function () {
                        // annotoApi.auth(token)
                    },
                    function (bootError) {
                        console.error(bootError);
                    }
                );

                // annotoApi.auth(configData.settings.token);

        });
});
