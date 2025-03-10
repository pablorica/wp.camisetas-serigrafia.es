<?php
/**
 * Plugin Name: WooCommerce Color Importer
 * Description: Import RGB color attribute from a CSV file.
 * Version: 1.0
 * Author:      Yoshua Lino
 * Author URI:  https://github.com/yoshualino
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Color_Importer {
    public function __construct() {
        add_action('admin_menu', [$this, 'create_plugin_page']);
        add_action('admin_post_wc_import_colors', [$this, 'handle_file_upload']);
    }

    public function create_plugin_page() {
        add_submenu_page(
            'edit.php?post_type=product',
            'Color Importer',
            'Color Importer',
            'manage_options',
            'wc-color-importer',
            [$this, 'plugin_page_content']
        );
    }

    public function plugin_page_content() {
        ?>
        <div class="wrap">
            <h1><?= __('Color Importer', 'wc-color-importer'); ?></h1>
            <div id="poststuff">
                <div id="post-body" class="columns-2 metabox-holder">
                    <div id="post-body-content">
                        <div class="postbox">
                            <div class="inside">
                                <?php if (isset($_GET['imported'])): ?>
                                    <div class="notice notice-success is-dismissible">
                                        <p><?= __('Colors imported successfully', 'wc-color-importer'); ?></p>
                                    </div>
                                <?php endif; ?>

                                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="wc_import_colors">

                                    <table class="form-table">
                                        <tbody>
                                            <tr class="alternate">
                                                <td width="280">
                                                    <label for="taxonomy">
                                                        <strong><?= __('Select the taxonomy to import the colors', 'wc-color-importer'); ?></strong>
                                                    </label>
                                                </td>
                                                <td>
                                                    <select id="taxonomy" name="taxonomy" required style="width: 100%;max-width: 400px;">
                                                        <?php
                                                        $taxonomies = get_object_taxonomies('product', 'objects');
                                                        foreach ($taxonomies as $taxonomy): ?>
                                                            <option
                                                                value='<?= $taxonomy->name ?>'
                                                                <?= $taxonomy->name === 'pa_color' ? 'selected' : '' ?>
                                                            >
                                                                <?=$taxonomy->label?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="280">
                                                    <label for="meta_field">
                                                        <strong><?= __('Type the name of the meta field to store the color', 'wc-color-importer'); ?></strong>
                                                    </label>
                                                </td>
                                                <td>
                                                    <input type="text" id="meta_field" name="meta_field" value="agni_variation_swatch_field" required style="width: 100%;max-width: 400px;">
                                                </td>
                                            </tr>
                                            <tr class="alternate">
                                                <td width="280">
                                                    <label for="file">
                                                        <strong><?= __('Select the CSV file to import', 'wc-color-importer'); ?></strong>
                                                    </label>
                                                </td>
                                                <td>
                                                    <input type="file" name="csv_file" accept=".csv" required>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <p class="submit">
                                        <input type="submit" class="button button-primary" value="<?= __('Import Colors', 'wc-color-importer'); ?>">
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function handle_file_upload() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (isset($_FILES['csv_file']) && isset($_POST['taxonomy']) && isset($_POST['meta_field'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $taxonomy = sanitize_text_field($_POST['taxonomy']);
            $meta_field = sanitize_text_field($_POST['meta_field']);

            $this->import_colors_from_csv($file, $taxonomy, $meta_field);
        }

        wp_redirect(admin_url('admin.php?page=wc-color-importer&imported=1'));
        exit;
    }

    private function import_colors_from_csv($file, $taxonomy, $meta_field) {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return;
        }

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $color_name = sanitize_text_field($data[0]);
            $rgb = sanitize_text_field($data[1]);

            // Skip empty rows
            if (!$color_name || !$rgb) {
                continue;
            }

            // Skip first row
            if ($data[0] === 'Color' || $data[1] === 'RGB') {
                continue;
            }

            // Check if the term exists
            $term = term_exists($color_name, $taxonomy);
            if (!$term) {
                $term = wp_insert_term($color_name, $taxonomy);
            }

            // Update term meta
            if (!is_wp_error($term)) {
                // Add rgb() to $rgb if it doesn't have it
                if (strpos($rgb, 'rgb') === false) {
                    $rgb = "rgb($rgb)";
                }

                update_term_meta($term['term_id'], $meta_field, $rgb);
            }
        }

        fclose($handle);
    }
}

new WC_Color_Importer();
