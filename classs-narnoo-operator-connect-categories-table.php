<?php
/**
 * Narnoo Operator - Categories table.
 **/
class Narnoo_Operator_Connect_Categories_Table extends WP_List_Table {
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'custom_post_type':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_category( $item ) {    
		$actions = array(
			'delete'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'delete', 
											'custom_post_types[]' => $item['custom_post_type'], 
										)
									),
									__( 'Delete', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) 
								),
		);
		return sprintf( 
			'%1$s<br /> %2$s', 
			$item['category'], 
			$this->row_actions($actions) 
		);
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="custom_post_types[]" value="%s" />', esc_attr( $item['custom_post_type'] )
		);    
	}

	function column_slug( $item ) {
		return sprintf(
			'<input type="hidden" name="custom_post_type_list[]" value="%s" /><input type="text" name="slugs[]" value="%s" />', 
			esc_attr( $item['custom_post_type'] ),
			esc_attr( $item['slug'] ) 
		);
	}
	
	function column_description( $item ) {
		return sprintf(
			'<textarea name="descriptions[]">%s</textarea>', 
			esc_html( $item['description'] ) 
		);
	}

	function get_columns() {
		return array(
			'cb'				=> '<input type="checkbox" />',
			'category'	        => __( 'Imported Category', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'custom_post_type'	=> __( 'Custom Post Type', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'slug'		        => __( 'Slug', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
			'description'		=> __( 'Description', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN )
		);
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'		=> __( 'Delete', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN )
		);
		return $actions;
	}

	/**
	 * Process actions and returns true if the rest of the table SHOULD be rendered.
	 * Returns false otherwise.
	 **/
	function process_action() {
		if ( isset( $_REQUEST['cancel'] ) ) {
			noc_display_msg( __( 'Action cancelled.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) );
			return true;
		}
		
		if ( isset( $_REQUEST['back'] ) ) {
			return true;
		}

		if ( isset( $_POST['save_changes'] ) ) {
			// process save
			$custom_post_types = isset( $_REQUEST['custom_post_type_list'] ) ? $_REQUEST['custom_post_type_list'] : array();
			$slugs = isset( $_REQUEST['slugs'] ) ? $_REQUEST['slugs'] : array();
			$descriptions = isset( $_REQUEST['descriptions'] ) ? $_REQUEST['descriptions'] : array();
			$num_ids = count( $custom_post_types );
			if ( empty( $custom_post_types ) || ! is_array( $custom_post_types ) || $num_ids === 0 || 
				empty( $slugs ) || ! is_array( $slugs ) || count( $slugs ) !== $num_ids || 
				empty( $descriptions ) || ! is_array( $descriptions ) || count( $descriptions ) !== $num_ids ) 
			{
				return true;				
			}			

			$narnoo_custom_post_types = get_option( 'narnoo_custom_post_types', array() );
			foreach( $narnoo_custom_post_types as $category => $fields ) {
				$key = array_search( 'narnoo_' . $category, $custom_post_types );
				if ( $key === false ) {
					// not in POST data? just ignore the custom post type
					continue;
				}
				
				$narnoo_custom_post_types[ $category ] = array( 
					'slug' => strtolower( sanitize_title_with_dashes( $slugs[ $key ] ) ),
					'description' => $descriptions[ $key ]
				);
			}
			
			update_option( 'narnoo_custom_post_types', $narnoo_custom_post_types );
			noc_display_msg( __( 'Update successful.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) );
			
			return true;
		}

		$action = $this->current_action();
		if ( false !== $action ) {
			$custom_post_types = isset( $_REQUEST['custom_post_types'] ) ? $_REQUEST['custom_post_types'] : array();
			$num_ids = count( $custom_post_types );
			if ( empty( $custom_post_types ) || ! is_array( $custom_post_types ) || $num_ids === 0 ) {
				return true;				
			}
			
			switch ( $action ) {
					
				// confirm deletion
				case 'delete':
					?>
					<h3><?php _e( 'Confirm deletion', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( 'This action will delete all posts of the selected custom post types and cannot be undone. Please confirm deletion of the following %d custom post type(s):', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php 
					foreach ( $custom_post_types as $custom_post_type ) { 
						?>
						<input type="hidden" name="custom_post_types[]" value="<?php echo esc_attr( $custom_post_type ); ?>" />
						<li><span><?php echo esc_html( $custom_post_type ); ?></span></li>
						<?php 
					} 
					?>
					</ol>
					<input type="hidden" name="action" value="do_delete" />
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button-secondary" value="<?php _e( 'Confirm Deletion' ); ?>" />
						<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
					</p>
					<?php
					
					return false;
					
				// perform actual delete
				case 'do_delete':
					$count = 0;
					$post_count = 0;
					$narnoo_custom_post_types = get_option( 'narnoo_custom_post_types', array() );
					$updated_custom_post_types = array();
					foreach( $narnoo_custom_post_types as $category => $fields ) {
						if ( in_array( 'narnoo_' . $category, $custom_post_types ) ) {
							// get all posts of any post_status type of this custom post type
							$imported_posts = get_posts( 
								array( 
									'post_type' => 'narnoo_' . $category, 
									'numberposts' => -1, 
									'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ) // 'any' keyword excludes trash
								) 
							);
							
							// delete each post
							foreach( $imported_posts as $post ) {
								wp_delete_post( $post->ID, true );
								$post_count++;
							}

							$count++;
						} else {
							$updated_custom_post_types[ $category ] = $fields;	// category not marked for deletion
						}
					}
					
					update_option( 'narnoo_custom_post_types', $updated_custom_post_types );
					
					noc_display_msg( sprintf( __( '%1d post(s) of %2d custom post type(s) deleted.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), $post_count, $count ) );
					
					return true;
					
			} 	// end switch( $action )
		}	// endif ( false !== $action )
		
		return true;
	}
	
	function display() {
		parent::display();
		submit_button( __( 'Save Changes', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), 'button-primary action', "save_changes", false );
	}
	
	/**
	 * Request the current page data from Narnoo API server.
	 **/
	function get_current_page_data() {
		$data = array( 'per_page' => 30, 'items' => array() );
		
		$narnoo_custom_post_types = get_option( 'narnoo_custom_post_types', array() );
		foreach ( $narnoo_custom_post_types as $category => $fields ) {
			$item['category'] = ucfirst( $category );
			$item['custom_post_type'] = 'narnoo_' . $category;
			$item['slug'] = $fields['slug'];
			$item['description'] = $fields['description'];
			$data['items'][] = $item;
		}
		
		return $data;
	}
	
	/**
	 * Process any actions (displaying forms for the actions as well).
	 * If the table SHOULD be rendered after processing (or no processing occurs), prepares the data for display and returns true. 
	 * Otherwise, returns false.
	 **/
	function prepare_items() {		
		if ( ! $this->process_action() ) {
			return false;
		}		

		$this->_column_headers = $this->get_column_info();

		$data = $this->get_current_page_data();
		$this->items = $data['items'];
		
		$this->set_pagination_args( array(
			'total_items'	=> count( $data['items'] ),
			'per_page'	    => $data['per_page']
		) );  
		
		if ( count( $data['items'] ) === 0 ) {
			?><p><?php printf( __( 'Please visit the <a href="%s">Operators page</a> to import an operator.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), admin_url( "admin.php?page=
			narnoo_operator_connect" ) ); ?></p><?php
		}
		
		?>
		<p>
			<?php _e( 'You can access the Description field in your theme template file using the following code:', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); ?>
			<code>
				$custom_post_type = get_post_type_object( get_post_type() );
				echo $custom_post_type->description;				
			</code>
		</p>
		<?php
		
		return true;
	}
	
	/**
	 * Add screen options for Categories page.
	 **/
	static function add_screen_options() {
		global $narnoo_operator_connect_categories_page;
		$narnoo_operator_connect_categories_page = new Narnoo_Operator_Connect_Categories_Table();
	}
	
	/**
	 * Enqueue scripts and print out CSS stylesheets for this page.
	 **/
	static function load_scripts( $hook ) {
		global $narnoo_operator_connect_categories_page;
		
		if ( $narnoo_operator_connect_categories_page !== $hook ) {	// ensure scripts are only loaded on this Page
			return;
		}
		
		?>
		<style type="text/css">
		.wp-list-table .column-custom_post_type { width: 15%; }
		.wp-list-table .column-slug { width: 20% !important; }
		.wp-list-table .column-slug input { width: 98%; }
		.wp-list-table .column-description { width: 50%; }
		.wp-list-table .column-description textarea { width: 98%; height: 80px; }
		</style>
		<?php
	}	
}    