<?php

/**
 * Create the options page for the Claim Review Fact Check Plugin
 *
 * @return void
 */
function claim_review_add_menu_to_settings()
{
    add_options_page(
        'Claim Review + EE24 Repository Settings',
        'Claim Review + EE24 Repository Settings',
        'manage_options',
        'fact-check',
        'claim_review_options_page'
    );
}

add_action('admin_menu', 'claim_review_add_menu_to_settings');


/**
 * Scaffolding for the options page on fullfact
 *
 * @return void
 */
function claim_review_options_page()
{
    ?>
    <div class="wrap">
        <h1><?php _e('Claim Review + EE24 Repository - Options/Setup', 'claimreview'); ?></h1>
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
function claim_review_register_settings()
{
    register_setting('claimreviewee24-options', 'cr-organisation-name');
    register_setting('claimreviewee24-options', 'cr-organisation-url');
    register_setting('claimreviewee24-options', 'cr-organisation-alternate-url');
    register_setting('claimreviewee24-options', 'cr-organisation-min-number-rating');
    register_setting('claimreviewee24-options', 'cr-organisation-max-number-rating');
    register_setting('claimreviewee24-options', 'cr-post-types');
    register_setting('claimreviewee24-options', 'ee24-apikey');
    register_setting('claimreviewee24-options', 'ee24-domain');
    register_setting('claimreviewee24-options', 'ee24-country');
    register_setting('claimreviewee24-options', 'ee24-language');
}

add_action('admin_init', 'claim_review_register_settings');


/**
 * Create the settings fields for the Full Fact Claim Review Plugin
 *
 * @return void
 */
function claim_review_create_settings_fields()
{

    add_settings_section(
        'organistion-details',
        __('CLAIM REVIEW - Your Organisation Details', 'claimreview'),
        'claim_review_organisation_settings_callback',
        'fact-check'
    );

    add_settings_field(
        'claim_review_organisation_name-setting-id',
        __('Organisation Name', 'claimreview'),
        'claim_review_text_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-name', 'label_for' => 'Organisation Name')
    );

    add_settings_field(
        'claim_review_organisation_url-setting-id',
        __('Organisation URL', 'claimreview'),
        'claim_review_text_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-url', 'label_for' => 'Organisation URL', 'extra-text' => 'If not present, we will use the home page URL.')
    );

    add_settings_field(
        'claim_review_organisation_alternate_url-setting-id',
        __('Alternate URL', 'claimreview'),
        'claim_review_text_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-alternate-url', 'label_for' => 'Organisation Alternate URL', 'extra-text' => 'An alternate URL for the organisation. Can be a social media account.')
    );

    add_settings_field(
        'claim_review_organisation_max_rating-setting-id',
        __('Max Rating', 'claimreview'),
        'claim_review_number_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-max-number-rating', 'label_for' => 'Numerical Rating Max', 'extra-text' => 'The maximum rating for a number scale. Set this to -1 should you want no ratings.', 'step' => 1)
    );


    add_settings_field(
        'claim_review_organisation_min_rating-setting-id',
        __('Min Rating', 'claimreview'),
        'claim_review_number_field_callback_function',
        'fact-check',
        'organistion-details',
        array('name' => 'cr-organisation-min-number-rating', 'label_for' => 'Numerical Rating Max', 'extra-text' => 'The maximum rating for a number scale. Set this to -1 should you want no ratings.', 'step' => 1)
    );


    add_settings_section(
        'display-settings',
        __('CLAIM REVIEW - Display Settings', 'claimreview'),
        'claim_review_display_settings_callback',
        'fact-check'
    );

    add_settings_field(
        'claim_review_organisation_post_types-setting-id',
        __('Post Types', 'claimreview'),
        'claim_review_post_types_callback_function',
        'fact-check',
        'display-settings'
    );

    add_settings_section(
        'ee24',
        __('EE24 - Connection with the Repository', 'claimreview'),
        'ee24_section_callback',
        'fact-check'
    );

    add_settings_field(
        'ee24_api-key',
        __('API Key', 'claimreview'),
        'claim_review_text_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'ee24-apikey', 'label_for' => 'API Key', 'extra-text' => 'The Repository API Key provided.')
    );

    add_settings_field(
        'ee24_domain',
        __('Domain', 'claimreview'),
        'claim_review_text_field_callback_function',
        'fact-check',
        'ee24',
        array('name' => 'ee24-domain', 'label_for' => 'Domain', 'extra-text' => 'The domain of your organization, as provided by the Repository.')
    );

}

add_action('admin_init', 'claim_review_create_settings_fields');


/**
 * Add to the header of the Claim Review Organisation Detalis Settings
 *
 * @return void
 */
function claim_review_organisation_settings_callback()
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
function claim_review_text_field_callback_function($args)
{
    $option = get_option($args['name']);
    echo '<input type="text" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . $option . '" class="regular-text ltr" />';

    if (array_key_exists('extra-text', $args)) {
        ?>
        <p class="description"><?php _e($args['extra-text'], 'claimreview'); ?></p>
        <?php
    }
}


/**
 * Function to display simple number fields
 *
 * @param array $args All array arguments.
 * @return void
 */
function claim_review_number_field_callback_function($args)
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
function claim_review_display_settings_callback()
{
    ?><p><?php _e('This function controls the display settings of the plugin.', 'claimreview'); ?></p>
    <?php
}

function ee24_section_callback()
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
function claim_review_post_types_callback_function()
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
