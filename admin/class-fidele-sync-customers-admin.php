<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       test@test.com
 * @since      1.0.0
 *
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/admin
 * @author     hatem <hatem.said50@gmail.com>
 */
class Fidele_Sync_Customers_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /** @var string */
    private $fidele_table_name;

    /** @var string */
    private $customer_table_name;

    /** @var string */
    private $fidele_domain;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->fidele_domain = 'localhost:8000';
		$this->fidele_table_name = 'fidele';
		$this->customer_table_name = 'wp_wc_customer_lookup';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
    {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fidele_Sync_Customers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fidele_Sync_Customers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fidele-sync-customers-admin.css', [], $this->version, 'all' );
        wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', [], $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
    {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fidele_Sync_Customers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fidele_Sync_Customers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery', plugin_dir_url( __FILE__ ) . 'js/jquery-3.3.1.slim.min.js', [], $this->version, false );
		wp_enqueue_script( 'bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', [], $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fidele-sync-customers-admin.js', array( 'jquery' ), $this->version, false );
	}

    public function add_menu()
    {
        add_menu_page( "Fidele sync customers", "Fidele sync customers", 'manage_options', $this->plugin_name, [$this, 'sync']);
    }

    public function sync()
    {
        include(plugin_dir_path(__FILE__) . 'partials/fidele-sync-customers-admin-display.php');
    }

    public function sync_customers_action()
    {
        global $wpdb;

        $type = 'success';
        $message = 'Customers sync succeeded';

        $credentials = (array) $wpdb->get_row ( "SELECT * FROM $this->fidele_table_name LIMIT 1" );

        if (empty($credentials)) {
            $message = 'Please set email and password first';
            $type = 'error';
        }

        try {
            $accessToken = $this->getAccessToken($credentials['email'], $credentials['password']);
            $this->syncCustomers($accessToken);

        } catch (Exception $exception) {
            $type = 'error';
            $message = $exception->getMessage();
        }

        wp_redirect(admin_url('admin.php?page=fidele-sync-customers&message=' . $message . '&type=' . $type));
    }

    /** @throws */
    private function getAccessToken(string $email, string $password): string
    {
        try {
            $response = $this->getCurlResponse($this->fidele_domain . '/api/auth/login', ['email' => $email, 'password' => $password]);
            list($header, $body) = explode("\r\n\r\n", $response, 2);

            $headers = $this->parseRequestHeaders($header);

            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            } else {
                // API failed to retrieve access token
                throw new Exception('Something went wrong');
            }
        } catch (Exception $exception) {
            throw new Exception('Invalid email or password');
        }
    }

    private function parseRequestHeaders(string $header): array
    {
        foreach (explode("\r\n", $header) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers ?? [];
    }

    /** @throws */
    private function syncCustomers(string $accessToken)
    {
        global $wpdb;

        $customersCount = ((array) $wpdb->get_row("SELECT COUNT(*) as count FROM $this->customer_table_name "))['count'];

        $offset = 0;
        $patchSize = 100;
        while ($customersCount > 0 && $offset <= $customersCount) {
            $customers = $wpdb->get_results ( "SELECT customer_id, username, first_name, last_name, email, country, city, state, postcode FROM $this->customer_table_name LIMIT $offset, $patchSize");
            $preparedCustomers = $this->prepareCustomers($customers);

            try {
                $response = $this->getCurlResponse($this->fidele_domain . '/api/user/customers/sync', $preparedCustomers, false, $accessToken);

                $body = json_decode($response, true);

                if (!isset($body['status']) || $body['status'] === 'error') {
                    throw new Exception();
                }
            } catch (Exception $exception) {
                // API may be broken because of unexpected request body
                throw new Exception('Something went wrong');
            }

            $offset += $patchSize;
        }
    }

    private function prepareCustomers(array $customers): array
    {
        $preparedCustomers = [];

        foreach ($customers as $customer) {

            $customer = (array) $customer;

            $preparedCustomer = [
                'name' => $customer['username'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'country_code' => $customer['country'],
                'city' => $customer['city'],
                'state' => $customer['state'],
                'postal_code' => $customer['postcode'],
                'remote_customer_id' => $customer['customer_id']
            ];

            $preparedCustomers[] = $preparedCustomer;
        }

        return $preparedCustomers;
    }

    /** @throws */
    private function getCurlResponse(string $endpoint, array $body, bool $includeHeader = true, $accessToken = null): string
    {
        $payload = json_encode($body);

        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        if ($includeHeader) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if ($accessToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $accessToken"]);
        }

        if (curl_errno($ch)) {
            // mostly if fidele server is down
            throw new Exception('Could not connect to Fidele');
        }

        return curl_exec($ch);
    }

    public function save_settings_action()
    {
        global $wpdb;

        if (!isset($_POST['email'])) {
            $message = 'Email is Required ';
            $type = 'error';
            wp_redirect(admin_url('admin.php?page=fidele-sync-customers&message=' . $message . '&type=' . $type));
        }

        if (!isset($_POST['password'])) {
            $message = 'Password is Required ';
            $type = 'error';
            wp_redirect(admin_url('admin.php?page=fidele-sync-customers&message=' . $message . '&type=' . $type));
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $credentials = (array) $wpdb->get_row ( "SELECT * FROM $this->fidele_table_name LIMIT 1" );

        if (!empty($credentials)) {
            $wpdb->query($wpdb->prepare("UPDATE fidele SET `email` = %s, password = %s", $email, $password));

            $message = 'Email and password updated successfully ';
            $type = 'success';

            return wp_redirect(admin_url('admin.php?page=fidele-sync-customers&message=' . $message . '&type=' . $type));
        }

        $wpdb->insert('fidele', ['email' => $email, 'password' => $password]);

        $message = 'Email and password inserted successfully ';
        $type = 'success';

        return wp_redirect(admin_url('admin.php?page=fidele-sync-customers&message=' . $message . '&type=' . $type));
    }
}
