<?php
/**
 * Plugin Name: Clemson Sports Ticker
 * Description: A sports ticker displaying Clemson University sports scores
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue scripts and styles
function cst_enqueue_scripts() {
    wp_enqueue_script('react', 'https://unpkg.com/react@17/umd/react.production.min.js', array(), '17.0.0', true);
    wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@17/umd/react-dom.production.min.js', array('react'), '17.0.0', true);
    wp_enqueue_script('babel', 'https://unpkg.com/@babel/standalone/babel.min.js', array(), '7.14.3', true);
    wp_enqueue_script('swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array(), '8.0.0', true);
    wp_enqueue_style('swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), '8.0.0');
    wp_enqueue_script('clemson-sports-ticker', plugin_dir_url(__FILE__) . 'js/clemson-sports-ticker.js', array('react', 'react-dom', 'babel', 'swiper'), '1.0.0', true);
    wp_enqueue_style('clemson-sports-ticker', plugin_dir_url(__FILE__) . 'css/clemson-sports-ticker.css', array('swiper'), '1.0.0');
}
add_action('wp_enqueue_scripts', 'cst_enqueue_scripts');

// Add admin menu
function cst_admin_menu() {
    add_menu_page('Clemson Sports Ticker Settings', 'Sports Ticker', 'manage_options', 'clemson-sports-ticker', 'cst_admin_page', 'dashicons-chart-area');
    add_submenu_page('clemson-sports-ticker', 'Manual Entry', 'Manual Entry', 'manage_options', 'clemson-sports-ticker-manual', 'cst_manual_entry_page');
}
add_action('admin_menu', 'cst_admin_menu');

// Admin page content
function cst_admin_page() {
    // ... (keep the existing implementation)
    ?>
    <div class="wrap">
        <h1>Clemson Sports Ticker</h1>
        <div class="cst-layout"> <!-- Added layout wrapper -->
            <div class="cst-controls">
                <button type="button" id="cst-add-entry" class="button button-secondary">Add Entry</button>
            </div>
            <div class="cst-events-container"> <!-- Added events container -->
                <form method="post">
                    <table class="form-table">
                        <tbody id="cst-entries">
                            <?php foreach ($entries as $index => $entry): ?>
                                <tr class="cst-entry">
                                    <td>
                                        <span class="dashicons dashicons-menu handle"></span>
                                        <select name="entry[<?php echo $index; ?>][sport]" required>
                                            <option value="">Select a sport</option>
                                            <?php foreach ($sports_list as $sport): ?>
                                                <option value="<?php echo esc_attr($sport); ?>" <?php selected($entry['sport'], $sport); ?>><?php echo esc_html($sport); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="date" name="entry[<?php echo $index; ?>][date]" value="<?php echo esc_attr($entry['date']); ?>">
                                        <input type="time" name="entry[<?php echo $index; ?>][time]" value="<?php echo esc_attr($entry['time']); ?>">
                                        <input type="text" name="entry[<?php echo $index; ?>][team1]" value="<?php echo esc_attr($entry['team1']); ?>" placeholder="Team 1" required>
                                        <input type="text" name="entry[<?php echo $index; ?>][score1]" value="<?php echo esc_attr($entry['score1']); ?>" placeholder="Score 1">
                                        <input type="text" name="entry[<?php echo $index; ?>][team2]" value="<?php echo esc_attr($entry['team2']); ?>" placeholder="Team 2" required>
                                        <input type="text" name="entry[<?php echo $index; ?>][score2]" value="<?php echo esc_attr($entry['score2']); ?>" placeholder="Score 2">
                                        <button type="button" class="button button-secondary cst-remove-entry">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <input type="submit" name="cst_save_entries" value="Save Entries" class="button button-primary">
                </form>
            </div>
        </div> <!-- End of layout wrapper -->
    </div>
    <script>
        jQuery(document).ready(function($) {
            var index = <?php echo count($entries); ?>;
            var sportsList = <?php echo json_encode($sports_list); ?>;
            
            $('#cst-entries').sortable({
                handle: '.handle',
                update: function(event, ui) {
                    $('.cst-entry').each(function(i) {
                        $(this).find('select, input').each(function() {
                            var name = $(this).attr('name');
                            var newName = name.replace(/\[(\d+)\]/, '[' + i + ']');
                            $(this).attr('name', newName);
                        });
                    });
                }
            });

            $('#cst-add-entry').click(function() {
                var sportOptions = '<option value="">Select a sport</option>';
                sportsList.forEach(function(sport) {
                    sportOptions += '<option value="' + sport + '">' + sport + '</option>';
                });

                var newRow = '<tr class="cst-entry"><td>' +
                    '<span class="dashicons dashicons-menu handle"></span>' +
                    '<select name="entry[' + index + '][sport]" required>' + sportOptions + '</select>' +
                    '<input type="date" name="entry[' + index + '][date]">' +
                    '<input type="time" name="entry[' + index + '][time]">' +
                    '<input type="text" name="entry[' + index + '][team1]" value="Clemson" placeholder="Team 1" required>' +
                    '<input type="text" name="entry[' + index + '][score1]" placeholder="Score 1">' +
                    '<input type="text" name="entry[' + index + '][team2]" placeholder="Team 2" required>' +
                    '<input type="text" name="entry[' + index + '][score2]" placeholder="Score 2">' +
                    '<button type="button" class="button button-secondary cst-remove-entry">Remove</button>' +
                    '</td></tr>';
                $('#cst-entries').append(newRow);
                index++;
                $('#cst-entries').sortable('refresh');
            });

            $(document).on('click', '.cst-remove-entry', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}

// Define sports list
function cst_get_sports_list() {
    return array(
        'Baseball',
        'Men\'s Basketball',
        'Women\'s Basketball',
        'Men\'s Cross Country',
        'Women\'s Cross Country',
        'Football',
        'Men\'s Golf',
        'Women\'s Golf',
        'Rowing',
        'Men\'s Soccer',
        'Women\'s Soccer',
        'Softball',
        'Men\'s Tennis',
        'Women\'s Tennis',
        'Men\'s Track & Field',
        'Women\'s Track & Field',
        'Volleyball'
    );
}

// Manual entry page
function cst_manual_entry_page() {
    if (isset($_POST['cst_save_entries'])) {
        $entries = array();
        foreach ($_POST['entry'] as $entry) {
            if (!empty($entry['team1']) && !empty($entry['team2'])) {
                $entries[] = $entry;
            }
        }
        update_option('cst_manual_entries', $entries);
        update_option('cst_last_updated', time());
    }

    $entries = get_option('cst_manual_entries', array());
    $sports_list = cst_get_sports_list();
    ?>
    <div class="wrap">
        <h1>Clemson Sports Ticker</h1>
        <form method="post">
            <table class="form-table">
                <tbody id="cst-entries">
                    <?php foreach ($entries as $index => $entry): ?>
                        <tr class="cst-entry">
                            <td>
                                <span class="dashicons dashicons-menu handle"></span>
                                <select name="entry[<?php echo $index; ?>][sport]" required>
                                    <option value="">Select a sport</option>
                                    <?php foreach ($sports_list as $sport): ?>
                                        <option value="<?php echo esc_attr($sport); ?>" <?php selected($entry['sport'], $sport); ?>><?php echo esc_html($sport); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="date" name="entry[<?php echo $index; ?>][date]" value="<?php echo esc_attr($entry['date']); ?>">
                                <input type="time" name="entry[<?php echo $index; ?>][time]" value="<?php echo esc_attr($entry['time']); ?>">
                                <input type="text" name="entry[<?php echo $index; ?>][team1]" value="<?php echo esc_attr($entry['team1']); ?>" placeholder="Team 1" required>
                                <input type="text" name="entry[<?php echo $index; ?>][score1]" value="<?php echo esc_attr($entry['score1']); ?>" placeholder="Score 1">
                                <input type="text" name="entry[<?php echo $index; ?>][team2]" value="<?php echo esc_attr($entry['team2']); ?>" placeholder="Team 2" required>
                                <input type="text" name="entry[<?php echo $index; ?>][score2]" value="<?php echo esc_attr($entry['score2']); ?>" placeholder="Score 2">
                                <button type="button" class="button button-secondary cst-remove-entry">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" id="cst-add-entry" class="button button-secondary">Add Entry</button>
            <input type="submit" name="cst_save_entries" value="Save Entries" class="button button-primary">
        </form>
    </div>
    <script>
        jQuery(document).ready(function($) {
            var index = <?php echo count($entries); ?>;
            var sportsList = <?php echo json_encode($sports_list); ?>;
            
            $('#cst-entries').sortable({
                handle: '.handle',
                update: function(event, ui) {
                    $('.cst-entry').each(function(i) {
                        $(this).find('select, input').each(function() {
                            var name = $(this).attr('name');
                            var newName = name.replace(/\[(\d+)\]/, '[' + i + ']');
                            $(this).attr('name', newName);
                        });
                    });
                }
            });

            $('#cst-add-entry').click(function() {
                var sportOptions = '<option value="">Select a sport</option>';
                sportsList.forEach(function(sport) {
                    sportOptions += '<option value="' + sport + '">' + sport + '</option>';
                });

                var newRow = '<tr class="cst-entry"><td>' +
                    '<span class="dashicons dashicons-menu handle"></span>' +
                    '<select name="entry[' + index + '][sport]" required>' + sportOptions + '</select>' +
                    '<input type="date" name="entry[' + index + '][date]">' +
                    '<input type="time" name="entry[' + index + '][time]">' +
                    '<input type="text" name="entry[' + index + '][team1]" value="Clemson" placeholder="Team 1" required>' +
                    '<input type="text" name="entry[' + index + '][score1]" placeholder="Score 1">' +
                    '<input type="text" name="entry[' + index + '][team2]" placeholder="Team 2" required>' +
                    '<input type="text" name="entry[' + index + '][score2]" placeholder="Score 2">' +
                    '<button type="button" class="button button-secondary cst-remove-entry">Remove</button>' +
                    '</td></tr>';
                $('#cst-entries').append(newRow);
                index++;
                $('#cst-entries').sortable('refresh');
            });

            $(document).on('click', '.cst-remove-entry', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}

function cst_add_entry_fields() {
    $index = isset($_POST['index']) ? intval($_POST['index']) : 0;
    cst_render_entry_fields($index);
    wp_die();
}
add_action('wp_ajax_cst_add_entry_fields', 'cst_add_entry_fields');

// Enqueue admin scripts
function cst_enqueue_admin_scripts($hook) {
    if ('sports-ticker_page_clemson-sports-ticker-manual' !== $hook) {
        return;
    }
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'cst_enqueue_admin_scripts');

// Add REST API endpoint for fetching sports data
function cst_register_rest_route() {
    register_rest_route('clemson-sports-ticker/v1', '/sports', array(
        'methods' => 'GET',
        'callback' => 'cst_get_sports_data',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'cst_register_rest_route');

// Callback function for the REST API endpoint
function cst_get_sports_data() {
    $manual_entries = get_option('cst_manual_entries', array());
    $last_updated = get_option('cst_last_updated', 0);

    $formatted_entries = array_map(function($entry, $index) {
        return array(
            'id' => 'manual_' . $index,
            'date' => $entry['date'],
            'time' => !empty($entry['time']) ? $entry['time'] : 'TBA',
            'sport' => $entry['sport'],
            'team1' => !empty($entry['team1']) ? $entry['team1'] : 'Clemson',
            'team2' => $entry['team2'],
            'score1' => $entry['score1'],
            'score2' => $entry['score2'],
        );
    }, $manual_entries, array_keys($manual_entries));

    return array(
        'entries' => $formatted_entries,
        'last_updated' => $last_updated
    );
}

// Elementor widget registration
function register_clemson_sports_ticker_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/class-clemson-sports-ticker-widget.php' );
    $widgets_manager->register( new \Elementor_Clemson_Sports_Ticker_Widget() );
}
add_action( 'elementor/widgets/register', 'register_clemson_sports_ticker_widget' );

// Check if Elementor is active
function cst_is_elementor_active() {
    return did_action( 'elementor/loaded' );
}

// Initialize Elementor widget
function cst_init_elementor_widget() {
    if ( cst_is_elementor_active() ) {
        add_action( 'elementor/widgets/register', 'register_clemson_sports_ticker_widget' );
    }
}
add_action( 'plugins_loaded', 'cst_init_elementor_widget' );