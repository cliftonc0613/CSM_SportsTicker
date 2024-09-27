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
}

// Manual entry page
function cst_manual_entry_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cst_manual_entries'])) {
        $entries = $_POST['cst_manual_entries'];
        $sanitized_entries = array();
        foreach ($entries as $entry) {
            $sanitized_entries[] = array(
                'sport' => sanitize_text_field($entry['sport']),
                'date' => sanitize_text_field($entry['date']),
                'time' => sanitize_text_field($entry['time']),
                'team1' => sanitize_text_field($entry['team1']),
                'team2' => sanitize_text_field($entry['team2']),
                'score1' => is_numeric($entry['score1']) ? intval($entry['score1']) : null,
                'score2' => is_numeric($entry['score2']) ? intval($entry['score2']) : null,
            );
        }
        update_option('cst_manual_entries', $sanitized_entries);
        echo '<div class="notice notice-success"><p>Entries saved successfully!</p></div>';
    }

    $manual_entries = get_option('cst_manual_entries', array());
    ?>
    <div class="wrap">
        <h1>Manual Score Entry</h1>
        <form method="post" id="cst-manual-entry-form">
            <div id="cst-entries-container">
                <?php
                if (empty($manual_entries)) {
                    cst_render_entry_fields();
                } else {
                    foreach ($manual_entries as $index => $entry) {
                        cst_render_entry_fields($index, $entry);
                    }
                }
                ?>
            </div>
            <button type="button" id="cst-add-entry" class="button">Add New Entry</button>
            <?php submit_button('Save All Entries'); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var entryIndex = <?php echo count($manual_entries); ?>;

        $('#cst-add-entry').on('click', function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cst_add_entry_fields',
                    index: entryIndex
                },
                success: function(response) {
                    $('#cst-entries-container').append(response);
                    entryIndex++;
                }
            });
        });

        $(document).on('click', '.cst-remove-entry', function() {
            $(this).closest('.cst-entry-fields').remove();
        });
    });
    </script>
    <?php
}

function cst_render_entry_fields($index = 0, $entry = null) {
    $sports = array("Football", "Men's Basketball", "Women's Basketball", "Baseball", "Softball", "Men's Soccer", "Women's Soccer");
    ?>
    <div class="cst-entry-fields" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
        <h3>Entry #<?php echo $index + 1; ?></h3>
        <table class="form-table">
            <tr>
                <th>Sport</th>
                <td>
                    <select name="cst_manual_entries[<?php echo $index; ?>][sport]">
                        <?php foreach ($sports as $sport) : ?>
                            <option value="<?php echo esc_attr($sport); ?>" <?php selected($entry['sport'] ?? '', $sport); ?>><?php echo esc_html($sport); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Date</th>
                <td><input type="date" name="cst_manual_entries[<?php echo $index; ?>][date]" value="<?php echo esc_attr($entry['date'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <th>Time</th>
                <td><input type="time" name="cst_manual_entries[<?php echo $index; ?>][time]" value="<?php echo esc_attr($entry['time'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <th>Team 1</th>
                <td><input type="text" name="cst_manual_entries[<?php echo $index; ?>][team1]" value="<?php echo esc_attr($entry['team1'] ?? 'Clemson'); ?>" required></td>
            </tr>
            <tr>
                <th>Team 2</th>
                <td><input type="text" name="cst_manual_entries[<?php echo $index; ?>][team2]" value="<?php echo esc_attr($entry['team2'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <th>Score 1</th>
                <td><input type="number" name="cst_manual_entries[<?php echo $index; ?>][score1]" value="<?php echo esc_attr($entry['score1'] ?? ''); ?>"></td>
            </tr>
            <tr>
                <th>Score 2</th>
                <td><input type="number" name="cst_manual_entries[<?php echo $index; ?>][score2]" value="<?php echo esc_attr($entry['score2'] ?? ''); ?>"></td>
            </tr>
        </table>
        <button type="button" class="button cst-remove-entry">Remove Entry</button>
    </div>
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
    $scraped_data = cst_scrape_sports_data();
    $manual_entries = get_option('cst_manual_entries', array());

    error_log('Scraped data: ' . print_r($scraped_data, true));
    error_log('Manual entries: ' . print_r($manual_entries, true));

    // Transform manual entries to match the format of scraped data
    $formatted_manual_entries = array_map(function($entry) {
        return array(
            'id' => 'manual_' . uniqid(),
            'date' => $entry['date'],
            'time' => $entry['time'],
            'sport' => $entry['sport'],
            'team1' => $entry['team1'],
            'team2' => $entry['team2'],
            'score1' => $entry['score1'],
            'score2' => $entry['score2'],
        );
    }, $manual_entries);

    error_log('Formatted manual entries: ' . print_r($formatted_manual_entries, true));

    // Combine scraped data and manual entries
    $combined_data = array_merge($scraped_data, $formatted_manual_entries);

    // Sort combined data by date and time
    usort($combined_data, function($a, $b) {
        $date_a = strtotime($a['date'] . ' ' . $a['time']);
        $date_b = strtotime($b['date'] . ' ' . $b['time']);
        return $date_b - $date_a;
    });

    error_log('Combined and sorted data: ' . print_r($combined_data, true));

    return $combined_data;
}

// Implement the scraping function (example implementation)
function cst_scrape_sports_data() {
    // Example data; replace with actual scraping logic
    return array(
        // array(
        //     'id' => uniqid('scraped_'),
        //     'date' => '2023-10-01',
        //     'time' => '15:00',
        //     'sport' => 'Football',
        //     'team1' => 'Clemson',
        //     'team2' => 'Opponent',
        //     'score1' => 28,
        //     'score2' => 14,
        // ),
        // Add more entries as needed
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