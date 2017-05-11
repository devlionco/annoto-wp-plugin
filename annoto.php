<?php
/*
Plugin Name: Annoto
Plugin URI:  http://www.annoto.net/
Description: WP plugin for Annoto
Version:     0.1
Author:      Dima Stelmakh (Andersen)
Author URI:  https://www.andersenlab.com/
Text Domain: annoto
License:     GPLv3

Annoto Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Annoto Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Annoto Plugin. If not, see https://www.gnu.org/licenses/quick-guide-gplv3.html.
*/



define( 'ANNOTO_VERSION', '0.1' );
define( 'ANNOTO_MINIMUM_WP_VERSION', '4.7' );
define( 'ANNOTO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANNOTO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


register_activation_hook( __FILE__, array( Annoto::class, 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( Annoto::class, 'plugin_deactivation' ) );

require_once( ANNOTO_PLUGIN_DIR . 'class.annoto.php' );
require_once( ANNOTO_PLUGIN_DIR . 'class.annoto-admin.php' );

//require_once( AKISMET__PLUGIN_DIR . 'class.annoto-widget.php' );

//add_action( 'init', array( 'Annoto', 'init' ) );

if ( is_admin() ) {
    add_action( 'init', array( Annoto_Admin::class, 'init' ) );
}

