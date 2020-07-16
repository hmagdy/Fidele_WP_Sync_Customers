<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link
 * @since      1.0.0
 *
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/admin/partials
 */

if (isset($_GET['message']) && isset($_GET['type']) && $_GET['type'] == 'error') {
    echo "<div class='alert alert-danger' role='alert'>" . $_GET['message'] . "</div>";
}

if (isset($_GET['message']) && isset($_GET['type']) && $_GET['type'] == 'success') {
    echo "<div class='alert alert-success' role='alert'>" . $_GET['message'] . "</div>";
}

$nds_add_meta_nonce = $nds_add_meta_nonce = wp_create_nonce( 'nds_add_user_meta_form_nonce' );

global $wpdb;

$credentials = (array) $wpdb->get_row ( "SELECT * FROM $this->fidele_table_name LIMIT 1" );
$email = $credentials['email'] ?? '';
$password = $credentials['password'] ?? '';
?>

<div class="container">
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#settings">Settings</button>

    <div id="settings" class="collapse <?php if (empty($credentials)) echo "show"; ?>">
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" value="<?php echo $email ?>" class="form-control" name="email" id="email" aria-describedby="emailHelp" placeholder="Enter Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" value="<?php echo $password ?>" class="form-control" name="password" id="password" placeholder="Password" required>
            </div>

            <input type="hidden" name="nds_add_user_meta_nonce" value="<?php echo $nds_add_meta_nonce ?>" />

            <input name='action' type="hidden" value='save_settings_action'>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
    <hr/ >
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
        <input type="hidden" name="nds_add_user_meta_nonce" value="<?php echo $nds_add_meta_nonce ?>" />
        <input name='action' type="hidden" value='sync_customers_action'>
        <button type="submit" name="sync" class="btn btn-primary">Sync customers</button>
    </form>
</div>
