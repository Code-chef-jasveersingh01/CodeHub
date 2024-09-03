function initialize() {
    $('form').on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    var jsRangeSlider = $(".js-range-slider").ionRangeSlider({
        min: 0.171,
        max: 100,
        step: 0.001,
        from: 0.171,
        to: 100,
        postfix: " KM",
    });
    var jsRangeSlider = jsRangeSlider.data("ionRangeSlider");

    const locationInputs = document.getElementsByClassName("map-input");
    const autocompletes = [];
    const geocoder = new google.maps.Geocoder;

    for (let i = 0; i < locationInputs.length; i++) {
        const input = locationInputs[i];
        const fieldKey = input.id.replace("-input", "");
        const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';
        const latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || -33.8688;
        const longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 151.2195;
        const circle_radius = parseFloat(document.getElementById(fieldKey + "-zone-radius").value) ? parseFloat(document.getElementById(fieldKey + "-zone-radius").value) : 0;

        const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
            center: { lat: latitude, lng: longitude },
            zoom: 17
        });

        const marker = new google.maps.Marker({
            map: map,
            position: { lat: latitude, lng: longitude },
        });

        const circle = new google.maps.Circle({
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.35,
            map,
            center: { lat: latitude, lng: longitude },
            radius: circle_radius * 1000,
        });
        circle.bindTo('center', marker, 'position');

        jsRangeSlider.update({ from: circle_radius });

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.key = fieldKey;
        autocompletes.push({ input: input, map: map, marker: marker, autocomplete: autocomplete, circle: circle });
    }

    for (let i = 0; i < autocompletes.length; i++) {
        const input = autocompletes[i].input;
        const autocomplete = autocompletes[i].autocomplete;
        const map = autocompletes[i].map;
        const marker = autocompletes[i].marker;
        const circle = autocompletes[i].circle;
        const circleRadiusRang = document.getElementById(autocomplete.key + "-circle-radius-range");
        const zoneRadius = document.getElementById(autocomplete.key + "-zone-radius");

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            marker.setVisible(false);
            const place = autocomplete.getPlace();
            geocoder.geocode({ 'placeId': place.place_id }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();
                    setLocationCoordinates(autocomplete.key, lat, lng);
                }
            });

            if (!place.geometry) {
                window.alert("No details available for input: '" + place.name + "'");
                input.value = "";
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
        });

        map.addListener('click', (event) => {
            if (event.latLng.lat()) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                setLocationCoordinates(autocomplete.key, lat, lng);
            }

            map.setCenter(event.latLng);
            map.setZoom(17);
            marker.setPosition(event.latLng);
        });

        // Update the current circle range (each time you drag the slider handle)
        circleRadiusRang.oninput = function () {
            zoneRadius.value = this.value;
            updateRadius(circle, parseFloat(this.value * 1000));
        }

        zoneRadius.oninput = function () {
            jsRangeSlider.update({ from: this.value });
            circleRadiusRang.value = this.value;
            updateRadius(circle, parseFloat(this.value * 1000));
        }
    }
}
function updateRadius(circle, radius) {
    circle.setRadius(radius);
}

function setLocationCoordinates(key, lat, lng) {
    const latitudeField = document.getElementById(key + "-" + "latitude");
    const longitudeField = document.getElementById(key + "-" + "longitude");
    latitudeField.value = lat;
    longitudeField.value = lng;
}



