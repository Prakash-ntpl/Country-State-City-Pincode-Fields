<?php
/**
 * Plugin Name: Country State City Pincode Fields
 * Description: Adds custom country, state, and city fields to Contact Form 7.
 * Version: 1.2
 * Author: Prakash
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
// Enqueue scripts and styles
function cscp_enqueue_scripts() {
    wp_enqueue_style('cscp-style', plugins_url('assets/css/cscp_style.css', __FILE__));
    wp_enqueue_script('cscp-script', plugins_url('assets/js/cscp_script.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('cscp-script2', plugins_url('assets/js/cscp_billing_shipping.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', array('jquery'), null, true);

    // Localize script to use AJAX
    wp_localize_script('cscp-script', 'cscp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
    wp_localize_script('cscp-script2', 'cscp_ajax2', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}

add_action('wp_enqueue_scripts', 'cscp_enqueue_scripts');

include_once plugin_dir_path(__FILE__) . 'checkout-country-state-city-fields.php';

// Load AJAX functions
require_once plugin_dir_path(__FILE__) . 'includes/cscp_ajax.php';

// Add hooks to register the custom form tags for country, state, and city
add_action( 'wpcf7_init', 'cscp_add_form_tag_country' );
add_action( 'wpcf7_init', 'cscp_add_form_tag_state' );
add_action( 'wpcf7_init', 'cscp_add_form_tag_city' );




// Register custom form tags
function cscp_add_form_tag_country() {
    wpcf7_add_form_tag( array( 'country_auto', 'country_auto*' ), 'cscp_country_form_tag_handler', array( 'name-attr' => true ) );
}

function cscp_add_form_tag_state() {
    wpcf7_add_form_tag( array( 'state_auto', 'state_auto*' ), 'cscp_state_form_tag_handler', array( 'name-attr' => true ) );
}

function cscp_add_form_tag_city() {
    wpcf7_add_form_tag( array( 'city_auto', 'city_auto*' ), 'cscp_city_form_tag_handler', array( 'name-attr' => true ) );
}

// Register the admin tag generator UI
add_action( 'wpcf7_admin_init', 'country_auto_admin' );
add_action( 'wpcf7_admin_init', 'state_auto_admin' );
add_action( 'wpcf7_admin_init', 'city_auto_admin' );

function country_auto_admin() {
    $tag_generator = WPCF7_TagGenerator::get_instance();
    $tag_generator->add( 'country_auto', __( 'Country Dropdown', 'cf7-country-auto' ), 'country_auto_ui' );
}

function state_auto_admin() {
    $tag_generator = WPCF7_TagGenerator::get_instance();
    $tag_generator->add( 'state_auto', __( 'State Dropdown', 'cf7-state-auto' ), 'state_auto_ui' );
}

function city_auto_admin() {
    $tag_generator = WPCF7_TagGenerator::get_instance();
    $tag_generator->add( 'city_auto', __( 'City Dropdown', 'cf7-city-auto' ), 'city_auto_ui' );
}

// UI for the tag generator in admin panel
function country_auto_ui( $contact_form, $args = '' ) {
    generate_ui( 'country_auto', $contact_form, $args );
}

function state_auto_ui( $contact_form, $args = '' ) {
    generate_ui( 'state_auto', $contact_form, $args );
}

function city_auto_ui( $contact_form, $args = '' ) {
    generate_ui( 'city_auto', $contact_form, $args );
}

function generate_ui( $type, $contact_form, $args ) {
    $args = wp_parse_args( $args, array() );
    ?>
    <div class="control-box">
        <fieldset>
            <legend><?php echo esc_html( "Generate a form-tag for the $type dropdown.", 'cf7-location-auto' ); ?></legend>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>">Name</label></th>
                    <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>">ID attribute</label></th>
                    <td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>">Class attribute</label></th>
                    <td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <div class="insert-box">
        <input type="text" name="<?php echo esc_attr( $type ); ?>" class="tag code" onfocus="this.select()" />
        <div class="submitbox">
            <input type="button" class="button button-primary insert-tag" value="Insert Tag" />
        </div>
    </div>
    <?php
}

// Custom form tag handlers for country, state, and city with default IDs
function cscp_country_form_tag_handler( $tag ) {
    return generate_select_dropdown( 'country', $tag, 'my_country_field' );
}

function cscp_state_form_tag_handler( $tag ) {
    return generate_select_dropdown( 'state', $tag, 'my_state_field' );
}

function cscp_city_form_tag_handler( $tag ) {
    return generate_select_dropdown( 'city', $tag, 'my_city_field' );
}

// Modify the generate_select_dropdown function to accept default ID
function generate_select_dropdown( $type, $tag, $default_id = '' ) {
    global $wpdb;
    $table = "wppx_address_informations";
    

    // Fetch data based on type (country, state, or city)
    if ($type == 'country') {
        $list = $wpdb->get_results("SELECT DISTINCT country_code, country FROM $table WHERE country_code = 'CA' ORDER BY country ASC");
    } elseif ($type == 'state') {
        $list = $wpdb->get_results("SELECT DISTINCT state_code, state FROM $table WHERE country_code = 'CA' ORDER BY state ASC");
    } else {
        $list = $wpdb->get_results("SELECT DISTINCT city FROM $table WHERE country_code = 'CA' ORDER BY city ASC");
    }

    // Set attributes for the select dropdown
    $atts = array(
        'class' => $tag->get_class_option(),
        'id'    => $tag->get_id_option() ?: $default_id,  // Assign default ID if no ID is provided
        'name'  => $tag->name,
    );

    $atts = wpcf7_format_atts( $atts );
    $html = "<select $atts class='wpcf7-form-control wpcf7-validates-as-required' aria-required='true' aria-invalid='false'>";
    if ($type == 'country') {
        $html .= "<option value='0'>Choose a $type</option>";
    }else{
        $html .= "<option value='0' selected>Choose a $type</option>";
    }

    // Loop through list and create option elements based on type
    foreach ( $list as $row ) {
        if ($type == 'country') {
            $html .= "<option value='{$row->country_code}' data-id='{$row->country_code}' selected>{$row->country}</option>";
        } elseif ($type == 'state') {
            $html .= "<option value='{$row->state_code}' data-id='{$row->state_code}'>{$row->state}</option>";
        } else {
            $html .= "<option value='{$row->city}'>{$row->city}</option>";
        }
    }
    if ($type == 'city') {
        $html .= "<option value='yes'>Others</option>";
    }

    $html .= '</select>';
    $html .= '<span class="errorMsg-'. $type .'"></span>';
    return $html;
}

// Add custom validation filter for country, state, city
// Custom validation filter for country field
add_filter('wpcf7_validate_country_auto*', 'cscp_country_validation_filter', 10, 2);
add_filter('wpcf7_validate_state_auto*', 'cscp_state_validation_filter', 10, 2);
add_filter('wpcf7_validate_city_auto*', 'cscp_city_validation_filter', 10, 2);
add_filter('wpcf7_validate_country_auto', 'cscp_country_validation_filter', 10, 2);
add_filter('wpcf7_validate_state_auto', 'cscp_state_validation_filter', 10, 2);
add_filter('wpcf7_validate_city_auto', 'cscp_city_validation_filter', 10, 2);

function cscp_country_validation_filter($result, $tag) {
    $name = $tag->name;
    
    // Check if the country field is selected as "0"
    if (isset($_POST[$name]) && $_POST[$name] == '0') {
        // Invalidate the specific field with the custom message
        $result->invalidate($tag, "Please choose a valid country.");
    }

    return $result;
}

function cscp_state_validation_filter($result, $tag) {
    $name = $tag->name;

    // Check if the state field is selected as "0"
    if (isset($_POST[$name]) && $_POST[$name] == '0') {
        // Invalidate the specific field with the custom message
        $result->invalidate($tag, "Please choose a valid state.");
    }

    return $result;
}

function cscp_city_validation_filter($result, $tag) {
    $name = $tag->name;

    // Check if the city field is selected as "0"
    if (isset($_POST[$name]) && $_POST[$name] == '0') {
        // Invalidate the specific field with the custom message
        $result->invalidate($tag, "Please choose a valid city.");
    }

    return $result;
}

function wpm_create_user_form_registration( $cfdata ) {
    global $wpdb;
    $table_ai = 'wppx_address_informations';
    $formdata = [];

    // Retrieve the form data from Contact Form 7 submission
    if ( class_exists( 'WPCF7_Submission' ) ) {
        $submission = WPCF7_Submission::get_instance();
        if ( $submission ) {
            $formdata = $submission->get_posted_data();
        }
    } else {
        return $cfdata;
    }

    // Proceed if it's the correct form
    if ( in_array( $cfdata->title(), ['contact_form2', 'contact_form1_FR'] ) ) {
        $username = $formdata['contactFname'];
        $email    = $formdata['contactEmail'];
        $password = $formdata['contactPwd'];
        $fname    = $formdata['contactFname'];
        $lname    = $formdata['contactLname'];
        $country  = $formdata['my_country_field'];
        $state    = $formdata['my_state_field'];
        $city     = $formdata['my_city_field'];
        $postcode = $formdata['my_zip_field'];
        $phone    = trim(str_replace("-", "", $formdata['contactPhone']));
        $address_1= $formdata['contactAddress'];
        $address_2= $formdata['contactApartment'] ?? null;

        // Check if address exists in the database
        $checkIfExists = $wpdb->get_var( $wpdb->prepare("SELECT * FROM $table_ai WHERE zip=%s AND country_code=%s", $postcode, $country) );

        if ( is_null( $checkIfExists ) ) {
            $code = $wpdb->get_results( $wpdb->prepare("SELECT DISTINCT country, country_code, state, state_code FROM $table_ai WHERE state_code=%s", $state) );

            if ( $code ) {
                $wpdb->insert($table_ai, [
                    "country"      => $code[0]->country,
                    "country_code" => $code[0]->country_code,
                    "state"        => $code[0]->state,
                    "state_code"   => $code[0]->state_code,
                    "city"         => $city,
                    "zip"          => $postcode,
                    "street"       => "",
                    "FM_ID"        => "",
                    "city_flag"    => 'Y'
                ]);
            }
        }

        // Check if the user already exists
        if ( ! email_exists( $email ) ) {
            // Find a unique username
            $username_tocheck = $username;
            $i = 1;
            while ( username_exists( $username_tocheck ) ) {
                $username_tocheck = $username . $i++;
            }
            $username = $username_tocheck;

            // Create new user
            $userdata = [
                'user_login'   => $username,
                'user_pass'    => $password,
                'user_email'   => $email,
                'nickname'     => $fname . ' ' . $lname,
                'display_name' => $fname . ' ' . $lname,
                'first_name'   => $fname,
                'last_name'    => $lname,
                'role'         => 'customer'
            ];

            $user_id = wp_insert_user( $userdata );

            if ( ! is_wp_error( $user_id ) ) {
                wp_set_current_user( $user_id );
                wp_set_auth_cookie( $user_id );

                do_action( 'woocommerce_created_customer', $user_id );

                // Update user meta for billing and shipping
                $user_meta = [
                    'billing_first_name' => $fname,
                    'billing_last_name'  => $lname,
                    'billing_country'    => $country,
                    'billing_email'      => $email,
                    'billing_address_1'  => $address_1,
                    'billing_city'       => $city,
                    'billing_state'      => $state,
                    'billing_postcode'   => $postcode,
                    'billing_phone'      => $phone,
                    'shipping_first_name' => $fname,
                    'shipping_last_name'  => $lname,
                    'shipping_country'    => $country,
                    'shipping_email'      => $email,
                    'shipping_address_1'  => $address_1,
                    'shipping_city'       => $city,
                    'shipping_state'      => $state,
                    'shipping_postcode'   => $postcode,
                    'shipping_phone'      => $phone
                ];

                foreach( $user_meta as $key => $value ) {
                    update_user_meta( $user_id, $key, $value );
                }
            }
        }
    }

    return $cfdata;
}

add_action( 'wpcf7_mail_sent', 'wpm_create_user_form_registration', 1 );


add_filter('wpcf7_form_elements', 'imp_wpcf7_form_elements');

function imp_wpcf7_form_elements($content) {
    // Add autocomplete="off" for email field
    $content = preg_replace('/(<input[^>]*name="contactEmail"[^>]*)(>)/', '$1 autocomplete="off"$2', $content);

    // Add autocomplete="new-password" for password field
    $content = preg_replace('/(<input[^>]*name="contactPwd"[^>]*)(>)/', '$1 autocomplete="new-password"$2', $content);

    return $content;
}

add_action('wp_footer', 'cscp_wp_footer');
function cscp_wp_footer() {
          // Get language parameter
          $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : ''; ?>

          <!-- Spinner code -->
           <div class="loader-cover">
             <div class="loader-img">
               <p></p>
                 <p class="load-txt">Loading ...</p>
               </div>
                 </div>
            <?php
          // Only load on Contact Form 7 pages
          if (is_page() && has_shortcode(get_post()->post_content, 'contact-form-7')) {
              // Modal HTML content
              ?>
              <div id="errorModal" class="modal">
                  <div class="modal-dialog modal-dialog-centered">
                      <input type="hidden" value="<?php echo $lang; ?>" id="cscp-Lang" name="cscp-Lang">
                      <div class="modal-content">
                          <div class="modal-icon" id="modalIcon">✔️</div>
                          <div class="modalMessage" id="modalMessage">
                              <p>Thank you for your message!</p>
                          </div>
                          <div style="text-align:center"> 
                              <button class="modal-button" id="okButton">OK</button>
                          </div>
                      </div>
                  </div>
              </div>
      
              <!-- Country/State Modal -->
              <div id="myModal" class="modal">
                  <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                          <span class="ctn-cl">&times;</span>
                          <label for="editcountry"><?php echo ($lang == 'fr') ? 'Sélectionnez votre pays' : 'Select Country'; ?> <span class="mant-txt"> * </span></label>
                          <select id="editcountry" name="editcountry" required>
                              <option value="" disabled><?php echo ($lang == 'fr') ? 'Sélectionnez votre pays' : 'Select Your Country'; ?></option>
                              <option value="CA" selected>Canada</option>
                          </select>
                          <span class="editcountrymsg"></span>
      
                          <label for="editstate"><?php echo ($lang == 'fr') ? 'Sélectionnez une province/un territoire' : 'Select Province/Territory'; ?> <span class="mant-txt"> * </span></label>
                          <select id="editstate" name="editstate" required>
                              <option value="" disabled><?php echo ($lang == 'fr') ? 'Choisissez une province/un territoire' : 'Select Your Province'; ?></option>
                              <?php
                              global $wpdb;
                              $table = "wppx_address_informations";
                              $states = $wpdb->get_results("SELECT DISTINCT state_code, state FROM $table WHERE country_code = 'CA' ORDER BY state ASC");
                              foreach ($states as $row) {
                                  echo "<option value='{$row->state_code}' data-id='{$row->state_code}'>{$row->state}</option>";
                              }
                              ?>
                          </select>
                          <span class="editstatemsg"></span>
      
                          <label for="city"><?php echo ($lang == 'fr') ? 'Entrez votre ville' : 'Enter your city'; ?> <span class="mant-txt"> * </span></label>
                          <input type="text" id="editcity" name="editcity" required>
                          <span class="editcitymsg"></span>
      
                          <div style="text-align:center">
                              <button type="button" class="sub-btn" id="cityeditbtn">Submit</button>
                          </div>
                      </div>
                  </div>
              </div>
      
              <script>
              // Modal handling for Contact Form 7 events
              document.addEventListener('wpcf7mailsent', function(event) {
                  let message = "<?php echo ($lang == 'fr') ? 'Merci pour votre message. Il a été envoyé.' : 'Thank you for your message. It has been sent.'; ?>";
                  let redirectUrl = "<?php echo esc_url(home_url('/contact-us/')); ?>";
      
                  <?php if ($lang == 'fr') : ?>
                      redirectUrl = "<?php echo esc_url(home_url('/contact-us-fr/?lang=fr')); ?>";
                  <?php endif; ?>
      
                  showModal(message, 'success', redirectUrl);
              }, false);
      
              document.addEventListener('wpcf7invalid', function(event) {
                  let errorMessage = "<?php echo ($lang == 'fr') ? 'Un ou plusieurs champs ont une erreur. Veuillez vérifier et réessayer.' : 'One or more fields have an error. Please check and try again.'; ?>";
                  showModal(errorMessage, 'error');
              }, false);
      
              // Function to display the modal
              function showModal(message, type, redirectUrl = null) {
                  var modalMsg = document.getElementById('modalMessage');
                  modalMsg.innerText = message;
      
                  var modalIcon = document.getElementById('modalIcon');
                  if (type === 'success') {
                      modalIcon.innerHTML = '✔️';
                      modalIcon.classList.add('success-icon');
                      modalIcon.classList.remove('error-icon');
                      modalMsg.classList.add('bd-success');
                      modalMsg.classList.remove('bd-warning');
                  } else {
                      modalIcon.innerHTML = '⚠️';
                      modalIcon.classList.add('error-icon');
                      modalIcon.classList.remove('success-icon');
                      modalMsg.classList.add('bd-warning');
                      modalMsg.classList.remove('bd-success');
                  }
      
                  var modal = document.getElementById('errorModal');
                  modal.style.display = 'flex';
      
                  document.getElementById('okButton').onclick = function() {
                      modal.style.display = 'none';
                      if (redirectUrl) {
                          window.location.href = redirectUrl;
                      }
                  };
              }
              </script>
              <?php
          }
      }
      



