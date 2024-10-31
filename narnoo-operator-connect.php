<?php
/*
Plugin Name: Narnoo Operator Connect
Plugin URI: http://narnoo.com/
Description: Allows Wordpress users to connect with other Narnoo products and import their listings into their website.
Version: 1.2.2
Author: Narnoo Wordpress developer
Author URI: http://www.narnoo.com/
License: GPL2 or later
*/

/*  Copyright 2019  Narnoo.com  (email : info@narnoo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// plugin definitions
define( 'NARNOO_OPERATOR_CONNECT_PLUGIN_NAME', 'Narnoo Operator Connect' );
define( 'NARNOO_OPERATOR_CONNECT_CURRENT_VERSION', '1.2.2' );
define( 'NARNOO_OPERATOR_CONNECT_I18N_DOMAIN', 'narnoo-operator-connect' );

define( 'NARNOO_OPERATOR_CONNECT_URL', plugin_dir_url( __FILE__ ) );
define( 'NARNOO_OPERATOR_CONNECT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'NARNOO_OPERATOR_DIR', 'narnoo-operator-plugin' );

// include files
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


if( narnoo_operator_is_active() ) {

	if( !class_exists ( 'NarnooToken' ) && file_exists( WP_PLUGIN_DIR . '/' . NARNOO_OPERATOR_DIR . '/libs/narnooauthn.php' ) ) {

		require_once( WP_PLUGIN_DIR . '/' . NARNOO_OPERATOR_DIR . '/libs/narnooauthn.php' );

	}

	if( !class_exists ( 'Narnoosdk' ) && file_exists( WP_PLUGIN_DIR . '/' . NARNOO_OPERATOR_DIR . '/libs/narnooapi.php' ) ) {

		require_once( WP_PLUGIN_DIR . '/' . NARNOO_OPERATOR_DIR . '/libs/narnooapi.php' );
		
	}

}

require_once( NARNOO_OPERATOR_CONNECT_PLUGIN_PATH . 'class-operator-connect-helper.php' );
require_once( NARNOO_OPERATOR_CONNECT_PLUGIN_PATH . 'class-narnoo-connect-find-table.php' );
require_once( NARNOO_OPERATOR_CONNECT_PLUGIN_PATH . 'class-narnoo-connect-following-table.php' );
require_once( NARNOO_OPERATOR_CONNECT_PLUGIN_PATH . 'class-narnoo-connect-product-import-table.php' );
require_once( NARNOO_OPERATOR_CONNECT_PLUGIN_PATH . 'class-narnoo-connect-search-operators-table.php' );
require_once( NARNOO_OPERATOR_CONNECT_PLUGIN_PATH . 'classs-narnoo-operator-connect-categories-table.php' );


function narnoo_operator_is_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php');

	if( file_exists( WP_PLUGIN_DIR . '/'.NARNOO_OPERATOR_DIR.'/narnoo-operator.php' ) ) {

    	if( is_plugin_active( NARNOO_OPERATOR_DIR . '/narnoo-operator.php' ) ) {

    		return true;
    	}
    }
	deactivate_plugins( plugin_basename( __FILE__ ) );
    $narnoo_message = __( 'Operator Connect plugin can only be used if Operator plugin is installed and activated in your website.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN );
    wp_die( $narnoo_message, __( 'Require operator plugin', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), admin_url('plugins.php') );
    return false;
}

// begin!
if( narnoo_operator_is_active() ) {
	new Narnoo_Operator_Connect();
}

class Narnoo_Operator_Connect {

	/**
	 * Plugin's main entry point.
	 **/
	function __construct() {

		add_action( 'init', array( &$this, 'create_custom_post_types' ) );
		
		if ( is_admin() ) {

			add_action( 'admin_menu', array( &$this, 'create_menu' ) );

			add_action( 'wp_ajax_narnoo_operator_connect_api_request', array( 'Narnoo_Operator_Connect_Helper', 'ajax_api_request' ) );

		}else{	

		}
		
	}

	/**
	 * Register custom post types for Narnoo Operator Posts.
	 **/
	function create_custom_post_types() {
		// create custom post types
		$post_types = get_option( 'narnoo_custom_post_types', array() );

		foreach( $post_types as $category => $fields ) {
			register_post_type(
				'narnoo_' . $category,
				array(
					'label' => ucfirst( $category ),
					'labels' => array(
						'singular_name' => ucfirst( $category ),
					),
					'hierarchical' => true,
					'rewrite' => array( 'slug' => $fields['slug'] ),
					'description' => $fields['description'],
					'public' => true,
					'exclude_from_search' => true,
					'has_archive' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'show_in_menu' => 'narnoo-operator-connect-categories',
					'show_in_nav_menus'	=> TRUE,
					'show_in_admin_bar' => true,
					'supports' => array( 'title', 'excerpt', 'thumbnail', 'editor', 'author', 'revisions', 'page-attributes' ),
				)
			);
		}

		$options = get_option('narnoo_operator_settings');
		if ( !empty( $options['operator_import'] )  ) {
			$this->create_product_post_type();
		}


		flush_rewrite_rules();
	}

	/**
	*	Create the shortcode help menu
	**/
	function create_menu(){	

		add_menu_page( 
			__('Narnoo Operator Connect', 	NARNOO_OPERATOR_CONNECT_I18N_DOMAIN), 
			__('Connect', 			NARNOO_OPERATOR_CONNECT_I18N_DOMAIN),  
			'manage_options', 
			'narnoo_operator_connect', 
			array( &$this, 'following_page' ), 
			NARNOO_OPERATOR_CONNECT_URL . 'images/icon-16.png',
			12
			);
		add_action( 'admin_init', array( 'Narnoo_Connect_Following_Table', 'add_screen_options' ) );
		

		$page = add_submenu_page(
			'narnoo_operator_connect',
			__( 'Find', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			__( 'Find', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-find',
			array( &$this, 'find_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Connect_Find_Table', 'add_screen_options' ) );

		$page = add_submenu_page(
			'narnoo_operator_connect',
			__( 'Following', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			__( 'Following', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-following',
			array( &$this, 'following_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Connect_Following_Table', 'add_screen_options' ) );



		// add main Narnoo Imports menu
		add_menu_page(
			__( 'Listings', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			__( 'Listings', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-connect-categories',
			array( &$this, 'categories_page' ),
			'dashicons-location',
			15
		);

		// add submenus to Narnoo Imports menu
		$page = add_submenu_page(
			'narnoo-operator-connect-categories',
			__( 'Listings - Categories', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			__( 'Categories', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-connect-categories',
			array( &$this, 'categories_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Operator_Connect_Categories_Table', 'add_screen_options' ) );
		global $narnoo_operator_connect_categories_page;
		$narnoo_operator_connect_categories_page = $page;

	}

	function connected_page() {
		global $narnoo_connected_operator_table;

		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_CONNECT_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Connected Operators', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) ?></h2>
				<form id="narnoo-find-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_connected_operator_table->get_pagenum() ) ) ); ?>">
				<?php
				if ( $narnoo_connected_operator_table->prepare_items() ) {
					
					$narnoo_connected_operator_table->display_data();
				}
				
				?>
				</form>
		</div>
		<?php
	}
	
	function find_page(){
		global $narnoo_connect_find_table;
		if ( $narnoo_connect_find_table->func_type === 'search' ) {
			$this->search_add_operators_page();
			return;
		}

		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_CONNECT_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Connect - Find Operators', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-operator-find&func_type=search"><?php _e( 'Search/Add Operators', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); ?></a></h2>
				<form id="narnoo-find-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_connect_find_table->get_pagenum() ) ) ); ?>">

				<?php
    				
				if ( $narnoo_connect_find_table->prepare_items() ) {
					$narnoo_connect_find_table->display();
				}
				
				?>
				</form>
		</div>
		<?php
	}


	/**
	 * Display Search/Add Narnoo Operators page.
	 **/
	function search_add_operators_page() {
		global $narnoo_connect_find_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_CONNECT_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Search/Add Operators', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-operator-following"><?php _e( "View Added Operators", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); ?></a></h2>
			<form id="narnoo-search-add-operators-form" method="post" action="?<?php echo esc_attr( build_query( array(
				'page' 				 => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'paged' 			 => $narnoo_connect_find_table->get_pagenum(),
				'func_type' 		 => $narnoo_connect_find_table->func_type
			) ) ); ?>">
				<?php
				if ( $narnoo_connect_find_table->prepare_items() ) {
					$narnoo_connect_find_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Followers page.
	 **/
	function following_page() {
		global $narnoo_connect_following_table;
		if( $narnoo_connect_following_table->func_type === 'product') {
			$this->product_import_table();
			return false;
		}
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_CONNECT_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Connect - Operators you are following', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) ?></h2>
				<form id="narnoo-following-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_connect_following_table->get_pagenum() ) ) ); ?>">

				<?php

				if ( $narnoo_connect_following_table->prepare_items() ) {
					$narnoo_connect_following_table->display();
				}
				
				?>
				</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Operator Product for import 
	 **/
	function product_import_table() {
		global $narnoo_connect_following_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_CONNECT_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Product ', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo_operator_connect"><?php _e( 'Back to Operators', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); ?></a>
			</h2>
				<form id="narnoo-following-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_connect_following_table->get_pagenum(), 'func_type' => $narnoo_connect_following_table->func_type, 'narnoo_id' => $narnoo_connect_following_table->narnoo_id ) ) ); ?>">
				<?php

				if ( $narnoo_connect_following_table->prepare_items() ) {
					$narnoo_connect_following_table->display();
				}
				
				?>
				</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Categories page.
	 **/
	function categories_page() {
		global $narnoo_operator_connect_categories_page;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_CONNECT_I18N_DOMAIN; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Categories', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-categories-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_connect_categories_page->get_pagenum() ) ) ); ?>">
			<?php
			if ( $narnoo_operator_connect_categories_page->prepare_items() ) {
				$narnoo_operator_connect_categories_page->display();
			}
			?>
			</form>
		</div>
		<?php
	}

}

function noc_display_msg( $msg ) {
	$content = '<div id="message" class="updated notice is-dismissible"><p>'.$msg;    
    $content.= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    return $content;
}

function noc_display_error_msg( $msg ) {
	$content = '<div id="message" class="error notice is-dismissible"><p>'.$msg;    
    $content.= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    return $content;
}
