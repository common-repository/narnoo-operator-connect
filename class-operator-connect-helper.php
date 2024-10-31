<?php
/**
 * Helper functions used throughout plugin.
 **/
class Narnoo_Operator_Connect_Helper {


    /**
     * Inits and returns operator request object with user's access and secret keys.
     * If either app or secret key is empty, returns null.
     * @date_modified: 28.09.2017
     * @change_log: Added authentication via token.
     *              Split out the token from authentication keys
     **/
    static function init_api() {
        $options  = get_option( 'narnoo_operator_settings' );

        /**
        *
        *   Store keys in a different setting option
        *
        */
        $token   = get_option( 'narnoo_api_token' );

        if ( empty( $options['access_key'] ) || empty( $options['secret_key'] ) ) {
            return null;
        }
        /**
        *
        *   Check to see if we have access keys and a token.
        *
        */
        if( !empty( $options['access_key'] ) && !empty( $options['secret_key'] ) && empty($token) ){
            /**
            *
            *   Call the Narnoo authentication to return our access token
            *
            */
             //Try get API token
            $narnooToken = new NarnooToken();
            $apiToken = $narnooToken->authenticate($options['access_key'], $options['secret_key']);
            if(!empty($apiToken)){
                 update_option( 'narnoo_api_token', $apiToken, 'yes' );
            }else{
                return null;
            }


        }

        if(empty($_apiToken)){
           $apiToken = get_option( 'narnoo_api_token' );
        }
        /**
        *
        *       Check to see if the current token is expired or not
        *
        **/
        $narnooToken = new NarnooToken();
        $tokenValid = $narnooToken->validCheck( $_apiToken );
        if(empty($tokenValid)){
            $apiToken = $narnooToken->authenticate($options['access_key'], $options['secret_key']);
            if(!empty($apiToken)){
                 update_option( 'narnoo_api_token', $apiToken, 'yes' );
            }else{
                return null;
            }
        }
        //End
        /**
        *   Create authentication Header to access the API.
        **/
        $request = new Narnoosdk($apiToken);
 
        return $request;

    }

    /**
     * Show generic error message.
     * */
    static function show_error($msg) {
        echo '<div class="error"><p>' . $msg . '</p></div>';
    }

    /**
     * In case of API error (e.g. invalid API keys), display error message.
     * */
    static function show_api_error($ex, $prefix_msg = '') {
        $error_msg = $ex->getMessage();
        $msg = '<strong>' . __('Narnoo API error:', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN) . '</strong> ' . $prefix_msg . ' ' . $error_msg;
        if (false !== strchr(strtolower($error_msg), ' authentication fail')) {
            $msg .= '<br />' . sprintf(
                __('Please ensure your API settings in the Settings->Narnoo API page are correct and try again.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN)
                );
        }
        self::show_error($msg);
    }

    /**
     * Retrieves list of operator IDs that have been imported into Wordpress database.
     * */
    static function get_imported_operator_ids() {
        $imported_ids = array();

        $narnoo_custom_post_types = get_option('narnoo_custom_post_types', array());
        foreach ($narnoo_custom_post_types as $category => $fields) {
            $imported_posts = get_posts(array('post_type' => 'narnoo_' . $category, 'numberposts' => -1));
            foreach ($imported_posts as $post) {
                $id = get_post_meta($post->ID, 'operator_id', true);
                if (!empty($id)) {
                    $imported_ids[] = $id;
                }
            }
        }

        return $imported_ids;
    }

    /**
     * Retrieves Wordpress post ID for imported operator ID, if it exists.
     * Returns false if no such operator exists in Wordpress DB.
     * */
    static function get_post_id_for_imported_operator_id($operator_id) {
        $imported_ids = array();

        $narnoo_custom_post_types = get_option('narnoo_custom_post_types', array());
        foreach ($narnoo_custom_post_types as $category => $fields) {
            $imported_posts = get_posts(array('post_type' => 'narnoo_' . $category, 'numberposts' => -1));
            foreach ($imported_posts as $post) {
                $id = get_post_meta($post->ID, 'operator_id', true);
                if ($id === $operator_id) {
                    return $post->ID;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves Wordpress post ID for imported sub-category, if it exists.
     * Returns false if no such subcategory post exists in Wordpress DB.
     * */
    static function get_post_id_for_imported_sub_category($category, $sub_category) {
        $imported_ids = array();

        $imported_posts = get_posts(array('post_type' => 'narnoo_' . $category, 'numberposts' => -1, 'parent' => 0));
        foreach ($imported_posts as $post) {
            $sub_category_archive = get_post_meta($post->ID, 'narnoo_sub_category_archive', true);
            if ($sub_category_archive === $sub_category) {
                return $post->ID;
            }
        }

        return false;
    }

    static function add_custom_post_types($categories) {
        $custom_post_types = get_option('narnoo_custom_post_types', array());
        foreach ($categories as $category) {
            if (!array_key_exists($category, $custom_post_types)) {
                // known categories to pluralize
                $pluralize = array('attraction', 'accommodation', 'service', 'retail');
                $pluralized_category = $category;
                if (in_array($pluralized_category, $pluralize)) {
                    $pluralized_category .= 's';
                }
                $custom_post_types[$category] = array('slug' => sanitize_title_with_dashes($pluralized_category), 'description' => '');
            }
        }
        update_option('narnoo_custom_post_types', $custom_post_types);

        // return response object
        $response = new stdClass();
        $response->success = new stdClass();
        $response->success->successMessage = 'success';
       // $response->success->successMessage = $categories;
        return $response;
    }

    static function print_ajax_script_body($id, $func_name, $params_array, $text = '', $func_type = '', $is_import_operators = false) {
        static $count = 0;

        if (empty($text)) {
            $text = __('Item ID:', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN) . ' ' . $id;
        }
        $text .= '...';
        ?>
        <li>
            <img id="narnoo-icon-process-<?php echo $id; ?>" src="<?php echo admin_url(); ?>images/wpspin_light.gif" /> 
            <img style="display:none;" id="narnoo-icon-success-<?php echo $id; ?>" src="<?php echo admin_url(); ?>images/yes.png" /> 
            <img style="display:none;" id="narnoo-icon-fail-<?php echo $id; ?>" src="<?php echo admin_url(); ?>images/no.png" /> 
            <span><?php echo esc_html($text); ?></span>
            <strong><span id="narnoo-item-<?php echo $id; ?>"><?php _e('Processing...', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN); ?></span></strong>
        </li>
        <script type="text/javascript">
            <?php if ($is_import_operators && $count === 0) { ?>
                var narnoo_categories = [];
            <?php } ?>
            jQuery(document).ready(function($) {
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: { action: 'narnoo_operator_connect_api_request', 
                    type: '<?php echo $func_type; ?>',
                    func_name: '<?php echo $func_name; ?>', 
                    param_array: [ <?php echo "'" . implode("','", $params_array) . "'"; ?> ] },
                    timeout: 60000,
                    dataType: "json",
                    success: 
                    function(response, textStatus, jqXHR) {   
                        $('#narnoo-icon-process-<?php echo $id; ?>').hide();
                        processed++;
                        
                        if (response['success'] === 'success' && response['msg']) {
                            $('#narnoo-icon-success-<?php echo $id; ?>').show();
                            $('#narnoo-item-<?php echo $id; ?>').html(response['msg']);
                            success++;                                      
                            
                            <?php if ($is_import_operators) { ?>
                                narnoo_categories.push( response['response']['category'] );
                                $.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: { action: 'narnoo_operator_connect_api_request', 
                                    type: '<?php echo $func_type; ?>',
                                    func_name: 'add_custom_post_types', 
                                    param_array: [ narnoo_categories ] },
                                    timeout: 60000,
                                    dataType: "json"
                                });
                            <?php } ?>
                        } else {
                            $('#narnoo-icon-fail-<?php echo $id; ?>').show();
                            $('#narnoo-item-<?php echo $id; ?>').html('<?php _e('AJAX error: Unexpected response', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN); ?>');                                      
                        }
                        
                        check_complete($);
                    },
                    error: 
                    function(jqXHR, textStatus, errorThrown) {
                        $('#narnoo-icon-process-<?php echo $id; ?>').hide();
                        $('#narnoo-icon-fail-<?php echo $id; ?>').show();
                        processed++;

                        if (textStatus === 'timeout') {   // server timeout
                            $('#narnoo-item-<?php echo $id; ?>').html('<?php _e('AJAX error: Server timeout', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN); ?>');
                        } else {                  // other error
                            $('#narnoo-item-<?php echo $id; ?>').html(jqXHR.responseText);
                        }
                        
                        check_complete($);
                    }
                });
            });
        </script>
        <?php
        $count++;
    }

    /**
     * Prints out the footer HTML/Javascript needed for AJAX processing.
     * */
    static function print_ajax_script_footer($total_count, $back_button_text, $extra_button_text = '') {
        ?>
        <div class="narnoo-completed" style="display:none;">
            <br />
            <p><strong><?php echo sprintf(__("Processing completed. %s of %d item(s) successful.", NARNOO_OPERATOR_CONNECT_I18N_DOMAIN), '<span id="narnoo-success-count"></span>', $total_count); ?></strong></p>
        </div>
        <p class="submit narnoo-completed" style="display:none;">
            <?php
            if (!empty($extra_button_text)) {
                ?><input type="submit" name="extra_button" id="extra_button" class="button-secondary" value="<?php echo $extra_button_text; ?>" /><?php
            }
            ?>
            <input type="submit" name="back" id="cancel" class="button-secondary" value="<?php echo $back_button_text; ?>" />
        </p>
        <script type="text/javascript">
            var success = 0; 
            var processed = 0;
            function check_complete($) {
                if (processed >= <?php echo $total_count; ?>) {
                    $('#narnoo-success-count').text(success);
                    $('.narnoo-completed').show();
                }                           
            }
        </script>
        <?php
    }

    /**
     * Handling of AJAX request fatal error.
     * */
    static function ajax_fatal_error($sErrorMessage = '') {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        die($sErrorMessage);
    }

    /**
     * Handling of AJAX API requests. 
     * */
    static function ajax_api_request() {
        if (!isset($_POST['func_name']) || !isset($_POST['param_array'])) {
            self::ajax_fatal_error(__('AJAX error: Missing arguments.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN));
        }
        $func_name      = $_POST['func_name'];
        $param_array    = $_POST['param_array'];
        $func_type      = $_POST['type'];

        // init the API request object
        if ($func_type !== 'self') {
            $request = Narnoo_Operator_Connect_Helper::init_api($func_type);
            if (is_null($request)) {
                self::ajax_fatal_error(__('Narnoo API error: Incorrect API keys specified.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN));
            }
        }

        // attempt to call API function with specified params
        $response = array();
        try {
            if ($func_type === 'self') {
                // call static function in helper class
                $response['response'] = call_user_func_array(array('Narnoo_Operator_Connect_Helper', $func_name), $param_array);
            } else {
                $response['response'] = call_user_func_array(array($request, $func_name), $param_array);
            }

            if (false === $response['response']) {
                self::ajax_fatal_error(__('AJAX error: Invalid function or arguments specified.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN));
            }
            $response['success'] = 'success';

            // set success message depending on API function called
            $response['msg'] = __('Success!', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN);
            $item = $response['response'];
            if (!is_null($item)) {
                if (isset($item->success) && isset($item->success->successMessage)) {
                    // copy success message directly from API response
                    $response['msg'] = $item->success->successMessage;
                }
                if ('downloadImage' === $func_name) {
                    $response['msg'] .= ' <a target="_blank" href="' . $item->download_image_file . '">' . __('Download image link', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN) . '</a>';
                } else if ('downloadVideo' === $func_name) {
                    $item->download_video_stream_path = uncdata($item->download_video_stream_path);
                    $item->original_video_path = uncdata($item->original_video_path);
                    $response['msg'] .= ' <a target="_blank" href="' . $item->download_video_stream_path . '">' . __('Download video stream path', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN) . '</a>';
                    $response['msg'] .= ' <a target="_blank" href="' . $item->original_video_path . '">' . __('Original video path', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN) . '</a>';
                } else if ('getAlbums' === $func_name) {
                    // ensure each album name has slashes stripped
                    $albums = $item->distributor_albums;
                    if (is_array($albums)) {
                        foreach ($albums as $album) {
                            $album->album_name = stripslashes($album->album_name);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            self::ajax_fatal_error(__('Narnoo API error: ', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN) . $ex->getMessage());
        }

        echo json_encode($response);
        die();
    }

        /**
     * Imports specified operator along with all their media details from Narnoo database into Wordpress posts.
     * */
    static function import_operator($operator_id, $import_product = true) {
        global $user_ID;
        //$options = get_option('narnoo_distributor_settings');

        // init the API request objects
        $request            = Narnoo_Operator_Connect_Helper::init_api();
        
        if ( is_null($request) ) {
            throw new Exception(__('Incorrect API keys specified.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN));
        }

        // get operator details
        $response = $request->getBusinessListing( $operator_id ); //UPDATED THIS LINE OF CODE

        if(empty($response)){
            die("error notice");
        }

        $operator = $response->data;


        $category = strtolower($operator->profile->category);

        // get existing sub_category post, or create new one if it doesn't exist - the main page for the sub_category
        $sub_category_post_id = 0;
        if (!empty($operator->profile->subCategory)) {

            $sub_category_post_id = Narnoo_Operator_Connect_Helper::get_post_id_for_imported_sub_category($category, $operator->profile->subCategory);
            if ($sub_category_post_id === false) {
                $new_sub_category_post = array(
                    'post_title'        => $operator->profile->subCategory,
                    'post_content'      => '',
                    'post_status'       => 'publish',
                    'post_date'         => date('Y-m-d H:i:s'),
                    'post_author'       => $user_ID,
                    'post_type'         => 'narnoo_' . $category,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    );
                $sub_category_post_id = wp_insert_post($new_sub_category_post);
                update_post_meta($sub_category_post_id, 'narnoo_sub_category_archive', $operator->profile->subCategory);
            }
        }

        // get existing post with operator id, if any
        $post_id = Narnoo_Operator_Connect_Helper::get_post_id_for_imported_operator_id($operator_id);

        if ($post_id !== false) {
            // update existing post, ensuring parent is correctly set
            $update_post_fields = array(
                'ID'            => $post_id,
                'post_title'    => $operator->profile->name,
                'post_type'     => 'narnoo_' . $category,
                'post_status'   => 'publish',
                'post_parent'   => $sub_category_post_id,
                );
            wp_update_post($update_post_fields);
            
            update_post_meta($post_id, 'data_source',            'narnoo');

            //Store biography for the business
            if(!empty($operator->biography)){


                    foreach ($operator->biography as $text) {
                       if($text->language === 'english' && $text->size === 'summary'){
                            update_post_meta($post_id, 'operator_excerpt',        strip_tags( $text->text ) );
                       }

                       if($text->language === 'english' && $text->size === 'description'){
                            update_post_meta($post_id, 'operator_description',    $text->text );
                       }

                    }
            
            }
          
            $feature = get_the_post_thumbnail($post_id);
            if(empty($feature)){
                    if( !empty( $operator->profile->avatar->xxlargeImage ) ){
                        $url            = "https:" . $operator->profile->avatar->xxlargeImage;
                        $desc           = $operator->profile->name . " feature image";
                        $feature_image  = media_sideload_image($url, $post_id, $desc, 'src');
                        if(!empty($feature_image)){
                            global $wpdb;
                            $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                            set_post_thumbnail( $post_id, $attachment[0] );
                        }
                    }
            }

            $success_message = __('Success! Re-imported operator details to existing %1s post (%2s)', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN);
        } else {

            //Store biography for the business
            if(!empty($operator->biography)){

                foreach ($operator->biography as $text) {
                   if($text->language === 'english' && $text->size === 'summary'){
                        $_summary  =  strip_tags( $text->text );
                   }

                   if($text->language === 'english' && $text->size === 'description'){
                        $_description = $text->text;
                   }

                }
            
            }

            // create new post with operator details
            global $wpdb;
            $lastid = $wpdb->insert_id;
            if($lastid == $post_id){
                $new_post_fields = array(
                    'ID'            => $lastid,
                    'post_title'        => $operator->data->profile->name,
                    'post_content'      => $operator_description,
                    'post_excerpt'      => $operator_excerpt,
                    'post_status'       => 'publish',
                    'post_date'         => date('Y-m-d H:i:s'),
                    'post_author'       => $user_ID,
                    'post_type'         => 'narnoo_' . $category,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_parent'       => $sub_category_post_id,
                );
                $post_id = wp_update_post($new_post_fields);  

            }else{
                
                $new_post_fields = array(
                    'post_title'        => $operator->profile->name,
                    'post_status'       => 'publish',
                    'post_date'         => date('Y-m-d H:i:s'),
                    'post_author'       => $user_ID,
                    'post_type'         => 'narnoo_' . $category,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_parent'       => $sub_category_post_id,
                    );
                if(!empty($_summary)){
                    $new_post_fields['post_content'] = $_summary;
                }
                if(!empty($_description)){
                    $new_post_fields['post_excerpt'] = $_description;
                }
                $post_id = wp_insert_post($new_post_fields);
            }

            // set a feature image for this post
            if( !empty( $operator->profile->avatar->xxlargeImage ) ){
                $url            = "https:" . $operator->profile->avatar->xxlargeImage;
                $desc           = $operator->profile->name . " feature image";
                $feature_image  = media_sideload_image($url, $post_id, $desc, 'src');
                if(!empty($feature_image)){
                    global $wpdb;
                    $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                    set_post_thumbnail( $post_id, $attachment[0] );
                }
            }

            $success_message = __('Success! Imported operator details to new %1s post (%2s)', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN);
        }

        // insert/update custom fields with operator details into post
        update_post_meta($post_id, 'data_source',            'narnoo');
        update_post_meta($post_id, 'operator_id',            $operator->profile->id);
        update_post_meta($post_id, 'category',               $operator->profile->category);
        update_post_meta($post_id, 'sub_category',           $operator->profile->subCategory);
        update_post_meta($post_id, 'businessname',           $operator->profile->name);
        update_post_meta($post_id, 'country_name',           $operator->profile->country);
        update_post_meta($post_id, 'state',                  $operator->profile->state);
        update_post_meta($post_id, 'suburb',                 $operator->profile->suburb);
        update_post_meta($post_id, 'location',               $operator->profile->location);
        update_post_meta($post_id, 'postcode',               $operator->profile->postcode);
        update_post_meta($post_id, 'keywords',               $operator->profile->keywords);
        update_post_meta($post_id, 'phone',                  $operator->profile->phone);
        update_post_meta($post_id, 'url',                    $operator->profile->url);
        update_post_meta($post_id, 'email',                  $operator->profile->email);
        update_post_meta($post_id, 'latitude',               $operator->profile->latitude);
        update_post_meta($post_id, 'longitude',              $operator->profile->longitude);
        //Import social media links
        update_post_meta($post_id, 'facebook',               $operator->social->facebook);
        update_post_meta($post_id, 'twitter',                $operator->social->twitter);
        update_post_meta($post_id, 'instagram',              $operator->social->instagram);
        update_post_meta($post_id, 'youtube',                $operator->social->youtube);
        update_post_meta($post_id, 'tripadvisor',            $operator->social->tripadvisor);

        /*
        Import the products? Or do we just use the operator information?
        */
        //if ( !empty( $options['operator_import'] )  ) {
            if( !$import_product ) {
                return array( 
                    "op_id"               => $operator->profile->id,
                    "profile_catgegory"   => $operator->profile->category,
                    "profile_subCategory" => $operator->profile->subCategory,
                    "profile_name"        => $operator->profile->name,
                    "post_id"             => $post_id
                );
            }/* else {
                $opResponse = self::import_operator_products( $operator->profile->id, $operator->profile->category, $operator->profile->subCategory, $operator->profile->name, $post_id );
                if(!empty($opResponse)){
                    update_post_meta($post_id, 'products',            $opResponse);
                }
            } */

        //}


        // return response object
        $response = new stdClass();
        $response->success = new stdClass();
        $response->success->successMessage = 
        sprintf( $success_message, 
         '<a target="_blank" href="edit.php?post_type=narnoo_' . esc_attr( $category ) . '">' . esc_html( ucfirst( $category ) ) . ( empty( $operator->profile->subCategory ) ? '' : '/' . $operator->profile->subCategory ) . '</a>',
         '<a target="_blank" href="post.php?post=' . $post_id . '&action=edit">ID #' . $post_id . '</a>'
         );
        $response->category = $category;
        return $response;
    }

        /**
    *
    *   @dateCreated: 13.02.2018
    *   @title: Import an operators products
    *
    */
    static function import_operator_products( $op_id, $category, $subCategory, $businessName, $postId, $product_id = '' ){
        global $ncm;
        // init the API request objects
        $request            = self::init_api();
        
        if ( is_null($request) ) {
            throw new Exception(__('Incorrect API keys specified.', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN));
        }

        if( !empty($product_id) ) {

            $product_arr = new stdClass();
            $product_arr->productId = $product_id;

            $products = new stdClass();
            $products->data = new stdClass();
            $products->data->data = array( $product_arr );

        } else {

            // get operator details
            $import_bookable_product = apply_filters( 'narnoo_import_only_bookalble_product', false );
            if( $import_bookable_product ){
                $products =  $request->getBookableProducts( $op_id );
            } else {
                $products =  $request->getProducts( $op_id );
            }

            if(empty($products) || empty($products->success)){
               return false;
            }

        }

        /*
        *
        *       --- Check that this isn't the first time the custom post type has been created
        *
        *
        $postCheck = self::product_post_type_init( );
        if( empty($postCheck) ){
            throw new Exception(__('Error creating custom post type page.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }
        
        /************************************************************************************
        *
        *          ----- Loop through the products and return the information ----- 
        *
        *************************************************************************************/
        $product_title = '';
        foreach ($products->data->data as $item) {

            $productDetails = $request->getProductDetails( $item->productId, $op_id );
           
            if(!empty($productDetails) || !empty($productDetails->success)){
                        $postData = self::get_post_id_for_imported_product_id( $productDetails->data->productId );
                        $product_title = $productDetails->data->title;

                        if ( !empty( $postData['id'] ) && $postData['status'] !== 'trash') {
                            $post_id = $postData['id'];
                            // update existing post, ensuring parent is correctly set
                            $update_post_fields = array(
                                'ID'            => $post_id,
                                'post_title'    => $productDetails->data->title,
                                'post_type'     => 'narnoo_product',
                                'post_status'   => 'publish',
                                'post_author'   => $user_ID,
                                'post_modified' => date('Y-m-d H:i:s')
                            );
                            wp_update_post($update_post_fields);
                            
                            if(!empty($productDetails->data->description->description)){

                                foreach ($productDetails->data->description->description as $text) {
                                    if( !empty( $text->english->text ) ){
                                        update_post_meta( $post_id, 'product_description', $text->english->text);
                                    }
                                }


                            }


                            if(!empty($productDetails->data->description->summary)){

                                foreach ($productDetails->data->description->summary as $text) {
                                    if( !empty( $text->english->text ) ){
                                         update_post_meta( $post_id, 'product_excerpt',  strip_tags( $text->english->text ));
                                    }
                                }


                            }

                            
                           // set a feature image for this post but first check to see if a feature is present

                            $feature = get_the_post_thumbnail($post_id);
                            if(empty($feature)){

                                if( !empty( $productDetails->data->featureImage->xxlargeImage ) ){
                                    $url = "https:" . $productDetails->data->featureImage->xxlargeImage;
                                    $desc = $productDetails->data->title . " product image";
                                    //$feature_image = media_sideload_image($url, $post_id, $desc);
                                    $feature_image = media_sideload_image($url, $post_id, $desc, 'id');
                                    if(!empty($feature_image)){
                                        set_post_thumbnail( $post_id, $feature_image );
                                        // global $wpdb;
                                        // $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                                        // set_post_thumbnail( $post_id, $attachment[0] );
                                    }
                                }

                            }

                            //$response['msg'] = "Successfully re-imported product details";

                            $success_message = __('Success! Re-imported Product details to existing %1s post (%2s)', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN);

                        }else{
                        
                            //create new post with operator details
                            $new_post_fields = array(
                                'post_title'        => $productDetails->data->title,
                                'post_status'       => 'publish',
                                'post_date'         => date('Y-m-d H:i:s'),
                                'post_author'       => $user_ID,
                                'post_type'         => 'narnoo_product',
                                'comment_status'    => 'closed',
                                'ping_status'       => 'closed'
                            );


                            if(!empty($productDetails->data->description->description)){

                                        foreach ($productDetails->data->description->description as $text) {
                                            if( !empty( $text->english->text ) ){
                                               $new_post_fields['post_content'] = $text->english->text;
                                            }
                                        }


                            }


                            if(!empty($productDetails->data->description->summary)){

                                foreach ($productDetails->data->description->summary as $text) {
                                    if( !empty( $text->english->text ) ){
                                         $new_post_fields['post_excerpt'] = strip_tags( $text->english->text );
                                    }
                                }


                            }

                           
                            $post_id = wp_insert_post($new_post_fields);
                            
                            // set a feature image for this post
                            if( !empty( $productDetails->data->featureImage->xxlargeImage ) ){
                                $url = "https:" . $productDetails->data->featureImage->xxlargeImage;
                                $desc = $productDetails->data->title . " product image";
                                //$feature_image = media_sideload_image($url, $post_id, $desc);
                                $feature_image = media_sideload_image($url, $post_id, $desc, 'id');
                                if(!empty($feature_image)){
                                    set_post_thumbnail( $post_id, $feature_image );
                                    // global $wpdb;
                                    // $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                                    // set_post_thumbnail( $post_id, $attachment[0] );
                                }
                            }
                            
                            //$response['msg'] = "Successfully imported product details";
                            $success_message = __('Success! Product details to new %1s post (%2s)', NARNOO_OPERATOR_CONNECT_I18N_DOMAIN);

                      }
                    

                    // insert/update custom fields with operator details into post
                    
                    if(!empty($productDetails->data->primary)){
                        update_post_meta($post_id, 'primary_product',               "Primary Product");
                    }else{
                        update_post_meta($post_id, 'primary_product',               "Product");
                    }
                     
                    update_post_meta($post_id, 'narnoo_operator_imported',      true);

                    update_post_meta($post_id, 'narnoo_operator_id',            $op_id); 
                    update_post_meta($post_id, 'narnoo_operator_name',          $businessName);
                    update_post_meta($post_id, 'parent_post_id',                $postId);
                    update_post_meta($post_id, 'narnoo_booking_id',             $productDetails->data->bookingId);  
                    update_post_meta($post_id, 'narnoo_product_id',             $productDetails->data->productId);
                    update_post_meta($post_id, 'product_min_price',             $productDetails->data->minPrice);
                    update_post_meta($post_id, 'product_avg_price',             $productDetails->data->avgPrice);
                    update_post_meta($post_id, 'product_max_price',             $productDetails->data->maxPrice);
                    update_post_meta($post_id, 'narnoo_product_primary',        $productDetails->data->primary);
                    update_post_meta($post_id, 'product_booking_link',          $productDetails->data->directBooking);
                    
                    update_post_meta($post_id, 'narnoo_listing_category',       $category);
                    update_post_meta($post_id, 'narnoo_listing_subcategory',    $subCategory);

                    if( lcfirst( $category ) == 'attraction' ){


                        update_post_meta($post_id, 'narnoo_product_duration',   $productDetails->data->additionalInformation->operatingHours);
                        update_post_meta($post_id, 'narnoo_product_start_time', $productDetails->data->additionalInformation->startTime);
                        update_post_meta($post_id, 'narnoo_product_end_time',   $productDetails->data->additionalInformation->endTime);
                        update_post_meta($post_id, 'narnoo_product_transport',  $productDetails->data->additionalInformation->transfer);
                        update_post_meta($post_id, 'narnoo_product_purchase',   $productDetails->data->additionalInformation->purchases);
                        update_post_meta($post_id, 'narnoo_product_health',     $productDetails->data->additionalInformation->fitness);
                        update_post_meta($post_id, 'narnoo_product_packing',    $productDetails->data->additionalInformation->packing);
                        update_post_meta($post_id, 'narnoo_product_children',   $productDetails->data->additionalInformation->child);
                        update_post_meta($post_id, 'narnoo_product_additional', $productDetails->data->additionalInformation->additional);
                        update_post_meta($post_id, 'narnoo_product_terms',      $productDetails->data->additionalInformation->terms);
                        
                    }
                    /**
                    *
                    *   Import the gallery images as JSON encoded object
                    *
                    */
                    if(!empty($productDetails->data->gallery)){
                        update_post_meta($post_id, 'narnoo_product_gallery', json_encode($productDetails->data->gallery) );
                    }else{
                        delete_post_meta($post_id, 'narnoo_product_gallery');
                    }
                    /**
                    *
                    *   Import the video player object
                    *
                    */
                    if(!empty($productDetail->datas->featureVideo)){
                        update_post_meta($post_id, 'narnoo_product_video', json_encode($productDetails->data->featureVideo) );
                    }else{
                        delete_post_meta($post_id, 'narnoo_product_video');
                    }
                    /**
                    *
                    *   Import the brochure object
                    *
                    */
                    if(!empty($productDetails->data->featurePrint)){   

                        update_post_meta($post_id, 'narnoo_product_print', json_encode($productDetails->data->featurePrint) );
                    }else{

                        delete_post_meta($post_id, 'narnoo_product_print');
                    }
                        
            } //if success*/
        
        } //loop
        /************************************************************************************
        *
        *                   ----- End of products loop ----- 
        *
        *************************************************************************************/
        
        if( !empty($product_id) ) {
            // return response object
            $response = new stdClass();
            $response->success = new stdClass();
            $response->success->successMessage = 
            sprintf( $success_message, 
             '<a target="_blank" href="edit.php?post_type=narnoo_product">' . esc_html( ucfirst( $productDetails->data->title ) ) . '</a>',
             '<a target="_blank" href="post.php?post=' . $post_id . '&action=edit">ID #' . $post_id . '</a>'
             );
            return $response;
        } else {
            return $products->data->count;
        }

    }


     /**
     * Retrieves Wordpress post ID for imported product ID, if it exists.
     * Returns false if no such product exists in Wordpress DB.
     * */
    static function get_post_id_for_imported_product_id($product_id) {
            
            $imported_posts = get_posts(array('post_type' => 'narnoo_product','numberposts' => -1));
            foreach ($imported_posts as $post) {
                $id = get_post_meta($post->ID, 'narnoo_product_id', true);
                
                if ($id === $product_id) {
                    $result['id']       = $post->ID;
                    $result['status'] = get_post_status( $post->ID );                    
                    return $result;
                }

            }

        return false;
    }


}