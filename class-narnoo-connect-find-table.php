<?php
/**
 * Narnoo Operator - Following table.
 **/
class Narnoo_Connect_Find_Table extends WP_List_Table {

	function column_default( $item, $column_name ) {
        switch( $column_name ) { 
            case 'title':
            case 'narnoo_id':
            case 'location':
            case 'type':
            case 'modified_date':
            
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    function column_title( $item ) {
        $actions = array(
            
         
            'Connect'        => sprintf( 
                                    '<a href="?%s">%s</a>', 
                                    build_query( 
                                        array(
                                            'page'       => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
                                            'paged'      => $this->get_pagenum(),
                                            'action'     => 'connect', 
                                            'products[]' => $item['narnoo_id'], 
                                            'narnoo_id'  => $item['narnoo_id'],
                                        )
                                    ),
                                    __( 'Connect', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) 
                                ),
        );
        return sprintf( 
            '<input type="hidden" name="url%1$s" value="%2$s" /> %3$s <br /> %4$s', 
            $item['narnoo_id'],
            $item['title'],
            "<span class='row-title'>".$item['title']."</span>", 
            $this->row_actions($actions) 
        );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="products[]" value="%s" />', $item['narnoo_id']
        );    
    }

    function get_columns() {
        return array(
            'cb'                => '<input type="checkbox" />',
            'title'             => __( 'Title',         NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
            'narnoo_id'         => __( 'Narnoo ID',    NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
            'location'          => __( 'Location',       NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
            'type'              => __( 'Type',  NARNOO_OPERATOR_CONNECT_I18N_DOMAIN )
        );
    }


    function get_bulk_actions() {
        $actions = array(
            'Connect'        => __( 'Connect', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN )
        );
        return $actions;
    }   

    /**
     * Process actions and returns true if the rest of the table SHOULD be rendered.
     * Returns false otherwise.
     **/
    function process_action() {

        if ( isset( $_REQUEST['cancel'] ) ) {
            Narnoo_Operator_Connect_Helper::show_notification( __( 'Action cancelled.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) );
            return true;
        }
        
        if ( isset( $_REQUEST['back'] ) ) {
            return true;
        }
        
        $action = $this->current_action();
        if ( false !== $action ) {

            $product_ids = isset( $_REQUEST['products'] ) ? $_REQUEST['products'] : array();
            $num_ids = count( $product_ids );

            $narnoo_id = isset( $_REQUEST['narnoo_id'] ) ? $_REQUEST['narnoo_id'] : array();
            
            if ( ( empty( $product_ids ) || ! is_array( $product_ids ) || $num_ids === 0 ) && empty( $narnoo_id ) ) {
                return true;
            }

            
            switch ( $action ) {
               
                case 'connect':

                    if( isset( $_REQUEST['narnoo_id'] ) && !empty( $_REQUEST['narnoo_id'] ) ) {
                        $request    = Narnoo_Operator_Connect_Helper::init_api();
                        $response   = $request->followOperator( $_REQUEST['narnoo_id'] );
                        
                        echo noc_display_msg( __( "Successfully connected with this operator.", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) );
                    } else {
                        echo noc_display_msg( __( "Sorry! Something went wrong.", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) );
                    }
            }   // end switch( $action )
        }   // endif ( false !== $action )*/
        
        return true;
    }
    
    /**
     * Request the current page data from Narnoo API server.
     **/
    function get_current_page_data() {
        $data = array( 'total_pages' => 1, 'items' => array() );
        
        $list           = null;
        $current_page   = $this->get_pagenum();
        //$cache          = Narnoo_Operator_Connect_Helper::init_noo_cache();
        $request        = Narnoo_Operator_Connect_Helper::init_api();

        if ( ! is_null( $request ) ) {
    
                //$list = $cache->get('products_'.$current_page);

                if( empty($list) ){

                    try {
                        
                        $list = $request->find( $current_page );

                         // print_r( $list );

                        if ( ! is_array( $list->data ) ) {
                            throw new Exception( sprintf( __( "Error retrieving operators. Unexpected format in response page #%d.", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), $current_page ) );
                        }

                        if(!empty( $list->success ) ){
                         //   $cache->set('products_'.$current_page, $list, 21600);
                        }
                        

                    } catch ( Exception $ex ) {
                        Narnoo_Operator_Connect_Helper::show_api_error( $ex );
                    } 

                }

        }
        
        if ( ! is_null( $list ) ) {
            $data['total_pages'] = max( 1, intval( 1 ) );//check this..
            foreach ( $list->data as $operators ) {
                
                if(empty($operators->details->connected)){

                    $item['title']              = $operators->details->business;
                    $item['location']           = $operators->details->location;
                    $item['narnoo_id']          = $operators->details->id;
                    $item['type']               = ucwords( $operators->details->type );
                    $data['items'][]            = $item;

                }

                
            }
        }
        return $data;
    }
    
    /**
     * Process any actions (displaying forms for the actions as well).
     * If the table SHOULD be rendered after processing (or no processing occurs), prepares the data for display and returns true. 
     * Otherwise, returns false.
     **/
    function prepare_items() {      
        if ( !$this->process_action() ) {
            return false;
        }

        $this->_column_headers = $this->get_column_info();
        
        $data = $this->get_current_page_data();
        $this->items = $data['items'];
        
        $this->set_pagination_args( array(
            'total_items'   => count( $data['items'] ),
            'total_pages'   => $data['total_pages']
        ) );  

        return true;
    }
	
	/**
	 * Add screen options for find page.
	 **/
	static function add_screen_options() {
		global $narnoo_connect_find_table;
        $func_type = isset( $_POST['func_type'] ) ? trim( $_POST['func_type'] ) : ( isset( $_GET['func_type'] ) ? trim( $_GET['func_type'] ) : 'list' );
        switch ( $func_type ) {
            case 'list':    $narnoo_connect_find_table              = new Narnoo_Connect_Find_Table(); break;
            case 'search':  $narnoo_connect_find_table              = new Narnoo_Connect_Search_Add_Operators_Table(); break;
        }
        //print_r($func_type);
		//$narnoo_connect_find_table = new Narnoo_Connect_Find_Table();
	}
}    