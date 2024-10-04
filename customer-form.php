<?php
/*
Plugin Name: Customer Form
Description: Plugin untuk mengumpulkan data calon customer dan menampilkan data di admin.
Version: 1.0
Author: Nunu
*/

// Fungsi untuk mendaftarkan tabel di database saat plugin diaktifkan
$plugin_table_name = 'customer_forms';
function cf_install() {
    global $wpdb, $plugin_table_name;
    $table_name = $wpdb->prefix . $plugin_table_name;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(15) NOT NULL,
        birthdate date NOT NULL,
        address text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cf_install');

function my_enqueue_scripts() {
    wp_enqueue_style('my-style', plugin_dir_url(__FILE__) . '/bootstrap/css/bootstrap.min.css');
    wp_enqueue_style('my-script', plugin_dir_url(__FILE__) . '/bootstrap/js/bootstrap.min.js');
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');
add_action('admin_enqueue_scripts', 'my_enqueue_scripts');


function cf_customer_frontend() {
    ob_start();
    ?>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <input type="hidden" name="action" value="cf_post_customer">
        <div class="mb-2">
            <label for="name">Name:</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-2">
            <label for="email">Email:</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-2">
            <label for="phone">Phone:</label>
            <input type="text" class="form-control" name="phone" required>
        </div>
        <div class="mb-2">
            <label for="birthdate">Birthdate:</label>
            <input type="date" class="form-control" name="birthdate" required>
        </div>
        <div class="mb-4">
            <label for="address">Address:</label>
            <textarea class="form-control" name="address" required></textarea>
        </div>
        <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('customer_form', 'cf_customer_frontend');

function cf_post_customer() {
    global $plugin_table_name;
    if (isset($_POST['name']) && isset($_POST['email'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . $plugin_table_name;

        $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field($_POST['name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'birthdate' => sanitize_text_field($_POST['birthdate']),
                'address' => sanitize_textarea_field($_POST['address']),
            ]
        );

        if (isset($_POST['from']) && $_POST['from'] == 'admin_page')
            wp_redirect(admin_url('admin.php?page=customer-form'));
        else
            wp_redirect(home_url());
        exit;
    }
}
add_action('admin_post_nopriv_cf_post_customer', 'cf_post_customer');
add_action('admin_post_cf_post_customer', 'cf_post_customer');

function cf_register_menu_page() {
    add_menu_page(
        'Customer Form',
        'Customer Form',
        'manage_options',
        'customer-form',
        'cf_display_customers',
        'dashicons-universal-access',
        70
    );
}
add_action('admin_menu', 'cf_register_menu_page');

function cf_display_customers() {
    global $wpdb, $plugin_table_name;
    $table_name = $wpdb->prefix . $plugin_table_name;
    // Edit data customer
    if (isset($_GET['section']) && $_GET['section'] == 'create') {
        ?>
        <div class="wrap mt-5">
            <h4 class="my-3">Create Customer</h4>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="cf_post_customer">
                <input type="hidden" name="from" value="admin_page">
                <div class="mb-2">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-2">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-2">
                    <label for="phone">Phone:</label>
                    <input type="text" class="form-control" name="phone" required>
                </div>
                <div class="mb-2">
                    <label for="birthdate">Birthdate:</label>
                    <input type="date" class="form-control" name="birthdate" required>
                </div>
                <div class="mb-4">
                    <label for="address">Address:</label>
                    <textarea class="form-control" name="address" required></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100">Submit</button>
            </form>
        </div>
        <?php
    }
    else if (isset($_GET['edit'])) {
        $customer_id = intval($_GET['edit']);
        $customer = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $customer_id");
        ?>
        <div class="wrap mt-5">
            <h4>Edit data <?php echo esc_attr($customer->name); ?></h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input class="form-control" type="text" name="name" value="<?php echo esc_attr($customer->name); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email:</label>
                    <input class="form-control" type="email" name="email" value="<?php echo esc_attr($customer->email); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Phone:</label>
                    <input class="form-control" type="text" name="phone" value="<?php echo esc_attr($customer->phone); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="birthdate">Birthdate:</label>
                    <input class="form-control" type="date" name="birthdate" value="<?php echo esc_attr($customer->birthdate); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Address:</label>
                    <textarea class="form-control" name="address" required><?php echo esc_textarea($customer->address); ?></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100" name="update_customer">Update</button>
            </form>
        </div>
        <?php

    } else {

        $customers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

        echo '<div class="wrap">';
        echo '<div class="d-flex justify-content-between align-items-center mt-5 mb-3">';
        echo '<h4 class="mb-0">Customer Form</h4>
            <a href="?page=customer-form&section=create" class="btn btn-primary btn-sm">Add Customer</a>';
        echo '</div>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Birthdate</th><th>Address</th>
            <th>Actions</th></tr></thead><tbody>';
        foreach ($customers as $key => $customer) {
            echo "<tr>
            <td>"; echo $key+1; echo"</td>
            <td>{$customer->name}</td>
            <td>{$customer->email}</td>
            <td>{$customer->phone}</td>
            <td>{$customer->birthdate}</td>
            <td>{$customer->address}</td>
            <td><a href='?page=customer-form&edit={$customer->id}'>Edit</a> | <a href='?page=customer-form&delete={$customer->id}'>Delete</a></td>
        </tr>";
        }
        echo '</tbody></table></div>';
    }
}

function cf_handle_admin_actions() {
    global $wpdb, $plugin_table_name;
    $table_name = $wpdb->prefix . $plugin_table_name;
    // Hapus data customer
    if (isset($_GET['delete'])) {
        $wpdb->delete($table_name, ['id' => intval($_GET['delete'])]);
        wp_redirect(admin_url('admin.php?page=customer-form'));
        exit;
    }

    // Proses update data customer
    if (isset($_POST['update_customer'])) {
        $customer_id = intval($_GET['edit']);
        $wpdb->update(
            $table_name,
            [
                'name' => sanitize_text_field($_POST['name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'birthdate' => sanitize_text_field($_POST['birthdate']),
                'address' => sanitize_textarea_field($_POST['address']),
            ],
            ['id' => $customer_id]
        );

        wp_redirect(admin_url('admin.php?page=customer-form'));
        exit;
    }
}
add_action('admin_init', 'cf_handle_admin_actions');
