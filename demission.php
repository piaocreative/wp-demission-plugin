<?php
/*
    Plugin Name: Demission
    Plugin URI:
    Description:
    Version: 1
    Author: Carmbo
    Author URI:
    License:
*/

// if this file is called directly, abort
if ( !defined( 'WPINC' ) )
    die;

// require __DIR__ . '/vendor/autoload.php';

// use Khartnett\Normalization;

class Demission_Plugin {

    protected static $instance = null;
    protected $plugin_path;
    protected $plugin_url;

    protected $google_api_key = '';

    protected $state_due_months = array(
        'CA' => array(4, 11)
    );

    protected $monthly_tax_payment_id = 181;
    protected $quarterly_tax_payment_id = 182;
    protected $biyearly_tax_payment_id = 183;

    /**
     * create or return an instance of this class
     */
    public static function get_instance() {
        if ( null == self::$instance )
            self::$instance = new self;

        return self::$instance;
    }

    /**
     * construct
     */
    private function __construct() {
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->plugin_url  = plugin_dir_url( __FILE__ );

        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_styles' ) );

        register_activation_hook( __FILE__, array( $this, 'activation' ) );
		
		// shortcode
        add_shortcode( 'demission_main', array( $this, 'demission_main_shortcode' ) );
        add_shortcode( 'demission_property', array( $this, 'demission_property_shortcode' ) );

        // ajax - search by pin
        add_action( 'wp_ajax_nopriv_demission_pin_search', array( $this, 'handle_demission_pin_search' ) );
        add_action( 'wp_ajax_demission_pin_search', array( $this, 'handle_demission_pin_search' ) );

        // ajax - search by zip & streetnum
        add_action( 'wp_ajax_nopriv_demission_zip_streetnum_search', array( $this, 'handle_demission_zip_streetnum_search' ) );
        add_action( 'wp_ajax_demission_zip_streetnum_search', array( $this, 'handle_demission_zip_streetnum_search' ) );

        // ajax - calculate owed amount
        add_action( 'wp_ajax_nopriv_demission_calc_owe_amount', array( $this, 'handle_demission_calc_owe_amount' ) );
        add_action( 'wp_ajax_demission_calc_owe_amount', array( $this, 'handle_demission_calc_owe_amount' ) );

        // ajax - pay tax
        add_action( 'wp_ajax_nopriv_demission_pay_tax', array( $this, 'handle_demission_pay_tax' ) );
        add_action( 'wp_ajax_demission_pay_tax', array( $this, 'handle_demission_pay_tax' ) );
		
		// hook
        add_action( 'admin_menu', array( $this, 'csv_import_menu' ) );
        add_action( 'admin_head', array( $this, 'admin_head' ) );

        add_action( 'wp_head', array( $this, 'wp_head' ) );

        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_property_data_to_order' ), 10, 1 );

        add_filter( 'woocommerce_account_menu_items', array( $this, 'custom_account_menu_items' ), 999 );

        add_action( 'woocommerce_before_calculate_totals', array( $this, 'change_cart_product_price' ) );

        add_action( 'woocommerce_checkout_subscription_created', array( $this, 'update_created_subscription' ) );

        add_action( 'woocommerce_thankyou', array( $this, 'redirect_custom_thankyou' ) );

        add_action( 'wp_login', array( $this, 'redirect_after_login' ) );        
    }

    /**
     * Get plugin url
     */
    public function get_plugin_url() {
        return $this->plugin_url;
    }

    /**
     * Get plugin path
     */
    public function get_plugin_path() {
        return $this->plugin_path;
    }

    /**
     * Plugin Activation hook 
     * Create the {prefix}_demis_entries table
     */
    public function activation() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . 'demis_entries';
        $sql_create_table = "CREATE TABLE $table_name (
            id bigint(15) unsigned NOT NULL AUTO_INCREMENT,
            state_abbr varchar(2),
            tax_year int(4) unsigned,
            due_year int(4) unsigned,
            pin bigint(12) unsigned,
            situs varchar(150),
            legal_party_1 varchar(150),
            summary_amount decimal(10,2),
            zip int(5) unsigned,
            street_numb varchar(50),
            PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta($sql_create_table);
    }

    /*
    public function deactivation() {

    }*/

    /**
     * Register scripts and styles for the admin
     */
    public function admin_register_scripts_styles() {

    }

    /**
     * Register scripts and styles for the frontend
     * 
     */
    public function register_scripts_styles() {
        wp_register_script( 'demission', $this->plugin_url . 'js/demission-scripts.js',  array( 'jquery' ), '', true );

        $dl = array(            
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'demis_nonce' ),
            'doing_ajax' => false,
            'property_url' => get_permalink( get_page_by_path( 'property' ) ),
            'account_url' => get_permalink( wc_get_page_id( 'myaccount' ) ),
            'checkout_url' => wc_get_checkout_url(),
        );

        wp_localize_script(
            'demission',
            'Demission',
            $dl
        );

        wp_enqueue_script( 'demission' );

        // Mapbox
        wp_enqueue_script( 'mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v1.11.1/mapbox-gl.js', array(), false, true );
        wp_enqueue_style( 'mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v1.11.1/mapbox-gl.css' );        
        wp_enqueue_script( 'es6-promise', 'https://unpkg.com/es6-promise@4.2.4/dist/es6-promise.auto.min.js', array(), false, true );
        wp_enqueue_script( 'mapbox-sdk', 'https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min.js', array(), false, true );

        // Google Map
        wp_enqueue_script( 'google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCzx6VMeLysmf0jUp_sguVvZxnL9ORzG5U&libraries=places', array(), false, true );
    }

    /**
     * The function for shortcode [demission_main]
     */
    public function demission_main_shortcode() {
        ob_start();
        ?>

        <div id="demis_search_content">
            <!-- Alert -->
            <div class="row">
                <div class="col-12">
                    <div class="alert"></div>
                </div>
            </div>
            
            <!-- Map Area -->
            <div class="row">
                <div class="col-12">                    
                    <input type="text" class="form-control mb-3" placeholder="Search By Location" id="search_location" value="">
                    <div id="map"></div>
                </div>
            </div>

            <!-- Search Result Area -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-3">Search Result</h2>
                        <form class="form-inline">
                            <input type="text" class="form-control" placeholder="Search By Pin" id="search_pin" value="">  
                        </form>
                    </div>
                    <div id="demis_search_results_area">
                        <table class="table" id="demis_entry_table">
                            <thead class="table-dark">
                                <tr>
                                    <td scope="col">State</td>
                                    <td scope="col">Tax Year</td>
                                    <td scope="col">Due Year</td>
                                    <td scope="col">Pin</td>
                                    <td scope="col">Situs</td>
                                    <td scope="col">Legal Party 1</td>
                                    <td scope="col">Zip</td>
                                    <td scope="col">Street Number</td>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $shortcode_output = ob_get_contents();
        ob_end_clean();

        return $shortcode_output;
    }

    /**
     * The function for shortcode [demission_property]
     */
    public function demission_property_shortcode() {
        ob_start();

        if ( isset( $_GET['id'] ) )
            $property_id = $_GET['id'];

        if ( isset( $property_id ) && $property_id ) {
            ?>

            <div class="row" id="property_details" data-property="<?php echo $property_id; ?>">
                <div class="col-12 col-md-5">
                    <div class="property-details">

                        <?php
                        global $wpdb;   
                    
                        $records = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'demis_entries WHERE id=' . $property_id . ' LIMIT 1', ARRAY_A );
                        $property_data = $records[0];
                        ?>

                        <input type="hidden" id="property_id" value="<?php echo $property_data['id']; ?>" />
                        <input type="hidden" id="property_state_abbr" value="<?php echo $property_data['state_abbr']; ?>" />
                        <input type="hidden" id="property_tax" value="<?php echo $property_data['summary_amount']; ?>" />
                        <input type="hidden" id="property_tax_year" value="<?php echo $property_data['tax_year']; ?>" />
                        <input type="hidden" id="property_due_year" value="<?php echo $property_data['due_year']; ?>" />
                        <input type="hidden" id="property_pin" value="<?php echo $property_data['pin']; ?>" />
                        <input type="hidden" id="property_situs" value="<?php echo $property_data['situs']; ?>" />
                        <input type="hidden" id="property_legal_party_1" value="<?php echo $property_data['legal_party_1']; ?>" />
                        <input type="hidden" id="property_zip" value="<?php echo $property_data['zip']; ?>" />
                        <input type="hidden" id="property_street_numb" value="<?php echo $property_data['street_numb']; ?>" />

                        <h5><strong>State: </strong><?php echo $property_data['state_abbr']; ?></h5>
                        <h5><strong>Tax Year: </strong><?php echo $property_data['tax_year']; ?></h5>
                        <h5><strong>Due Year: </strong><?php echo $property_data['due_year']; ?></h5>
                        <h5><strong>Pin: </strong><?php echo $property_data['pin']; ?></h5>
                        <h5><strong>Situs: </strong><?php echo $property_data['situs']; ?></h5>
                        <h5><strong>Legal Party: </strong><?php echo $property_data['legal_party_1']; ?></h5>
                        <h5><strong>Zip: </strong><?php echo $property_data['zip']; ?></h5>
                        <h5><strong>Street Number: </strong><?php echo $property_data['street_numb']; ?></h5>

                        <h4 class="mt-5">Do you want to add your home insurance?</h4>
                        <div class="form-group">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="home_insurance_option" id="home_insurance_option1" value="yes" checked>
                                <label class="form-check-label" for="home_insurance_option1">
                                    Yes
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="home_insurance_option" id="home_insurance_option2" value="no">
                                <label class="form-check-label" for="home_insurance_option2">
                                    No
                                </label>
                            </div>
                        </div>

                        <div class="form-group home-insurance-wrapper">
                            <h4>Home Insurance</h4>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" id="home_insurance" class="form-control" value="0.00" pattern="[0-9]*" />
                            </div>                            
                        </div>

                        <h4 class="mt-5">Tax: $<span id="tax_amount"><?php echo $property_data['summary_amount']; ?></span></h4>                       

                        <h4 class="mt-5">Payment Options</h4>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pay_option" id="pay_option1" value="0" checked>
                                <label class="form-check-label" for="pay_option1">
                                    Monthly
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pay_option" id="pay_option2" value="1">
                                <label class="form-check-label" for="pay_option2">
                                    Quarterly
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pay_option" id="pay_option3" value="2">
                                <label class="form-check-label" for="pay_option3">
                                    Biyearly
                                </label>
                            </div>
                        </div>

                        <h3 id="calc_amount">You owe: $<span id="amount_owed">0.00</span> / <span id="amount_per_month">every month</span></h3>

                        <div class="alert" role="alert" style="display: none;"></div>

                        <button class="btn btn-primary btn-lg mt-4" id="btn_signup">Signup Now</button>

                    </div>
                </div>

                <div class="col-12 col-md-7">
                    <div id="map"></div>
                </div><!-- .col-md-7 -->

            </div>
            
            <?php
        }
        
        $shortcode_output = ob_get_contents();
        ob_end_clean();

        return $shortcode_output;
    }

    /**
     * Create the Pin Search Form
     * 
     */
    public function demission_pin_search_form() {
        ob_start();
        ?>

        <div class="pin_search">
            <label for="pin">PIN Search</label>
            <div class="row">
                <div class="col-12">
                    <input type="text" class="form-control pin" id="pin" name="pin" placeholder="Enter PIN">
                </div>
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-primary mt-2 pin_search_button">Search</button>
                </div>            
            </div>
        </div>

        <?php
        $shortcode_output = ob_get_contents();
        ob_end_clean();

        return $shortcode_output;
    }

    /**
     * Pin Search Ajax Hander
     * 
     * Retrieve the entries by pin
     */
    public function handle_demission_pin_search() {
        if ( !check_ajax_referer( 'demis_nonce', 'security' ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        $pin = $_POST['pin'];

        if ( $pin ) {
            global $wpdb;
            $records = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'demis_entries WHERE pin = ' . $pin . ';', ARRAY_A );

            echo json_encode( 
                array(
                    'success' => true,
                    'data' => $records
                )
            );
            
            exit;
        }

        echo json_encode( 
            array(
                'success' => false,
                'data' => __( 'The pin code is not available.' )
            )
        );
        
        exit;
    }

    /**
     * Create the Zip Search Form
     * 
     */
    public function demission_zip_search_form() {
        ob_start();
        ?>

        <div class="address_zip_search">
            <label>Address Search</label>
            <div class="row mb-2">
                <div class="col-6">
                    <input type="text" class="form-control zip" pattern="[0-9]*" placeholder="Enter Zip">
                </div>
                <div class="col-6">
                    <input type="text" class="form-control street_number" placeholder="Enter Street Number">
                </div>      
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-primary mt-2 zip_search_button">Search</button>
                </div>
            </div>            
        </div>

        <?php
        $shortcode_output = ob_get_contents();
        ob_end_clean();

        return $shortcode_output;
    }

    /**
     * Zip Search Ajax Handler
     * 
     * Retrieve the entries by Zip
     * 
     */
    public function handle_demission_zip_streetnum_search() {
        if ( !check_ajax_referer( 'demis_nonce', 'security' ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        $zip = $_POST['zip'];     
        $streetNumber = $_POST['streetNumber'];

        if ( $zip || $streetNumber ) {
            global $wpdb;

            $cond = 'WHERE 1';

            if ( $zip )
                $cond .= ' AND zip=' . $zip;

            if ( $streetNumber )
                $cond .= ' AND street_numb=' . $streetNumber;
        
            $records = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'demis_entries ' . $cond . ';', ARRAY_A );
        
            echo json_encode( 
                array(
                    'success' => true,
                    'data' => $records
                )
            );
            exit;
        }

        echo json_encode( 
            array(
                'success' => false,
                'data' => __( 'The zip code & street number is not available.' )
            )
        );
        exit;
    }

    /**
     * demission_calc_owe_amount ajax handler
     * 
     * Calcuate the owed amount and return it
     * 
     */
    public function handle_demission_calc_owe_amount() {
        // Check security
        if ( ! check_ajax_referer( 'demis_nonce', 'security' ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        $pay_option = $_POST['pay_option'];             // pay option
        $home_insurance = $_POST['home_insurance'];     // home insurance
        $tax = $_POST['tax'];                           // tax
        $due_year = $_POST['due_year'];                 // due year

        $start_date = time();
        $end_date = mktime( 23, 59, 39, 12, 31, $due_year );

        $diff_months = $this->get_month_diff( $start_date, $end_date );

        $result = 0;
        $total_amount = floatval( $home_insurance ) + $diff_months * floatval( $tax );

        if ( $pay_option == '0' ) {                     // Monthly payment
            $result = floatval( $total_amount / 12 );
        } else if ( $pay_option == '1' ) {              // Quarterl payment
            $result = floatval( $total_amount / 4 );
        } else if ( $pay_option == '2' ) {              // Biyearly payment
            $result = floatval( $total_amount / 2 );
        }
        
        echo json_encode(
            array(
                'success' => true,
                'data' => $result
            )
        );

        exit;
    }

    /**
     * Calculates how many months is past between two timestamps.
     *
     * @param  int $start : Start timestamp.
     * @param  int $end : End timestamp.
     *
     */
    protected function get_month_diff( $start, $end ) {
        $start = new DateTime( "@$start" );
        $end = new DateTime( "@$end" );

        $diff = $end->diff( $start );

        return $diff->format( '%y' ) * 12 + $diff->format( '%m' ) + 1;
    }

    /**
     * demission_pay_tax ajax handler
     * 
     */
    public function handle_demission_pay_tax() {
        // Check security
        if ( ! check_ajax_referer( 'demis_nonce', 'security' ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        // First make sure all required functions and classes exist
        if( ! function_exists( 'wc_create_order' ) || ! function_exists( 'wcs_create_subscription' ) || ! class_exists( 'WC_Subscriptions_Product' ) ){
            wp_send_json_error( 'Not support the subscription' );
            wp_die();
        }        

        global $wpdb;
        global $woocommerce;

        $id = $_POST['property_id'];                    // property id
        $tax = $_POST['property_tax'];                  // tax amount
        $home_insurance = $_POST['home_insurance'];     // home insurance amount
        $pay_option = $_POST['pay_option'];             // pay option {0, 1, 2}
        $owed_amount = $_POST['owed_amount'];           // owed amount
        
        // $user_id = get_current_user_id();               // logged in user id

        $cond = 'WHERE 1';
        if ( $id && !empty( $id ) )
            $cond .= ' AND id="' . $id . '"';                    
        
        $records = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'demis_entries ' . $cond . ';', ARRAY_A );        
        $property_data = $records[0];                   // choosed property data

        // Get the product with payment option
        if ( $pay_option == '0' ) {                     // Monthly payment
            $product_id = $this->monthly_tax_payment_id;            
        } else if ( $pay_option == '1' ) {              // Quarterl payment
            $product_id = $this->quarterly_tax_payment_id;            
        } else if ( $pay_option == '2' ) {              // Biyearly payment
            $product_id = $this->biyearly_tax_payment_id;            
        }

        // Add subscription product to the cart
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart( $product_id, 1 ); 

        // Save the subscription amount & product id to the session
        WC()->session->set( 'subscription_amount', $owed_amount );
        WC()->session->set( 'subscription_product_id', $product_id );
        WC()->session->set( 'property_data', $property_data );
        WC()->session->set( 'ordered', TRUE );

        // Check if the user is logged in
        if ( ! is_user_logged_in() ) {

            wp_send_json_error( 'Oops... don\'t worry. Your order was saved. Please <a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '">login</a> and continue.' );
            wp_die();
        }
        
        echo json_encode(
            array(
                'success' => true,
                'data' => __( 'Successfully added order and subscription. Please check your subscriptions in your <a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '/subscriptions">account.</a>' )
            )
        );

        exit;
    }

    /**
     * change the product price in the cart
     * 
     */
    public function change_cart_product_price( $cart_object ) {
        global $woocommerce;

        // Get the subscriptoin amount from the session
        $subscription_amount = WC()->session->get( 'subscription_amount' );
        
        // Change prices
        foreach ( $cart_object->get_cart() as $hash => $value ) {
            $value['data']->set_price( floatval( $subscription_amount ) );
        } 
    }

    /**
     * Update the created subscription
     * 
     */
    public function update_created_subscription( $subscription, $order, $recurring_cart ) {
        // Get the subscriptoin amount from the session
        $product_id = WC()->session->get( 'subscription_product_id' );
        $property_data = WC()->session->get( 'property_data' );

        // Update the order meta with property data
        update_post_meta( $order->get_id(), '_property_id', sanitize_text_field( $property_data['id'] ) );
        update_post_meta( $order->get_id(), '_state_abbr', sanitize_text_field( $property_data['state_abbr'] ) );
        update_post_meta( $order->get_id(), '_tax_year', sanitize_text_field( $property_data['tax_year'] ) );
        update_post_meta( $order->get_id(), '_due_year', sanitize_text_field( $property_data['due_year'] ) );
        update_post_meta( $order->get_id(), '_pin', sanitize_text_field( $property_data['pin'] ) );
        update_post_meta( $order->get_id(), '_situs', sanitize_text_field( $property_data['situs'] ) );
        update_post_meta( $order->get_id(), '_legal_party_1', sanitize_text_field( $property_data['legal_party_1'] ) );
        update_post_meta( $order->get_id(), '_zip', sanitize_text_field( $property_data['zip'] ) );
        update_post_meta( $order->get_id(), '_street_numb', sanitize_text_field( $property_data['street_numb'] ) );
        update_post_meta( $order->get_id(), '_summary_amount', sanitize_text_field( $property_data['summary_amount'] ) );
        update_post_meta( $order->get_id(), '_home_insurance', sanitize_text_field( $home_insurance ) );   
    }

    /**
     * Redirect subscriptions page after order
     * 
     */
    public function redirect_custom_thankyou( $order_id ) {
        $custom_link = get_permalink( wc_get_page_id( 'myaccount' ) ) . '/subscriptions';
        wp_safe_redirect( $custom_link );
        exit;
    }

    /**
     * Create the Address Search Form
     */
    public function demission_address_search_form() {
        ob_start();
        ?>

        <div class="address_search_wrap">
            <label>Address Search</label>
            <input id="autocomplete" class="address_string" type="text" placeholder="Enter Full Address">
        </div>
        
        <?php
        $shortcode_output = ob_get_contents();
        ob_end_clean();

        return $shortcode_output;
    }

    /**
     * Address Search Ajax Handler
     * Build the full address
     */
    public function handle_demission_address_search() {
        if ( !check_ajax_referer( 'demis_nonce', 'security' ) ) {
            wp_send_json_error( 'Invalid security token sent.' );
            wp_die();
        }

        $search_string = $_POST['search_string'];
        $state = $_POST['administrative_area_level_1'];
        $city = $_POST['locality'];
        $zip = $_POST['postal_code'];
        $street_name = $_POST['route'];
        $street_number = $_POST['street_number'];

        if ( ! strlen( $search_string ) ) {
            $full_address = '';
            // build address
            if ( strlen( $street_number ) ) {
                $full_address .= $street_number . ' ';
            }

            if ( strlen( $street_name ) ) {
                $full_address .= $street_name . ', ';
            }

            if ( strlen( $mail_state ) ) {
                $full_address .= $mail_state;
            }
            if ( strlen( $mail_zip ) ) {
                $full_address .= ' ' . $mail_zip;
            }

            $full_address = rtrim( $full_address, ', ' );
        }

        echo json_encode( 
            array(
                'success' => true,
                'data' => __( 'Successfully searched' )
            )
        );
        
        exit;
    }

    /**
     * Add CSV Import menu on the admin side
     */
    public function csv_import_menu() {
        add_menu_page( 'CSV Import', 'CSV Import', 'manage_options', 'demis_csv_import', array( $this, 'csv_import_admin_page' ) );
    }

    /**
     * Create the csv import page on the admin side
     */
    public function csv_import_admin_page() {
        global $wpdb;

        // Table name
        $tablename = $wpdb->prefix . 'demis_entries';

        // Import CSV
        if ( isset( $_POST['demisimport'] ) ) {

            // File extension
            $extension = pathinfo( $_FILES['import_file']['name'], PATHINFO_EXTENSION );

            // If file extension is 'csv'
            if ( !empty( $_FILES['import_file']['name'] ) && $extension == 'csv' ) {

                $totalInserted = 0;

                // Open file in read mode
                $csvFile = fopen( $_FILES['import_file']['tmp_name'], 'r' );

                fgetcsv( $csvFile ); // Skipping header row

                echo '<div class="demis_import">';

                // Read file
                $line_count = 1;

                while ( ( $csvData = fgetcsv( $csvFile ) ) !== FALSE ) {

                    $csvData = array_map( 'utf8_encode', $csvData );

                    // Row column length
                    $dataLen = count( $csvData );

                    // Assign value to variables
                    $state_abbr = trim( $csvData[0] );      // State Abbr
                    $tax_year = intval( $csvData[1] );      // Tax Year
                    $due_year = intval( $csvData[2] );      // Due Year    
                    $pin = trim( $csvData[3] );             // PIN
                    $situs = trim( $csvData[4] );           // Situs
                    $legal_party_1 = trim( $csvData[5] );   // Legal Party 1
                    $summary_amount = trim( $csvData[6] );  // Summary Amount

                    $exp_situs = explode( ' ', $situs );
                    $cnt_situs = count( $exp_situs );

                    if ( $cnt_situs > 1 ) {
                        $street_numb = $exp_situs[0];
                        $zip = $exp_situs[$cnt_situs-1];
                        $zip = preg_replace( '/\D/', '', $zip );
                        $zip = substr( $zip, 0, 5 );
                        if ( !$zip ) {
                            $zip = '';
                        }
                    } else {
                        $street_numb = '';
                        $zip = '';
                        $situs = 'No Property Address';
                    }
                    ?>

                    <div class="demis_import_line">
                        <div class="numb" style="font-weight:700;"><?php echo $line_count; ?></div>
                        <div class="field pin">state_abbr: <?php echo $state_abbr; ?></div>
                        <div class="field pin">tax_year: <?php echo $tax_year; ?></div>
                        <div class="field pin">due_year: <?php echo $due_year; ?></div>
                        <div class="field pin">pin: <?php echo $pin; ?></div>
                        <div class="field pin">situs: <?php echo $situs; ?></div>
                        <div class="field pin">legal_party_1: <?php echo $legal_party_1;?></div>
                        <div class="field pin">summary_amount: <?php echo $summary_amount; ?></div>
                        <div class="field pin">street_numb: <?php echo $street_numb; ?></div>
                        <div class="field pin">zip: <?php echo $zip; ?></div>

                    <?php
                    // Insert Record
                    $insert = $wpdb->insert( $tablename, array(
                        'state_abbr' => $state_abbr,
                        'tax_year' => $tax_year,
                        'due_year' => $due_year,
                        'pin' => $pin,
                        'situs' => $situs,
                        'legal_party_1' => $legal_party_1,
                        'summary_amount' => $summary_amount,
                        'street_numb' => $street_numb,
                        'zip' => $zip
                    ));

                    if ( $insert ) {
                        $totalInserted ++;
                        echo '<div class="insert_result pos" style="color: green;">Inserted</div>';
                    } else {
                        echo '<div class="insert_result neg" style="color: red;">Not Inserted</div>';
                    }
                    $line_count++;
                    
                    echo '</div>';
                }
                echo '<h3 style="color: green;">Total record Inserted : ' . $totalInserted . '</h3>';
            } else {
                echo '<h3 style="color: red;">Invalid File</h3>';
            }

        } else { 
        ?>

            <h2>CSV Import</h2>
            <!-- Form -->
            <form method='post' action='<?php $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
                <input type="file" name="import_file" >
                <input type="submit" name="demisimport" value="Import">
            </form>
            
        <?php
        }
    }

    /**
     * Admin Head
     * 
     */
    public function admin_head() { 
        ?>
        
        <style>
            .demis_import { padding: 30px 0; }
            .demis_import .demis_import_line { margin-bottom: 10px; }
        </style>
        
        <?php
    }
    
    /**
     * Fronend Head
     * 
     */
    function wp_head() {
        ?>
        
        <style>
            .no_subscriptions{ display: none; }
            #map { height: 500px; width: 100%; }            
            #demis_search_content { position: relative; }
            #demis_search_content.loading:after { content: ""; position: absolute; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.7); background-image: url(<?php echo $this->get_plugin_url(); ?>/images/loading.gif); background-repeat: no-repeat; background-position: center; }
            #demis_entry_table tbody tr { cursor: pointer; }
            #demis_entry_table tbody tr:hover td { background-color: #333b48; color: #fff; }
            #demis_entry_table td { vertical-align: middle; }
            .alert a { text-decoration: underline; font-weight: 700; }
        </style>

        <?php
    }

    /**
     * Add meta with property data to the order page 
     * 
     * @param  $order
     * 
     */
    public function add_property_data_to_order( $order ) {
        echo '<p><strong>' . __( 'Property Id' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_property_id', true ) . '</p>';
        echo '<p><strong>' . __( 'State' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_state_abbr', true ) . '</p>';
        echo '<p><strong>' . __( 'Tax Year' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_tax_year', true ) . '</p>';
        echo '<p><strong>' . __( 'Due Year' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_due_year', true ) . '</p>';
        echo '<p><strong>' . __( 'Pin' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_pin', true ) . '</p>';
        echo '<p><strong>' . __( 'Address' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_situs', true ) . '</p>';
        echo '<p><strong>' . __( 'Legal Party 1' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_legal_party_1', true ) . '</p>';
        echo '<p><strong>' . __( 'Zip' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_zip', true ) . '</p>';
        echo '<p><strong>' . __( 'Street Number' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_street_numb', true ) . '</p>';
        echo '<p><strong>' . __( 'Tax Amount' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_summary_amount', true ) . '</p>';
        echo '<p><strong>' . __( 'Home Insurance' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_home_insurance', true ) . '</p>';
    }

    /**
     * Customize the account menu
     * 
     * @param  $items - account menu items array
     * 
     */
    public function custom_account_menu_items( $items ) {
        unset( $items['downloads'] );
        unset( $items['orders'] );
        return $items;
    }

    /**
     * Redirect to home page or checkout page after login  
     * 
     */    
    public function redirect_after_login() {
        global $woocommerce;

        $ordered = WC()->session->get( 'subscription_amount' );

        if ( $ordered ) {
            wp_safe_redirect( wc_get_checkout_url() );
        } else {
            wp_safe_redirect( home_url() );
        }
        
        exit();
    }


}

Demission_Plugin::get_instance();