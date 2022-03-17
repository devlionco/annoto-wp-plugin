jQuery(
	function ($) {
		const getSettingsDeferred = $.Deferred();

		let configparams = "";

		// Kaltura
		setupKaltura = function () {
			const maKApp                             = window.moodleAnnoto.kApp;
			window.moodleAnnoto.setupKalturaKdpMap = this.setupKalturaKdpMap.bind( this );

			if (maKApp) {
				console.log( "AnnotoMoodle: Kaltura loaded on init" );
				this.setupKalturaKdpMap( maKApp.kdpMap );
				return true;
			} else {
				console.log( "AnnotoMoodle: Kaltura not loaded on init" );
			}
		};

		authKalturaPlayer = function (api) {
			// Api is the API to be used after Annoot is setup
			// It can be used for SSO auth.
			const jwt = configparams["token"];

			console.log( "AnnotoMoodle: annoto ready" );
			if (api && jwt && jwt !== "") {
				api.auth( jwt ).catch(
					function () {
						console.error( "AnnotoMoodle: SSO auth error" );
					}
				);
			} else {
				console.log( "AnnotoMoodle: SSO auth skipped" );
			}
		};

		setupKalturaKdpMap = function (kdpMap) {
			if ( ! kdpMap) {
				console.log( "AnnotoMoodle: skip setup Kaltura players - missing map" );
				return;
			}
			console.log( "AnnotoMoodle: setup Kaltura players" );
			for (let kdpMapKey in kdpMap) {
				if (kdpMap.hasOwnProperty( kdpMapKey )) {
					this.setupKalturaKdp( kdpMap[kdpMapKey] );
				}
			}
		};

		setupKalturaKdp = function (kdp) {
			if ( ! kdp.config || kdp.setupDone || ! kdp.doneCb) {
				console.log( "AnnotoMoodle: skip Kaltura player: " + kdp.id );
				return;
			}
			console.log( "AnnotoMoodle: setup Kaltura player: " + kdp.id );
			kdp.setupDone = true;
			kdp.player.kBind( "annotoPluginReady", this.authKalturaPlayer.bind( this ) );
			this.setupKalturaPlugin( kdp.config );
			kdp.doneCb();
		};

		setupKalturaPlugin = function (config) {
			// /*
			// * Config will contain the annoto widget configuration.
			// * This hook provides a chance to modify the configuration if required.
			// * Below we use this chance to attach the ssoAuthRequestHandle and mediaDetails hooks.
			// * https://github.com/Annoto/widget-api/blob/master/lib/config.d.ts#L128
			// *
			// * NOTICE: config is already setup by the Kaltura Annoto plugin,
			// * so we need only to override the required configuration, such as
			// * clientId, features, etc. DO NOT CHANGE THE PLAYER TYPE OR PLAYER ELEMENT CONFIG.
			// */

			const params = configparams;

			const ann = window.moodleAnnoto,
				annplayers = Object.keys(ann.kApp.kdpMap).filter((name) => /^kaltura_player_*/.test(name)),
				annplayer = ann.kApp.kdpMap[annplayers[0]],
				article = $(annplayer.player).closest('article'),
				postid = article[0].id.split('-')[1],
				posttitle = $(article).find('header.entry-header .entry-title').text();

			config.clientId = params["api-key"];
			config.hooks = {
				getPageUrl: function() {
					return window.location.href;
				},
				ssoAuthRequestHandle: function() {
					window.location.replace(params.loginUrl);
				},
				mediaDetails: this.enrichMediaDetails.bind(this),
			};
			config.group = {
				id: postid,
				title: posttitle,
				description: posttitle,
			};

			if (params.locale) {
				config.locale = params.locale;
			}
		};

		enrichMediaDetails = function (mediaParams) {
			// The details contains MediaDetails the plugin has managed to obtain
			// This hook gives a change to enrich the details, for example
			// providing group information for private discussions per course/playlist
			// https://github.com/Annoto/widget-api/blob/master/lib/media-details.d.ts#L6.
			// Annoto Kaltura plugin, already has some details about the media like title.
			//
			const params = configparams;
			const retVal = (mediaParams && mediaParams.details) || {};

			retVal.title = retVal.title || params.mediaTitle;
			retVal.description = retVal.description || params.mediaDescription;

			return retVal;
		};

		$.post(
			"",
			{
				action: "get-settings",
				data: {},
			}
		).done(
			function (serverResponse) {
				const response = JSON.parse( serverResponse );

				if (response.status === "success") {
					configparams = response.data;

					getSettingsDeferred.resolve( response.data );
				} else {
					getSettingsDeferred.reject(
						"WP Server: Fetching of the Annoto settings has failed!"
					);
				}
			}
		);

		const kaltura = setupKaltura();

		if (kaltura) {
			return;
		}

		$.when(getSettingsDeferred)
			.then(
				function (data) {
					let parent = document.body,
						h5p = $(parent).find("iframe.h5p-iframe").first().get(0),
						youtube = $(parent).find('iframe[src*="youtube.com"]').first().get(0),
						vimeo = $(parent).find('iframe[src*="vimeo.com"]').first().get(0),
						videojs = $(parent).find(".video-js").first().get(0),
						jwplayer = $(parent).find(".jwplayer").first().get(0),
						article = null,
						player = null;


					if (videojs) {
						data["mediaTitle"] = '';
						player = videojs;
						data["player-type"] = "videojs";
						article = videojs.closest('article');
					} else if (jwplayer) {
						data["mediaTitle"] = '';
						player = jwplayer;
						data["player-type"] = "jw";
						article = jwplayer.closest('article');
					} else if (h5p) {
						data["mediaTitle"] = H5PIntegration.contents['cid-1'].title;
						player = h5p;
						data["player-type"] = "h5p";
						article = h5p.closest('article');
					} else if (youtube) {
						data["mediaTitle"] = youtube.title;
						player = youtube;
						data["player-type"] = "youtube";
						article = youtube.closest('article');
					} else if (vimeo) {
						data["mediaTitle"] = vimeo.title;
						player = vimeo;
						data["player-type"] = "vimeo";
						article = vimeo.closest('article');
					} else {
						return {}; // No player found
					}

					data["mediaGroupId"] = article.id.split('-')[1];
					data["mediaGroupTitle"] = $(article).find('header.entry-header .entry-title').text();

					if (!player.id || player.id === '') {
						player.id = 'annoto_' + Math.random().toString(36).substr(2, 6);
					}

					return {
						playerId: player.id,
						settings: data,
					};
			},
			function (errText) {
				console && console.error( errText );
			}
		)
		.then(
			function (configData) {
				if (!Object.keys(configData).length) {
					console.info('AnnotoMoodle: Player not recognized');
					return;
				}
				if ( !configData ) {
					console && console.error( "Annoto Plugin: settings missing." );
					return;
				}

				$( "#" + configData.playerId ).after( '<div id="annoto-app"></div>' );

				// LearnDash check.
				const params = configData.settings;
				const videotitle = typeof lesson_title !== 'undefined' ? lesson_title : params.mediaTitle;
				const id = typeof course_id !== 'undefined' ? course_id : params.mediaGroupId;
				const grouptitle = typeof course_title !== 'undefined' ? course_title : params.mediaGroupTitle;
				const nonOverlayTimelinePlayers = ["youtube", "vimeo"];

				const config = {

					clientId: params["api-key"],
					backend: {
						domain: params.deploymentDomain,
					},
					hooks: {
						mediaDetails: function() {
							return {
								details: {
									title: videotitle,
									description: params.mediaTitle,
								}
							};
						},
						ssoAuthRequestHandle: function() {
							window.location.replace(params.loginUrl);
						},
					},
					group: {
						id: id,
						title: grouptitle,
						description: params.mediaGroupTitle,
					},
					widgets: [{
						player: {
							type: configData.settings["player-type"],
							element: `#${configData.playerId}`,
							timeline:  {
								overlay: (nonOverlayTimelinePlayers.indexOf(configData.settings['player-type']) === -1)
							},

						},
					}],
					... (params.locale) && {locale: params.locale},
				};

				if (configData.settings["player-type"] === "vimeo") {
					config.widgets[0].player.params =
					configData.settings["annoto-player-params"];
				}

				if ( ! window.Annoto) {
					console && console.error( "Annoto: not loaded" );
					return;
				}

				window.Annoto.on(
					"ready",
					function (api) {
						if (params.token.length > 0) {
							api.auth(
								params.token,
								function (annotoAuthError) {
									if (annotoAuthError) {
										console && console.error( annotoAuthError );
									}
								}
							);
						}
					}
				);
				         
				window.Annoto.boot( config );
			}
		);
	}
);
