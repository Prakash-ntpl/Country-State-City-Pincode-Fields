jQuery(document).ready( function($) {
//// checkout and my account page country,state,city,postcode start billing form/////

////// billing and shipping variable declarations///
    // Billing fields
    var billingFirstName = $('#billing_first_name');
    var billingLastName = $('#billing_last_name');
    var billingCompany = $('#billing_company');
    var billingCountry = $('#billing_country');
    var billingAddress1 = $('#billing_address_1');
    var billingAddress2 = $('#billing_address_2');
    var billingCity = $('#billing_city');
    var billingState = $('#billing_state');
    var billingPostcode = $('#billing_postcode');
    var billingPhone = $('#billing_phone');
    var billingEmail = $('#billing_email');
    // new city//
    var editCountry = $('#bseditcountry');
    var editState = $('#bseditstate');
    var editCity = $("#bseditcity");
    var editcountrymsg = $('.bseditcountrymsg');
    var editstatemsg = $('.bseditstatemsg');
    var editcitymsg = $('.bseditcitymsg');
    var NewCity= $('#bsModel');
    var NewCitybtn=$("#bseditbtn");
    var bsLogin = $('#checkoutpwdModel');
    var bsLoginclose = $('.checkpwdctn-cl');
    var bsClose = $('.bsClose');
    // Shipping fields
    var shippingFirstName = $('#shipping_first_name');
    var shippingLastName = $('#shipping_last_name');
    var shippingCompany = $('#shipping_company');
    var shippingCountry = $('#shipping_country');
    var shippingAddress1 = $('#shipping_address_1');
    var shippingAddress2 = $('#shipping_address_2');
    var shippingCity = $('#shipping_city');
    var shippingState = $('#shipping_state');
    var shippingPostcode = $('#shipping_postcode');
    var spinner =$('.loader-cover');
  


billingCountry.change(function() {
    var selectedCountry = billingCountry.val();
    if (selectedCountry && selectedCountry !== '') {

        $.ajax({
            type: "POST",
            url: cscp_ajax2.ajax_url,
            dataType: "json",
            data: {
                action: "checkoutstatelist",
                data_to_pass: selectedCountry
            },
        }).done(function(data) {

            // Clear and populate state dropdown
            billingState.empty().append('<option value="" selected>Choose a Province</option>');
            data['states'].forEach(function(state) {
                billingState.append('<option value="' + state['state_code'] + '">' + state['state'] + '</option>');
            });

            // Clear and populate city dropdown
            billingCity.empty().append('<option value=""  selected>Choose a City</option>');
            data['city'].forEach(function(city) {
                billingCity.append('<option value="' + city['city'] + '">' + city['city'] + '</option>');
            });
            // Add "Others" option at the end
            billingCity.append('<option value="yes">Others</option>');

        }).fail(function(jqXHR, textStatus) {
            alert('Error: ' + textStatus);
        });

    } else {
        billingState.html('<option  value="">Choose a Province</option>');
        billingCity.html('<option  value="">Choose a Province First</option>');
    }
});
billingState.change(function() {
    var selectedState = billingState.val();
    if (selectedState && selectedState !== '') {
        $.ajax({
            type: "POST",
            url: cscp_ajax2.ajax_url,
            data: {
                action: "checkoutcitylist",
                data_to_pass: selectedState
            },
            dataType: "json",
        }).done(function(data) {
            billingCity.empty();
            data['city'].forEach(function(city) {
                var st_name = city['city'];
                var opt = '<option value="' + st_name + '">' + st_name + '</option>';
                if (city === data['city'][0]) {
                    billingCity.html('<option value="" selected>Choose a City</option>');
                }
                billingCity.append(opt);
            });
            var opt = '<option value="yes">Others</option>';
            billingCity.append(opt);
        }).fail(function(data) {
            alert('Error ' + data[0]);
        });
    }
});

billingCity.change(function() {
    var selectedCountry = billingCountry.val();
    var selectedState = billingState.val();
    var selectedCity = billingCity.val();
    if (selectedCity && selectedCity !== '') {
        if (selectedCity === "yes") {
            spinner.show();
            editCity.val("");
            $.ajax({
                type: "POST",
                url: cscp_ajax2.ajax_url,
                data: {
                    action: "checkouteditcity",
                    country_data: selectedCountry
                },
                dataType: "json",
            }).done(function(data) {
                var userId = data['userId'];
                $('#cityID').val(1);
                data['countries'].forEach(function(country) {
                    var st_id = country['country_code'];
                    var st_name = country['country'];
                    var opt = (st_id === selectedCountry) ? 
                              '<option value="' + st_id + '" selected>' + st_name + '</option>' : 
                              '<option value="' + st_id + '">' + st_name + '</option>';
                    if (userId === 0 && country === data['countries'][0]) {
                        editCountry.html('<option value="">Choose a Country</option>');
                    }
                    editCountry.append(opt);
                });

                data['states'].forEach(function(state) {
                    var st_id = state['state_code'];
                    var st_name = state['state'];
                    var opt = (st_id === selectedState) ? 
                              '<option value="' + st_id + '" selected>' + st_name + '</option>' : 
                              '<option value="' + st_id + '">' + st_name + '</option>';
                    if (state === data['states'][0]) {
                        editState.html('<option value="" >Choose a Province</option>');
                    }
                    editState.append(opt);
                });
                spinner.hide();
            }).fail(function(data) {
                alert('Error ' + data[0]);
            });

            NewCity.show();
        } else {
            $.ajax({
                type: "POST",
                url: cscp_ajax2.ajax_url,
                data: {
                    action: "zipcodelist",
                    data_to_pass: selectedCity,
                    data_to_country: selectedCountry
                },
                dataType: "json",
            }).done(function(data) {
                var selected_statecode = data['selected'][0]['state_code'];
                data['states'].forEach(function(state) {
                    var st_id = state['state_code'];
                    var st_name = state['state'];
                    var opt = (selected_statecode === st_id) ? 
                              '<option value="' + st_id + '" selected>' + st_name + '</option>' : 
                              '<option value="' + st_id + '">' + st_name + '</option>';
                    if (state === data['states'][0]) {
                        billingState.html('<option value="">Choose a Province</option>');
                    }
                    billingState.append(opt);
                });
                spinner.hide();
            }).fail(function(data) {
                alert('Error ' + data[0]);
            });
        }
    }
    billingPostcode.val('');
});

billingPostcode.on('change', function(e) {
    var country = billingCountry.val();
    var city = billingCity.val();
    var postcode = billingPostcode.val().toUpperCase();

    if (postcodevalidation1(country, postcode)) {
        billingPostcode.val(postcode);
        if (city !== "") {
            $.ajax({
                type: "POST",
                url: cscp_ajax2.ajax_url,
                data: {
                    action: "postalcodevalidation",
                    country_data: country,
                    postalcode: postcode,
                    city: city
                },
                dataType: "json",
            }).done(function(data) {
                var status = data['status'];
                if (status === "N") {
                    alert("The entered postal code already exists for another city. Please enter a new postal code or city, or call/email us for further support");
                    billingPostcode.css('border-color', 'red');
                    billingPostcode.val('');
                    $("#place_order").prop("disabled", true);
                }
                if (status === "Y") {
                    alert('Postal code entered successfully');
                    billingPostcode.css('border-color', 'green');
                    $("#place_order").prop("disabled", false);
                }
            }).fail(function(data) {
                alert('Error ' + data[0]);
            });
        } else {
            alert("Select City First");
        }
    } else {
        alert('Enter valid postal code (Ex:"A1A 1A1")');
        billingPostcode.css('border-color', 'red');
        $("#place_order").prop("disabled", true);
    }
});

billingEmail.change(function() {
    var email = billingEmail.val();
    if (IsEmail1(email)) {
         
        console.log("test");

        $.ajax({
            type: "POST",
            url: cscp_ajax2.ajax_url,
            data: {
                action: "checkout_check_mail",
                email: email
            },
            dataType: "json",
        }).done(function(data) {
            if (data['Status'] === "Yes") {
                $('#custom_username').val(email);
                $('#pwd').val('');
                $('.account_password-2_field').hide();
                $('.account_password_field').hide();
                bsLogin.show();
            }
        }).fail(function(data) {
            alert('Error ' + data);
        });
    }
});

NewCitybtn.click(function() {
    var editcountry = editCountry.val();
    var editstate = editState.val();
    var editcity = editCity.val();
    var cityID = $('#cityID').val();

    // Validate fields
    if (editcountry == "") {
        editcountrymsg.show().html("Please select country").css("color", "red");
    } else {
        editcountrymsg.hide();
    }

    if (editstate == "") {
        editstatemsg.show().html("Please select state").css("color", "red");
    } else {
        editstatemsg.hide();
    }

    if (editcity == "") {
        editcitymsg.show().html("Please select city").css("color", "red");
    } else {
        editcitymsg.hide();
    }

    // Proceed if all fields are valid
    if (editcountry != 0 && editstate != 0 && editcity != 0) {
        if (cityvalidation1(editcity)) {
            editcitymsg.hide();
            spinner.show();

            $.ajax({
                type: "POST",
                url: cscp_ajax2.ajax_url,
                data: {
                    action: "editcitynew",
                    country_data: editcountry,
                    state_data: editstate,
                    city_data: editcity,
                },
                dataType: "json"
            }).done(function(data) {
                alert('City Added Successfully');
                NewCity.hide();
                editCity.val("");

                if (cityID == 1) {
                    billingCountry.empty();
                    billingState.empty();
                    billingCity.empty();

                    // Populate countries
                    data['countries'].forEach(function(item) {
                        var opt = `<option value="${item['country_code']}" ${item['country_code'] == editcountry ? 'selected' : ''}>${item['country']}</option>`;
                        billingCountry.append(opt);
                    });

                    // Populate states
                    data['states'].forEach(function(item) {
                        var opt = `<option value="${item['state_code']}" ${item['state_code'] == editstate ? 'selected' : ''}>${item['state']}</option>`;
                        billingState.append(opt);
                    });

                    // Populate cities
                    data['cities'].forEach(function(item) {
                        var opt = `<option value="${item['city']}" ${item['city'] == editcity ? 'selected' : ''}>${item['city']}</option>`;
                        billingCity.append(opt);
                    });
                    billingCity.append('<option value="yes">Others</option>');
                    var opt='<option  value="'+editcity+'" selected >'+editcity+'</option>';   
                    billingCity.append(opt);
                    billingPostcode.val('');

                } else if (cityID == 2) {
                    shippingCountry.empty();
                    shippingState.empty();
                    shippingCity.empty();
                    // Populate countries
                    data['countries'].forEach(function(item) {
                        var opt = `<option value="${item['country_code']}" ${item['country_code'] == editcountry ? 'selected' : ''}>${item['country']}</option>`;
                        shippingCountry.append(opt);
                    });

                    // Populate states
                    data['states'].forEach(function(item) {
                        var opt = `<option value="${item['state_code']}" ${item['state_code'] == editstate ? 'selected' : ''}>${item['state']}</option>`;
                        shippingState.append(opt);
                    });

                    // Populate cities
                    data['cities'].forEach(function(item) {
                        var opt = `<option value="${item['city']}" ${item['city'] == editcity ? 'selected' : ''}>${item['city']}</option>`;
                        shippingCity.append(opt);
                    });
                    shippingCity.append('<option value="yes">Others</option>');
                    var opt='<option  value="'+editcity+'" selected >'+editcity+'</option>';   
                    shippingCity.append(opt);
                    shippingPostcode.val('');
                }

                spinner.hide();
            });
        } else {
            editcitymsg.html("Please check your city, special character (?) not accepted").css("color", "red").show();
            spinner.hide();
        }
    }
});

bsClose.click(function(){
    
            var cityID=$('#cityID').val();
            spinner.show();  
            
            if(cityID==1)
            { 
            billingEmail.val("");
            NewCity.hide();
            spinner.hide();  
            }
            if(cityID==2)
            {
            shippingCity.val("");           
            NewCity.hide();
            spinner.hide();  
            }
            
            });

    //     //////////// checkout and my account page country,state,city,postcode billing form end///
    
    shippingCountry.change(function() {
        var selectedCountry = shippingCountry.val();
        if (selectedCountry && selectedCountry !== '') {
    
            $.ajax({
                type: "POST",
                url: cscp_ajax2.ajax_url,
                dataType: "json",
                data: {
                    action: "checkoutstatelist",
                    data_to_pass: selectedCountry
                },
            }).done(function(data) {
    
                // Clear and populate state dropdown
                shippingState.empty().append('<option value="" selected>Choose a Province</option>');
                data['states'].forEach(function(state) {
                    shippingState.append('<option value="' + state['state_code'] + '">' + state['state'] + '</option>');
                });
    
                // Clear and populate city dropdown
                shippingCity.empty().append('<option value=""  selected>Choose a City</option>');
                data['city'].forEach(function(city) {
                    shippingCity.append('<option value="' + city['city'] + '">' + city['city'] + '</option>');
                });
                // Add "Others" option at the end
                shippingCity.append('<option value="yes">Others</option>');
    
            }).fail(function(jqXHR, textStatus) {
                alert('Error: ' + textStatus);
            });
    
        } else {
            shippingState.html('<option  value="">Choose a Province</option>');
            shippingCity.html('<option  value="">Choose a Province First</option>');
        }
    });
    shippingState.change(function() {
        var selectedState = shippingState.val();
        if (selectedState && selectedState !== '') {
            $.ajax({
                type: "POST",
                url: cscp_ajax2.ajax_url,
                data: {
                    action: "checkoutcitylist",
                    data_to_pass: selectedState
                },
                dataType: "json",
            }).done(function(data) {
                shippingCity.empty();
                data['city'].forEach(function(city) {
                    var st_name = city['city'];
                    var opt = '<option value="' + st_name + '">' + st_name + '</option>';
                    if (city === data['city'][0]) {
                        shippingCity.html('<option value="" selected>Choose a City</option>');
                    }
                    shippingCity.append(opt);
                });
                var opt = '<option value="yes">Others</option>';
                shippingCity.append(opt);
            }).fail(function(data) {
                alert('Error ' + data[0]);
            });
        }
    });
    
    shippingCity.change(function() {
        var selectedCountry = shippingCountry.val();
        var selectedState = shippingState.val();
        var selectedCity = shippingCity.val();
        if (selectedCity && selectedCity !== '') {
            if (selectedCity === "yes") {
                spinner.show();
                editCity.val("");
                $.ajax({
                    type: "POST",
                    url: cscp_ajax2.ajax_url,
                    data: {
                        action: "checkouteditcity",
                        country_data: selectedCountry
                    },
                    dataType: "json",
                }).done(function(data) {
                    var userId = data['userId'];
                    $('#cityID').val(2);
                    data['countries'].forEach(function(country) {
                        var st_id = country['country_code'];
                        var st_name = country['country'];
                        var opt = (st_id === selectedCountry) ? 
                                  '<option value="' + st_id + '" selected>' + st_name + '</option>' : 
                                  '<option value="' + st_id + '">' + st_name + '</option>';
                        if (userId === 0 && country === data['countries'][0]) {
                            editCountry.html('<option value="">Choose a Country</option>');
                        }
                        editCountry.append(opt);
                    });
    
                    data['states'].forEach(function(state) {
                        var st_id = state['state_code'];
                        var st_name = state['state'];
                        var opt = (st_id === selectedState) ? 
                                  '<option value="' + st_id + '" selected>' + st_name + '</option>' : 
                                  '<option value="' + st_id + '">' + st_name + '</option>';
                        if (state === data['states'][0]) {
                            editState.html('<option value="" >Choose a Province</option>');
                        }
                        editState.append(opt);
                    });
                    spinner.hide();
                }).fail(function(data) {
                    alert('Error ' + data[0]);
                });
    
                NewCity.show();
            } else {
                $.ajax({
                    type: "POST",
                    url: cscp_ajax2.ajax_url,
                    data: {
                        action: "zipcodelist",
                        data_to_pass: selectedCity,
                        data_to_country: selectedCountry
                    },
                    dataType: "json",
                }).done(function(data) {
                    var selected_statecode = data['selected'][0]['state_code'];
                    data['states'].forEach(function(state) {
                        var st_id = state['state_code'];
                        var st_name = state['state'];
                        var opt = (selected_statecode === st_id) ? 
                                  '<option value="' + st_id + '" selected>' + st_name + '</option>' : 
                                  '<option value="' + st_id + '">' + st_name + '</option>';
                        if (state === data['states'][0]) {
                            shippingState.html('<option value="">Choose a Province</option>');
                        }
                        shippingState.append(opt);
                    });
                    spinner.hide();
                }).fail(function(data) {
                    alert('Error ' + data[0]);
                });
            }
        }
        shippingPostcode.val('');
    });
    
    shippingPostcode.on('change', function(e) {
        var country = shippingCountry.val();
        var city = shippingCity.val();
        var postcode = shippingPostcode.val().toUpperCase();
    
        if (postcodevalidation1(country, postcode)) {
            shippingPostcode.val(postcode);
            if (city !== "") {
                $.ajax({
                    type: "POST",
                    url: cscp_ajax2.ajax_url,
                    data: {
                        action: "postalcodevalidation",
                        country_data: country,
                        postalcode: postcode,
                        city: city
                    },
                    dataType: "json",
                }).done(function(data) {
                    var status = data['status'];
                    if (status === "N") {
                        alert("The entered postal code already exists for another city. Please enter a new postal code or city, or call/email us for further support");
                        shippingPostcode.css('border-color', 'red');
                        shippingPostcode.val('');
                        $("#place_order").prop("disabled", true);
                    }
                    if (status === "Y") {
                        alert('Postal code entered successfully');
                        shippingPostcode.css('border-color', 'green');
                        $("#place_order").prop("disabled", false);
                    }
                }).fail(function(data) {
                    alert('Error ' + data[0]);
                });
            } else {
                alert("Select City First");
            }
        } else {
            alert('Enter valid postal code (Ex:"A1A 1A1")');
            shippingPostcode.css('border-color', 'red');
            $("#place_order").prop("disabled", true);
        }
    });
    // Country change event

    
bsLoginclose.click(function(){
    
    billingEmail.val("");       
    bsLogin.hide();
    
    
    });
    
      
$('form#custom_login').on('submit', function(e){
                
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: cscp_ajax2.ajax_url,
                data: { 
                    'action': 'billinglogin', //calls wp_ajax_nopriv_ajaxlogin
                    'username': $('form#custom_login #custom_username').val(), 
                    'password': $('form#custom_login #custom_password').val(), 
                    'security': $('form#custom_login #security').val() },
                success: function(data){
                    $('form#custom_login p.status').text(data.message);
                    $('form#custom_login p.status').css("color","red");
                    if (data.loggedin == true){
                    $('form#custom_login p.status').text("Login successful, redirecting...");
                    $('form#custom_login p.status').css("color","green");
                    window.location.href = window.location.href;
    
                    }
                }
            });
            e.preventDefault();
    
           
        });
    

    
    });
    
    
    
    
    function postcodevalidation1(country,postcode)
     {
    
        switch (country) {
            case "US":
                postalCodeRegex = /^([0-9]{5})(?:[-\s]*([0-9]{4}))?$/;
                break;
            case "CA":
                //postalCodeRegex = /^([A-Z][0-9][A-Z]) \s*([0-9][A-Z][0-9])$/;
                postalCodeRegex = /^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z] [0-9][ABCEGHJ-NPRSTV-Z][0-9]$/;
                break;
            default:
                postalCodeRegex = /^(?:[A-Z0-9]+([- ]?[A-Z0-9]+)*)?$/;
        }
       
        return postalCodeRegex.test(postcode);
    
    
    }
    
    function cityvalidation1(city) {
            const regex=/^([a-zA-Zà-ÿÀ-ÿ0080-024F0-9]+(?:\`|\~|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\+|\=|\[|\{|\]|\}|\||\\|\'|\<|\,|\.|\>|\/|\;|\:|-| ))*[a-zA-Zà-ÿÀ-ÿ0080024F0-9]*[0-9]*$/; 
            if(!regex.test(city)) {
    
               return false;
    
            }else{
    
               return true;
            }
    }
    
    function pwd_check1(pwd) {
    const regex =/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^\w\s]).{8,}$/;
        
            if(!regex.test(pwd)) {
    
               return false;
    
            }else{
    
               return true;
            }
    }
    
    
    function IsEmail1(email) {
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(!regex.test(email)) {
               return false;
            }else{
               return true;
            }
    }
    
    
    
    