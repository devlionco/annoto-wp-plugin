<?php

require_once 'class.jwt.php';

/**
 * Class Annoto
 */
class Annoto {

	/** @var bool $initiated */
	private static $initiated = false;

	/** @var WP_User|null $current_user_identity */
	private static $current_user_identity = null;

	/** @var array $defaultSettingValues */
	private static $defaultSettingValues = array(
		'sso-support'                     => 0,
		'demo-mode'                       => 1,
		'annoto-advanced-settings-switch' => 0,
		'annoto-timeline-overlay-switch'  => 0,
		'widget-position'                 => 'right',
		'locale'                          => 'auto',
		'rtl-support'                     => 0,
		'player-type'                     => 'youtube',
		'annoto-vimeo-premium-player'     => 0,
		'sso-secret'                      => '',
		'api-key'                         => '',
		'widget-max-width'                => '300',
		'widget-align-vertical'           => 'center',
		'widget-align-horizontal'         => 'center',
		'annoto-player-params'            => '{}',
		'overlayMode'                     => 'element_edge',
		'zindex'                          => '100',
        'widget-features-private'         => 1,
	);

	/**
	 * Init method will initiate all hooks and handle ajax to return settings
	 */
	public static function init() {
		if ( ! static::$initiated ) {
			static::init_hooks();
		}

		$post = $_POST;

		if (
			array_key_exists( 'action', $post )
			&& 'get-settings' === $post['action']
		) {
			static::getConfigData();
		}
	}

	/**
	 * All hooks to initialize
	 */
	public static function init_hooks() {
		static::$initiated = true;

		static::load_resources();

		add_action( 'wp_loaded', static::set_current_user() );
		add_filter( 'embed_oembed_html', array( 'Annoto', 'prepare_video_iframe_attributes' ), 10, 3 );
	}

	/**
	 * Load all sources
	 */
	public static function load_resources() {
		wp_register_script(
			'annoto-bootstrap.js',
			'https://app.annoto.net/annoto-bootstrap.js',
			array(),
			ANNOTO_VERSION,
			true
		);
		wp_enqueue_script( 'annoto-bootstrap.js' );

		wp_register_script(
			'annoto.js',
			plugin_dir_url( __FILE__ ) . 'src/js/annoto.js',
			array( 'jquery' ),
			ANNOTO_VERSION,
			true
		);
		wp_enqueue_script( 'annoto.js' );

		wp_register_script(
			'initkaltura.js',
			plugin_dir_url( __FILE__ ) . 'src/js/initkaltura.js',
			array( 'jquery' ),
			ANNOTO_VERSION,
			true
		);
		wp_enqueue_script( 'initkaltura.js' );
	}

	/**
	 * Setter to store current user to class property
	 */
	public static function set_current_user() {
		$currentUser = wp_get_current_user();

		if ( $currentUser instanceof WP_User ) {
			static::$current_user_identity = $currentUser;
		}
	}

	/**
	 * Handle the AJAX and incapsulate business-logic according to retrieving correct config values
	 */
	public static function getConfigData() {
		$post = $_POST;

		if (
			array_key_exists( 'action', $post )
			&& 'get-settings' === $post['action']
		) {
			$plugin_settings = get_option( 'annoto_settings' );

			if ( ! $plugin_settings ) {
				echo wp_json_encode( array( 'status' => 'failed' ) );
				exit();
			}

			if ( ! $plugin_settings['sso-support'] ) {
				$plugin_settings['sso-secret'] = '';
			}

			$plugin_settings['token'] = '';

			if (
				! $plugin_settings['demo-mode']
				&& $plugin_settings['sso-support']
				&& is_user_logged_in()
			) {
				$plugin_settings['token'] = static::generateToken( $plugin_settings );
			}

			unset( $plugin_settings['sso-secret'] );

			if ( 'auto' === $plugin_settings['locale'] || '' === $plugin_settings['locale'] ) {
				$plugin_settings['locale'] = substr( get_locale(), 0, 2 );
			}
			if ( 'he' === $plugin_settings['locale'] ) {
				$plugin_settings['rtl-support'] = 1;
			}

			$widgetposition      = 'right';
			$widgetverticalalign = 'center';
			if ( stripos( $plugin_settings['widget-position'], 'left' ) !== false ) {
				$widgetposition = 'left';
			}
			if ( stripos( $plugin_settings['widget-position'], 'top' ) !== false ) {
				$widgetverticalalign = 'top';
			}
			if ( stripos( $plugin_settings['widget-position'], 'bottom' ) !== false ) {
				$widgetverticalalign = 'bottom';
			}
			$plugin_settings['position']      = $widgetposition;
			$plugin_settings['alignVertical'] = $widgetverticalalign;
			$plugin_settings['loginUrl']      = wp_login_url();

			echo wp_json_encode(
				array(
					'status' => 'success',
					'data'   => $plugin_settings,
				)
			);
			exit();
		}
	}

	/**
	 * Make IFrames compatible with Annoto API
	 *
	 * @param string $html
	 * @param array  $attr
	 *
	 * @return string
	 */
	public static function prepare_video_iframe_attributes( $html, $attr ) {
		if ( empty( $attr['id'] ) ) {
			$unique_id = uniqid( 'annoto_', true );
			$html      = str_replace( '<iframe', sprintf( '<iframe id="%s"', $unique_id ), $html );
		}

		if ( strpos( $html, 'youtube' ) && ! strpos( $html, 'enablejsapi=1' ) ) {
			$html = str_replace( 'feature=oembed', 'feature=oembed&enablejsapi=1', $html );
		}

		return $html;
	}

	/**
	 * Render view by name
	 *
	 * @param string $name
	 */
	public static function view( $name ) {

		$file = ANNOTO_PLUGIN_DIR . 'views/' . $name . '.php';

		include $file;
	}

	/**
	 * Plugin activation handler
	 */
	public static function plugin_activation() {
		if ( static::checkCurrentVersionCorresponding() ) {
			static::showUpdateMessageInfo();

			return;
		}

		if ( ! static::setDefaultSettingsValuesForPlugin() ) {
			static::showSettingFailsInfo();

			return;
		}
	}

	/**
	 * Plugin deactivation handler
	 */
	public static function plugin_deactivation() {
		return delete_option( ANNOTO_SETTING_KEY_NAME );
	}

	/**
	 * Set default setting values for plugin
	 *
	 * @return bool
	 */
	private static function setDefaultSettingsValuesForPlugin() {
		return add_option( ANNOTO_SETTING_KEY_NAME, static::$defaultSettingValues, 'no' );
	}

	/**
	 * Check is current version of Annoto plugin corresponding to WP version
	 *
	 * @return mixed
	 */
	private static function checkCurrentVersionCorresponding() {
		return version_compare( $GLOBALS['wp_version'], ANNOTO_MINIMUM_WP_VERSION, '<' );
	}

	/**
	 * Show setting fails info
	 */
	private static function showSettingFailsInfo() {
		$message = '<strong>' . __( 'Can\'t set default values for Annoto plugin.', 'annoto' ) . '</strong>';

		static::showMessageTemplate( $message );
	}

	/**
	 * Show update message info
	 */
	private static function showUpdateMessageInfo() {
		$message = '<strong>'
		. sprintf(
			esc_html__( 'Annoto %1$s requires WordPress %2$s or higher.', 'annoto' ),
			ANNOTO_VERSION,
			ANNOTO_MINIMUM_WP_VERSION
		)
		. '</strong> '
		. sprintf(
			__( 'Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'annoto' ),
			'https://codex.wordpress.org/Upgrading_WordPress'
		);

		static::showMessageTemplate( $message );
	}

	/**
	 * Generate JWT Token
	 *
	 * @param array $plugin_settings
	 *
	 * @return string
	 */
	private static function generateToken( array $plugin_settings ) {
		$issued_at = time();
		$expire    = $issued_at + 60 * 60;

		$payload = array(
			'iss'      => $plugin_settings['api-key'],
			'exp'      => $expire,
			'jti'      => static::$current_user_identity->ID,
			'name'     => static::$current_user_identity->display_name,
			'email'    => static::$current_user_identity->user_email,
			'photoUrl' => get_avatar_url( static::$current_user_identity->ID ),
			'scope'    => ( static::$current_user_identity->caps['administrator'] ? 'super-mod' : 'user' ),
		);

		$ret = JWT::encode( $payload, $plugin_settings['sso-secret'] );

		return $ret;
	}

	/**
	 * Show message template
	 *
	 * @param string $message
	 */
	private static function showMessageTemplate( $message ) {
		?>
		<!doctype html>
		<html>

		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<style>
				* {
					text-align: center;
					margin: 0;
					padding: 0;
					font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
				}

				p {
					margin-top: 1em;
					font-size: 18px;
				}
			</style>

		<body>
			<p><?php echo $message; ?></p>
		</body>

		</html>
		<?php
		exit();
	}
}
