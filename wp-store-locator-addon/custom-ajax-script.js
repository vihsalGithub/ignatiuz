jQuery(document).ready(function($) {

    var $checkVariable  =  `<div id="store-status-tabs2">
        <ul>
            <li><a href="#" class="check_btn_click" data-status="all">All</a></li>
            <li><a href="#" class="check_btn_click" data-status="active">Active</a></li>
            <li><a href="#" class="check_btn_click" data-status="deactive">Deactive</a></li>
        </ul>
    </div>`;

    $("#wpsl-result-list").prepend($checkVariable);

  $("body").on('click', '.check_btn_click', function(e) {
    e.preventDefault();
    var status = $(this).data('status');
        
        //alert(status);
        $.ajax({
            url: ajax_params.ajax_url,
            type: 'GET',
            data: {
                action: 'store_search',
                lat: 22.7195687,  // Replace with dynamic latitude value
                lng: 75.8577258,  // Replace with dynamic longitude value
                max_results: 25,
                search_radius: 50,
                store_status: status,
                skip_cache: 1
            },
            success: function(response) {
                $('#wpsl-result-list').html('');
                $('#wpsl-result-list').append($checkVariable);
                var storeHtml = '';
                response.forEach(function(store) {
                     storeHtml += `
                        <li data-store-id="${store.ID}">
                            <div class="wpsl-store-location">
                                <p>
                                    ${store.thumb}
                                    <strong>${store.store}</strong>
                                    <span class="wpsl-street">${store.address}</span>
                                    <span>${store.city} ${store.state} ${store.zip}</span>
                                    <span class="wpsl-country">${store.country}</span>
                                </p>
                            </div>
                            <div class="wpsl-direction-wrap">
                                ${store.distance.toFixed(1)} km
                                <a class="wpsl-directions" href="#">Directions</a>
                            </div>
                        </li>`;
                    
                });
                
                $('#wpsl-result-list').append(storeHtml);
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', status, error);
            }
        });
});
    // Hook into the WP Store Locator map rendering
    $(document).on('wpsl_rendered', function(event, map, markers) {
        markers.forEach(function(marker) {
            var storeStatus = marker.location.store_status;

            // Define your custom marker icons
            var activeIcon = '/path-to-your-icons/active-marker.png';
            var inactiveIcon = '/path-to-your-icons/inactive-marker.png';

            // Set the marker icon based on store status
            if (storeStatus === 'active') {
                marker.setIcon(activeIcon);
            } else if (storeStatus === 'inactive') {
                marker.setIcon(inactiveIcon);
            }
        });
    });
});
