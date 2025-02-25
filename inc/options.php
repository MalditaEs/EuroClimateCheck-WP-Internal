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
        'fact-check',
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
            settings_fields('claimreviewee24-options');
            do_settings_sections('fact-check');
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
    register_setting('claimreviewee24-options', 'cr-organisation-name');
    register_setting('claimreviewee24-options', 'cr-organisation-url');
    register_setting('claimreviewee24-options', 'cr-organisation-alternate-url');
    register_setting('claimreviewee24-options', 'cr-organisation-min-number-rating');
    register_setting('claimreviewee24-options', 'cr-organisation-max-number-rating');
    register_setting('claimreviewee24-options', 'cr-post-types');
    register_setting('claimreviewee24-options', 'euroclimatecheck-apikey');
    register_setting('claimreviewee24-options', 'euroclimatecheck-domain');
    register_setting('claimreviewee24-options', 'euroclimatecheck-endpoint');
    register_setting('claimreviewee24-options', 'euroclimatecheck-country');
    register_setting('claimreviewee24-options', 'euroclimatecheck-language');
    register_setting('claimreviewee24-options', 'euroclimatecheck-compat');
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
        'organistion-details',
        __('CLAIM REVIEW - Your Organisation Details', 'claimreview'),
        'euroclimatecheck_organisation_settings_callback',
        'fact-check'
    );

    add_settings_field(
        'euroclimatecheck_organisation_name-setting-id',
        __('Organisation Name', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-name', 'label_for' => 'Organisation Name')
    );

    add_settings_field(
        'euroclimatecheck_organisation_url-setting-id',
        __('Organisation URL', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-url', 'label_for' => 'Organisation URL', 'extra-text' => 'If not present, we will use the home page URL.')
    );

    add_settings_field(
        'euroclimatecheck_organisation_alternate_url-setting-id',
        __('Alternate URL', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-alternate-url', 'label_for' => 'Organisation Alternate URL', 'extra-text' => 'An alternate URL for the organisation. Can be a social media account.')
    );

    add_settings_field(
        'euroclimatecheck_organisation_max_rating-setting-id',
        __('Max Rating', 'claimreview'),
        'euroclimatecheck_number_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-max-number-rating', 'label_for' => 'Numerical Rating Max', 'extra-text' => 'The maximum rating for a number scale. Set this to -1 should you want no ratings.', 'step' => 1)
    );


    add_settings_field(
        'euroclimatecheck_organisation_min_rating-setting-id',
        __('Min Rating', 'claimreview'),
        'euroclimatecheck_number_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-min-number-rating', 'label_for' => 'Numerical Rating Max', 'extra-text' => 'The maximum rating for a number scale. Set this to -1 should you want no ratings.', 'step' => 1)
    );


    add_settings_section(
        'display-settings',
        __('CLAIM REVIEW - Display Settings', 'claimreview'),
        'euroclimatecheck_display_settings_callback',
        'fact-check'
    );

    add_settings_field(
        'euroclimatecheck_organisation_post_types-setting-id',
        __('Post Types', 'claimreview'),
        'euroclimatecheck_post_types_callback_function',
        'fact-check',
        'display-settings'
    );

    add_settings_section(
        'ee24',
        __('EE24 - Connection with the Repository', 'claimreview'),
        'euroclimatecheck_section_callback',
        'fact-check'
    );

    add_settings_field(
        'euroclimatecheck_api-key',
        __('API Key', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'euroclimatecheck-apikey', 'label_for' => 'API Key', 'extra-text' => 'The Repository API Key provided.')
    );

    add_settings_field(
        'euroclimatecheck_domain',
        __('Domain', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'euroclimatecheck-domain', 'label_for' => 'Domain', 'extra-text' => 'The domain of your organization, as provided by the Repository.')
    );

    add_settings_field(
        'euroclimatecheck_endpoint',
        __('EE24 Repository endpoint', 'claimreview'),
        'euroclimatecheck_text_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'euroclimatecheck-endpoint', 'label_for' => 'EE24 Repository endpoint', 'extra-text' => 'Endpoint of the repository.')
    );

    add_settings_field(
        'euroclimatecheck-country',
        __('Country of Organization', 'claimreview'),
        'euroclimatecheck_select_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'euroclimatecheck-country', 'label_for' => 'Country of the organization', 'extra-text' => 'The country of your organization.', 'values' => countries()));


    add_settings_field(
        'euroclimatecheck-language',
        __('Language of Organization', 'claimreview'),
        'euroclimatecheck_select_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'euroclimatecheck-language', 'label_for' => 'Language of the organization', 'extra-text' => 'The default language of your organization.', 'values' => languages()));

    add_settings_field(
        'euroclimatecheck-compat',
        __('Enable compat mode', 'claimreview'),
        'euroclimatecheck_compat_mode',
        'fact-check',
        'ee24');
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

        <input type="checkbox" id="ee24-compat" name="ee24-compat"
               value="1" <?php checked(true, $option, true); ?> /> <?php echo "Compat mode can help if you're having design/UI issues" ?><br/>
        <?php
}

function euroclimatecheck_section_callback()
{
    ?>
    <p><?php _e('Here you can manage the connection data between your website and the EE24 Repository.', 'claimreview'); ?></p>
    <?php
}

/**
 * Function to display the post type display settings
 *
 * @return void
 */
function euroclimatecheck_post_types_callback_function()
{
    $option = get_option('cr-post-types');

    if (!$option) {
        $option = array(
            'cr-showonpost' => true,
            'cr-showonpage' => true
        );
    }

    $posttypeargs = array(
        'public' => true,
    );

    $post_types = get_post_types($posttypeargs, 'objects');

    foreach ($post_types as $post_type) {

        $string = 'cr-showon' . $post_type->name;

        if (array_key_exists($string, $option)) {
            $ticked = true;
        } else {
            $ticked = false;
        }
        ?>
        <input type="checkbox" id="<?php echo $string; ?>" name="cr-post-types[<?php echo $string ?>]"
               value="true" <?php checked(true, $ticked, true); ?> /> <?php echo $post_type->label; ?><br/>
        <?php
    }
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
