<?php

require_once ('class.jwt.php');

class Annoto {

    /** @var bool $initiated */
    private static $initiated = false;

    /** @var WP_User|null $currentUserIdentity */
    private static $currentUserIdentity = null;

    private static $defaultSettingValues = [
        'sso-support' => 1,
        'demo-mode' =>  0,
        'widget-position' => 'right',
        'rtl-support' => 0,
        'player-type' => 'youtube',
        'sso-secret' => '',
        'api-key' => ''
    ];

    public static function init()
    {
        if ( ! static::$initiated ) {
            static::init_hooks();
        }

        if (
            array_key_exists('action', $_POST)
            && $_POST['action'] === 'get-settings'
        ) {
            static::getConfigData();
        }
    }

    public static function init_hooks()
    {
        static::$initiated = true;

        static::loadResources();

        add_action( 'wp_loaded', static::setCurrentUser() );
        add_filter('embed_oembed_html', [ static::class, 'prepareVideoIFrameAttributes' ], 10, 3);
    }

    public static function loadResources()
    {
//        wp_register_script(
//            'annoto-bootstrap.js',
//            'https://app.annoto.net/annoto-bootstrap.js',
//            [],
//            ANNOTO_VERSION
//        );
        wp_register_script( 'annoto-bootstrap.js', 'https://staging-app.annoto.net/annoto-bootstrap.js', array(), AKISMET_VERSION );
        wp_enqueue_script( 'annoto-bootstrap.js' );

        wp_register_script(
            'annoto.js',
            plugin_dir_url( __FILE__ ) . 'src/js/annoto.js',
            [ 'jquery' ],
            ANNOTO_VERSION
        );
        wp_enqueue_script( 'annoto.js' );
    }

    public static function setCurrentUser()
    {
        $currentUser = wp_get_current_user();

        if ( $currentUser instanceof WP_User ) {
            static::$currentUserIdentity = $currentUser;
        }
    }

    public static function getConfigData()
    {
        if (
            array_key_exists('action', $_POST)
            && $_POST['action'] === 'get-settings'
        ) {
            $pluginSettings = get_option('annoto_settings');

            if (!$pluginSettings) {
                echo json_encode(['status' => 'failed']);
                exit();
            }

            if ( ! $pluginSettings['sso-support'] ) {
                $pluginSettings['sso-secret'] = '';
            }

            $pluginSettings['token'] = '';
            $pluginSettings['is-annoto-auth'] = false;

            if ( ! $pluginSettings['demo-mode'] && is_user_logged_in() ) {
                $pluginSettings['token'] = static::generateToken($pluginSettings);

                if ( $pluginSettings['sso-support'] ) {
                    $pluginSettings['is-annoto-auth'] = true;
                }
            }

            unset($pluginSettings['sso-secret']);

            echo json_encode([
                'status' => 'success',
                'data' => $pluginSettings
            ]);
            exit();
        }
    }

    public static function prepareVideoIFrameAttributes($html, $url, $attr)
    {
        if ( empty( $attr['id'] ) ) {
            $uniqueId = uniqid('annoto_', true);
            $html = str_replace( '<iframe', sprintf( '<iframe id="%s"', $uniqueId ), $html );
        }

        if ( strpos( $html, 'youtube' ) && !strpos( $html, 'enablejsapi=1' ) ) {
            $html = str_replace( 'feature=oembed', 'feature=oembed&enablejsapi=1', $html );
        }

        return $html;
    }

    public static function view( $name ) {

        $file = ANNOTO_PLUGIN_DIR . 'views/'. $name . '.php';

        include( $file );
    }

    /**
     * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
     * @static
     */
    public static function plugin_activation() {
        if ( static::checkCurrentVersionCorresponding() ) {
            static::showUpdateMessageInfo();

            return;
        }

        if ( !static::setDefaultSettingsValuesForPlugin() ) {
            static::showSettingFailsInfo();

            return;
        }
    }

    /**
     * Removes all connection options
     */
    public static function plugin_deactivation( ) {
        return delete_option( ANNOTO_SETTING_KEY_NAME );
    }

    private static function setDefaultSettingsValuesForPlugin()
    {
        return add_option( ANNOTO_SETTING_KEY_NAME, static::$defaultSettingValues, 'no' );
    }

    private static function checkCurrentVersionCorresponding()
    {
        return version_compare( $GLOBALS['wp_version'], ANNOTO_MINIMUM_WP_VERSION, '<' );
    }

    private static function showSettingFailsInfo()
    {
        $message = '<strong>' . __('Can\'t set default values for Annoto plugin.', 'annoto') . '</strong>';

        static::bail_on_activation( $message );
    }

    private static function showUpdateMessageInfo()
    {
        $message = '<strong>'
            . sprintf(
                esc_html__( 'Annoto %s requires WordPress %s or higher.' , 'annoto'),
                ANNOTO_VERSION,
                ANNOTO_MINIMUM_WP_VERSION
            )
            . '</strong> '
            . sprintf(
                __('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'annoto'),
                'https://codex.wordpress.org/Upgrading_WordPress'
            );

        static::bail_on_activation( $message );
    }

    private static function generateToken(array $pluginSettings)
    {
        $issuedAt = time();
        $expire = $issuedAt + 60*60;

        $payload = [
            'iss' => $pluginSettings['api-key'],
            'exp' => $expire,
            'jti' => static::$currentUserIdentity->ID,
            'name' => static::$currentUserIdentity->display_name,
            'email' => static::$currentUserIdentity->user_email,
            'photoUrl' => get_avatar_url(static::$currentUserIdentity->ID),
        ];

        return JWT::encode($payload, $pluginSettings['sso-secret']);
    }

    private static function bail_on_activation( $message ) {
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
                    font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
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
