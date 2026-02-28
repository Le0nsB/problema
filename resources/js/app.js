import './bootstrap';
import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

setOptions({
  apiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
  key: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
  libraries: ["places", "marker"],
  mapId: import.meta.env.VITE_GOOGLE_MAP_ID || undefined,
});

(async () => {
  try {

    await importLibrary('maps');
    await importLibrary('places');
    console.log('Google Maps and Places loaded successfully');
    
    const checkInitMap = setInterval(() => {
      if (window.initMap) {
        clearInterval(checkInitMap);
        console.log('Calling initMap');
        window.initMap();
      }
    }, 100);
    
    setTimeout(() => {
      clearInterval(checkInitMap);
      if (!window.initMap) {
        console.error('initMap function not found after 5 seconds');
      }
    }, 5000);
  } catch (e) {
    console.error('Failed to load Google Maps via js-api-loader:', e);
  }
})();
