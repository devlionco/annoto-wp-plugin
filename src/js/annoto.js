jQuery(
	function ($) {
		var getSettingsDeferred = $.Deferred();

		var configparams = "";

		// Kaltura
		setupKaltura = function () {
			var maKApp                             = window.moodleAnnoto.kApp;
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
			var jwt = configparams["token"];

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
			for (var kdpMapKey in kdpMap) {
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

			var params = configparams;

			var widget       = config.widgets[0];
			var playerConfig = widget.player;

			var ux       = config.ux || {};
			var align    = config.align || {};
			var features = config.features || {};

			config.ux       = ux;
			config.align    = align;
			config.features = features;

			config.clientId  = params["api-key"];
			config.userToken = params["token"];
			config.position  = params["position"];
			config.demoMode  = Boolean( params["demo-mode"] );
			config.locale    = params["locale"];
			config.backend   = {domain: params["deploymentDomain"]};
			features.tabs    = Boolean( params["widget-features-tabs"] );
			align.vertical   = params["alignVertical"];

			align.horizontal = "inner";

			/**
			 * Attach the ssoAuthRequestHandle hook.
			 * https://github.com/Annoto/widget-api/blob/master/lib/config.d.ts#L159
			 */

			config.ux = {
				ssoAuthRequestHandle: function () {
					window.location.replace(params["loginUrl"]);
				},
				openOnLoad: Boolean(params["openOnLoad"]),
			};

			playerConfig.mediaDetails = this.enrichMediaDetails.bind( this );
		};

		enrichMediaDetails = function (details) {
			// The details contains MediaDetails the plugin has managed to obtain
			// This hook gives a change to enrich the details, for example
			// providing group information for private discussions per course/playlist
			// https://github.com/Annoto/widget-api/blob/master/lib/media-details.d.ts#L6.
			// Annoto Kaltura plugin, already has some details about the media like title.
			//
			var params = configparams,
				ann = window.moodleAnnoto,
				annplayers = Object.keys(ann.kApp.kdpMap).filter((name) => /^kaltura_player_*/.test(name)),
				annplayer = ann.kApp.kdpMap[annplayers[0]],
				article = $(annplayer.player).closest('article.post'),
				postid = article[0].id.split('-')[1],
				posttitle = $(article).find('header.entry-header .entry-title').text();

			var retVal = details || {};
			retVal.title = retVal.title || params.mediaTitle;
			retVal.description = retVal.description
				? retVal.description
				: params.mediaDescription;
			retVal.group = {
				id: postid,
				type: "playlist",
				title: posttitle,
				description: posttitle,
				privateThread: params['widget-features-private'],
			};

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
				var response = JSON.parse( serverResponse );

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

		var kaltura = setupKaltura();

		if (kaltura) {
			return;
		}

		$.when(getSettingsDeferred)
			.then(
				function (data) {
					var parent = document.body,
						h5p = $(parent).find("iframe.h5p-iframe").first().get(0),
						youtube = $(parent).find('iframe[src*="youtube.com"]').first().get(0),
						vimeo = $(parent).find('iframe[src*="vimeo.com"]').first().get(0),
						videojs = $(parent).find(".video-js").first().get(0),
						jwplayer = $(parent).find(".jwplayer").first().get(0),
						article = null;


					if (videojs) {
						data["mediaTitle"] = '';
						playerId = videojs.id;
						data["player-type"] = "videojs";
						article = videojs.closest('article.post');
					} else if (jwplayer) {
						data["mediaTitle"] = '';
						playerId = jwplayer.id;
						data["player-type"] = "jw";
						article = jwplayer.closest('article.post');
					} else if (h5p) {
						data["mediaTitle"] = H5PIntegration.contents['cid-1'].title;
						playerId = h5p.id;
						data["player-type"] = "h5p";
						article = h5p.closest('article.post');
					} else if (youtube) {
						data["mediaTitle"] = youtube.title;
						playerId = youtube.id;
						data["player-type"] = "youtube";
						article = youtube.closest('article.post');
					} else if (vimeo) {
						data["mediaTitle"] = vimeo.title;
						playerId = vimeo.id;
						data["player-type"] = "vimeo";
						article = vimeo.closest('article.post');
					}

					data["mediaGroupId"] = article.id.split('-')[1];
					data["mediaGroupTitle"] = $(article).find('header.entry-header .entry-title').text();

					return {
						playerId: playerId,
						settings: data,
					};
			},
			function (errText) {
				console && console.error( errText );
			}
		)
		.then(
			function (configData) {
				if ( ! configData) {
					console && console.error( "Annoto Plugin: settings missing." );
					return;
				}

				if (configData.playerId.length === 0) {
					console &&
					console.error( "Annoto Plugin: Can't determine the player ID." );
					return;
				}

				if (
				! configData.settings["demo-mode"] &&
				configData.settings["api-key"].length === 0
				) {
					console &&
					console.error(
						"Annoto Plugin: Plugin isn't in the Demo Mode, please, set the SSO Secret."
					);
					return;
				}

				if (configData.settings["demo-mode"]) {
					console && console.warn( "Annoto Plugin: Plugin boot in the Demo Mode." );
				}

				$( "#" + configData.playerId ).after( '<div id="annoto-app"></div>' );

				var locale = configData.settings["locale"];
				var rtl    = false;
				if (locale === "he") {
					rtl = true;
				}

				// LearnDash check.
				var videotitle = typeof lesson_title !== 'undefined' ? lesson_title : configData.settings["mediaTitle"];
				var id = typeof course_id !== 'undefined' ? course_id : configData.settings["mediaGroupId"];
				var grouptitle = typeof course_title !== 'undefined' ? course_title : configData.settings["mediaGroupTitle"];

				var config = {
					clientId: configData.settings["api-key"],
					backend: {
						domain: configData.settings["deploymentDomain"]
					},
					position: configData.settings["position"],
					widgets: [{
						player: {
							type: configData.settings["player-type"],
							element: configData.playerId,
							mediaDetails: function() {
								return {
									title: videotitle,
									description: configData.settings["mediaTitle"],
									group: {
										id: id,
										type: 'playlist',
										title: grouptitle,
										description: configData.settings["mediaGroupTitle"],
										privateThread: configData.settings["widget-features-private"],
									}
								};
							},
						},
						timeline: {
							embedded: false,
							overlayVideo: Boolean(
								configData.settings["annoto-timeline-overlay-switch"]
							),
						},
					}],
					simple: true,
					rtl: rtl,
					locale: locale,
					demoMode: Boolean( configData.settings["demo-mode"] ),
					zIndex: configData.settings["zindex"],
				};

				nonOverlayTimelinePlayers = ["youtube", "vimeo"];
				innerAlignPlayers         = ["h5p"];
				horizontalAlign           = "element_edge";

				if (
				! configData.settings["overlayMode"] ||
				configData.settings["overlayMode"] === "auto"
				) {
					horizontalAlign =
					innerAlignPlayers.indexOf( configData.settings["player-type"] ) !== -1
						? "inner"
						: "element_edge";
				} else if (configData.settings["overlayMode"] === "inner") {
					horizontalAlign = "inner";
				}

				config.align = {
					vertical: configData.settings["alignVertical"],
					horizontal: horizontalAlign,
				};

				if (configData.settings["annoto-advanced-settings-switch"]) {
					config.width = { max: configData.settings["widget-max-width"] };
				}

				config.features = {
					tabs: Boolean( configData.settings["widget-features-tabs"] ),
				};

				config.ux = {
					ssoAuthRequestHandle: function () {
						window.location.replace(configData.settings["loginUrl"]);
					},
					openOnLoad: Boolean(configData.settings["openOnLoad"]),
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
						if (configData.settings["token"].length > 0) {
							api.auth(
								configData.settings["token"],
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
