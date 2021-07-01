<?php

/**
 * Class AnnotoAdmin
 */
class AnnotoAdmin {

	/** @var bool $initiated */
	private static $initiated = false;

	public static $intConfigValueNames = array(
		'sso-support',
		'demo-mode',
		// 'locale',
		'widget-max-width',
		// 'annoto-advanced-settings-switch',
		// 'annoto-timeline-overlay-switch',
		'annoto-vimeo-premium-player',
		'widget-features-tabs',
		'widget-features-private',
		'zindex',
		'openOnLoad',
	);

	/**
	 * Init method will initiate all hooks and handle ajax to save settings
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::initHooks();
		}

		$post = $_POST;

		if (
			array_key_exists( 'action', $post )
			&& array_key_exists( 'data', $post )
			&& $post['action'] === 'save-settings'
		) {
			static::saveSettings( $post['data'] );
		}
	}

	/**
	 * All hooks to initialize
	 */
	public static function initHooks() {
		self::$initiated = true;

		add_action( 'admin_menu', array( 'AnnotoAdmin', 'loadMenu' ) );
		add_action( 'admin_enqueue_scripts', array( 'AnnotoAdmin', 'load_resources' ) );
	}

	/**
	 * Load menu Annoto in the admin menu side bar
	 */
	public static function loadMenu() {
		add_options_page(
			__( 'Annoto', 'annoto' ),
			__( 'Annoto', 'annoto' ),
			'manage_options',
			'annoto-key-config',
			array( 'AnnotoAdmin', 'displaySettingsPage' )
		);
	}

	/**
	 * Render Settings page
	 */
	public static function displaySettingsPage() {
		Annoto::view( 'settings' );
	}

	/**
	 * Load all sources
	 */
	public static function load_resources() {
		global $hook_suffix;

		if (
			in_array(
				$hook_suffix,
				apply_filters( 'annoto_admin_page_hook_suffixes', array( 'settings_page_annoto-key-config' ) ),
				true
			)
		) {

			wp_register_style(
				'annoto.css',
				plugin_dir_url( __FILE__ ) . 'src/styles/annoto.css',
				array(),
				ANNOTO_VERSION
			);
			wp_enqueue_style( 'annoto.css' );

			wp_register_style(
				'annoto-bootstrap.css',
				plugin_dir_url( __FILE__ ) . 'src/styles/annoto-bootstrap.css',
				array(),
				ANNOTO_VERSION
			);
			wp_enqueue_style( 'annoto-bootstrap.css' );

			wp_register_script(
				'annoto-admin.js',
				plugin_dir_url( __FILE__ ) . 'src/js/annoto-admin.js',
				array( 'jquery' ),
				ANNOTO_VERSION,
				true
			);
			wp_enqueue_script( 'annoto-admin.js' );

			wp_register_script(
				'annoto-bootstrap.min.js',
				'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
				array( 'jquery' ),
				ANNOTO_VERSION,
				true
			);
			wp_enqueue_script( 'annoto-bootstrap.min.js' );
		}
	}

	/**
	 * AJAX handler to save settings
	 *
	 * @param array $settingsData
	 */
	public static function saveSettings( array $settingsData ) {

		$optionSaveStatus = update_option( 'annoto_settings', static::castSettingValueTypes( $settingsData ) );

		if ( ! $optionSaveStatus ) {
			echo wp_json_encode( array( 'status' => 'failed' ) );
			exit();
		}

		echo wp_json_encode(
			array(
				'status' => 'success',
				'data'   => get_option( 'annoto_settings' ),
			)
		);
		exit();
	}

	/**
	 * Make type casting for some setting value
	 *
	 * @param array $settingData
	 * @return array
	 */
	private static function castSettingValueTypes( array $settingData ) {
		foreach ( $settingData as $settingName => &$settingValue ) {
			if ( in_array( $settingName, static::$intConfigValueNames, true ) ) {
				$settingValue = (int) $settingValue;
			}
		}

		return $settingData;
	}
}
