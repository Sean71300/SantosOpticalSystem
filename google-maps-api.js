// Google Maps Shared Loader
let googleMapsLoaded = false;
let googleMapsCallbacks = [];

function loadGoogleMaps(callback) {
    if (googleMapsLoaded) {
        callback();
        return;
    }
    
    if (window.google && window.google.maps) {
        googleMapsLoaded = true;
        callback();
        return;
    }
    
    googleMapsCallbacks.push(callback);
    
    if (!window.googleMapsLoading) {
        window.googleMapsLoading = true;
        const script = document.createElement("script");
        script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBfPI5kUaCUugAlg9iU0I-fhkOrqKqRtUA&libraries=places";
        script.async = true;
        script.defer = true;
        script.onload = () => {
            googleMapsLoaded = true;
            window.googleMapsLoading = false;
            googleMapsCallbacks.forEach(cb => cb());
            googleMapsCallbacks = [];
        };
        document.head.appendChild(script);
    }
}