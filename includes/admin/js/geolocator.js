( function( $ ) {
  var testResponse = {"type":"FeatureCollection","query":["lisbon"],"features":[{"id":"place.61919","type":"Feature","text":"Lisboa","place_name":"Lisboa, Lisboa, Portugal","relevance":0.99,"center":[-9.1484,38.7263],"geometry":{"type":"Point","coordinates":[-9.1484,38.7263]},"bbox":[-9.229825999999997,38.69137479999999,-9.090163899999999,38.79585289999999],"properties":{},"context":[{"id":"region.2718889447","text":"Lisboa"},{"id":"country.214075340","text":"Portugal"}]},{"id":"place.30309","type":"Feature","text":"Lisbon","place_name":"Lisbon, 44432, Ohio, United States","relevance":0.99,"center":[-80.7681,40.772],"geometry":{"type":"Point","coordinates":[-80.7681,40.772]},"bbox":[-80.89347600995283,40.66264999000151,-80.62047999011159,40.82928100982221],"properties":{},"context":[{"id":"postcode.3525944550","text":"44432"},{"id":"region.2820712142","text":"Ohio"},{"id":"country.4150104525","text":"United States"}]},{"id":"place.30308","type":"Feature","text":"Lisbon","place_name":"Lisbon, 58054, North Dakota, United States","relevance":0.99,"center":[-97.6812,46.4416],"geometry":{"type":"Point","coordinates":[-97.6812,46.4416]},"bbox":[-97.88853200999571,46.282345989999996,-97.4059629928948,46.5582680072125],"properties":{},"context":[{"id":"postcode.2828423407","text":"58054"},{"id":"region.2274388644","text":"North Dakota"},{"id":"country.4150104525","text":"United States"}]},{"id":"place.30311","type":"Feature","text":"Lisbon Falls","place_name":"Lisbon Falls, 04252, Maine, United States","relevance":0.99,"center":[-70.0606,43.9962],"geometry":{"type":"Point","coordinates":[-70.0606,43.9962]},"bbox":[-70.09236400998033,43.99098199037813,-70.02510799134265,44.059682008390716],"properties":{},"context":[{"id":"postcode.2015401417","text":"04252"},{"id":"region.901260515","text":"Maine"},{"id":"country.4150104525","text":"United States"}]},{"id":"place.30307","type":"Feature","text":"Lisbon","place_name":"Lisbon, 52253, Iowa, United States","relevance":0.99,"center":[-91.3854,41.9211],"geometry":{"type":"Point","coordinates":[-91.3854,41.9211]},"bbox":[-91.4131980098074,41.8044589903839,-91.28797099000124,41.984068009999774],"properties":{},"context":[{"id":"postcode.3030113138","text":"52253"},{"id":"region.3529999094","text":"Iowa"},{"id":"country.4150104525","text":"United States"}]}],"attribution":"NOTICE: © 2015 Mapbox and its suppliers. All rights reserved. Use of this data is subject to the Mapbox Terms of Service (https://www.mapbox.com/about/maps/). This response and the information it contains may not be retained."};
  var accessToken = 'pk.eyJ1Ijoic2FyYWhzZW1hcmsiLCJhIjoiMDc1OWI2ODA3ZmY5YTU5N2JmNGVhMmFhODQ0MjJlNWUifQ.vLvh5YqCclf_pNMlGXKrZw';
  $('#wanderlist-geolocation-input').on('change', function() {
    var locationString = $(this).attr('value');
    var geocodeURI =  'https://api.mapbox.com/v4/geocode/mapbox.places/' + encodeURI(locationString) + '.json?access_token=' + accessToken;
    // $.ajax(geocodeURI, {
    //   success: function(response) {
    //     console.log(response.features[0].place_name);
    //   }
    // })
    var city = testResponse.features[0].text;
    var region = testResponse.features[0].context[0].text;
    var country = testResponse.features[0].context[1].text;
    var lng = testResponse.features[0].center[0];
    var lat = testResponse.features[0].center[1];
    $('#wanderlist-city').val(city);
    $('#wanderlist-country').val(country);
    $('#wanderlist-lng').val(lng);
    $('#wanderlist-lat').val(lat);
  });
} )( jQuery );
