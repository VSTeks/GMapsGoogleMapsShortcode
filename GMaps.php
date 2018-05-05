<?php
/*
Plugin Name:  GMaps Google Maps Shortcode
Plugin URI:   https://github.com/VSTeks/GMapsGoogleMapsShortcode
Description:  Adds shortcode's to insert Google Maps via the javascript API.
Version:      1.0.0
Author:       VSteks
Author URI:   http://vsteks.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  vs-gm
*/

/*
 * This plugin Add's a shortcode to insert google maps into a wordpress site,
 * it requires an API key be set in Settings > General before use.
 *
 * Request Flow:
 *     1. Create global variables to share map data between the shortcode functions
 *        and the footer functions.
 *     2. Handle short codes and any possible nested shortcodes
 *        storing the generated objects in their appropriate global variables.
 *     3. In footer read global variables and generate a function to create all
 *        all them maps on the page and insert the googlemaps link, then unset
 *        all grlobal variables.
 */

 $prefix = '5ae9fcc8cf7e2-';

/*
 *  Generate a Maps javascript object, set's defaults
 *  seperate from the Javascript API's defaults.
 *  If an `id` element is not passed it will be generated
 *  in the form of 'map_'.uniqid().
 */
function do_gmaps_maps_5ae9fcc8cf7e2($attrs, $content = null) {
    $type = isset($attrs["type"]) ? $attrs["type"] : 'roadmap';
    $lat = isset($attrs["lat"]) ? $attrs["lat"] : 0;
    $lng = isset($attrs["lng"]) ? $attrs["lng"] : 0;
    $zoom = isset($attrs["zoom"])  ? $attrs["zoom"] : 6;
    $defaultUi = isset($attrs["disable-default-ui"]) ? $attrs["disable-default-ui"] : 'false';
    $draggable = isset($attrs["draggable"]) ? $attrs["draggable"] : 'false';
    $streetview_control = isset($attrs["streetview-control"]) ? $attrs["streeview-control"] : 'false';
    $scale_control = isset($attrs["scalable"]) ? $attrs["scalable"] : 'true';
    $shortcuts = isset($attrs["shortcuts"]) ? $attrs["shortcuts"] : 'true';
    $type_control = isset($attrs["type-control"]) ? $attrs["type-control"] : 'false';
    $disable_click_zoom = isset($attrs["disable-click-zoom"]) ? $attrs["disable-click-zoom"] : 'false';
    $clickable_icons = isset($attrs["clickable-icons"]) ? $attrs["clickable-icons"] : 'false';
    $fullscreen_control = isset($attrs["fullscreen-control"]) ? $attrs["fullscreen-conrol"] : 'false';
    $max_zoom = isset($attrs["max-zoom"]) ? $attrs["max-zoom"] : 16;
    $min_zoom = isset($attrs["min-zoom"]) ? $attrs["min-zoom"] : 0;
    $pan_control = isset($attrs["pan-control"]) ? $attrs["pan-control"] : 'true';
    $rotate_control = isset($attrs["rotate-control"]) ? $attrs["rotate-control"] : 'true';
    $scroll_wheel = isset($attrs["scroll-wheel"]) ? $attrs["scroll-wheel"] : 'true';
    $mapId = isset($attrs["id"]) ? $attrs["id"] : 'map_'.uniqid();
    $func_id = uniqid();

    // Generate map element.
    $out = "<div ";

    // Optionally inline width & height styles.
    if(isset($attrs["height"]) || isset($attrs["width"])) {
      $out = $out."style='";
      if(isset($attrs["height"])) {
        $out = $out."height:".$attrs["height"]."px;";
      }
      if(isset($attrs["width"])) {
        $out = $out."width:".$attrs["width"]."px;";
      }
      $out = $out."' ";
    }

    // Generate the map and push it onto the global maps array.
    array_push($GLOBALS[$prefix."gmaps-maps"], "
    var $mapId = new google.maps.Map(document.getElementById('$mapId'), {
              center: {lat: $lat, lng: $lng},
              zoom: $zoom,
              mapTypeId: \"$type\",
              disableDefaultUi: $defaultUi,
              draggable: $draggable,
              streetViewControl: $streetview_control,
              scaleControl: $scale_control,
              keyboardShortcuts: $shortcuts,
              mapTypeControl: $type_control,
              disableDoubleClickZoom: $disable_click_zoom,
              clickableIcons: $clickable_icons,
              fullscreenControl: $fullscreen_control,
              maxZoom: $max_zoom,
              minZoom: $min_zoom,
              panControl: $pan_control,
              rotateControl: $rotate_control,
              scrollWheel: $scroll_wheel
          });");

    // Create a global variable to store our map id,
    // so any child element's will know what map they belong too.
    $GLOBALS[$prefix."current-gmaps-map-id"] = $mapId;

    // Parse for nested shortcode.
    if($content) {
        do_shortcode($content);
    }
    unset($GLOBALS[$prefix."current-gmaps-map-id"]);
    return $out."id='$mapId'></div>";
}

/*
 *  Generate a marker Object and add it to are global list of markers.
 *  The `long` and 'lang' attribute are required.
 *  If the `id` attribute is not set one will be generated in they
 *  format 'marker_'.uniqid().
 */
function do_gmaps_marker_5ae9fcc8cf7e2($attrs) {
  if(!isset($attrs["lng"]) || !isset($attrs['lat'])) {
    echo "Google Maps Markers require Long & lang param's";
    return;
  } else {

    if(isset($GLOBALS[$prefix."current-gmaps-map-id"])) {
      $current_map = $GLOBALS[$prefix."current-gmaps-map-id"];
    }

    $title = isset($attrs["title"]) ? $attrs["title"] : "''";
    $draggable = isset($attrs["draggable"]) ? $attrs["draggable"] : 'false';
    $opacity = isset($attrs["opacity"]) ? $attrs["opacity"] : 1.0;
    $visible = isset($attrs["visible"]) ? $attrs["visible"] : 'true';
    $zIndex = isset($attrs["zindex"]) ? $attrs["zindex"] : 0;
    $lat = $attrs["lat"];
    $lng = $attrs["lng"];
    $markerId = isset($attrs["id"]) ? $attrs["id"] : 'marker_'.uniqid();

    $marker = "
    var $markerId = new google.maps.Marker({
      position: {lat: $lat, lng: $lng},
      map: $current_map,
      title: '$title',
      draggable: $draggable,
      opacity: $opacity,
      visible: $visible,
      zIndex: $zIndex
    });";

    array_push($GLOBALS[$prefix."gmaps-markers"], $marker);
  }
}

/*
 *  Generate a Polygone Javascript object.
 *  The `path` attribute is required
 */
function do_gmaps_polygon_5ae9fcc8cf7e2($attrs) {
    if(!isset($attrs["path"])) {
      echo "gmaps-polygon requires the 'path' attribute";
    } else {

      $stroke_color = isset($attrs["stroke-color"]) ? $attrs["stroke-color"] : '#000000';
      $stroke_opacity = isset($attrs["stroke-opacity"]) ? $attrs["stroke-opacity"] : 1.0;
      $stroke_weight = isset($attrs["stroke-weight"]) ? $attrs["Stroke-weight"] : 2;
      $clickable = isset($attrs["clickable"]) ? $attrs["clickable"] : 'false';
      $zindex = isset($attrs["zindex"]) ? $attrs["zindex"] : 2;
      $editable = isset($attrs["editable"]) ? $attrs["editable"] : 'false';
      $visible = isset($attrs["visible"]) ? $attrs["visible"] : 'true';
      $draggable = isset($attrs["draggable"]) ? $attrs["draggable"] : 'true';
      $geodesic = isset($attrs["geodesic"]) ? $attrs["geodesic"] : 'true';
      $fill_color = isset($attrs["fill-color"]) ? $attrs["fill-color"] : '#000000';
      $fill_opacity = isset($attrs["fill-opacity"]) ? $attrs["fill-opacity"] : 0.5;
      $gone_id = isset($attrs["id"]) ? $attrs["id"] : 'polygon_'.uniqid();

      // Generate JavaScript map of points in the provided path.
      $points = array();
      foreach(explode(',', $attrs["path"]) as $point) {
        $arr = explode(':', $point);
        $lat = $arr[0];
        $lang = $arr[1];
        array_push($points, "{lat:".$lat.",lng:".$lang."}");
      }

      $out = "var $gone_id = new google.maps.Polygon({ path:[";
      foreach($points as $key => $point) {
        if($key === sizeof($points)-1) {
          $out = $out.$point."],";
        } else {
          $out = $out.$point.",";
        }
      }

      if(isset($GLOBALS[$prefix."current-gmaps-map-id"])) {
        $out = $out."
            strokeColor: '$stroke_color',
            strokeOpacity: $stroke_opacity,
            strokeWeight: $stroke_weight,
            dragggable: $draggable,
            geodesic: $geodesic,
            visible: $visible,
            zIndex: $zindex,
            editable: $editable,
            clickable: $clickable,
            fillColor: '$fill_color',
            fillOpacity: $fill_opacity
        });
        $gone_id.setMap(".$GLOBALS[$prefix."current-gmaps-map-id"].");";
      }

      array_push($GLOBALS[$prefix."gmaps-polygons"], $out);
    }
}

/*
 * Generate a Polyline JavaScript Object, the  `path` attribute is required.
 */
function do_gmaps_polyline_5ae9fcc8cf7e2($attrs) {
    if(!isset($attrs["path"])) {
      echo "gmaps-polyline requires the 'path' attribute";
    } else {

      $stroke_color = isset($attrs["stroke-color"]) ? $attrs["stroke-color"] : '#000000';
      $stroke_opacity = isset($attrs["stroke-opacity"]) ? $attrs["stroke-opacity"] : 1.0;
      $stroke_weight = isset($attrs["stroke-weight"]) ? $attrs["stroke-weight"] : 2;
      $clickable = isset($attrs["clickable"]) ? $attrs["clickable"] : 'false';
      $zindex = isset($attrs["zindex"]) ? $attrs["zindex"] : 2;
      $editable = isset($attrs["editable"]) ? $attrs["editable"] : 'false';
      $visible = isset($attrs["visible"]) ? $attrs["visible"] : 'true';
      $draggable = isset($attrs["draggable"]) ? $attrs["draggable"] : 'true';
      $geodesic = isset($attrs["geodesic"]) ? $attrs["geodesic"] : 'false';
      $line_id = 'polyline_'.uniqid();
      $points = array();

      foreach(explode(',', $attrs["path"]) as $point) {
        $arr = explode(':', $point);
        $lat = $arr[0];
        $lang = $arr[1];
        array_push($points, "{lat:".$lat.",lng:".$lang."}\n");
      }

      if(sizeof($points) <=1 ) {
        echo "gmaps-polyline requires more than one point";
        return;
      } else {
        $out = "var $line_id = new google.maps.Polyline({
          path:[";
        foreach($points as $key => $point) {
          if($key === sizeof($points)-1) {
            $out = $out.$point."],";
          } else {
            $out = $out.$point.",";
          }
        }
        if(isset($GLOBALS[$prefix."current-gmaps-map-id"])) {
          $out = $out."
              strokeColor: '$stroke_color',
              strokeOpacity: $stroke_opacity,
              strokeWeight: $stroke_weight,
              dragggable: $draggable,
              geodesic: $geodesic,
              visible: $visible,
              zIndex: $zindex,
              editable: $editable,
              clickable: $clickable
          });
          $line_id.setMap(".$GLOBALS[$prefix."current-gmaps-map-id"].");";
          array_push($GLOBALS[$prefix."gmaps-polylines"], $out);
        }
      }
    }
}

/*
 * Generate a InfoWindow JavaScript object.
 * Must set marker_id manually in gmaps-marker and
 * pass the marker id in via the `marker` attribute.
 */
function do_gmaps_info_window_5ae9fcc8cf7e2($attrs, $content = null) {
  $winId = 'info_window_'.uniqid();
  if(!isset($attrs["marker"])) {
    echo "Info window requires the marker attribute";
    return;
  } else {
    $marker = $attrs["marker"];
  }
  if(isset($GLOBALS[$prefix."current-gmaps-map-id"])) {
    $current_map = $GLOBALS[$prefix."current-gmaps-map-id"];
  }

  $out = "
      var $winId = new google.maps.InfoWindow({
        content: `$content`";
      if (isset($attrs["max-width"])) {
        $out = $out.", maxWidth:".$attrs["max-width"];
      }
      $out = $out."});
      $marker.addListener('click', function() {
        $winId.open($current_map, $marker);
      });
  ";

  array_push($GLOBALS[$prefix."gmaps-info-windows"], $out);
}

/*
 * Read the global list of all map objects and generate
 * a function that builds them. Grabs API Key from
 * Settings > General > Gmaps API Key and link to
 * google maps api.
 */
function include_google_maps_5ae9fcc8cf7e2() {

    $maps = implode('', $GLOBALS[$prefix."gmaps-maps"]);
    $markers = implode('', $GLOBALS[$prefix."gmaps-markers"]);
    $polylines = implode('', $GLOBALS[$prefix."gmaps-polylines"]);
    $polygons = implode('', $GLOBALS[$prefix."gmaps-polygons"]);
    $info_windows = implode('', $GLOBALS[$prefix."gmaps-info-windows"]);
    $key = get_option("google_maps_api_key_5ae9fcc8cf7e2");

    if(!$key) {
        echo "<div class='notice notice-error'>Google Maps Require an api key be set in Settings > General</div>";
    }
    $script = "
      function gmaps_init_5ae9fcc8cf7e2() {
              $maps
              $markers
              $polylines
              $polygons
              $info_windows
      }";
    wp_enqueue_script('gmaps-includes-5ae9fcc8cf7e2', "https://maps.googleapis.com/maps/api/js?key=".$key."&callback=gmaps_init_5ae9fcc8cf7e2", array(), "1.0", false);
    wp_add_inline_script("gmaps-includes-5ae9fcc8cf7e2", $script, 'before');
    // Unset Global variables.
    unset($GLOBALS[$prefix."gmaps-maps"]);
    unset($GLOBALS[$prefix."gmaps-markers"]);
    unset($GLOBALS[$prefix."gmaps-polylines"]);
    unset($GLOBALS[$prefix."gmaps-polygons"]);
}

/*
 * Ensure global variables exist and are empty
 */
function create_gmaps_global_vars_5ae9fcc8cf7e2() {
  $GLOBALS[$prefix."gmaps-maps"] = [];
  $GLOBALS[$prefix."gmaps-markers"] = [];
  $GLOBALS[$prefix."gmaps-polylines"] = [];
  $GLOBALS[$prefix."gmaps-polygons"] = [];
  $GLOBALS[$prefix."gmaps-info-windows"] = [];
}

/*
   Callback generate the GoogleMaps Api key input element.
*/
function vstek_generate_gmaps_setting_5ae9fcc8cf7e2() {
  $value = get_option('google_maps_api_key_5ae9fcc8cf7e2');
  echo "<input type='text' name='google_maps_api_key_5ae9fcc8cf7e2' id='google_maps_api_key_5ae9fcc8cf7e2' style='width:25%' value='$value'/>";
}

/*
    Register the GoogleMaps Api key setting to the 'General' settings page.
*/
function gmaps_register_setting_field_5ae9fcc8cf7e2() {
  register_setting(
      'general',
      'google_maps_api_key_5ae9fcc8cf7e2'
  );
  add_settings_field(
      'html_guidelines_message',
      'Google Maps Api Key',
      'vstek_generate_gmaps_setting_5ae9fcc8cf7e2',
      'general'
  );
}

// Add shortcode handlers
add_shortcode('gmaps', 'do_gmaps_maps_5ae9fcc8cf7e2');
add_shortcode('gmaps-marker', 'do_gmaps_marker_5ae9fcc8cf7e2');
add_shortcode('gmaps-polyline', 'do_gmaps_polyline_5ae9fcc8cf7e2');
add_shortcode('gmaps-polygon', 'do_gmaps_polygon_5ae9fcc8cf7e2');
add_shortcode('gmaps-info-window', 'do_gmaps_info_window_5ae9fcc8cf7e2');

// Add request actions.
add_action('wp_head', 'create_gmaps_global_vars_5ae9fcc8cf7e2');
add_action('wp_footer', 'include_google_maps_5ae9fcc8cf7e2');

// Add custom admin settings field.
add_filter('admin_init', 'gmaps_register_setting_field_5ae9fcc8cf7e2');
