<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_ai = $wpdb->prefix . 'address_informations';

// Handle state list action
if (isset($_POST['action']) && $_POST['action'] == 'statelist') {
    $cid = isset($_POST['data_to_pass']) ? sanitize_text_field($_POST['data_to_pass']) : '';
    $cid = isset($_POST['editcountry']) ? sanitize_text_field($_POST['editcountry']) : $cid;

    if ($cid) {
        $states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE country_code = %s ORDER BY state ASC", $cid));
        $cities = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE country_code = %s ORDER BY city ASC", $cid));
        echo json_encode(['states' => $states, 'cities' => $cities]);
    }

    wp_die();
}

// Handle city list action based on state
if (isset($_POST['action']) && $_POST['action'] == 'citylist') {
    $state = isset($_POST['state_field']) ? sanitize_text_field($_POST['state_field']) : '';

    if ($state) {
        $cities = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE state_code = %s ORDER BY city ASC", $state));
        echo json_encode(['city' => $cities]);
    }

    wp_die();
}

// Handle zip list action
if (isset($_POST['action']) && $_POST['action'] == 'ziplist') {
    $city = isset($_POST['data_to_pass']) ? stripslashes($_POST['data_to_pass']) : '';
    $country = isset($_POST['data_to_country']) ? sanitize_text_field($_POST['data_to_country']) : '';

    if ($city && $country) {
        $states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE country_code = %s ORDER BY state ASC", $country));
        $selected = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE city = %s ORDER BY state ASC", $city));

        echo json_encode(['states' => $states, 'selected' => $selected]);
    }

    wp_die();
}

// Handle city edit action
if (isset($_POST['action']) && in_array($_POST['action'], ['editcity', 'editcitynew', 'contacteditcity'])) {
    $country_code = isset($_POST['country_data']) ? sanitize_text_field($_POST['country_data']) : 'CA';
    $state_code = isset($_POST['state_data']) ? sanitize_text_field($_POST['state_data']) : '';
    $city = isset($_POST['city_data']) ? stripslashes($_POST['city_data']) : '';

    if ($_POST['action'] == 'editcity') {
        $checkIfExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_ai WHERE city = %s AND country_code = %s", $city, $country_code));

        if (!$checkIfExists) {
            $code = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT country, country_code, state, state_code FROM $table_ai WHERE state_code = %s", $state_code));
            
            if ($code) {
                $state_code = $code[0]->state_code;
                $state = $code[0]->state;
                $country = $code[0]->country;
                $country_code = $code[0]->country_code;

                $wpdb->insert($table_ai, [
                    "country" => $country,
                    "country_code" => $country_code,
                    "state" => $state,
                    "state_code" => $state_code,
                    "city" => $city,
                    "zip" => "",
                    "street" => "",
                    "FM_ID" => "",
                    "city_flag" => 'Y'
                ]);

                $lastid = $wpdb->insert_id;
                $code_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_ai WHERE ID = %d", $lastid));
                $states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE country_code = %s ORDER BY state ASC", $country_code));
                $cities = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE state_code = %s ORDER BY city ASC", $state_code));

                echo json_encode(['status' => 'No', 'select' => $code_data, 'countries' => $country, 'states' => $states, 'cities' => $cities]);
            } else {
                echo json_encode(['failed' => 'yes']);
            }
        } else {
            echo json_encode(['status' => 'Yes']);
        }
    } else {
        // Handle editcitynew and contacteditcity
        $countries = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT country,country_code FROM $table_ai WHERE country_code = %s ORDER BY country ASC", $country_code));
        $states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE country_code = %s ORDER BY state ASC", $country_code));
        $cities = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE state_code = %s ORDER BY city ASC", $state_code));

        echo json_encode(['status' => 'No', 'countries' => $countries, 'states' => $states, 'cities' => $cities]);
    }

    wp_die();
}

// Handle postal code validation
if (isset($_POST['action']) && $_POST['action'] == 'postalcodevalidation') {
    $country_code = isset($_POST['country_data']) ? sanitize_text_field($_POST['country_data']) : '';
    $postal_code = isset($_POST['postalcode']) ? sanitize_text_field($_POST['postalcode']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';

    if ($city && $postal_code && $country_code) {
        $cityExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_ai WHERE city = %s AND country_code = %s", $city, $country_code));
        $postalCodeExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_ai WHERE zip = %s AND country_code = %s", $postal_code, $country_code));

        if (!$cityExists && $postalCodeExists) {
            echo json_encode(['status' => 'N']);
        } else {
            echo json_encode(['status' => 'Y']);
        }
    }

    wp_die();
}

// Checkout state list
if (isset($_POST['action']) && $_POST['action'] == 'checkoutstatelist') {
    $country_code = isset($_POST['data_to_pass']) ? sanitize_text_field($_POST['data_to_pass']) : '';

    if ($country_code) {
        $states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE country_code = %s ORDER BY state ASC", $country_code));
        $cities = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE country_code = %s ORDER BY city ASC", $country_code));

        echo json_encode(['states' => $states, 'cities' => $cities]);
    }

    wp_die();
}

// Checkout city list based on state
if (isset($_POST['action']) && $_POST['action'] == 'checkoutcitylist') {
    $state_code = isset($_POST['data_to_pass']) ? sanitize_text_field($_POST['data_to_pass']) : '';

    if ($state_code) {
        $cities = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE state_code = %s ORDER BY city ASC", $state_code));
        echo json_encode(['city' => $cities]);
    }

    wp_die();
}

// Checkout edit city based on country
if (isset($_POST['action']) && $_POST['action'] == "checkouteditcity") {
  // Fetch country, state, and city data based on conditions
  $coid = isset($_POST["country_data"]) ? $_POST["country_data"] : 'CA';

  // Prepare SQL queries based on the country code
  $country_query = $wpdb->prepare("SELECT DISTINCT country_code, country FROM $table_ai ORDER BY country ASC");
  $states_query = $wpdb->prepare("SELECT DISTINCT state_code, state FROM $table_ai WHERE country_code='CA' ORDER BY state ASC");
  $city_query = $wpdb->prepare("SELECT DISTINCT city FROM $table_ai WHERE country_code=%s ORDER BY city ASC", $coid);

  // Fetch data from the database
  $country = $wpdb->get_results($country_query);
  $states = $wpdb->get_results($states_query);
  $city = $wpdb->get_results($city_query);

  // Return JSON response
  echo json_encode([
      "userId" => 0,
      "countries" => $country,
      "states" => $states,
      "city" => $city
  ]);

  wp_die();
}


// Zipcode List AJAX handler
if (isset($_POST['action']) && $_POST['action'] == "zipcodelist") {

    // Verify nonce for security
    // check_ajax_referer('tc_csca_ajax_nonce', 'nonce_ajax');

    // Initialize variables
    $cid = isset($_POST["data_to_pass"]) ? sanitize_text_field(stripslashes($_POST["data_to_pass"])) : '';
    $conid = isset($_POST["data_to_country"]) ? sanitize_text_field($_POST["data_to_country"]) : '';

    // Ensure values are provided
    if ($cid && $conid) {
        // Retrieve states and selected states
        $states = $wpdb->get_results($wpdb->prepare(
            'SELECT DISTINCT state_code, state FROM wppx_address_informations WHERE country_code = %s ORDER BY state ASC', 
            $conid
        ));

        $selected = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT state_code, state FROM wppx_address_informations WHERE city = %s ORDER BY state ASC", 
            esc_sql($cid)
        ));

        echo json_encode(['states' => $states, 'selected' => $selected]);
    } else {
        echo json_encode(['error' => 'Invalid data']);
    }

    wp_die();
}





function billinglogin(){

 // AJAX Login handler{
    // Verify nonce for security
    check_ajax_referer('ajax-login-nonce', 'security');

    // Sanitize input and authenticate user
    $info = [
        'user_login' => sanitize_text_field($_POST['username']),
        'user_password' => sanitize_text_field($_POST['password']),
        'remember' => true
    ];

    $user_signon = wp_signon($info, false);
    if (is_wp_error($user_signon)) {
        echo json_encode(['loggedin' => false, 'message' => __('Wrong username or password.')]);
    } else {
        echo json_encode(['loggedin' => true, 'message' => __('Login successful, redirecting...')]);
    }

    wp_die();
}
add_action('wp_ajax_nopriv_billinglogin', 'billinglogin');
add_action('wp_ajax_billinglogin', 'billinglogin');



// Check if email exists during checkout
function checkout_check_mail()
{

  global $wpdb;

  if (!empty($_POST['email'])) {
      $email = sanitize_email($_POST['email']);  // Use sanitize_email for email input sanitization

      if (email_exists($email)) {
          echo json_encode(['Status' => "Yes"]);
      } else {
          echo json_encode(['Status' => "No"]);
      }
  } else {
      echo json_encode(['Status' => "No Email Provided"]);  // Optional: Handle case where email is not provided
  }

  wp_die();  // Properly end execution in an AJAX request
}
add_action('wp_ajax_nopriv_checkout_check_mail', 'checkout_check_mail');
add_action('wp_ajax_checkout_check_mail', 'checkout_check_mail');

// Contact Form email check
function contact_check_email() {
    if (isset($_POST["data_to_pass"])) {
        $email = sanitize_email($_POST['data_to_pass']);
        $exists = email_exists($email);

        echo json_encode(['Status' => $exists ? "Yes" : "No"]);
    }

    wp_die();
}
add_action('wp_ajax_nopriv_contact_check_email', 'contact_check_email');
add_action('wp_ajax_contact_check_email', 'contact_check_email');
