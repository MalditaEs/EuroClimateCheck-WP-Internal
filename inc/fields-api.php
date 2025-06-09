<?php

/**
 * EuroClimateCheck Fields API handler
 * Manages fetching and storing dynamic field values from the API
 */

class EuroClimateCheckFieldsAPI {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'euroclimatecheck_values';
    }
    
    /**
     * Create the database table for storing API field values
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            field_name varchar(100) NOT NULL,
            field_values longtext NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY field_name (field_name)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Fetch field values from the API endpoint
     */
    public function fetch_fields_from_api() {
        $endpoint = get_option('euroclimatecheck-endpoint');
        if (!$endpoint) {
            throw new Exception('EuroClimateCheck endpoint not configured');
        }
        
        // Remove trailing slash and append /fields
        $endpoint = rtrim($endpoint, '/') . '/fields';
        
        $headers = [
            'X-API-KEY' => get_option('euroclimatecheck-apikey'),
            'X-DOMAIN' => get_option('euroclimatecheck-domain'),
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $headers['X-API-KEY'],
                'X-DOMAIN: ' . $headers['X-DOMAIN'],
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($statusCode !== 200) {
            if (!$response) {
                throw new Exception('API request failed with status ' . $statusCode);
            }
            $json = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['message'])) {
                $message = is_array($json['message']) ? implode(', ', $json['message']) : $json['message'];
                throw new Exception('API error: ' . $message);
            } else {
                throw new Exception('API request failed with status ' . $statusCode);
            }
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }
        
        return $data;
    }
    
    /**
     * Store field values in the database
     */
    public function store_field_values($fields_data) {
        global $wpdb;
        
        foreach ($fields_data as $field_name => $field_values) {
            $wpdb->replace(
                $this->table_name,
                [
                    'field_name' => $field_name,
                    'field_values' => json_encode($field_values)
                ],
                ['%s', '%s']
            );
        }
    }
    
    /**
     * Get field values from the database
     */
    public function get_field_values($field_name = null) {
        global $wpdb;
        
        if ($field_name) {
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT field_values FROM {$this->table_name} WHERE field_name = %s",
                $field_name
            ));
            
            return $result ? json_decode($result, true) : null;
        } else {
            $results = $wpdb->get_results(
                "SELECT field_name, field_values FROM {$this->table_name}",
                ARRAY_A
            );
            
            $fields = [];
            foreach ($results as $row) {
                $fields[$row['field_name']] = json_decode($row['field_values'], true);
            }
            
            return $fields;
        }
    }
    
    /**
     * Refresh all field values from the API
     */
    public function refresh_fields() {
        try {
            $fields_data = $this->fetch_fields_from_api();
            $this->store_field_values($fields_data);
            return true;
        } catch (Exception $e) {
            error_log('EuroClimateCheck Fields API Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if we have any stored field values
     */
    public function has_stored_values() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        return $count > 0;
    }
    
    /**
     * Get the last update time
     */
    public function get_last_update_time() {
        global $wpdb;
        return $wpdb->get_var("SELECT MAX(last_updated) FROM {$this->table_name}");
    }
}

/**
 * Initialize the fields API on plugin activation
 */
function euroclimatecheck_activate() {
    $fields_api = new EuroClimateCheckFieldsAPI();
    $fields_api->create_table();
    
    // Try to fetch initial values if API is configured
    $endpoint = get_option('euroclimatecheck-endpoint');
    $apikey = get_option('euroclimatecheck-apikey');
    $domain = get_option('euroclimatecheck-domain');
    
    if ($endpoint && $apikey && $domain) {
        try {
            $fields_api->refresh_fields();
        } catch (Exception $e) {
            // Log the error but don't fail activation
            error_log('EuroClimateCheck: Could not fetch initial field values: ' . $e->getMessage());
        }
    }
}

/**
 * Handle plugin upgrades
 */
function euroclimatecheck_upgrade() {
    $fields_api = new EuroClimateCheckFieldsAPI();
    $fields_api->create_table();
    
    // If we don't have stored values yet, try to fetch them
    if (!$fields_api->has_stored_values()) {
        $endpoint = get_option('euroclimatecheck-endpoint');
        $apikey = get_option('euroclimatecheck-apikey');
        $domain = get_option('euroclimatecheck-domain');
        
        if ($endpoint && $apikey && $domain) {
            try {
                $fields_api->refresh_fields();
            } catch (Exception $e) {
                error_log('EuroClimateCheck: Could not fetch field values during upgrade: ' . $e->getMessage());
            }
        }
    }
}

// Hook into plugin activation
register_activation_hook(EUROCLIMATECHECK_PLUGIN_PATH . '/euroclimatecheck-plugin.php', 'euroclimatecheck_activate');

// Check for upgrades on admin_init
add_action('admin_init', function() {
    $current_version = get_option('euroclimatecheck_version', '0');
    
    // Get plugin version from main plugin file
    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $plugin_data = get_plugin_data(EUROCLIMATECHECK_PLUGIN_PATH . '/euroclimatecheck-plugin.php');
    $plugin_version = $plugin_data['Version'];
    
    if (version_compare($current_version, $plugin_version, '<')) {
        euroclimatecheck_upgrade();
        update_option('euroclimatecheck_version', $plugin_version);
    }
});