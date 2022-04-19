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

    /** @var array $annotoDefaultSettings */
    public static $annotoDefaultSettings = [
        [
            'name' => 'sso-secret',
            'value' => '',
            'desc' => "SSO secret is provided by Annoto (keep in secret)",
            'type' => 'input',
        ],
        [
            'name' => 'api-key',
            'value' => '',
            'desc' => "ClientID is provided by Annoto (keep in secret)",
            'type' => 'input',
        ],
        [
            'name' => 'scripturl',
            'value' => 'https://cdn.annoto.net/widget/latest/bootstrap.js',
            'desc' => "Provide Annoto's script URL here",
            'type' => 'input',
        ],
        [
            'name' => 'locale',
            'value' => 1,
            'desc' => "Locale",
            'type' => 'checkbox',
        ],
        [
            'name' => 'deploymentDomain',
            'value' => 'euregion',
            'desc' => "Deployment domain",
            'type' => 'select',
        ],
    ];

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

		// LearnDash hooks.
		function get_course_ID() {
			if (get_post_type() == 'product') {
				return get_post_meta(get_the_ID(), '_related_course', true)[0];
			}
			if (get_post_type() == 'forum') {
				return get_post_meta(get_the_ID(), '_id_associated_courses', true)[0];
			}
			if (get_post_type() == 'topic' || get_post_type() == 'reply') {
				$forum_id = get_post_meta(get_the_ID(), '_bbp_forum_id', true);
				return get_post_meta($forum_id, '_id_associated_courses', true)[0];
			}
			if (get_post_type() == 'sfwd-topic' || get_post_type() == 'sfwd-quiz') {
				return get_post_meta(get_the_ID(), 'course_id', true);
			}
			return get_the_ID();
		}

		function wpb_hook_javascript() {
			if (get_post_type() != 'sfwd-topic' && get_post_type() != 'sfwd-quiz') {
				return;
			}
			update_user_meta(get_current_user_id(), get_course_ID() . 'last_step', get_the_ID());
			?>
				<script>
				var lesson_title = '<?php echo get_the_title(); ?>';
				var course_id = '<?php echo get_course_ID(); ?>';
				var course_title = '<?php echo get_the_title(get_course_ID()); ?>';
				</script>
				<?php
		}
		add_action( 'wp_head', 'wpb_hook_javascript' );
		add_action( 'wp_loaded', array( 'Annoto', 'set_current_user' ) );
		add_filter( 'embed_oembed_html', array( 'Annoto', 'prepare_video_iframe_attributes' ), 10, 3 );
	}

	/**
	 * Load all sources
	 */
	public static function load_resources() {

        $settings = get_option( ANNOTO_SETTING_KEY_NAME );
        wp_register_script(
            'annoto-bootstrap.js',
            $settings['scripturl'],
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
			$plugin_settings = get_option( ANNOTO_SETTING_KEY_NAME );

			if ( ! $plugin_settings ) {
				echo wp_json_encode( array( 'status' => 'failed' ) );
				exit();
			}


			$plugin_settings['token'] = '';

			if (
				$plugin_settings['sso-secret']
				&& is_user_logged_in()
			) {
				$plugin_settings['token'] = static::generateToken( $plugin_settings );
			}

			unset( $plugin_settings['sso-secret'] );

			$plugin_settings['locale'] = $plugin_settings['locale'] ? substr( get_locale(), 0, 2 ) : null;
			$plugin_settings['loginUrl']      = wp_login_url();

            if ($plugin_settings['deploymentDomain'] == 'euregion' || $plugin_settings['deploymentDomain'] == '') {
                $plugin_settings['deploymentDomain'] = 'eu.annoto.net';
            } else if ($plugin_settings['deploymentDomain'] == 'usregion') {
                $plugin_settings['deploymentDomain'] = 'us.annoto.net';
            }

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
			$unique_id = uniqid( 'annoto_');
			$html      = str_replace( '<iframe', sprintf( '<iframe id="%s"', $unique_id ), $html );
		}

		if ( strpos( $html, 'youtube' ) && ! strpos( $html, 'enablejsapi=1' ) ) {
			$html = str_replace( 'feature=oembed', 'feature=oembed&enablejsapi=1', $html );
		}

		return $html;
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
     * get default setting values for plugin
     *
     * @return array $defaultSettingValues
     */
    public function getDefaultSettingValues() {
        $defaultSettingValues = [];
        $allValues = static::$annotoDefaultSettings;
        foreach ($allValues as $value) {
            $defaultSettingValues[$value['name']] = $value['value'];
        }

        return $defaultSettingValues;
    }
    
	/**
	 * Set default setting values for plugin
	 *
	 * @return bool
	 */
    private static function setDefaultSettingsValuesForPlugin() {
        if (!get_option( ANNOTO_SETTING_KEY_NAME )) {
            return add_option( ANNOTO_SETTING_KEY_NAME, self::getDefaultSettingValues(), 'no' );
        }
        return true;
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

		return JWT::encode( $payload, $plugin_settings['sso-secret'] );
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
