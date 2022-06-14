/* Global variables */
jQuery.extend( Demission, {
    owedAmount: 0,
    accessToken: 'pk.eyJ1Ijoia2lyYW5jc2U3IiwiYSI6ImNrZDUyb3I4OTFuMDEycW10ZWk1cWZrZ2YifQ.v7ZhnuHSg018lg3MXKfkZA',
    map: null,
    marker: null,
    mapboxClient: null,
    flying: false,
    zip: 0,
    streetNumber: '',
});

/**
 * Handle search results
 * 
 * @param {array} results
 */
Demission.handleSearchResults = function(results) {
    // clear results area
    jQuery('#demis_entry_table tbody').html('');
    
    var numRes = results.length;

    if (numRes) {
        for (var i = 0; i < results.length; i++) {
            Demission.displaySearchResults(results[i]);
        }

        Demission.scrollTo('demis_search_results_area');
    }
}

/**
 * Handle search no results
 * 
 */
Demission.displayNoResults = function() {
    // clear results area
    jQuery('#demis_entry_table tbody').html('No record Found');    
    Demission.scrollTo('demis_search_results_area');
}

/**
 * Apeend the entity to the table
 * 
 * @param {object} result 
 */
Demission.displaySearchResults = function(result) {
    // console.log(result);

    var searchResCont = jQuery('#demis_entry_table tbody');
    var resultHTML = '';   

    resultHTML += '<tr scope="row" data-id="' + result.id + '">';
    resultHTML += '<td>' + result.state_abbr + '</td>';
    resultHTML += '<td>' + result.tax_year + '</td>';
    resultHTML += '<td>' + result.due_year + '</td>';
    resultHTML += '<td>' + result.pin + '</td>';
    resultHTML += '<td>' + result.situs + '</td>';
    resultHTML += '<td>' + result.legal_party_1 + '</td>';
    resultHTML += '<td>' + result.zip + '</td>';
    resultHTML += '<td>' + result.street_numb + '</td>';
    resultHTML += '</tr>';

    searchResCont.append(resultHTML);
}

/**
 * Calculate the owed amount
 */
Demission.calcOwedAmount = function() {
    var home_insurance_option = jQuery('[name=home_insurance_option]:checked').val();
    var home_insurance = Demission.getValueFromCurrency(jQuery('#home_insurance').val());
    var tax = jQuery('#property_tax').val();
    var pay_option = jQuery('[name=pay_option]:checked').val();
    var due_year = jQuery('#property_due_year').val();

    if (home_insurance_option == 'no')
        home_insurance = 0;

    Demission.owedAmount = 0;

    var ajaxData = {
        "action": "demission_calc_owe_amount",
        "security": Demission.security,
        "pay_option": pay_option,
        "home_insurance": home_insurance,
        "tax": tax,
        "due_year": due_year
    };

    jQuery.ajax({
        type: 'POST',
        url: Demission.ajaxurl,
        data: ajaxData,
        complete: function(response) {
            var result = jQuery.parseJSON(response.responseText);
            Demission.doing_ajax = false;
            
            if (result.success) {
                Demission.owedAmount = parseFloat(result.data).toFixed(2);
                jQuery('#amount_owed').html(Demission.getCurrencyFormat(Demission.owedAmount));
            } else {
                Demission.showAlert(result.data, 'error');
            }
        }
    });
}

/**
 * Get the currency format value
 * 
 * @param {text} value - number
 */
Demission.getCurrencyFormat = function(value) {
    if (value == '')
        return '0.00';

    value = value.replace(/,/g, '');
    var formatVal = parseFloat(value).toLocaleString('en-US', {
        style: 'decimal',
        maximumFractionDigits: 2,
        minimumFractionDigits: 2
    });

    return formatVal;
}

/**
 * Get the number from the currency format text value
 * 
 * @param {text} value - currency format text
 */
Demission.getValueFromCurrency = function(value) {
    value = value.replace(/[^0-9.-]+/g, "");

    return parseFloat(value);
}

/**
 * Show alert
 * 
 * @param {text} alret 
 */
Demission.showAlert = function(alert, type='error') {
    jQuery('.alert').html(alert);

    if (type == 'error')
        jQuery('.alert').removeClass('alert-success').addClass('alert-danger');
    else if (type == 'success')
        jQuery('.alert').removeClass('alert-danger').addClass('alert-success');

    jQuery('.alert').show();
}

/**
 * Hide alert
 * 
 */
Demission.hideAlert = function() {
    jQuery('.alert').html('');
    jQuery('.alert').hide();
}

/**
 * Scroll page
 * 
 * @param {string} hash 
 */
Demission.scrollTo = function(hash) {    
    if (hash !== "") {
        hash = '#' + hash;

        jQuery('html, body').animate({
            scrollTop: jQuery(hash).offset().top
        }, 800, function() {
            
        });
    }
}

/**
 * Geocoding with address
 * 
 * @param {string} query - address
 */
Demission.forwardGeocode = function(query = '') {
    if (query == '')
        return;    

    Demission.mapboxClient.geocoding
        .forwardGeocode({
            query: query,
            autocomplete: false,
            limit: 1
        })
        .send()
        .then(function(response) {
            if (
                response &&
                response.body &&
                response.body.features &&
                response.body.features.length
            ) {
                var feature = response.body.features[0];

                Demission.map.fire('flystart', {feature: feature});
            }
        });
}

/**
 * Get the zip code and address number
 * 
 * @param {array} address_components 
 */
Demission.getZipStreetnum = function(address_components) {
    Demission.zip = 0;
    Demission.streetNumber = '';

    if (address_components.length) {
        for (var i=0; i<address_components.length; i++) {
            var addr = address_components[i];

            if (addr.types.indexOf('postal_code') > -1)
                Demission.zip = addr.long_name;

            if (addr.types.indexOf('street_number') > -1)
                Demission.streetNumber = addr.long_name;
        }
    }
}

/**
 * Get the result by zip code
 * 
 * @param {number} zip - zip code
 * @param {number} streetNumber - street number
 */
Demission.getResultByZipStreetnum = function(zip, streetNumber) {
    if (!Demission.doing_ajax) {
        var ajaxData = {
            "action": "demission_zip_streetnum_search",
            "security": Demission.security,
            "zip": zip,
            "streetNumber": streetNumber
        };

        Demission.doing_ajax = true;        
        jQuery('#demis_search_content').addClass('loading');

        jQuery.ajax({
            type: 'POST',
            url: Demission.ajaxurl,
            data: ajaxData,
            complete: function(response) {
                var result = jQuery.parseJSON(response.responseText);

                Demission.doing_ajax = false;
                jQuery('#demis_search_content').removeClass('loading');

                if (result.success) {                    
                    if (result.data.length)
                        Demission.handleSearchResults(result.data);
                    else
                        Demission.displayNoResults();
                } else {
                    Demission.showAlert(result.data);
                }     
            }
        })
    }
}

/**
 * Get the result by pin code
 * 
 * @param {number} pin - pin code
 */
Demission.getResultByPin = function(pin) {
    if (!Demission.doing_ajax) {
        var ajaxData = {
            "action": "demission_pin_search",
            "security": Demission.security,
            "pin": pin
        };

        Demission.doing_ajax = true;        
        jQuery('#demis_search_content').addClass('loading');

        jQuery.ajax({
            type: 'POST',
            url: Demission.ajaxurl,
            data: ajaxData,
            complete: function(response) {
                var result = jQuery.parseJSON(response.responseText);

                Demission.doing_ajax = false;
                jQuery('#demis_search_content').removeClass('loading');

                if (result.success) {                    
                    if (result.data.length)
                        Demission.handleSearchResults(result.data);
                    else
                        Demission.displayNoResults();
                } else {
                    Demission.showAlert(result.data);
                }     
            }
        })
    }
}

if (typeof is_home === "undefined") var is_home = jQuery('body').hasClass('home');
if (typeof is_property_detail === "undefined") var is_property_detail = jQuery('#property_details').length != 0;


jQuery(document).ready(function($) {

    // Initialize map
    if ($('#map').length) {
        mapboxgl.accessToken = Demission.accessToken;

        Demission.mapboxClient = mapboxSdk({ accessToken: Demission.accessToken });

        // Map initialize
        Demission.map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [-77.050636, 38.889248],
            zoom: 13
        });

        // Add navigation control
        Demission.map.addControl(new mapboxgl.NavigationControl());

        // Add geolocate control to the map.
        Demission.map.addControl(
            new mapboxgl.GeolocateControl({
                positionOptions: {
                    enableHighAccuracy: true
                },
                trackUserLocation: true
            })
        );

        Demission.map.on('moveend', function(e) {
            if (Demission.flying)            
                Demission.map.fire('flyend');
        });

        Demission.map.on('flystart', function(event) {
            Demission.flying = true;

            var feature = event.feature;
            
            Demission.map.flyTo({
                zoom: 15,
                center:[feature.center[0], feature.center[1]],
                curve: 2, // change the speed at which it zooms out
                easing: function(t) {
                    return t;
                },
                essential: true // this animation is considered essential with respect to prefers-reduced-motion
            });                    

            if (Demission.marker != null)
                Demission.marker.remove();

            Demission.marker = new mapboxgl.Marker({color: 'orange'}).setLngLat(feature.center).addTo(Demission.map);
			
			// disable the map dragging
			Demission.map.dragPan.disable();
        });

        Demission.map.on('flyend', function() {
            Demission.flying = false;
            
            // enable the map dragging
            Demission.map.dragPan.enable();
            
            if (Demission.zip != 0 || Demission.streetNumber != '')
                Demission.getResultByZipStreetnum(Demission.zip, Demission.streetNumber);
            else
                Demission.displayNoResults();
        });
    }

    // Home page
    if (is_home) {

        var search_location = document.getElementById('search_location');
        
        var autocomplete = new google.maps.places.Autocomplete(search_location, {
            componentRestrictions: {country: "us"}
        });

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            
            if (!place.geometry) {
                // User entered the name of a Place that was not suggested and
                // pressed the Enter key, or the Place Details request failed.
                // Do anything you like with what was entered in the ac field.
                search_location.value = "";
                return;
            }            
                    
            console.log('You selected: ' + place.formatted_address);  
            console.log(place.address_components);   
            
            Demission.hideAlert();

            Demission.getZipStreetnum(place.address_components);                
            Demission.forwardGeocode(place.formatted_address);                                              
        });

        /* Property table entity click event */
        $('#demis_entry_table').on('click', 'tbody tr', function() {
            var property_id = $(this).attr('data-id');

            if (property_id == undefined)
                return;

            var url = Demission.property_url + '?id=' + property_id;
            
            window.location.href = url;
        });

        /* Search by Pin input blue event */
        $('#search_pin').on('blur', function() {
            Demission.hideAlert();
            Demission.getResultByPin($(this).val());
        });
        
    }

    // Property detail page
    if (is_property_detail) { 
        // Calc owed amount   
        Demission.calcOwedAmount();

        // Get the address
        var query = $('#property_situs').val();

        // Add marker of the address to the map
        if (query != 'No Property Address' && query != '') {
            Demission.forwardGeocode(query);
        }

        // Home Insurance Options click event
        $('[name=home_insurance_option]').on('click', function() {
            var opt = $(this).val();            

            if (opt == 'yes') {
                $('.home-insurance-wrapper').show();                
            } else {
                $('.home-insurance-wrapper').hide();
            }

            Demission.hideAlert();
        });

        // Pay Options click event
        $('[name=pay_option]').on('click', function() {
            var opt = $(this).val();            

            if (opt == '0') {
                $('#amount_per_month').html('every month');
            } else if (opt == '1') {
                $('#amount_per_month').html('every 3 months');
            } else if (opt == '2') {
                $('#amount_per_month').html('every 6 months');
            }

            Demission.calcOwedAmount();
            Demission.hideAlert();
        });

        // Home Insurance Input Blur event
        $('#home_insurance').on('blur', function() {
            var formatVal = Demission.getCurrencyFormat($(this).val());

            $(this).val(formatVal);

            Demission.calcOwedAmount();
            Demission.hideAlert();
        });        

        // Signup Button click event
        $('#btn_signup').on('click', function() {
            var property_id = $('#property_id').val();
            var property_tax = $('#property_tax').val();
            var home_insurance = $('#home_insurance').val();
            var pay_option = $('[name=pay_option]:checked').val();    

            var ajaxData = {
                "action": "demission_pay_tax",
                "security": Demission.security,
                "property_id": property_id,
                "property_tax": property_tax,
                "home_insurance": Demission.getValueFromCurrency(home_insurance),
                "pay_option": pay_option,
                "owed_amount": Demission.owedAmount
            };

            Demission.doing_ajax = true;

            $.ajax({
                type: 'POST',
                url: Demission.ajaxurl,
                data: ajaxData,
                complete: function(response) {
                    var result = $.parseJSON(response.responseText);
                    Demission.doing_ajax = false;
                    
                    if (result.success) {
                        Demission.showAlert(result.data, 'success');
                        window.location.href = Demission.checkout_url;
                    } else {
                        Demission.showAlert(result.data, 'error');
                    }
                }
            });
        });

    }

});