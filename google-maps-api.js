// Google Maps Shared Loader
let googleMapsLoaded = false;
let googleMapsCallbacks = [];
let googleMapsLoading = false;

function loadGoogleMaps(callback) {
    if (googleMapsLoaded) {
        console.log('Google Maps already loaded, calling callback');
        callback();
        return;
    }
    
    if (window.google && window.google.maps) {
        console.log('Google Maps already available, calling callback');
        googleMapsLoaded = true;
        callback();
        return;
    }
    
    googleMapsCallbacks.push(callback);
    
    if (!googleMapsLoading) {
        googleMapsLoading = true;
        console.log('Loading Google Maps API...');
        
        const script = document.createElement("script");
        script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBfPI5kUaCUugAlg9iU0I-fhkOrqKqRtUA&libraries=places&callback=handleGoogleMapsLoad";
        script.async = true;
        script.defer = true;
        
        script.onload = () => {
            console.log('Google Maps API loaded successfully');
        };
        
        script.onerror = (error) => {
            console.error('Failed to load Google Maps API:', error);
            googleMapsLoading = false;
            // Still try to call callbacks in case maps were loaded by other means
            setTimeout(() => {
                if (window.google && window.google.maps) {
                    googleMapsLoaded = true;
                    googleMapsCallbacks.forEach(cb => cb());
                    googleMapsCallbacks = [];
                }
            }, 1000);
        };
        
        document.head.appendChild(script);
    }
}

// Global callback for when Google Maps loads
window.handleGoogleMapsLoad = function() {
    console.log('Google Maps callback executed');
    googleMapsLoaded = true;
    googleMapsLoading = false;
    googleMapsCallbacks.forEach(cb => {
        try {
            cb();
        } catch (e) {
            console.error('Error in Google Maps callback:', e);
        }
    });
    googleMapsCallbacks = [];
};