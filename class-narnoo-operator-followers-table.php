<?php
/**
 * Narnoo Operator - Followers table.
 **/
class Narnoo_Operator_Followers_Table extends WP_List_Table {
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'business_name':
			case 'country':
			case 'state':
			case 'postcode':
			case 'url':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_url( $item ) {    
		return "<a target='_blank' href='" . $item['url'] . "'>" . $item['url'] . "</a>";
	}
	
	function get_columns() {
		return array(
			'business_name'	=> __( 'Business Name', NARNOO_OPERATOR_I18N_DOMAIN ),
			'country'		=> __( 'Country', NARNOO_OPERATOR_I18N_DOMAIN ),
			'state'			=> __( 'State', NARNOO_OPERATOR_I18N_DOMAIN ),
			'postcode'		=> __( 'Post Code', NARNOO_OPERATOR_I18N_DOMAIN ),
			'url'			=> __( 'URL', NARNOO_OPERATOR_I18N_DOMAIN )
		);
	}

	/**
	 * Request the current page data from Narnoo API server.
	 **/
	function get_current_page_data() {
		$data = array( 'total_pages' => 1, 'items' => array() );
		
		$list = null;
		$current_page = $this->get_pagenum();
		$request = Narnoo_Operator_Helper::init_api();
		if ( ! is_null( $request ) ) {
			try {
				$list = $request->getDistributors( $current_page );
				if ( ! is_array( $list->distributors ) ) {
					throw new Exception( sprintf( __( "Error retrieving followers. Unexpected format in response page #%d.", NARNOO_OPERATOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Operator_Helper::show_api_error( $ex );
			} 
		}
		
		if ( ! is_null( $list ) ) {
			$data['total_pages'] = max( 1, intval( $list->total_pages ) );
			foreach ( $list->distributors as $distributor ) {
				$item['business_name'] = $distributor->business_name;
				$item['country'] = $distributor->country;
				$item['state'] = $distributor->state;
				$item['postcode'] = $distributor->postcode;
				$item['url'] = uncdata( $distributor->url );
				$data['items'][] = $item;
			}
		}

		return $data;
	}

	/**
	 * Prepare the table data for display.
	 **/
	function prepare_items() {		
		$this->_column_headers = $this->get_column_info();
			
		$data = $this->get_current_page_data();
		$this->items = $data['items'];
		
		$this->set_pagination_args( array(
			'total_items'	=> count( $data['items'] ),
			'total_pages'	=> $data['total_pages']
		) );  			
	}
	
	/**
	 * Add screen options for followers page.
	 **/
	static function add_screen_options() {
		global $narnoo_operator_followers_table;
		$narnoo_operator_followers_table = new Narnoo_Operator_Followers_Table();
	}
}    