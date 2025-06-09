<?php

/**
 * Create the options page for the Claim Review Fact Check Plugin
 *
 * @return void
 */
function euroclimatecheck_add_menu_to_settings()
{
    add_options_page(
        'EuroClimateCheck Repository Settings',
        'EuroClimateCheck Repository Settings',
        'manage_options',
        'euroclimatecheck',
        'euroclimatecheck_options_page'
    );
}

add_action('admin_menu', 'euroclimatecheck_add_menu_to_settings');


/**
 * Scaffolding for the options page on fullfact
 *
 * @return void
 */
function euroclimatecheck_options_page()
{
    ?>
    <div class="wrap">
        <h1><?php _e('EuroClimateCheck Repository - Options/Setup', 'claimreview'); ?></h1>
        <form method="post" action="options.php">
            <?php
            // This prints out all hidden setting fields
            settings_fields('claimrevieweuroclimatecheck-options');
            do_settings_sections('euroclimatecheck');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}


/**
 * All settings for the Full Fact Page
 *
 * @return void
 */
function euroclimatecheck_register_settings()
{
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-apikey');
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-domain');
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-endpoint');
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-country');
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-language');
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-compat');
    register_setting('claimrevieweuroclimatecheck-options', 'euroclimatecheck-enabled-post-types', array(
        'type' => 'array',
        'default' => array('post')
    ));
}

add_action('admin_init', 'euroclimatecheck_register_settings');


/**
 * Create the settings fields for the Full Fact Claim Review Plugin
 *
 * @return void
 */
function euroclimatecheck_create_settings_fields()
{

    add_settings_section(
        'euroclimatecheck',
        __('EuroClimateCheck - Connection with the Repository', 'claimreview'),
        'euroclimatecheck_section_callback',
        'euroclimatecheck'
    );

    add_settings_field(
        'euroclimatecheck_api-key',
        __('API Key', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'euroclimatecheck',
        'euroclimatecheck',
        array('name' => 'euroclimatecheck-apikey', 'label_for' => 'API Key', 'extra-text' => 'The Repository API Key provided.')
    );

    add_settings_field(
        'euroclimatecheck_domain',
        __('Domain', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'euroclimatecheck',
        'euroclimatecheck',
        array('name' => 'euroclimatecheck-domain', 'label_for' => 'Domain', 'extra-text' => 'The domain of your organization, as provided by the Repository.')
    );

    add_settings_field(
        'euroclimatecheck_endpoint',
        __('euroclimatecheck Repository endpoint', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'euroclimatecheck',
        'euroclimatecheck',
        array('name' => 'euroclimatecheck-endpoint', 'label_for' => 'euroclimatecheck Repository endpoint', 'extra-text' => 'Endpoint of the repository.')
    );

    add_settings_field(
        'euroclimatecheck-country',
        __('Country of Organization', 'claimreview'),
        'euroclimatecheck_select_field_callback_function',
        'euroclimatecheck',
        'euroclimatecheck',
        array('name' => 'euroclimatecheck-country', 'label_for' => 'Country of the organization', 'extra-text' => 'The country of your organization.', 'values' => countries()));


    add_settings_field(
        'euroclimatecheck-language',
        __('Language of Organization', 'claimreview'),
        'euroclimatecheck_select_field_callback_function',
        'euroclimatecheck',
        'euroclimatecheck',
        array('name' => 'euroclimatecheck-language', 'label_for' => 'Language of the organization', 'extra-text' => 'The default language of your organization.', 'values' => languages()));

    add_settings_section(
        'euroclimatecheck_post_types',
        __('EuroClimateCheck - Post Type Configuration', 'claimreview'),
        'euroclimatecheck_post_types_section_callback',
        'euroclimatecheck'
    );

    add_settings_field(
        'euroclimatecheck_enabled_post_types',
        __('Enable ClaimBox in Post Types', 'claimreview'),
        'euroclimatecheck_post_types_callback',
        'euroclimatecheck',
        'euroclimatecheck_post_types'
    );

    add_settings_section(
        'euroclimatecheck_fields_api',
        __('EuroClimateCheck - Dynamic Field Values', 'claimreview'),
        'euroclimatecheck_fields_api_section_callback',
        'euroclimatecheck'
    );

    add_settings_field(
        'euroclimatecheck_refresh_fields',
        __('Field Values Management', 'claimreview'),
        'euroclimatecheck_refresh_fields_callback',
        'euroclimatecheck',
        'euroclimatecheck_fields_api'
    );
}

add_action('admin_init', 'euroclimatecheck_create_settings_fields');


/**
 * Add to the header of the Claim Review Organisation Detalis Settings
 *
 * @return void
 */
function euroclimatecheck_organisation_settings_callback()
{
    ?><p><?php _e('Put details of your organisation here, these will be used on all articles', 'claimreview'); ?></p>
    <?php
}


/**
 * Function to display simple text fields
 *
 * @param array $args All array arguments.
 * @return void
 */
function euroclimatecheck_text_field_callback_function($args)
{
    $option = get_option($args['name']);
    echo '<input type="text" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . $option . '" class="regular-text ltr" />';

    if (array_key_exists('extra-text', $args)) {
        ?>
        <p class="description"><?php _e($args['extra-text'], 'claimreview'); ?></p>
        <?php
    }
}

function euroclimatecheck_select_field_callback_function($args)
{
    $option = get_option( $args['name'] );
    echo '<select id="' . $args['name'] . '" name="' . $args['name'] . '" required="">';

    foreach ( $args['values'] as $value => $name ) {
        echo '<option value="' . $value . '"' . selected( $value, $option, false ) . '>' . $name . '</option>';
    }

    echo '</select>';
}

/**
 * Function to display simple number fields
 *
 * @param array $args All array arguments.
 * @return void
 */
function euroclimatecheck_number_field_callback_function($args)
{
    $option = get_option($args['name']);
    echo '<input type="number" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . $option . '" step="' . $args['step'] . '" class="regular-text ltr" />';

    if (array_key_exists('extra-text', $args)) {
        ?>
        <p class="description"><?php _e($args['extra-text'], 'claimreview'); ?></p>
        <?php
    }
}


/**
 * Add the header to the display settings callback
 *
 * @return void
 */
function euroclimatecheck_display_settings_callback()
{
    ?><p><?php _e('This function controls the display settings of the plugin.', 'claimreview'); ?></p>
    <?php
}

function euroclimatecheck_compat_mode()
{
    $option = get_option('euroclimatecheck-compat');

    ?>

        <input type="checkbox" id="euroclimatecheck-compat" name="euroclimatecheck-compat"
               value="0" <?php checked(true, $option, true); ?> /> <?php echo "Compat mode can help if you're having design/UI issues" ?><br/>
        <?php
}

function euroclimatecheck_section_callback()
{
    ?>
    <p><?php _e('Here you can manage the connection data between your website and the EuroClimateCheck Repository.', 'claimreview'); ?></p>
    <?php
}

function countries()
{
    return [
        'AL' => 'Albania',
        'AD' => 'Andorra',
        'AM' => 'Armenia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BE' => 'Belgium',
        'BA' => 'Bosnia And Herzegovina',
        'BG' => 'Bulgaria',
        'HR' => 'Croatia',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'EE' => 'Estonia',
        'FI' => 'Finland',
        'FR' => 'France',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GR' => 'Greece',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'LV' => 'Latvia',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MT' => 'Malta',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'ME' => 'Montenegro',
        'NL' => 'Netherlands',
        'MK' => 'North Macedonia',
        'NO' => 'Norway',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'RO' => 'Romania',
        'SM' => 'San Marino',
        'RS' => 'Serbia',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'ES' => 'Spain',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'TR' => 'Turkey',
        'UA' => 'Ukraine',
        'GB' => 'United Kingdom',
        'XK' => 'Kosovo',
        'BY' => 'Belarus',
        'RU' => 'Russia',
        'OTHER' => 'Other',
    ];
}

function languages()
{
    return [
        'SQ' => 'Albanian',
        'HY' => 'Armenian',
        'AZ' => 'Azerbaijani',
        'BE' => 'Belarusian',
        'BS' => 'Bosnian',
        'BG' => 'Bulgarian',
        'CA' => 'Catalan',
        'HR' => 'Croatian',
        'CS' => 'Czech',
        'DA' => 'Danish',
        'NL' => 'Dutch',
        'EN' => 'English',
        'ET' => 'Estonian',
        'FI' => 'Finnish',
        'FR' => 'French',
        'GL' => 'Galician',
        'KA' => 'Georgian',
        'DE' => 'German',
        'EL' => 'Greek',
        'HU' => 'Hungarian',
        'IS' => 'Icelandic',
        'GA' => 'Irish',
        'IT' => 'Italian',
        'LV' => 'Latvian',
        'LT' => 'Lithuanian',
        'LB' => 'Luxembourgish',
        'MK' => 'Macedonian',
        'MT' => 'Maltese',
        'MO' => 'Montenegrin',
        'NO' => 'Norwegian',
        'PL' => 'Polish',
        'PT' => 'Portuguese',
        'RO' => 'Romanian',
        'RU' => 'Russian',
        'SR' => 'Serbian',
        'SK' => 'Slovak',
        'SL' => 'Slovene',
        'ES' => 'Spanish',
        'SV' => 'Swedish',
        'TR' => 'Turkish',
        'UK' => 'Ukrainian',
        'EU' => 'Basque',
        'OTHER' => 'Other',
    ];
}

function euroclimatecheck_post_types_section_callback()
{
    ?>
    <p><?php _e('Configure which post types should display the ClaimBox metabox.', 'claimreview'); ?></p>
    <?php
}

function euroclimatecheck_post_types_callback()
{
    $enabled_post_types = get_option('euroclimatecheck-enabled-post-types', array('post'));
    $custom_post_types = get_post_types(array(
        'public' => true,
        '_builtin' => false
    ), 'objects');

    // Add default post type
    ?>
    <label style="display: block; margin-bottom: 5px;">
        <input type="checkbox" 
               name="euroclimatecheck-enabled-post-types[]" 
               value="post"
               <?php checked(in_array('post', $enabled_post_types)); ?>>
        <?php echo esc_html(get_post_type_object('post')->labels->singular_name); ?>
    </label>
    <?php

    if (empty($custom_post_types)) {
        echo '<p>' . __('No custom post types found.', 'claimreview') . '</p>';
        return;
    }

    foreach ($custom_post_types as $post_type) {
        ?>
        <label style="display: block; margin-bottom: 5px;">
            <input type="checkbox" 
                   name="euroclimatecheck-enabled-post-types[]" 
                   value="<?php echo esc_attr($post_type->name); ?>"
                   <?php checked(in_array($post_type->name, $enabled_post_types)); ?>>
            <?php echo esc_html($post_type->labels->singular_name); ?>
        </label>
        <?php
    }
}

function euroclimatecheck_fields_api_section_callback()
{
    ?>
    <p><?php _e('Manage dynamic field values fetched from the EuroClimateCheck API. These values are used to populate form dropdowns instead of hardcoded options.', 'claimreview'); ?></p>
    <?php
}

// Add this new function to handle the refresh action
function euroclimatecheck_handle_refresh_fields() {
    if (isset($_POST['refresh_fields']) && check_admin_referer('euroclimatecheck_refresh_fields', 'euroclimatecheck_refresh_nonce')) {
        $fields_api = new EuroClimateCheckFieldsAPI();
        try {
            $fields_api->refresh_fields();
            add_settings_error(
                'euroclimatecheck_messages',
                'euroclimatecheck_message',
                __('Field values updated successfully!', 'claimreview'),
                'updated'
            );
        } catch (Exception $e) {
            add_settings_error(
                'euroclimatecheck_messages',
                'euroclimatecheck_message',
                sprintf(__('Error updating field values: %s', 'claimreview'), esc_html($e->getMessage())),
                'error'
            );
        }
    }
}
add_action('admin_init', 'euroclimatecheck_handle_refresh_fields');

function euroclimatecheck_refresh_fields_callback()
{
    $fields_api = new EuroClimateCheckFieldsAPI();
    $has_values = $fields_api->has_stored_values();
    $last_update = $fields_api->get_last_update_time();
    
    // Display any messages
    settings_errors('euroclimatecheck_messages');
    
    ?>
    <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
        <h4 style="margin-top: 0;"><?php _e('Status', 'claimreview'); ?></h4>
        
        <?php if ($has_values): ?>
            <p><strong><?php _e('Status:', 'claimreview'); ?></strong> 
                <span style="color: green;"><?php _e('Field values are stored', 'claimreview'); ?></span>
            </p>
            <?php if ($last_update): ?>
                <p><strong><?php _e('Last updated:', 'claimreview'); ?></strong> 
                    <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update))); ?>
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p><strong><?php _e('Status:', 'claimreview'); ?></strong> 
                <span style="color: orange;"><?php _e('No field values stored', 'claimreview'); ?></span>
            </p>
            <p style="color: #666; font-style: italic;">
                <?php _e('Click "Refresh Field Values" to fetch the latest values from the API.', 'claimreview'); ?>
            </p>
        <?php endif; ?>
        
        <form method="post" action="">
            <?php wp_nonce_field('euroclimatecheck_refresh_fields', 'euroclimatecheck_refresh_nonce'); ?>
            <input type="submit" 
                   name="refresh_fields" 
                   class="button button-secondary" 
                   value="<?php esc_attr_e('Refresh Field Values', 'claimreview'); ?>"
                   <?php echo (!get_option('euroclimatecheck-endpoint') || !get_option('euroclimatecheck-apikey') || !get_option('euroclimatecheck-domain')) ? 'disabled title="' . esc_attr__('Please configure API settings first', 'claimreview') . '"' : ''; ?>
            />
            <p class="description">
                <?php _e('Fetch the latest field values from the EuroClimateCheck API. This will update all dropdown options in the form.', 'claimreview'); ?>
            </p>
        </form>
        
        <?php if (!get_option('euroclimatecheck-endpoint') || !get_option('euroclimatecheck-apikey') || !get_option('euroclimatecheck-domain')): ?>
            <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px;">
                <strong><?php _e('Note:', 'claimreview'); ?></strong>
                <?php _e('Please configure the API Key, Domain, and Endpoint settings above before refreshing field values.', 'claimreview'); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
