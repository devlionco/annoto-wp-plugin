jQuery( function ( $ ) {

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
                    if ( this.src.indexOf( data[ 'player-type' ] ) !== -1 && typeof this.id !== 'undefined' ) {
                        playerId = this.id;
                        return false;
                    }

                    return true;
                });

                return {
                    playerId: playerId,
                    settings: data
                };
            },
            function ( errText ){
                console && console.error( errText );
            })
        .then(
            function ( configData ) {

                console.log(configData);

                if ( configData.playerId.length === 0 ) {
                    console && console.error('Annoto Plugin: Can\'t determine the player ID.');
                    return;
                }

                if ( !configData.settings['demo-mode'] && configData.settings['api-key'].length === 0 ) {
                    console && console.error(
                        'Annoto Plugin: Plugin isn\'t in the Demo Mode, please, set the SSO Secret.'
                    );
                    return;
                }

                if (configData.settings['demo-mode']) {
                    console && console.warn('Annoto Plugin: Plugin boot in the Demo Mode.');
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


                if ( ! window.Annoto ) {
                    console && console.error( 'Annoto: not loaded' );

                    return;
                }

                window.Annoto.on( 'ready', function ( api ) {
                    if ( configData.settings['token'].length > 0 ) {
                        api.auth( configData.settings['token'], function ( annotoAuthError ) {
                            if ( annotoAuthError ) {
                                console && console.error( annotoAuthError );
                            }
                        } );
                    }
                } );

                window.Annoto.boot( config );
        });
});
