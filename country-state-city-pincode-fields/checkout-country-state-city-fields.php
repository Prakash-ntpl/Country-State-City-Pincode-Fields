<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_filter( 'woocommerce_default_address_fields' , 'custom_override_default_address_fields' );
function custom_override_default_address_fields( $fields ) {
    $fields['first_name']['label'] = 'First name';
    $fields['last_name']['label'] = 'Last name';
    $fields['company']['label'] = 'Company name';
    $fields['address_1']['label'] = 'Street address';
    $fields['address_2']['label'] = 'Apartment, unit, etc.';
    $fields['city']['label'] = 'City';
    $fields['country']['label'] = 'Country';
    $fields['state']['label'] = 'Province';
    $fields['postcode']['label'] = 'Postal code';

    $name="";
    if (isset($_GET['lang'])) {
    $name = $_GET['lang'];
    }
    if ($name == "fr") {
        $fields['first_name']['label'] = 'Prénom';
        $fields['last_name']['label'] = 'Nom';
        $fields['company']['label'] = 'Nom de l’entreprise';
        $fields['address_1']['label'] = 'Numéro et nom de rue';
        $fields['address_2']['label'] = 'Apartment, unit, etc.';
        $fields['city']['label'] = 'Ville';
        $fields['country']['label'] = 'Pays';
        $fields['state']['label'] = 'Province';
        $fields['postcode']['label'] = 'code postal';
    }

    return $fields;
}

add_filter( 'woocommerce_checkout_fields', 'custom_checkout_fields' );

function custom_checkout_fields( $fields ) {

 $fields['billing']['billing_city']['placeholder'] = 'Select a City';
 $fields['billing']['billing_city']['label'] = 'City';
 $fields['billing']['billing_email']['class'] = 'test';
 $fields['billing']['billing_state']['placeholder'] = 'Select a Province';
 $fields['billing']['billing_state']['label'] = 'Province';
 $fields['billing']['billing_postcode']['placeholder'] = 'Enter a postal code';
 $fields['billing']['billing_postcode']['label'] = 'Postal code';
 return $fields;
}

add_filter("woocommerce_checkout_fields", "custom_override_checkout_fields", 1);
function custom_override_checkout_fields($fields) {
    $fields['billing']['billing_first_name']['priority'] = 2;
    $fields['billing']['billing_last_name']['priority'] = 3;
    $fields['billing']['billing_full_name']['priority'] = 4;
    $fields['billing']['billing_company']['priority'] =5;
    $fields['billing']['billing_country']['priority'] = 6;
    $fields['billing']['billing_state']['priority'] = 7;
    $fields['billing']['billing_address_1']['priority'] = 8;
    $fields['billing']['billing_address_2']['priority'] = 9;
    $fields['billing']['billing_city']['priority'] = 10;
    $fields['billing']['billing_postcode']['priority'] = 11;
    $fields['billing']['billing_phone']['priority'] = 12;
    $fields['billing']['billing_email']['priority'] = 1;

    return $fields;
}


////////Default billing and shipping address////

add_filter( 'woocommerce_billing_fields' , 'customize_country_fields' );

function customize_country_fields( $adresses_fields ) {
 global $wpdb;
 $master='wppx_address_informations';
 $user_id=get_current_user_id();
 $data=get_user_meta($user_id);

 if(is_user_logged_in()){


   if(empty($data['billing_country']) && empty($data['billing_state']) ){
     
    $data = $wpdb->get_results("SELECT DISTINCT country_code,country FROM $master  where country_code='CA' ORDER BY country",ARRAY_A);
    $countryArray = array();
    $countryArray[""] = "Select Country";
    foreach ($data as $value) {
    
    $countryArray[$value['country_code']] = $value['country'];
    }
    $adresses_fields['billing_country']['type'] = 'select';
    $adresses_fields['billing_country']['default'] = 'CA';
    $adresses_fields['billing_country']['options'] = $countryArray;


   }else{
          
    
    $country=$data['billing_country'][0];
    $state=$data['billing_state'][0];
    $data = $wpdb->get_results("SELECT DISTINCT country_code,country FROM $master where country_code='CA' ORDER BY country",ARRAY_A);
    $countryArray = array();
    $countryArray[""] = "Select Country";
    foreach ($data as $value) {
    
    $countryArray[$value['country_code']] = $value['country'];
    }
    $adresses_fields['billing_country']['type'] = 'select';
    $adresses_fields['billing_country']['default'] = $country;
    $adresses_fields['billing_country']['options'] = $countryArray;

   }
    

 }else{

 $data = $wpdb->get_results("SELECT DISTINCT country_code,country FROM $master where country_code='CA' ORDER BY country",ARRAY_A);
 $countryArray = array();
    $countryArray[""] = "Select Country";
    foreach ($data as $value) {
    
    $countryArray[$value['country_code']] = $value['country'];
    }
    $adresses_fields['billing_country']['type'] = 'select';
    $adresses_fields['billing_country']['options'] = $countryArray;
    $adresses_fields['billing_country']['default'] = 'CA';



 }
    



    return $adresses_fields;
}

add_filter( 'woocommerce_billing_fields' , 'customize_state_fields' );

function customize_state_fields( $adresses_fields ) {
    global $wpdb;
    $master='wppx_address_informations';

    $user_id=get_current_user_id();
    $data=get_user_meta($user_id);
    if(is_user_logged_in())
      { 
      if(empty($data['billing_country']) && empty($data['billing_state'])){

        $data = $wpdb->get_results("SELECT DISTINCT state_code,state FROM $master where country_code='CA' ORDER BY state",ARRAY_A);
        $stateArray = array();
        $stateArray[""] = "Select Province/Territory";
        foreach ($data as $value) {
    
    $stateArray[$value['state_code']] = $value['state'];
    }
    $adresses_fields['billing_state']['type'] = 'select';
    $adresses_fields['billing_state']['options'] = $states['CA'] = $stateArray;

      }else{

    $user_id=get_current_user_id();
    $data=get_user_meta($user_id);
    $country=$data['billing_country'][0];
    $state=$data['billing_state'][0];
    $datas = $wpdb->get_results("SELECT DISTINCT state_code,state FROM $master where country_code='$country' ORDER BY state",ARRAY_A);
    $stateArray = array();
    $stateArray[""] = "Select Province/Territory";
    foreach ($datas as $value) {
    
    $stateArray[$value['state_code']] = $value['state'];
    }
    $adresses_fields['billing_state']['type'] = 'select';
    $adresses_fields['billing_state']['default'] = $state;
    $adresses_fields['billing_state']['options'] = $states[$country] = $stateArray;

      }

    

  }else{
    $data = $wpdb->get_results("SELECT DISTINCT state_code,state FROM $master where country_code='CA' ORDER BY state",ARRAY_A);
    $stateArray = array();
    $stateArray[""] = "Select Province/Territory";
    foreach ($data as $value) {
    
    $stateArray[$value['state_code']] = $value['state'];
    }
    $adresses_fields['billing_state']['type'] = 'select';
    $adresses_fields['billing_state']['options'] = $states['CA'] = $stateArray;

   }


    return $adresses_fields;
}

add_filter('woocommerce_billing_fields', 'customize_city_fields');

function customize_city_fields($adresses_fields) {
    global $wpdb;
    $master = 'wppx_address_informations';
    $user_id = get_current_user_id();
    $data = get_user_meta($user_id);

    // For logged-in users
    if (is_user_logged_in()) {
        $billing_state = isset($data['billing_state'][0]) ? $data['billing_state'][0] : '';
        $billing_city = isset($data['billing_city'][0]) ? $data['billing_city'][0] : '';

        // If no billing state and city are set, retrieve cities for country 'CA'
        if (empty($billing_state) && empty($billing_city)) {
            $data = $wpdb->get_results("SELECT DISTINCT city FROM $master WHERE country_code = 'CA'", ARRAY_A);
        } else {
            // If billing state is set, retrieve cities based on the state
            $data = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT city FROM $master WHERE state_code = %s", 
                $billing_state
            ), ARRAY_A);
        }

        $cityArray = ["Select City" => "Select City"];
        foreach ($data as $value) {
            $cityArray[$value['city']] = $value['city'];
        }

        // Add an option for "Others"
        $cityArray['yes'] = 'Others';

        // If the user has already added a new city (not in the database yet), add it to the dropdown
        if (!empty($billing_city) && !isset($cityArray[$billing_city])) {
            $cityArray[$billing_city] = $billing_city;
        }

        // Set the city field as a dropdown and set the default value
        $adresses_fields['billing_city']['type'] = 'select';
        $adresses_fields['billing_city']['options'] = $cityArray;
        if (!empty($billing_city)) {
            $adresses_fields['billing_city']['default'] = $billing_city;
        }

    } else {
        // For guests
        $data = $wpdb->get_results("SELECT DISTINCT city FROM $master WHERE country_code = 'CA'", ARRAY_A);

        $cityArray = ["Select City" => "Select City"];
        foreach ($data as $value) {
            $cityArray[$value['city']] = $value['city'];
        }

        // Add an option for "Others"
        $cityArray['yes'] = 'Others';

        // Set the city field as a dropdown for guests
        $adresses_fields['billing_city']['type'] = 'select';
        $adresses_fields['billing_city']['options'] = $cityArray;
    }

    return $adresses_fields;
}




///////////////////shipping////////////////////



add_filter( 'woocommerce_shipping_fields' , 'customize_shipping_country_fields' );

function customize_shipping_country_fields( $adresses_fields ) {
 global $wpdb;
 $master='wppx_address_informations';
 $user_id=get_current_user_id();
 $data=get_user_meta($user_id);

 if(is_user_logged_in()){


   if(empty($data['shipping_country']) && empty($data['shipping_state'])){
     
    $data = $wpdb->get_results("SELECT DISTINCT country_code,country FROM $master where country_code='CA' ORDER BY country",ARRAY_A);
    $countryArray = array();
    $countryArray[""] = "Select Country";
    foreach ($data as $value) {
    
    $countryArray[$value['country_code']] = $value['country'];
    }
    $adresses_fields['shipping_country']['type'] = 'select';
    $adresses_fields['shipping_country']['default'] = 'CA';
    $adresses_fields['shipping_country']['options'] = $countryArray;


   }else{
          
    
    $country=$data['shipping_country'][0];
    $state=$data['shipping_state'][0];
    $data = $wpdb->get_results("SELECT DISTINCT country_code,country FROM $master where country_code='CA' ORDER BY country",ARRAY_A);
    $countryArray = array();
    $countryArray[""] = "Select Country";
    foreach ($data as $value) {
    
    $countryArray[$value['country_code']] = $value['country'];
    }
    $adresses_fields['shipping_country']['type'] = 'select';
    $adresses_fields['shipping_country']['default'] = $country;
    $adresses_fields['shipping_country']['options'] = $countryArray;

   }
    

 }else{

 $data = $wpdb->get_results("SELECT DISTINCT country_code,country FROM $master where country_code='CA' ORDER BY country",ARRAY_A);
 $countryArray = array();
    $countryArray[""] = "Select Country";
    foreach ($data as $value) {
    
    $countryArray[$value['country_code']] = $value['country'];
    }
    $adresses_fields['shipping_country']['type'] = 'select';
    $adresses_fields['shipping_country']['options'] = $countryArray;
    $adresses_fields['shipping_country']['default'] = 'CA';



 }
    



    return $adresses_fields;
}



add_filter( 'woocommerce_shipping_fields' , 'customize_shipping_state_fields' );

function customize_shipping_state_fields( $adresses_fields ) {
    global $wpdb;
    $master='wppx_address_informations';

    $user_id=get_current_user_id();
    $data=get_user_meta($user_id);
    if(is_user_logged_in())
    { 

     if(empty($data['shipping_country']) && empty($data['shipping_state'])){

     $data = $wpdb->get_results("SELECT DISTINCT state_code,state FROM $master where country_code='CA' ORDER BY state",ARRAY_A);
     $stateArray = array();
     $stateArray[""] = "Select Province/Territory";
     foreach ($data as $value) {
    
     $stateArray[$value['state_code']] = $value['state'];
     }
     $adresses_fields['shipping_state']['type'] = 'select';
     $adresses_fields['shipping_state']['options'] = $states['CA'] = $stateArray;

      }else{

    $user_id=get_current_user_id();
    $data=get_user_meta($user_id);
    $country=$data['shipping_country'][0];
    $state=$data['shipping_state'][0];
    $datas = $wpdb->get_results("SELECT DISTINCT state_code,state FROM $master where country_code='$country' ORDER BY state",ARRAY_A);
    $stateArray = array();
    $stateArray[""] = "Select Province/Territory";
    foreach ($datas as $value) {
    
    $stateArray[$value['state_code']] = $value['state'];
    }
    $adresses_fields['shipping_state']['type'] = 'select';
    $adresses_fields['shipping_state']['default'] = $state;
    $adresses_fields['shipping_state']['options'] = $states[$country] = $stateArray;

      }

    

  }else{
    $data = $wpdb->get_results("SELECT DISTINCT state_code,state FROM $master where country_code='CA' ORDER BY state",ARRAY_A);
    $stateArray = array();
    $stateArray[""] = "Select Province/Territory";
    foreach ($data as $value) {
    
    $stateArray[$value['state_code']] = $value['state'];
    }
    $adresses_fields['shipping_state']['type'] = 'select';
    $adresses_fields['shipping_state']['options'] = $states['CA'] = $stateArray;

   }


    return $adresses_fields;
}

add_filter('woocommerce_shipping_fields', 'customize_shipping_city_fields');

function customize_shipping_city_fields($adresses_fields) {
    global $wpdb;
    $master = 'wppx_address_informations';
    $user_id = get_current_user_id();
    $data = get_user_meta($user_id);

    // For logged-in users
    if (is_user_logged_in()) {
        $shipping_state = isset($data['shipping_state'][0]) ? $data['shipping_state'][0] : '';
        $shipping_city = isset($data['shipping_city'][0]) ? $data['shipping_city'][0] : '';

        // If no shipping state and city are set, retrieve cities for country 'CA'
        if (empty($shipping_state) && empty($shipping_city)) {
            $data = $wpdb->get_results("SELECT DISTINCT city FROM $master WHERE country_code = 'CA'", ARRAY_A);
        } else {
            // If shipping state is set, retrieve cities based on the state
            $data = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT city FROM $master WHERE state_code = %s", 
                $shipping_state
            ), ARRAY_A);
        }

        $cityArray = ["Select City" => "Select City"];
        foreach ($data as $value) {
            $cityArray[$value['city']] = $value['city'];
        }

        // Add an option for "Others"
        $cityArray['yes'] = 'Others';

        // If the user has already added a new city (not in the database yet), add it to the dropdown
        if (!empty($shipping_city) && !isset($cityArray[$shipping_city])) {
            $cityArray[$shipping_city] = $shipping_city;
        }

        // Set the city field as a dropdown and set the default value
        $adresses_fields['shipping_city']['type'] = 'select';
        $adresses_fields['shipping_city']['options'] = $cityArray;
        if (!empty($shipping_city)) {
            $adresses_fields['shipping_city']['default'] = $shipping_city;
        }

    } else {
        // For guests
        $data = $wpdb->get_results("SELECT DISTINCT city FROM $master WHERE country_code = 'CA'", ARRAY_A);

        $cityArray = ["Select City" => "Select City"];
        foreach ($data as $value) {
            $cityArray[$value['city']] = $value['city'];
        }

        // Add an option for "Others"
        $cityArray['yes'] = 'Others';

        // Set the city field as a dropdown for guests
        $adresses_fields['shipping_city']['type'] = 'select';
        $adresses_fields['shipping_city']['options'] = $cityArray;
    }

    return $adresses_fields;
}


add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );

function change_default_checkout_country() {

   
  if (is_user_logged_in()) {
    
     return get_user_meta( get_current_user_id() , 'billing_country', true ); 

    }  

    return "CA";


}
add_filter( 'default_checkout_shipping_country', 'change_default_shipping_country' );

function change_default_shipping_country() {

   
  if (is_user_logged_in()) {
    
     return get_user_meta( get_current_user_id() , 'shipping_country', true ); 

    }  

    return "CA";


}

add_filter( 'default_checkout_billing_state', 'change_default_checkout_state' );

function change_default_checkout_state() {

    return get_user_meta( get_current_user_id() , 'billing_state', true );  

 }
 add_filter( 'default_checkout_billing_city', 'change_default_checkout_city' );

function change_default_checkout_city() {

    return get_user_meta( get_current_user_id() , 'billing_city', true );  

 }
add_filter( 'default_checkout_billing_postcode', 'change_default_checkout_postcode' );

function change_default_checkout_postcode() {   

    return get_user_meta( get_current_user_id() , 'billing_postcode', true );  

 }

add_filter( 'default_checkout_shipping_city', 'change_default_checkout_shipping_city' );

function change_default_checkout_shipping_city() {
    return get_user_meta( get_current_user_id() , 'shipping_city', true );  
}
add_filter( 'default_checkout_shipping_postcode', 'change_default_checkout_shipping_postcode' );

function change_default_checkout_shipping_postcode() {
    return get_user_meta( get_current_user_id() , 'shipping_postcode', true ); 
}


add_filter( 'default_checkout_shipping_state', 'change_default_checkout_shipping_state' );

function change_default_checkout_shipping_state() {
   
  return get_user_meta( get_current_user_id() , 'shipping_state', true ); 
}

add_action('woocommerce_after_checkout_validation', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process() {
    global $woocommerce;
    // Check if set, if its not set add an error. This one is only requite for companies
    if ( $_POST['billing_phone'] != "") {
        if ( ! (preg_match('/^[0-9]{10}$/D',  $_POST['billing_phone']))){
            wc_add_notice( "Invalid! Please enter valid 10 digits phone number"  ,'error' );
        }
    }   
    
}

////end////////////
add_action('wp_footer', 'cscp_bs_wp_footer');
function cscp_bs_wp_footer() {

    // Check if the current page is the WooCommerce checkout page
    if (is_checkout()) {
        ?>
        <div class='custom_login_popup' id='custom_login_popup'>
            <div id="checkoutpwdModel" class="modal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <span class="checkpwdctn-cl">&times;</span>
                        <h2 style="text-align:center">Login</h2>
                        <br><br>
                        <div>
                            <form id="custom_login" action="custom_login" method="post">
                                <p class="status"></p>
                                <label for="custom_username">Email</label>
                                <input id="custom_username" type="text" name="custom_username" readonly>
                                <label for="custom_password">Password</label>
                                <input id="custom_password" type="password" name="custom_password">
                                
                                <div style="text-align:center">
                                    <input class="submit_button woocommerce-Button highlight-button btn-small button btn" type="submit" value="Login" name="custom_submit">
                                </div>
                                
                                <?php wp_nonce_field('ajax-login-nonce', 'security'); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php


    }

    ?>
                    <!-- The Modal -->
              <div id="bsModel" class="modal ">
              <div class="modal-dialog modal-dialog-centered">

              <!-- Modal content -->
              <div class="modal-content ">
                <span class="bsClose">&times;</span>
            <label for="bseditcountry">Select your country <span class="mant-txt"> * </span></label>
            <select id="bseditcountry" name="bseditcountry" >
              <option value="" disabled >Select Country</option>
            </select>
            <span class="bseditcountrymsg"></span>
            <label for="bseditstate">Select Province <span class="mant-txt"> * </span></label>
            <select id="bseditstate" name="bseditstate">
              <option value="" disabled >Select Province</option>
            </select>
            <span class="bseditstatemsg"></span>
                <label for="bseditcity">Enter your city <span class="mant-txt"> * </span> </label>
                <input type="text" id="bseditcity" name="bseditcity"  >
            <span class="bseditcitymsg" style="display:none"></span>
            <div style="text-align:center"><button type="button" class="sub-btn"  id="bseditbtn">Submit</button></div>
              </div>
            <input type="hidden" id="cityID" name="cityID" value="1" >
            </div>
            </div>
    
    <?php
}

      

