<?php
/**
 * Narnoo Operator - Following table.
 **/
class Narnoo_Connect_Product_Import_table extends WP_List_Table {

    public $func_type = 'product';
    public $narnoo_id = '';

    function __construct( $args = array() ) {
        parent::__construct( $args );
        
        $this->narnoo_id = isset( $_POST['narnoo_id'] ) ? trim( $_POST['narnoo_id'] ) : ( isset( $_GET['narnoo_id'] ) ? trim( $_GET['narnoo_id'] ) : '' );
        
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) { 
            case 'product_name':
            case 'product_id':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    function column_product_name( $item ) {

        $postData = Narnoo_Operator_Connect_Helper::get_post_id_for_imported_product_id( $item['product_id'] );
        if ( !empty( $postData['id'] ) && $postData['status'] !== 'trash') {
            $link_text = __( 'Re-import', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); 
        } else {
            $link_text = __( 'Import', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); 
        }

        $actions = array( 
            'Import' => sprintf( 
                        '<a href="?%s">%s</a>', 
                        build_query( 
                            array(
                                'page'       => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
                                'paged'      => $this->get_pagenum(),
                                'func_type'  => $this->func_type,
                                'narnoo_id'  => $this->narnoo_id,
                                'action'     => 'do_import', 
                                'products[]' => $item['product_id'],  
                            )
                        ),
                        $link_text
                    ),
        );
        return sprintf( 
            '<input type="hidden" name="url%1$s" value="%2$s" /> %3$s <br /> %4$s', 
            $item['product_id'],
            $item['product_name'],
            "<span class='row-title'>".$item['product_name']."</span>", 
            $this->row_actions($actions) 
        );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="products[]" value="%s" />', $item['product_id']
        );    
    }

    function get_bulk_actions() {
        $actions = array(
            'do_import' => __( 'Import', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN )
        );
        return $actions;
    } 

    function get_columns() {
        return array(
            'cb'           => '<input type="checkbox" />',
            'product_name' => __( 'Product Name', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
            'product_id'   => __( 'Product ID', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ),
        );
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
               
                // perform actual import
                case 'do_import':
                    ?>
                    <h3><?php _e( 'Import', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ); ?></h3>
                    <p><?php echo sprintf( __( "Importing the following %s product(s):", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), $num_ids ); ?></p>
                    <ol>
                    <?php
                    // Get operator details
                    $operator_data = Narnoo_Operator_Connect_Helper::import_operator( $narnoo_id, false );
                    // Import Products
                    foreach( $product_ids as $key => $id ) {
                        Narnoo_Operator_Connect_Helper::print_ajax_script_body(
                            $id, 'import_operator_products', array_merge( $operator_data, array( 'product_id' => $id ) ),
                            'ID #' . $id . ': ', 'self', true
                        );
                    }
                    ?>
                    </ol>
                    <?php
                    Narnoo_Operator_Connect_Helper::print_ajax_script_footer( $num_ids, __( 'Back to products', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ) );
                    return false;
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
                        
                        $import_bookable_product = apply_filters( 'narnoo_import_only_bookalble_product', false );
                        if( $import_bookable_product ){
                            $list =  $request->getBookableProducts( $this->narnoo_id );
                        } else {
                            $list =  $request->getProducts( $this->narnoo_id );
                        }

                         // print_r( $list );

                        if ( ! is_array( $list->data->data ) ) {
                            throw new Exception( sprintf( __( "Error retrieving Products. Unexpected format in response page #%d.", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN ), $current_page ) );
                        }

                        if( !empty( $list->success ) ){
                         //   $cache->set('products_'.$current_page, $list, 21600);
                        }
                        

                    } catch ( Exception $ex ) {
                        Narnoo_Operator_Connect_Helper::show_api_error( $ex );
                    } 
                }
        }


        if ( ! is_null( $list ) ) {
            $data['total_pages'] = max( 1, intval( 1 ) );//check this..
            foreach ( $list->data->data as $product ) {
                $item['product_name']       = $product->title;
                $item['product_id']         = $product->productId;
                $data['items'][]            = $item;
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
        global $narnoo_connect_following_table;
        $narnoo_connect_following_table = new Narnoo_Connect_Product_Import_table();
    }
}    