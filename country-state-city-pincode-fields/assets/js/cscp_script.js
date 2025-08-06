jQuery(document).ready(function($) {
    ///// contact form variable declarations/////
    var contactFname=$('#contactFname');
    var contactLname=$('#contactLname');
    var contactCountry = $("#my_country_field");
    var contactState = $("#my_state_field");
    var contactCity = $("#my_city_field");
    var contactPostalcode = $("#my_zip_field");
    var contactPhone =$('#contactPhone');
    var contactEmail =$("#contactEmail");
    var contactPwd =$('#contactPwd');
    var contactCnpwd =$('#contactCnpwd');

    var lang=getUrlParameter('lang');


    var contactEditCountry = $('#editcountry');
    var contactEditState = $('#editstate');
    var contactEditCity = $('#editcity');
    var contactEditClose =$('.ctn-cl');
    var contactEditBtn=$("#cityeditbtn");
    var editcountrymsg = $('.editcountrymsg');
    var editstatemsg = $('.editstatemsg');
    var editcitymsg = $('.editcitymsg');
    var contactNewCity= $('#myModal');

    var errorMsgCountry = $('.errorMsg-country');
    var errorMsgState = $('.errorMsg-state');
    var errorMsgCity = $('.errorMsg-city');
    var countryError = errorMsgCountry.text(lang == "fr" ? "Veuillez choisir un pays valide." :"Please choose a valid country.");
    var stateError = errorMsgState.text(lang == "fr" ? "Veuillez choisir une province/territoire valide" : "Please choose a valid Province/Territory");
    var cityError = errorMsgCity.text(lang == "fr" ? "Veuillez choisir une ville valide." : "Please choose a valid city");
    var errorMsgEmail = $('#mail_msg');
    var errorPwd = $('#pwd_msg');
    var errorCnpwd = $('#pwd_con_msg');
    var emailError= (lang=="fr") ? errorMsgEmail.html('Veuillez saisir ladresse e-mail correcte') : errorMsgEmail.html('Please enter correct email');
    var spinner =$('.loader-cover');

    /// mobile number format(mask)
    if (contactPhone.length) {
        // Ensure jQuery Mask Plugin is loaded
        if ($.fn.mask) {
            contactPhone.mask('000-000-0000'); // Apply mask for mobile phone formatting
        } else {
            console.log('jQuery Mask Plugin is not loaded');
        }
    } else {
        console.log('contactPhone element not found');
    }

    
    contactCountry.change(function() {
        var selectedCountry = contactCountry.val();
        if (selectedCountry && selectedCountry !== '0') {

            contactCountry.css("border-color", "#dfdfdf");
            countryError.hide();
            // spinner.show();

            $.ajax({
                type: "POST",
                url: cscp_ajax.ajax_url,
                dataType: "json",
                data: {
                    action: "statelist",
                    data_to_pass: selectedCountry
                }
            }).done(function(response) {

                // spinner.hide();

                var $stateField = contactState.empty().append('<option value="0" selected>Choose a Province</option>');
                if (response.states && response.states.length) {
                    $.each(response.states, function(index, state) {
                        $stateField.append(`<option value="${state.state_code}">${state.state}</option>`);
                    });
                }

                var $cityField = contactCity.empty().append('<option value="0" selected>Choose a City</option>');
                if (response.city && response.city.length) {
                    $.each(response.city, function(index, city) {
                        $cityField.append(`<option value="${city.city}">${city.city}</option>`);
                    });
                }
                $cityField.append('<option value="yes">Others</option>');
            }).fail(function(xhr) {
                alert('Error: ' + xhr.responseText);
            });
        } else {
            resetFields(contactState, contactCity, errorMsgState, errorMsgCity);
        }
    });


    // State change event
    contactState.change(function() {
        var selectedState = $(this).val();

        if (selectedState && selectedState !== '0') {
            $(this).css("border-color", "#dfdfdf");
            stateError.hide();
            $.ajax({
                type: "POST",
                url: cscp_ajax.ajax_url,
                data: {
                    action: "citylist",
                    state_field: selectedState
                },
                dataType: 'json'
            }).done(function(response) {
                var $cityField = contactCity.empty().append('<option value="0" selected>Choose a City</option>');

                if (response.city && response.city.length) {
                    $.each(response.city, function(index, city) {
                        $cityField.append(`<option value="${city.city}">${city.city}</option>`);
                    });
                }

                $cityField.append('<option value="yes">Others</option>');
            }).fail(function(xhr) {
                alert('Error: ' + xhr.responseText);
            });
        } else {
            contactState.css("border-color", "#dc3232");
            stateError.show();
            contactCity.html('<option value="0">Choose a City</option>');

        }
    });

    // City change event
    contactCity.change(function() {
        var selectedCity = contactCity.val();
        var country = contactCountry.val();

        if (selectedCity && selectedCity !== '0') {
            if (selectedCity === "yes") {
                $(this).css("border-color", "#dfdfdf");
                cityError.hide();
                stateError.hide();
                contactState.css("border-color", "#dfdfdf");
                contactEditCountry.empty();
                contactEditState.empty();
                contactEditCity.val("");
                contactNewCity.css('display', 'block');

                $.ajax({
                    type: "POST",
                    url: cscp_ajax.ajax_url,
                    dataType: "json",
                    data: { action: "contacteditcity", country_data: country }
                }).done(function(data) {
                    populateEditModal(data, country);
                }).fail(function(xhr) {
                    alert('Error: ' + xhr.responseText);
                });
            } else {
                $(this).css("border-color", "#dfdfdf");
                cityError.hide();
                stateError.hide();
                contactState.css("border-color", "#dfdfdf");
                updateCityState(country, selectedCity);
            }
        } else {
            contactCity.css("border-color", "#dc3232");
            cityError.show();
            contactPostalcode.val('');
        }
    });

    // Postal code change event
    contactPostalcode.on('change', function() {
        var country = contactCountry.val();
        var city = contactCity.val();
        var postcode = contactPostalcode.val().toUpperCase();

        if (postcodevalidation(country, postcode)) {
            contactPostalcode.val(postcode);

            if (city !== '0' && city !== 'yes' && city !== '') {
                $.ajax({
                    type: "POST",
                    url: cscp_ajax.ajax_url,
                    data: {
                        action: "postalcodevalidation",
                        country_data: country,
                        postalcode: postcode,
                        city: city,
                    },
                    dataType: "json"
                }).done(function(data) {
                    handlePostalCodeValidation(data);
                });
            } else {
                alert("Select City First");
                contactPostalcode.val('');
            }
        } else {
            alert('Enter valid postal code (Ex: "A1A 1A1")');
            contactPostalcode.css('border-color', '#dc3232');
            $("#conBtn").prop("disabled", true);
        }
    });
    // New city close button
    contactEditClose.on('click',function(){
        contactCity.val("0");
        contactNewCity.css('display','none');
    });

    // New city submit
    contactEditBtn.on('click', function() {
        var editCountry = contactEditCountry.val();
        var editState = contactEditState.val();
        var editCity = contactEditCity.val();
    
        // Clear previous messages and hide the loader
        $('.editcountrymsg, .editstatemsg, .editcitymsg').css("display", "none");
        spinner.hide();
    
        // Validation flags
        var isValid = true;
        // Validate country
        if (!editCountry) {
            editcountrymsg.html("Please select country").css({ "color": "red", "display": "block" });
            isValid = false;
        }
    
        // Validate state
        if (!editState) {
            editstatemsg.html("Please select state").css({ "color": "red", "display": "block" });
            isValid = false;
        }
    
        // Validate city
        if (!editCity) {
            editcitymsg.html("Please select city").css({ "color": "red", "display": "block" });
            isValid = false;
        }
    
        // If all fields are valid, proceed with further actions
        if (isValid) {
            spinner.show();
    
            if (cityvalidation(editCity)) {
                // Update fields in the form and close the modal
                contactCountry.val(editCountry);
                contactState.val(editState);
                var opt = `<option value="${editCity}" selected>${editCity}</option>`;
                contactCity.append(opt);
                contactNewCity.hide();
                spinner.hide();
                console.log('Valid');
            } else {
                // Invalid city input
                editcitymsg.html("Please check your city, special characters (?) not accepted")
                                .css({ "color": "red", "display": "block" });
                spinner.hide();
                console.log('Invalid');
            }
        }
    });
    
    contactEmail.on("change", function(){
       var name=contactFname.val();
       var lastName=contactLname.val();
       var email =$(this).val();

       if (name!=null && lastName!=null ) {
   
          if(IsEmail(email)==true){
   
            errorMsgEmail.hide();
            spinner.show();
            $.ajax({
               type: "POST",
               url: cscp_ajax.ajax_url,
               data: {
                      action: "contact_check_email",
                      nonce_ajax : cscp_ajax.nonce,
                      data_to_pass:email},
               dataType : "json",
           }).done(function(data){
               //console.log(data);
   
   
               //console.log(data['Status']);
   
               if (data['Status']=="Yes") {
                   contactPwd.val("Pwd@123");
                   contactPwd.prop('required',false);
                   contactCnpwd.prop('required',false);
                   contactCnpwd.val("Pwd@123");
                   $('.nodisplay').css('display','none');
                   contactPwd.hide();
                   contactCnpwd.hide();
               }
   
               if (data['Status']=="No") {
                     
                    contactPwd.empty();
                    contactCnpwd.empty();
                    errorPwd.hide();
                    errorCnpwd.hide();
                    contactPwd.prop('required',true);
                    contactCnpwd.prop('required',true);
                   $('.nodisplay').css('display','block');
                    contactPwd.show();
                    contactCnpwd.show();
   
               }   
   
             spinner.hide();
      
   
   
           }).
           fail(function(data) {
                   alert('Error' + data[0]);
               });
            
           
   
           }else{
   
              console.log('failed');
              emailError.show();
              errorMsgEmail.css('color','red');
              errorMsgEmail.css('display','block');
           }
        
        }
   
        // else{
   
        //    alert("Please enter Frist name & Last Name");
        // }
   
   }); 
     
   /// contact us after submit validation

   document.addEventListener('wpcf7submit', function(event) {

    if (contactCity.length && contactCity.val() === '0') {
        contactCity.css("border-color", "#dc3232");
        cityError.show();
    } else {
        contactCity.css("border-color", "#dfdfdf");
        cityError.hide();
    }
    

    // Validate state field
    if (contactState.length && contactState.val() === '0') {
        contactState.css("border-color", "#dc3232");
        stateError.show();
    } else {
        contactState.css("border-color", "#dfdfdf");
        stateError.hide();
    }

    // Validate country field
    if (contactCountry.length && contactCountry.val() === '0') {
        contactCountry.css("border-color", "#dc3232");
        countryError.show();
    } else {
        contactCountry.css("border-color", "#dfdfdf");
        countryError.hide();
    }
});

 
    // Utility functions
    function resetFields(stateField, cityField, errorMsgState, errorMsgCity) {
        stateField.html('<option value="0">Choose a Province</option>').css("border-color", "#dc3232");
        stateError.show();

        cityField.html('<option value="0">Choose a City</option>').css("border-color", "#dc3232");
        cityError.show();
    }

    function populateEditModal(data, country) {
        contactEditCountry.append(data['countries'].map(country => 
            `<option value="${country.country_code}" ${country.country_code === country ? 'selected' : ''}>${country.country}</option>`
        ));
        contactEditState.append(data['states'].map(state => 
            `<option value="${state.state_code}">${state.state}</option>`
        ));
        contactNewCity.css('display', 'block');
    }

    function updateCityState(country, city) {
        $.ajax({
            type: "POST",
            url: cscp_ajax.ajax_url,
            dataType: "JSON",
            data: { action: "ziplist", data_to_pass: city, data_to_country: country }
        }).done(function(data) {
            var selectedStateCode = data.selected[0].state_code;
            contactState.empty().append('<option value="0">Choose a Province/Territory</option>');

            $.each(data.states, function(index, state) {
                var selected = (selectedStateCode === state.state_code) ? 'selected' : '';
                contactState.append(`<option value="${state.state_code}" ${selected}>${state.state}</option>`);
            });
        }).fail(function(xhr) {
            alert('Error: ' + xhr.responseText);
        });
    }

    function handlePostalCodeValidation(data) {
        if (data.status === "N") {
            alert("Postal code already exists. Please use a different postal code or contact support.");
            contactPostalcode.css('border-color', '#dc3232');
            $("#conBtn").prop("disabled", true);
        } else if (data.status === "Y") {
            alert('Postal code entered successfully');
            contactPostalcode.css('border-color', '#dfdfdf');
            $("#conBtn").prop("disabled", false);
        }
    }
    function cityvalidation(city) {

        const regex=/^([a-zA-Zà-ÿÀ-ÿ0080-024F0-9]+(?:\`|\~|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\+|\=|\[|\{|\]|\}|\||\\|\'|\<|\,|\.|\>|\/|\;|\:|-| ))*[a-zA-Zà-ÿÀ-ÿ0080024F0-9]*[0-9]*$/; 
         
        if(!regex.test(city)) {
      
             return false;
      
          }else{
      
             return true;
          }
      }

    function postcodevalidation(country,postcode)
      {
     
         switch (country) {
             case "US":
                 postalCodeRegex = /^([0-9]{5})(?:[-\s]*([0-9]{4}))?$/;
                 break;
             case "CA":
                 postalCodeRegex = /^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z] [0-9][ABCEGHJ-NPRSTV-Z][0-9]$/;
                 break;
             default:
                 postalCodeRegex = /^(?:[A-Z0-9]+([- ]?[A-Z0-9]+)*)?$/;
         }
        
         return postalCodeRegex.test(postcode);
     
     
     }
    function pwd_check(pwd) {
        const regex =/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^\w\s]).{8,}$/;
            
                if(!regex.test(pwd)) {
        
                   return false;
        
                }else{
        
                   return true;
                }
    } 
    function IsEmail(email) {
                var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if(!regex.test(email)) {
                   return false;
                }else{
                   return true;
                }
    }
    
});

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};

