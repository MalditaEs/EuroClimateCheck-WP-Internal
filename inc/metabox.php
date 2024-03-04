<?php

include_once 'EE24Api.php';

/**
 * Add the Claim Review Metabox to various post types
 *
 * @return void
 */
function claim_review_add_custom_box()
{
    $screens = array();
    $post_types = get_option('cr-post-types') ? get_option('cr-post-types') : array('post', 'page');


    foreach ($post_types as $key => $value) {
        if ($value) {
            $screentoshow = str_replace('cr-showon', '', $key);
            $screens[] = $screentoshow;
        }
    }


    foreach ($screens as $screen) {
        add_meta_box(
            'claim_review_metabox',           // Unique ID
            __('Claim Review Schema', 'claimreview'),  // Box title
            'claim_review_custom_box_html',  // Content callback, must be of type callable
            $screen,                   // Post type
            'normal',
            'high'
        );

        add_meta_box(
            'ee24_repository_metabox',           // Unique ID
            __('EE24 Repository', 'ee24'),  // Box title
            'ee24_custom_box_html',  // Content callback, must be of type callable
            $screen,                   // Post type
            'normal',
            'high'
        );
    }
}

add_action('add_meta_boxes', 'claim_review_add_custom_box');


/**
 * Function to add the claim review meta data
 *
 * @param object $post The post object for the pag we're currently on
 * @return void
 */
function claim_review_custom_box_html($post)
{

    $claims = get_post_meta($post->ID, '_fullfact_all_claims', true);
    $x = 1;
    echo '<div class="allclaims-box">';
    wp_nonce_field(basename(__FILE__), 'claim_review_nonce');
    if ($claims) {
        foreach ($claims as $claim) {

            $claimbox = claim_review_build_claim_box($x, $claim);

            if ($claimbox) {
                echo $claimbox;
                $x++;
            }
        }
    }

    echo claim_review_build_claim_box($x);

    echo '</div>';
    $x++;

    echo '<p class="cr-add-wrapper"><button type="button" class="cr-add-claim-field button button-primary" data-target="' . $x . '">' . __('Add a New Claim', 'claimreview') . '</button></p>';
}

/**
 * Function to add the claim review meta data
 *
 * @param object $post The post object for the pag we're currently on
 * @return void
 */
function ee24_custom_box_html($post)
{

    $ee24Metadata = get_post_meta($post->ID, '_ee24_repository', true);
    wp_nonce_field(basename(__FILE__), 'ee24_nonce');
    echo ee24_build_claim_box($ee24Metadata ?: []);
}

function ee24_build_claim_box($data)
{

    $apikey = get_option('ee24-apikey');
    $domain = get_option('ee24-domain');

    $ee24Box = "<div id='ee24-data' data-endpoint='" . get_option('ee24-endpoint') . "' data-apikey='$apikey' data-domain='$domain'></div>";


    $typeOfPublication = '
<div>
<h3 class="font-bold text-sm mb-2">Type of content</h3>
       <div class="flex flex-row gap-4">
        <div class="flex items-center ps-4 border border-teal-200 rounded dark:border-gray-700">
    <input id="type-political-factcheck" type="radio" value="Factcheck" name="article-type"' . (array_key_exists('type', $data) && $data['type'] === 'Factcheck' ? ' checked ' : '') . 'class="text-teal-500 w-4 h-2 border-gray-300">
    <label for="type-political-factcheck" class="pr-4 w-full py-2 ms-2 text-sm font-medium text-gray-600 dark:text-gray-300">Political Fact-Check</label>
</div>
<div class="flex items-center ps-4 border border-red-200 rounded dark:border-gray-700">
    <input id="type-debunk" type="radio" value="Debunk" name="article-type"' . (array_key_exists('type', $data) && $data['type'] === 'Debunk' ? ' checked ' : '') . 'class="w-4 h-2 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
    <label for="type-debunk" class="pr-4 w-full py-2 ms-2 text-sm font-medium text-gray-600 dark:text-gray-300">Debunk</label>
</div>
<div class="flex items-center ps-4 border border-green-200 rounded dark:border-gray-700">
    <input id="type-prebunk" type="radio" value="Prebunk" name="article-type"' . (array_key_exists('type', $data) && $data['type'] === 'Prebunk' ? ' checked ' : '') . 'class="w-4 h-2 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
    <label for="type-prebunk" class="pr-4 w-full py-2 ms-2 text-sm font-medium text-gray-600 dark:text-gray-300">Prebunk</label>
</div>
<div class="flex items-center ps-4 border border-blue-200 rounded dark:border-gray-700">
    <input id="type-narrative" type="radio" value="Narrative" name="article-type"' . (array_key_exists('type', $data) && $data['type'] === 'Narrative' ? ' checked ' : '') . 'class="w-4 h-2 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
    <label for="type-narrative" class="pr-4 w-full py-2 ms-2 text-sm font-medium text-gray-600 dark:text-gray-300">Narrative report</label>
</div>
</div>
</div>';

    ob_start();

    echo '<div class="relative">
    <label for="headline-english" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Headline (in English)</label>
    <input id="headline-english" class="translatable px-2 h-9 border border-{#d0d0d0} text-gray-900 text-sm rounded-l-lg w-full" name="headline-english"  value="';
    echo $data['headline'] ?? '';
    echo '" autocomplete="off" placeholder="">
    <button type="button" data-source=".wp-block-post-title|#title" class="translate-button absolute bottom-0 right-0 px-3 flex items-center h-9 bg-gray-500 text-white rounded-r-lg"><i class="fa-solid fa-language mr-2"></i> Translate from native</button>
    </div>';

    $headline = ob_get_clean();

    $keywords = '
<div>
    <label for="keywords" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keywords</label>
    <input id="input-keywords" name="keywords" value="' . (isset($data['keywords']) ? implode(",", $data['keywords']) : '') . '" autocomplete="off" placeholder="Add keywords separated by commas">
</div>';

    $availableCountries = array(
        "AF" => "Afghanistan",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BR" => "Brazil",
        "BN" => "Brunei Darussalam",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CR" => "Costa Rica",
        "CI" => "Côte d'Ivoire",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GR" => "Greece",
        "GD" => "Grenada",
        "GT" => "Guatemala",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HN" => "Honduras",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran, Islamic Republic of",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IL" => "Israel",
        "IT" => "Italy",
        "XK" => "Kosovo",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao People's Democratic Republic",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "MX" => "Mexico",
        "FM" => "Micronesia, Federated States of",
        "MD" => "Moldova, Republic of",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PL" => "Poland",
        "PT" => "Portugal",
        "QA" => "Qatar",
        "RO" => "Romania",
        "RU" => "Russia",
        "RW" => "Rwanda",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "VC" => "Saint Vincent and the Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome and Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "SC" => "Seychelles",
        "SS" => "South Sudan",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "ZA" => "South Africa",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "MK" => "Republic of North Macedonia",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syrian Arab Republic",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania, United Republic of",
        "SO" => "Somalia",
        "TH" => "Thailand",
        "TG" => "Togo",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VE" => "Venezuela",
        "VN" => "Vietnam",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe"
    );


    ob_start();

    echo '<div>
        <label for="content-location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Country/Countries identified in claim/article</label>
    <select id="select-content-location" name="content-location[]" multiple placeholder="Select a country..." autocomplete="off">
            <option value="">Select a country...</option>';

    foreach ($availableCountries as $countryCode => $countryName) {
        $isSelected = in_array($countryCode, $data['contentLocation'] ?? []) ? 'selected' : '';
        echo "<option value=\"{$countryCode}\" {$isSelected}>{$countryName}</option>";
    }

    echo '</select></div>';

    $contentLocation = ob_get_clean();

    $availableTopics = [
        "Politics related with the EU",
        "National-Regional context issues",
        "Legislation",
        "Migration",
        "Gender",
        "Religion",
        "Climate",
        "Terrorism",
        "Ukraine war",
        "Israel - Gaza",
        "EU funds",
        "Election integrity",
        "EU institutions",
        "Agenda 2030",
        "Security and defense",
        "Economy",
        "Energy",
        "Covid 19",
        "Others"
    ];

    ob_start(); // Start capturing echo output to buffer

    echo '<div>
<label for="topics" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Topics</label>
    <select id="select-topics" name="topics[]" multiple placeholder="Select a topic..." autocomplete="off">
        <option value="">Select a topic...</option>';
    foreach ($availableTopics as $topic) {
        $isSelected = '';
        if (isset($data['topics']) && is_array($data['topics'])) {
            $isSelected = in_array($topic, $data['topics']) ? 'selected' : '';
        }

        echo "<option value=\"{$topic}\" {$isSelected}>{$topic}</option>";
    }
    echo '</select></div>';

    $topics = ob_get_clean();

    $availableLanguages = [
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

    ob_start();

    echo '<div>
<label for="language" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Language of the article/report</label>
    <select id="language" name="language" placeholder="Select a language..." autocomplete="off">
        <option value="">Select a language...</option>';

    foreach ($availableLanguages as $languageCode => $languageName) {
        $isSelected = ($languageCode == $data['inLanguage'] ?? get_option('ee24-language')) ? 'selected' : '';
        echo "<option value=\"{$languageCode}\" {$isSelected}>{$languageName}</option>";
    }

    echo '</select></div>';

    $language = ob_get_clean();

    $keywordsTopicsRow = '<div class="flex flex-col gap-4"><div class="grid grid-cols-1">' . $typeOfPublication . '</div><div class="grid grid-cols-1">' . $headline . '</div><div class="grid grid-cols-3 gap-4">' . $keywords . $topics . $language . '</div>';
    $keywordsCountriesRow = '<div class="grid grid-cols-2 gap-4">' . $contentLocation . '</div>';

    $commonHeader = '
<div class="flex flex-row items-center gap-4">
        <i class="text-2xl fa-solid fa-circle-check"></i>
    <div>
        <p class="text-sm font-bold text-gray-900">Common fields</p>
        <p class="text-sm text-gray-600">Fields common to all types of publications.</p>
    </div>
</div>';

    $commonRow = '<div><div class="bg-gray-50 my-4 p-4 rounded">' . $commonHeader . '<div class="my-4">' . $keywordsTopicsRow . $keywordsCountriesRow . "</div></div></div>";
    $ee24Box .= $commonRow;

    ob_start();

    echo '<div>
    <label for="political-claim" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Claim reviewed (in native language)</label>
    <input id="political-claim" class="political-claim px-2 h-9 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full" name="political-claim"  value="';

    if (array_key_exists('type', $data) && $data['type'] === 'Factcheck') {
        echo $data['claimreviewedNative'] ?? '';
    }

    echo '" autocomplete="off" placeholder=""></div>';

    $politicalReviewedClaim = ob_get_clean();

    ob_start();

    echo '<div class="relative">
    <label for="political-claim-english" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Claim reviewed (in English)</label>
    <input id="political-claim-english" class="translatable px-2 h-9 border border-{#d0d0d0} text-gray-900 text-sm rounded-l-lg w-full" name="political-claim-english"  value="';
    if (array_key_exists('type', $data) && $data['type'] === 'Factcheck') {
        echo $data['claimReviewed'] ?? '';
    }
    echo '" autocomplete="off" placeholder="">
    <button type="button" data-source="#political-claim" class="translate-button absolute bottom-0 right-0 px-3 flex items-center h-9 bg-gray-500 text-white rounded-r-lg"><i class="fa-solid fa-language mr-2"></i> Translate from native</button>
    </div>';

    $politicalEnglishReviewedClaim = ob_get_clean();

    $politicalNameRelated = '
 <div class="flex flex-row gap-1">
 <div class="flex-grow">    
    <label for="political_author" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Author/Person (Name & Surname)</label>
    <input type="text" id="political_author" name="author" value="' . ($data['itemReviewed']['author'] ?? '') . '" class="h-9 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full">
</div>
</div>';

    $availableParties =
        [
            'Alliance of Liberals and Democrats for Europe Party (ALDE)' => 'Alliance of Liberals and Democrats for Europe Party (ALDE)',
            'European Christian Political Movement (ECPM)' => 'European Christian Political Movement (ECPM)',
            'European Conservatives and Reformists Party (ECR Party)' => 'European Conservatives and Reformists Party (ECR Party)',
            'European Democratic Party (EDP)' => 'European Democratic Party (EDP)',
            'European Free Alliance (EFA)' => 'European Free Alliance (EFA)',
            'European Green Party (EFP)' => 'European Green Party (EFP)',
            'European People\'s Party (EPP)' => 'European People\'s Party (EPP)',
            'Identity and Democracy Party (ID Party)' => 'Identity and Democracy Party (ID Party)',
            'Non-Inscrits (NI)' => 'Non-Inscrits (NI)',
            'Party of the European Left (PEL)' => 'Party of the European Left (PEL)',
            'Party of European Socialists (PES)' => 'Party of European Socialists (PES)',
            'Other party' => 'Other party'
        ];

    $availableRatings = [
        "False",
        "Partly false",
        "Missing context",
        "Satire",
        "True",
        "AI generated"
    ];

    $availableEuRelations = [
        "Direct",
        "Indirect"
    ];

    $claimPublishedDate = '<div>
<label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="claim-date-published">Claim Date Published:</label>
  <input class="w-full" type="datetime-local" style="height: 2.2em" id="claim-date-published" name="claim-date-published" value="' . (isset($data['itemReviewed']['datePublished']) ? $data['itemReviewed']['datePublished'] : '') . '">
</div>';

    ob_start();

    echo '<div>
    <label for="select-parties" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EU party related to the claim</label>
    <select id="select-parties" name="party" placeholder="Select a party..." autocomplete="off">
        <option value="">Select a party...</option>';

    foreach ($availableParties as $key => $party) {
        $isSelected = '';
        if (isset($data['itemReviewed']['politicalParty']) && $key == $data['itemReviewed']['politicalParty']) {
            $isSelected = 'selected';
        }
        echo "<option value=\"{$key}\" {$isSelected}>{$party}</option>";
    }

    echo '</select></div>';

    $politicalParties = ob_get_clean();

    ob_start();

    echo '<div>
    <label for="select-rating" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rating</label>
    <select id="select-rating" name="political-rating" placeholder="Select rating..." autocomplete="off">
        <option value="">Select a rating...</option>';

    foreach ($availableRatings as $rating) {
        $isSelected = ($data['type'] === 'Factcheck' && $rating == $data['reviewRating']) ? 'selected' : '';
        echo "<option value=\"{$rating}\" {$isSelected}>{$rating}</option>";
    }

    echo '</select></div>';

    $politicalRating = ob_get_clean();

    ob_start();

    echo '<div>
    <label for="select-eu-relation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EU relation</label>
    <select id="select-eu-relation" name="political-eu-relation" placeholder="Select the relation with the EU..." autocomplete="off">
        <option value="">Select a relation...</option>';

    foreach ($availableEuRelations as $euRelation) {
        $isSelected = ($data['type'] === 'Factcheck' && $euRelation == $data['euRelation']) ? 'selected' : '';
        echo "<option value=\"{$euRelation}\" {$isSelected}>{$euRelation}</option>";
    }

    echo '</select></div>';

    $politicalEuRelation = ob_get_clean();

    $politicalHeader = '
<div class="flex flex-row items-center gap-4">
        <i class="text-2xl fa-solid fa-user-tie"></i>
    <div>
        <p class="text-sm font-bold text-gray-900">Political factcheck-related fields</p>
        <p class="text-sm text-gray-600">These fields are specific to the political fact-check type of publication.</p>
    </div>
</div>';

    $politicalRow = '<div class="article-type-field article-type-field-factcheck hidden"><div class="bg-teal-50 my-4 p-4 rounded">' . $politicalHeader . '<div class="my-4 grid grid-cols-1">' . $politicalReviewedClaim . $politicalEnglishReviewedClaim . '</div><div class="my-4 grid grid-cols-4 gap-4">' . $politicalNameRelated . $claimPublishedDate . $politicalParties . $politicalRating . $politicalEuRelation . "</div></div></div>";
    $ee24Box .= $politicalRow;

    ob_start();

    echo '<div>
    <label for="debunk-claim" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Claim reviewed (in native language)</label>
    <input id="debunk-claim" class="debunk-claim h-9 px-2 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full" name="debunk-claim"  value="';

    if (array_key_exists('type', $data) && $data['type'] === 'Debunk') {
        echo $data['claimreviewedNative'];
    }

    echo '" autocomplete="off" placeholder=""></div>';

    $debunkReviewedClaim = ob_get_clean();

    ob_start();

    echo '<div class="relative">
    <label for="debunk-claim-english" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Claim reviewed (in English)</label>
    <input id="debunk-claim-english" class="translatable px-2 h-9 border border-{#d0d0d0} text-gray-900 text-sm rounded-l-lg w-full" name="debunk-claim-english"  value="';
    if (array_key_exists('type', $data) && $data['type'] === 'Debunk') {
        echo $data['claimReviewed'] ?? '';
    }
    echo '" autocomplete="off" placeholder="">
    <button type="button" data-source="#debunk-claim" class="translate-button absolute bottom-0 right-0 px-3 flex items-center h-9 bg-gray-500 text-white rounded-r-lg"><i class="fa-solid fa-language mr-2"></i> Translate from native</button>
    </div>';

    $debunkEnglishReviewedClaim = ob_get_clean();

    ob_start();

    $claimPublishedDate = '<div>
<label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="claim-date-published">Claim Date Published:</label>
  <input class="w-full" type="datetime-local" style="height: 2.2em" id="claim-date-published" name="claim-date-published" value="' . (isset($data['itemReviewed']['datePublished']) ? $data['itemReviewed']['datePublished'] : '') . '">
</div>';

    echo '<div>
    <label for="select-debunk-rating" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rating</label>
    <select id="select-debunk-rating" name="debunk-rating" placeholder="Select rating..." autocomplete="off">
        <option value="">Select a rating...</option>';

    foreach ($availableRatings as $rating) {
        $isSelected = ($data['type'] === 'Debunk' && $rating == $data['reviewRating']) ? 'selected' : '';
        echo "<option value=\"{$rating}\" {$isSelected}>{$rating}</option>";
    }

    echo '</select></div>';

    $debunkRating = ob_get_clean();

    ob_start();

    echo '<div>
    <label for="select-debunk-eu-relation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EU relation</label>
    <select id="select-debunk-eu-relation" name="debunk-eu-relation" placeholder="Select the relation with the EU..." autocomplete="off">
        <option value="">Select a relation...</option>';

    foreach ($availableEuRelations as $euRelation) {
        $isSelected = ($data['type'] === 'Debunk' && $euRelation == $data['euRelation']) ? 'selected' : '';
        echo "<option value=\"{$euRelation}\" {$isSelected}>{$euRelation}</option>";
    }

    echo '</select></div>';

    $debunkEuRelation = ob_get_clean();

    $debunkHeader = '
<div class="flex flex-row items-center gap-4">
        <i class="text-2xl fa-solid fa-circle-check"></i>
    <div>
        <p class="text-sm font-bold text-gray-900">Debunk-related fields</p>
        <p class="text-sm text-gray-600">These fields are specific to the debunk type of publication.</p>
    </div>
</div>';

    $politicalRow = '<div class="article-type-field article-type-field-debunk hidden"><div class="bg-red-50 my-4 p-4 rounded">' . $debunkHeader . '<div class="my-4 grid grid-cols-1">' . $debunkReviewedClaim . $debunkEnglishReviewedClaim . '</div><div class="my-4 grid grid-cols-3 gap-4">' . $claimPublishedDate . $debunkRating . $debunkEuRelation . "</div></div></div>";
    $ee24Box .= $politicalRow;

    $recurrencesHeader = '
<div class="flex flex-row items-center gap-4">
        <i class="text-2xl fa-solid fa-file-circle-plus"></i>
    <div>
        <p class="text-sm font-bold text-gray-900">Appearances</p>
        <p class="text-sm text-gray-600">Both Political Fact-checks and Debunks can have appearances. You can also add the original contents to them.</p>
    </div>
</div>';

    $appearanceIndex = 0;
    $appearances = '';

    foreach ($data['itemReviewed']['appearances'] ?? [] as $appearance) {

        $appearanceUrl = "
    <div>
        <label for='appearance-url-{$appearanceIndex}' class='block mb-2 text-sm font-medium text-gray-900 dark:text-white'>URL</label>
        <input id='appearance-url-{$appearanceIndex}' class='h-9 px-2 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full' name='appearance-url[]' value='" . ($appearance['url'] ?? '') . "' autocomplete='off' placeholder=''>
    </div>";
        $archivedAt = "
    <div>
        <label for=\"archived-url-{$appearanceIndex}\" class=\"block mb-2 text-sm font-medium text-gray-900 dark:text-white\">Archived URL</label>
        <input id=\"archived-url-{$appearanceIndex}\" class=\"h-9 px-2 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full\" name=\"archived-url[]\"  value=\"" . ($appearance['archivedAt'] ?? '') . "\" autocomplete=\"off\" placeholder=\"\">
    </div>";

        $addMediaElement = '
<div class="flex items-end w-100">
    <div data-index="' . $appearanceIndex . '" role="button" class="choose-media-button flex-grow h-9 text-sm flex justify-center items-center font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"><div><i class="fa-solid fa-photo-film"></i> Add media element</div></div>
</div>';

        $platform = "
<div>
    <label for=\"platforms-{$appearanceIndex}\" class=\"block mb-2 text-sm font-medium text-gray-900 dark:text-white\">Platform</label>
    <select class=\"tomselect-init platforms\" id=\"platforms-{$appearanceIndex}\" name=\"platforms[]\" placeholder=\"Select a platform...\" autocomplete=\"off\">";
        $platformOptions = ["facebook", "x", "youtube", "instagram", "whatsapp", "tiktok", "linkedin", "pinterest", "telegram", "signal", "snapchat", "other"];
        foreach ($platformOptions as $option) {
            $selected = ($appearance['platform'] ?? '') === $option ? "selected" : "";
            $optionLabel = ucfirst($option);
            $platform .= "<option value=\"$option\" $selected>$optionLabel</option>";
        }
        $platform .= "</select></div>";

        $format = "
<div>
    <label for=\"formats-{$appearanceIndex}\" class=\"block mb-2 text-sm font-medium text-gray-900 dark:text-white\">Format</label>
    <select class=\"tomselect-init formats\" id=\"formats-{$appearanceIndex}\" name=\"formats[]\" placeholder=\"Select a format...\" autocomplete=\"off\">";
        $formatOptions = ["", "Text", "Image", "Video", "Audio"];
        foreach ($formatOptions as $option) {
            $selected = ($appearance['format'] ?? '') === $option ? "selected" : "";
            $format .= "<option value=\"$option\" $selected>$option</option>";
        }
        $format .= "</select></div>";

        $associatedMultimediaFormat = "
<div>
    <label for='associatedMultimediaFormat-{$appearanceIndex}' class='block mb-2 text-sm font-medium text-gray-900 dark:text-white'>Associated Multimedia Format</label>
    <select class='formats tomselect-init' id='associatedMultimediaFormat-{$appearanceIndex}' name='associatedMultimediaFormats[]' placeholder='Select a format...' autocomplete='off'>";
        $associatedFormatOptions = ["", "image", "video", "audio", "other"];
        foreach ($associatedFormatOptions as $option) {
            $selected = ($appearance['mediaFormat'] ?? '') === $option ? "selected" : "";
            $optionLabel = ucfirst($option);
            $associatedMultimediaFormat .= "<option value=\"$option\" $selected>$optionLabel</option>";
        }
        $associatedMultimediaFormat .= "</select></div>";

        $associatedMediaValue = !empty($appearance['associatedMedia']) ? $appearance['associatedMedia'] : '';
        $associatedMedia = "<input type='hidden' id='associatedMedia-${appearanceIndex}' name='associatedMedia[]' value='$associatedMediaValue'/>";

        $appearances .= '<div class="appearance pl-4 border-l border-l-4 rounded border-green-400"><div class="image-attachment-' . $appearanceIndex . ' w-8"></div><div class="my-4 grid grid-cols-2 gap-4">' . $appearanceUrl . $archivedAt . '</div><div class="my-4 grid grid-cols-4 gap-4">' . $platform . $format . $associatedMultimediaFormat . $addMediaElement . $associatedMedia . '</div></div>';
        $appearanceIndex++;
    }


    $recurrencesRow = '<div class="article-type-field article-type-field-factcheck article-type-field-debunk hidden"><div class="bg-green-50 my-4 p-4 rounded">' . $recurrencesHeader . '<div class="appearances grid grid-cols-1 gap-4 py-4">' . $appearances . '</div><hr><a href="#" role="button" id="add-appearance">Add appearance</a></div></div>';
    $ee24Box .= $recurrencesRow;

    $footer = '<div class="flex flex-row gap-4 items-center"><p>EE24 integration made with 🖤 by </p><img class="w-20" alt="" src="data:image/svg+xml;base64,PHN2ZyBpZD0iQ2FwYV8xIiBkYXRhLW5hbWU9IkNhcGEgMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMjgxLjA1IDYzLjkiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDpub25lO30uY2xzLTJ7Y2xpcC1wYXRoOnVybCgjY2xpcC1wYXRoKTt9PC9zdHlsZT48Y2xpcFBhdGggaWQ9ImNsaXAtcGF0aCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSI+PHJlY3QgY2xhc3M9ImNscy0xIiB3aWR0aD0iNjMuOSIgaGVpZ2h0PSI2My45Ii8+PC9jbGlwUGF0aD48L2RlZnM+PGcgY2xhc3M9ImNscy0yIj48cGF0aCBkPSJNMzguNDIsMTEuMThINTUuMjVWNTIuNDdINDQuN1YyMS41MmwtNy45MSwzMUgyNy4yNmwtNy45LTMwLjk1djMxSDguODJWMTEuMThIMjUuNjZMMzIuMDYsMzZaTS4wNiw2My44NEg2My44NFYuMDZILjA2WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0zOC40NywxMS4yNEg1NS4xOVY1Mi40MUg0NC43N1YyMWwtOCwzMS40SDI3LjMxTDE5LjMsMjF2MzEuNEg4Ljg4VjExLjI0SDI1LjYxbDYuNDUsMjVabTE2LjcyLS4xM0gzOC4zN2wwLC4xTDMyLjA2LDM1Ljc3LDI1LjczLDExLjIxbDAtLjFoLTE3VjUyLjUzSDE5LjQzVjIybDcuNzYsMzAuNDEsMCwuMDloOS42M2wwLS4wOUw0NC42NCwyMnYzMC41SDU1LjMxVjExLjExWk0uMTMuMTNINjMuNzdWNjMuNzdILjEzWk0wLDYzLjlINjMuOVYwSDBaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PC9nPjxyZWN0IHg9IjY4Ljc2IiB5PSIwLjUiIHdpZHRoPSIzLjM2IiBoZWlnaHQ9IjYyLjkiLz48cGF0aCBkPSJNNzEuNjEsMVY2Mi45SDY5LjI2VjFoMi4zNW0xLTFINjguMjZWNjMuOWg0LjM1VjBaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTc4LjA1LDE3LjM2aDguODdsMy40MywxMy4yOSwzLjM5LTEzLjI5aDguODdWMzkuMTlIOTcuMDhWMjIuNTRMOTIuODMsMzkuMTloLTVMODMuNTgsMjIuNTRWMzkuMTlINzguMDVaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTEyMC4zMywzNS41OWgtNy42NWwtMS4wNywzLjZoLTYuODlsOC4yMS0yMS44M2g3LjM2bDguMiwyMS44M2gtNy4wNlptLTEuMzktNC43MkwxMTYuNTMsMjNsLTIuMzksNy44NVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTMwLjY0LDE3LjM2aDYuNzRWMzMuODJoMTAuNTN2NS4zN0gxMzAuNjRaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTE1MS4wNywxNy4zNmgxMGExMiwxMiwwLDAsMSw0Ljc5LjgxLDcuNjYsNy42NiwwLDAsMSwzLDIuMzFBOS40NSw5LjQ1LDAsMCwxLDE3MC42MiwyNGExNi4xMywxNi4xMywwLDAsMSwuNTMsNC4yMywxNC42OSwxNC42OSwwLDAsMS0uNzksNS40Myw5LDksMCwwLDEtMi4yMSwzLjIzLDcuMyw3LjMsMCwwLDEtMywxLjc0LDE1LjUsMTUuNSwwLDAsMS00LC41OWgtMTBabTYuNzQsNC45NVYzNC4yM2gxLjY1YTcsNywwLDAsMCwzLS40NiwzLjE2LDMuMTYsMCwwLDAsMS40LTEuNjQsMTAuMjUsMTAuMjUsMCwwLDAsLjUxLTMuNzljMC0yLjMyLS4zOC0zLjktMS4xMy00Ljc1YTQuNzgsNC43OCwwLDAsMC0zLjc1LTEuMjhaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTE3NSwxNy4zNmg2Ljc2VjM5LjE5SDE3NVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTg1LDE3LjM2aDIwLjUxdjUuMzloLTYuODhWMzkuMTloLTYuNzVWMjIuNzVIMTg1WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yMTkuOTQsMzUuNTloLTcuNjZsLTEuMDYsMy42aC02Ljg5bDguMjEtMjEuODNoNy4zNmw4LjIsMjEuODNIMjIxWm0tMS40LTQuNzJMMjE2LjEzLDIzbC0yLjM4LDcuODVaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTIyOS44NiwzMy4xMmg2LjQ3djYuMDdoLTYuNDdaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTI0MC4zOCwxNy4zNmgxOC4wOFYyMkgyNDcuMTR2My40N2gxMC41VjMwaC0xMC41djQuM2gxMS42NXY0Ljk0SDI0MC4zOFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMjYxLjI1LDMybDYuNDItLjRhNC44NSw0Ljg1LDAsMCwwLC44NCwyLjM4LDMuNTksMy41OSwwLDAsMCwzLDEuMzMsMy4zLDMuMywwLDAsMCwyLjIzLS42OCwyLDIsMCwwLDAsMC0zLjA5LDguNjQsOC42NCwwLDAsMC0zLjQ2LTEuMjdBMTQuNjQsMTQuNjQsMCwwLDEsMjY0LDI3LjU5YTUuMzEsNS4zMSwwLDAsMS0xLjktNC4yMSw1LjczLDUuNzMsMCwwLDEsMS0zLjE4QTYuNDYsNi40NiwwLDAsMSwyNjYsMTcuODVhMTMuNzQsMTMuNzQsMCwwLDEsNS4zNy0uODYsMTAuOTQsMTAuOTQsMCwwLDEsNi4zOCwxLjU2LDYuNjgsNi42OCwwLDAsMSwyLjYyLDQuOTVsLTYuMzYuMzdhMywzLDAsMCwwLTMuMzEtMi44MSwyLjczLDIuNzMsMCwwLDAtMS43Ny40OSwxLjUzLDEuNTMsMCwwLDAtLjU5LDEuMjIsMS4yMSwxLjIxLDAsMCwwLC40OS45NCw1LjYzLDUuNjMsMCwwLDAsMi4yNi44LDI4LjY2LDI4LjY2LDAsMCwxLDYuMzQsMS45Myw2LjcsNi43LDAsMCwxLDIuNzgsMi40Miw2LjEzLDYuMTMsMCwwLDEsLjg3LDMuMjNBNi44Nyw2Ljg3LDAsMCwxLDI3OS44OSwzNmE3LjI2LDcuMjYsMCwwLDEtMy4yNSwyLjY5LDEzLjEsMTMuMSwwLDAsMS01LjI1LjkycS01LjU4LDAtNy43Mi0yLjE1QTguNDYsOC40NiwwLDAsMSwyNjEuMjUsMzJaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTc4LDQxLjEzaDMuMTlhMi4yLDIuMiwwLDAsMSwxLjU2LjQ5QTEuODgsMS44OCwwLDAsMSw4My4yNCw0M2ExLjksMS45LDAsMCwxLS41NywxLjQ3QTIuNDYsMi40NiwwLDAsMSw4MSw0NWgtMXYyLjNINzhabTEuOTMsMi42NGguNDdhMS4yLDEuMiwwLDAsMCwuNzgtLjE5LjYxLjYxLDAsMCwwLC4yMi0uNDkuNzQuNzQsMCwwLDAtLjE5LS41LDEuMDYsMS4wNiwwLDAsMC0uNzQtLjJINzkuOVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNODQuMjMsNDEuMTNoNS4xNHYxLjMySDg2LjE1djFoM1Y0NC43aC0zdjEuMjJoMy4zMXYxLjQxSDg0LjIzWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik05MC41Miw0Ny4zM3YtNi4yaDMuMTlhNC42Nyw0LjY3LDAsMCwxLDEuMzYuMTUsMS40MywxLjQzLDAsMCwxLC43Ni41NiwxLjc0LDEuNzQsMCwwLDEsLjI5LDEsMS44LDEuOCwwLDAsMS0uMjIuODksMS43OCwxLjc4LDAsMCwxLS42MS42MSwyLjQ0LDIuNDQsMCwwLDEtLjY3LjI0LDIsMiwwLDAsMSwuNS4yMywxLjc1LDEuNzUsMCwwLDEsLjMuMzMsMS43MiwxLjcyLDAsMCwxLC4yNy4zOGwuOTMsMS44SDk0LjQ1bC0xLTEuOWExLjQ3LDEuNDcsMCwwLDAtLjM1LS40OC44Mi44MiwwLDAsMC0uNDctLjE0aC0uMTZ2Mi41MlptMS45My0zLjY5aC44YTIuNywyLjcsMCwwLDAsLjUxLS4wOS40OC40OCwwLDAsMCwuMzEtLjE5LjYzLjYzLDAsMCwwLS4wNy0uODIsMS4xMiwxLjEyLDAsMCwwLS43MS0uMTZoLS44NFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNOTcuMzEsNDEuMTNoMS45MnY2LjJIOTcuMzFaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTEwMC4zNiw0NC4yM2EzLjIsMy4yLDAsMCwxLC44NS0yLjM2LDMuMTYsMy4xNiwwLDAsMSwyLjM2LS44NSwzLjI1LDMuMjUsMCwwLDEsMi4zOC44MywzLjE0LDMuMTQsMCwwLDEsLjg0LDIuMzMsMy43LDMuNywwLDAsMS0uMzcsMS43OCwyLjU3LDIuNTcsMCwwLDEtMSwxLjA5LDMuNiwzLjYsMCwwLDEtMS43My4zOCw0LDQsMCwwLDEtMS43My0uMzNBMi41OSwyLjU5LDAsMCwxLDEwMC43OSw0NiwzLjU0LDMuNTQsMCwwLDEsMTAwLjM2LDQ0LjIzWm0xLjkyLDBhMi4wOCwyLjA4LDAsMCwwLC4zNSwxLjM1LDEuMzMsMS4zMywwLDAsMCwxLjkxLDAsMi4zNCwyLjM0LDAsMCwwLC4zMy0xLjQ0LDEuODgsMS44OCwwLDAsMC0uMzUtMS4yOCwxLjI5LDEuMjksMCwwLDAtMS44OSwwQTIuMTMsMi4xMywwLDAsMCwxMDIuMjgsNDQuMjRaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTEwNy44NSw0MS4xM2gyLjg0YTMuMjMsMy4yMywwLDAsMSwxLjM2LjIzLDIuMTYsMi4xNiwwLDAsMSwuODYuNjUsMi42NywyLjY3LDAsMCwxLC40OSwxLDQuNTksNC41OSwwLDAsMSwuMTUsMS4yLDQuMjIsNC4yMiwwLDAsMS0uMjIsMS41NCwyLjYsMi42LDAsMCwxLS42My45MiwyLjE1LDIuMTUsMCwwLDEtLjg2LjQ5LDQuNTcsNC41NywwLDAsMS0xLjE1LjE3aC0yLjg0Wm0xLjkxLDEuNHYzLjM5aC40N2EyLDIsMCwwLDAsLjg2LS4xMywxLDEsMCwwLDAsLjQtLjQ3LDMuMDgsMy4wOCwwLDAsMCwuMTQtMS4wOCwyLjEsMi4xLDAsMCwwLS4zMi0xLjM0LDEuMzUsMS4zNSwwLDAsMC0xLjA3LS4zN1oiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTE0LjY0LDQxLjEzaDEuOTJ2Ni4yaC0xLjkyWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0xMTcuNiw0NS4yOGwxLjgyLS4xMmExLjQzLDEuNDMsMCwwLDAsLjI0LjY4LDEsMSwwLDAsMCwuODUuMzguOTMuOTMsMCwwLDAsLjYzLS4yLjU1LjU1LDAsMCwwLDAtLjg3LDIuMzYsMi4zNiwwLDAsMC0xLS4zNiw0LjEyLDQuMTIsMCwwLDEtMS44LS43NiwxLjUsMS41LDAsMCwxLS41NC0xLjE5LDEuNjIsMS42MiwwLDAsMSwuMjgtLjkxLDEuODgsMS44OCwwLDAsMSwuODMtLjY3LDQsNCwwLDAsMSwxLjUzLS4yNCwzLjE2LDMuMTYsMCwwLDEsMS44MS40NCwyLDIsMCwwLDEsLjc0LDEuNDFsLTEuOC4xMWEuODUuODUsMCwwLDAtLjk0LS44Ljc2Ljc2LDAsMCwwLS41MS4xNC40NC40NCwwLDAsMC0uMTcuMzQuMzYuMzYsMCwwLDAsLjE0LjI3LDEuNjIsMS42MiwwLDAsMCwuNjUuMjMsOCw4LDAsMCwxLDEuOC41NSwxLjkxLDEuOTEsMCwwLDEsLjc5LjY4LDEuOCwxLjgsMCwwLDEsLjI0LjkyQTIsMiwwLDAsMSwxMjIsNDcuMTdhMy41OSwzLjU5LDAsMCwxLTEuNDkuMjYsMywzLDAsMCwxLTIuMTktLjYxQTIuMzUsMi4zNSwwLDAsMSwxMTcuNiw0NS4yOFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTI0LjE3LDQxLjEzaDIuNTJsMSwzLjc3LDEtMy43N2gyLjUxdjYuMmgtMS41N1Y0Mi42bC0xLjIxLDQuNzNoLTEuNDJsLTEuMi00LjczdjQuNzNoLTEuNTdaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTEzMi4xMiw0NC4yM0EyLjk1LDIuOTUsMCwwLDEsMTM1LjMzLDQxYTMuMjUsMy4yNSwwLDAsMSwyLjM4LjgzLDMuMTQsMy4xNCwwLDAsMSwuODQsMi4zMywzLjcsMy43LDAsMCwxLS4zNywxLjc4LDIuNTcsMi41NywwLDAsMS0xLjA1LDEuMDksMy42LDMuNiwwLDAsMS0xLjczLjM4LDQsNCwwLDAsMS0xLjczLS4zM0EyLjU5LDIuNTksMCwwLDEsMTMyLjU1LDQ2LDMuNTQsMy41NCwwLDAsMSwxMzIuMTIsNDQuMjNabTEuOTIsMGEyLjA4LDIuMDgsMCwwLDAsLjM1LDEuMzUsMS4zMywxLjMzLDAsMCwwLDEuOTEsMCwyLjM0LDIuMzQsMCwwLDAsLjMzLTEuNDQsMS44OCwxLjg4LDAsMCwwLS4zNS0xLjI4LDEuMjksMS4yOSwwLDAsMC0xLjg5LDBBMi4xMywyLjEzLDAsMCwwLDEzNCw0NC4yNFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTQyLjQ2LDQxLjEzaDMuMTlhMi4yLDIuMiwwLDAsMSwxLjU2LjQ5LDEuODgsMS44OCwwLDAsMSwuNTIsMS40MSwxLjksMS45LDAsMCwxLS41NywxLjQ3LDIuNDQsMi40NCwwLDAsMS0xLjcyLjUzaC0xLjA1djIuM2gtMS45M1ptMS45MywyLjY0aC40N2ExLjIsMS4yLDAsMCwwLC43OC0uMTkuNjEuNjEsMCwwLDAsLjIyLS40OS42OS42OSwwLDAsMC0uMTktLjUsMS4wNiwxLjA2LDAsMCwwLS43NC0uMmgtLjU0WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0xNTEuOCw0Ni4zaC0yLjE3bC0uMywxaC0ybDIuMzMtNi4yaDIuMDlsMi4zMyw2LjJoLTJaTTE1MS40MSw0NWwtLjY5LTIuMjNMMTUwLjA1LDQ1WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0xNTQuNzYsNDcuMzN2LTYuMkgxNThhNC43Miw0LjcyLDAsMCwxLDEuMzYuMTUsMS41MSwxLjUxLDAsMCwxLC43Ni41NiwxLjc0LDEuNzQsMCwwLDEsLjI4LDEsMS43LDEuNywwLDAsMS0uMjIuODksMS43NSwxLjc1LDAsMCwxLS42LjYxLDIuNTIsMi41MiwwLDAsMS0uNjguMjQsMS44MiwxLjgyLDAsMCwxLC41LjIzLDEuNzEsMS43MSwwLDAsMSwuMzEuMzMsMi44NiwyLjg2LDAsMCwxLC4yNy4zOGwuOTMsMS44aC0yLjE3bC0xLTEuOWExLjY0LDEuNjQsMCwwLDAtLjM1LS40OC44NC44NCwwLDAsMC0uNDctLjE0aC0uMTd2Mi41MlptMS45Mi0zLjY5aC44MWEyLjcsMi43LDAsMCwwLC41MS0uMDkuNTEuNTEsMCwwLDAsLjMxLS4xOS41Ni41NiwwLDAsMCwuMTItLjM2LjU5LjU5LDAsMCwwLS4xOS0uNDYsMS4xNiwxLjE2LDAsMCwwLS43Mi0uMTZoLS44NFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTY1LjI4LDQ2LjNoLTIuMTdsLS4zMSwxaC0ybDIuMzMtNi4yaDIuMDlsMi4zMyw2LjJoLTJaTTE2NC44OSw0NWwtLjY5LTIuMjNMMTYzLjUyLDQ1WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0xNzYuNjEsNDYuNDJsLjQ3LjMyYTQuNzYsNC43NiwwLDAsMCwuNDQuMjFMMTc3LDQ4YTYsNiwwLDAsMS0uOC0uNDdsLS41NS0uNGE0LjA2LDQuMDYsMCwwLDEtMS41OS4yNywzLjIyLDMuMjIsMCwwLDEtMi4yMy0uNzMsMy4xNCwzLjE0LDAsMCwxLTEtMi40NSwzLjIzLDMuMjMsMCwwLDEsLjg0LTIuMzgsMy4xNiwzLjE2LDAsMCwxLDIuMzYtLjg1LDMuMjUsMy4yNSwwLDAsMSwyLjM4LjgzLDMuMTcsMy4xNywwLDAsMSwuODQsMi4zN0EzLjM2LDMuMzYsMCwwLDEsMTc2LjYxLDQ2LjQyWm0tMS40Ny0xYTIuNjEsMi42MSwwLDAsMCwuMjMtMS4yMywyLjA3LDIuMDcsMCwwLDAtLjM1LTEuMzQsMS4yNCwxLjI0LDAsMCwwLTEtLjQsMS4xOSwxLjE5LDAsMCwwLS45My40MSwxLjkyLDEuOTIsMCwwLDAtLjM2LDEuMjgsMi4yNSwyLjI1LDAsMCwwLC4zNSwxLjQzLDEuMTksMS4xOSwwLDAsMCwuOTUuNDEsMS40NSwxLjQ1LDAsMCwwLC4zNywwLDIuNDUsMi40NSwwLDAsMC0uNzYtLjQ0bC4zLS42OWExLjQ3LDEuNDcsMCwwLDEsLjQuMTIsNS4zMyw1LjMzLDAsMCwxLC41NS4zNVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMTgyLjM1LDQxLjEzaDEuOTF2My42OWEzLDMsMCwwLDEtLjE3LDEsMi4xMywyLjEzLDAsMCwxLS41NC44NSwyLDIsMCwwLDEtLjc2LjUyLDQuMTEsNC4xMSwwLDAsMS0xLjM0LjIsOC43NCw4Ljc0LDAsMCwxLTEtLjA2LDIuNjcsMi42NywwLDAsMS0uOS0uMjUsMi4wNiwyLjA2LDAsMCwxLS42NS0uNTQsMS44NSwxLjg1LDAsMCwxLS40MS0uNzEsMy42NywzLjY3LDAsMCwxLS4xOC0xVjQxLjEzaDEuOTF2My43OGExLjEsMS4xLDAsMCwwLC4yOC43OSwxLjA2LDEuMDYsMCwwLDAsLjc4LjI5LDEsMSwwLDAsMCwxLjA2LTEuMDhaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTE4NS41Myw0MS4xM2g1LjEzdjEuMzJoLTMuMjF2MWgzVjQ0LjdoLTN2MS4yMmgzLjMxdjEuNDFoLTUuMjNaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTE5NC42OSw0MS4xM2gxLjc5bDIuMzQsMy40M1Y0MS4xM2gxLjh2Ni4yaC0xLjhsLTIuMzMtMy40MXYzLjQxaC0xLjhaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTIwMS42NSw0NC4yM2EzLjIsMy4yLDAsMCwxLC44NS0yLjM2LDMuMTYsMy4xNiwwLDAsMSwyLjM2LS44NSwzLjI1LDMuMjUsMCwwLDEsMi4zOC44MywzLjE0LDMuMTQsMCwwLDEsLjg0LDIuMzMsMy43LDMuNywwLDAsMS0uMzcsMS43OCwyLjU3LDIuNTcsMCwwLDEtMS4wNSwxLjA5LDMuNiwzLjYsMCwwLDEtMS43My4zOCw0LDQsMCwwLDEtMS43My0uMzNBMi41OSwyLjU5LDAsMCwxLDIwMi4wOCw0NiwzLjU0LDMuNTQsMCwwLDEsMjAxLjY1LDQ0LjIzWm0xLjkyLDBhMi4wOCwyLjA4LDAsMCwwLC4zNSwxLjM1LDEuMTksMS4xOSwwLDAsMCwxLC40MSwxLjE2LDEuMTYsMCwwLDAsLjk1LS40LDIuMjcsMi4yNywwLDAsMCwuMzQtMS40NCwxLjg4LDEuODgsMCwwLDAtLjM1LTEuMjgsMS4yMSwxLjIxLDAsMCwwLTEtLjQxLDEuMTgsMS4xOCwwLDAsMC0uOTMuNDFBMi4xMywyLjEzLDAsMCwwLDIwMy41Nyw0NC4yNFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMjExLjU3LDQxLjEzaDUuODJ2MS41M2gtMS45NXY0LjY3aC0xLjkyVjQyLjY2aC0yWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yMTguMjUsNDEuMTNoNS4xNHYxLjMyaC0zLjIydjFoM1Y0NC43aC0zdjEuMjJoMy4zMXYxLjQxaC01LjIzWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yMjcuNCw0MS4xM2gxLjkyVjQ1LjhoM3YxLjUzSDIyNy40WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yMzcsNDYuM2gtMi4xN2wtLjMsMWgtMmwyLjMzLTYuMkgyMzdsMi4zMyw2LjJoLTJaTTIzNi42LDQ1bC0uNjktMi4yM0wyMzUuMjQsNDVaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTI0Ni45NCw0NC43OWwxLjY4LjUxYTMuMTIsMy4xMiwwLDAsMS0uNTMsMS4xOCwyLjQyLDIuNDIsMCwwLDEtLjkxLjcxLDMuMzcsMy4zNywwLDAsMS0xLjM3LjI0LDMuOSwzLjksMCwwLDEtMS42NS0uMjksMi42NSwyLjY1LDAsMCwxLTEuMTEtMSwzLjU0LDMuNTQsMCwwLDEtLjQ2LTEuOSwzLjIzLDMuMjMsMCwwLDEsLjgxLTIuMzYsMy4xMywzLjEzLDAsMCwxLDIuMzItLjgzLDMuMTcsMy4xNywwLDAsMSwxLjg0LjQ3LDIuODQsMi44NCwwLDAsMSwxLDEuNDZsLTEuNjkuMzhhMS4zOSwxLjM5LDAsMCwwLS4xOS0uNDIsMS4yNiwxLjI2LDAsMCwwLS4zOS0uMzQsMS4xMywxLjEzLDAsMCwwLS41Mi0uMTEsMS4xMiwxLjEyLDAsMCwwLTEsLjUyLDIuMTksMi4xOSwwLDAsMC0uMjYsMS4yMiwyLjI5LDIuMjksMCwwLDAsLjMxLDEuNDEsMS4wNiwxLjA2LDAsMCwwLC44OC4zOSwxLjA5LDEuMDksMCwwLDAsLjgzLS4zMUEyLDIsMCwwLDAsMjQ2Ljk0LDQ0Ljc5WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yNTMuNTksNDEuMTNoMS45MXYzLjY5YTMuMjQsMy4yNCwwLDAsMS0uMTcsMSwyLjM3LDIuMzcsMCwwLDEtLjU0Ljg1LDIuMTQsMi4xNCwwLDAsMS0uNzcuNTIsNC4wNyw0LjA3LDAsMCwxLTEuMzQuMiw5LDksMCwwLDEtMS0uMDYsMi42OCwyLjY4LDAsMCwxLS44OS0uMjUsMi4yMSwyLjIxLDAsMCwxLS42Ni0uNTQsMS44NSwxLjg1LDAsMCwxLS40MS0uNzEsMy42NywzLjY3LDAsMCwxLS4xOC0xVjQxLjEzaDEuOTJ2My43OGExLjA2LDEuMDYsMCwwLDAsLjI4Ljc5LDEsMSwwLDAsMCwuNzguMjksMS4wNSwxLjA1LDAsMCwwLC43Ny0uMjgsMS4wOSwxLjA5LDAsMCwwLC4yOS0uOFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBkPSJNMjU2Ljc2LDQxLjEzaDUuMTR2MS4zMmgtMy4yMnYxaDNWNDQuN2gtM3YxLjIySDI2MnYxLjQxaC01LjIzWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yNjMsNDEuMTNoMS45MlY0NS44aDN2MS41M0gyNjNaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggZD0iTTI2OC44LDQxLjEzaDUuMTN2MS4zMmgtMy4yMXYxaDNWNDQuN2gtM3YxLjIySDI3NHYxLjQxSDI2OC44WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGQ9Ik0yNzUuMDcsNDEuMTNoMS43OWwyLjMzLDMuNDNWNDEuMTNIMjgxdjYuMmgtMS44MWwtMi4zMi0zLjQxdjMuNDFoLTEuOFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48L3N2Zz4K" /> - <a href="mailto:support@efcsn.freshdesk.com">Need help?</a></div></div>';
    $ee24Box .= $footer;


    return $ee24Box;


}

/**
 * Function to build the claim review box
 *
 * @param integer $x The number of the claim we're adding
 * @param mixed $data The data to be added.
 * @return string         The claim review box
 */
function claim_review_build_claim_box($x = 1, $data = [])
{
    $claimbox = '';
    $claimreviewedpresent = '';
    $claimdatecurrent = '';
    $claimauthorcurrent = '';
    $claimappearancecurrent = array();
    $claimanchorcurrent = '';
    $claimlocationcurrent = '';
    $claimjobtitlecurrent = '';
    $claimimagecurrent = '';
    $claimnumericcurrent = '';
    $claimratingimagecurrent = '';

    $max = get_option('cr-organisation-max-number-rating');
    $min = get_option('cr-organisation-min-number-rating');

    if (is_numeric($x)) {
        $arraykey = $x - 1;
    } else {
        $arraykey = $x;
    }

    $claimreviewedcurrent = array_key_exists('claimreviewed', $data) ? $data['claimreviewed'] : '';
    $claimdatecurrent = array_key_exists('date', $data) ? $data['date'] : '';
    $claimauthorcurrent = array_key_exists('author', $data) ? $data['author'] : '';
    $claimasssessmentcurrent = array_key_exists('assessment', $data) ? $data['assessment'] : '';
    $claimanchorcurrent = array_key_exists('anchor', $data) ? $data['anchor'] : '';
    $claimlocationcurrent = array_key_exists('location', $data) ? $data['location'] : '';
    $claimjobtitlecurrent = array_key_exists('job-title', $data) ? $data['job-title'] : '';
    $claimimagecurrent = array_key_exists('image', $data) ? $data['image'] : '';
    $claimnumericcurrent = array_key_exists('numeric-rating', $data) ? $data['numeric-rating'] : '';
    $claimratingimagecurrent = array_key_exists('rating-image', $data) ? $data['rating-image'] : '';

    if ($data) {
        $claimappearancecurrent = array_key_exists('url', $data['appearance']) ? $data['appearance']['url'] : array();
        $claimoriginalcurrent = array_key_exists('original', $data['appearance']) ? $data['appearance']['original'] : '';
    } else {
        $claimappearancecurrent = array();
        $claimoriginalcurrent = '';
    }

    if ($data && '' == $claimreviewedcurrent) {
        return false;
    }

    $claimbox .= '<div class="claimbox" id="claimbox' . $x . '" data-box="' . $x . '">';

    $claimbox .= '<h3>' . sprintf(__('Claim Review #%s', 'claimreview'), $x) . '</h3>';

    $claimbox .= '<div class="crfull"><label for="claim-reviewed-' . $x . '"><strong>' . __('Claim Reviewed', 'claimreview') . '</strong></label>
	<br />
	<textarea name="claim[' . $arraykey . '][claimreviewed]" id="claim-reviewed-' . $x . '" placeholder="" cols="90" rows="5" />' . $claimreviewedcurrent . '</textarea><br/>
	<span class="description">' . __('What the person or entity claimed to be true. Required by Google, Facebook &amp; Bing.', 'claimreview') . '</span></div>';

    $claimbox .= '<div class="crhalf"><label for="claim-date-' . $x . '"><strong>' . __('Claim Date', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat crdatepicker" type="text" name="claim[' . $arraykey . '][date]" id="claim-date-' . $x . '" value="' . $claimdatecurrent . '" /><br/>
	<span class="description">' . __('When the person or entity made the claim.', 'claimreview') . '</span></div>';

    $claimbox .= '<div class="crfull"><label for="claim-appearance-' . $x . '"><strong>' . __('Claim Appearance(s)', 'claimreview') . '</strong></label>
	<br /><span class="description">' . __('Url(s) for a document where this claim appears.', 'claimreview') . '
	<table class="claim-appearance">
	<tbody>';

    $firstrow = TRUE;

    foreach ($claimappearancecurrent as $url) {

        if (!wp_http_validate_url($url)) {
            continue;
        }

        if ($firstrow) {
            $claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" id="claim-reviewed-' . $x . '" value="' . $url . '" placeholder="" /></td><td style="width:25%;""><input type="checkbox" name="claim[' . $arraykey . '][appearance][original]" id="claim-reviewed-' . $x . '" value="1" ' . checked($claimoriginalcurrent, '1', false) . '/>' . __('Original Appearance', 'claimreview') . '</td></tr>';
            $firstrow = FALSE;
        } else {
            $claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" value="' . $url . '" placeholder="" /></td><td style="width:25%;"><button type="button" class="button button-secondary cr-remove-row">Remove</button></td></tr>';
        }
    }

    if ($firstrow) {
        $claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" id="claim-reviewed-' . $x . '" value="" placeholder="" /></td><td style="width:25%;""><input type="checkbox" name="claim[' . $arraykey . '][appearance][original]' . $x . '" id="claim-reviewed-' . $x . '" value="1" ' . checked($claimoriginalcurrent, '1', false) . '/>' . __('Original Appearance', 'claimreview') . '</td></tr>';
    } else {
        $claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" value="" placeholder="" /></td><td style="width:25%;"><button type="button" class="button button-secondary cr-remove-row">Remove</button></td></tr>';
    }

    $claimbox .= '</tbody>
	</table>
	<a href="#" class="add-claim-appearance" data-arraykey="' . $arraykey . '">+' . __('Add another claim appearance', 'claimreview') . '</a></span></div>';

    $claimbox .= '<div class="crfull"><label for="claim-author-' . $x . '"><strong>' . __('Claim Author Name', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][author]" id="claim-author-' . $x . '" value="' . $claimauthorcurrent . '" /><br/>
	<span class="description">' . __('Name of the person or entity who made the claim. Just their name, not their job or title. For viral social media posts without a clear source, use your discretion to show that the claim is viral e.g. ‘Viral social media post’. Take care not to imply that a particular social media company made the claim.', 'claimreview') . '</span></div>';

    $claimbox .= '<div class="crfull"><label for="claim-assesment-' . $x . '"><strong>' . __('Claim Assessment', 'claimreview') . '</strong></label>
	<br />
	<textarea name="claim[' . $arraykey . '][assessment]" id="claim-assesment-' . $x . '"  cols="90" rows="5" />' . $claimasssessmentcurrent . '</textarea>
	<br/><span class="description">' . __('Your written assessment of the claim. Required by Google, Facebook &amp; Bing.', 'claimreview') . '</span></div>';

    $claimbox .= '<p><button type="button" class="claim-more-fields button button-secondary">' . __('More Fields', 'claimreview') . '</button></p>';

    $claimbox .= '<div class="claim-more-fields-box">';

    $claimbox .= '<div class="crfull"><label for="claim-review-anchor-' . $x . '"><strong>' . __('Claim Review Anchor', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][anchor]" id="claim-review-anchor-' . $x . '" value="' . $claimanchorcurrent . '" /><br/>
	<span class="description">' . __('If provided, this will be added to the end of the URL of the page. This will be sanitized to be a URL slug.', 'claimreview') . '</span></div>';

    $claimbox .= '<div class="crfull"><label for="claim-location-' . $x . '"><strong>' . __('Claim Location', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][location]" id="claim-location-' . $x . '" value="' . $claimlocationcurrent . '" /><br/>
	<span class="description">' . __('Where the claim was made e.g. “At a press conference”.', 'claimreview') . '</span></div>';

    $claimbox .= '<div class="crhalf"><label for="claim-author-job-title-' . $x . '"><strong>' . __('Claim Author Job Title', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][job-title]" id="claim-author-job-title-' . $x . '" value="' . $claimjobtitlecurrent . '" /><br/>
	<span class="description">' . __('Position of the person or entity making the claim.', 'claimreview') . '</span></div>';

    $claimbox .= '<div class="crhalf"><label for="claim-author-image-' . $x . '"><strong>' . __('Claim Author Image', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][image]" id="claim-author-image-' . $x . '" value="' . $claimimagecurrent . '" /><br/>
	<span class="description">' . __('Image URL of the person or entity making the claim.', 'claimreview') . '</span></div>';

    if (-1 != $max && -1 != $min) {

        $claimbox .= '<div class="crhalf"><label for="claim-numeric-rating-' . $x . '"><strong>' . __('Numeric Rating', 'claimreview') . '</strong></label>
		<br />
		<input class="widefat" type="number" step="1" name="claim[' . $arraykey . '][numeric-rating]" id="claim-numeric-rating-' . $x . '" value="' . $claimnumericcurrent . '" max="' . $max . '" min="' . $min . '" /><br/>
		<span class="description">' . sprintf(__('A number rating for the claim. Between %s and %s.', 'claimreview'), $min, $max) . '</span></div>';

    }

    $claimbox .= '<div class="crfull"><label for="claim-rating-image-' . $x . '"><strong>' . __('Claim Rating Image', 'claimreview') . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][rating-image]" id="claim-rating-image-' . $x . '" value="' . $claimratingimagecurrent . '" /><br/>
	<span class="description">' . __('Image URL for the given rating.', 'claimreview') . '</span></div>';

    if ($x != 1) {
        $claimbox .= '<div class="crfull cr-text-right"><button type="button" class="button button-secondary cr-remove-claim" data-remove-target="' . $x . '">' . __('Remove Claim', 'claimreview') . '</button></div>';
    }

    $claimbox .= '</div>';

    $claimbox .= '</div>';

    return $claimbox;
}


/**
 * Helper function to get an arrow to put anywhere.
 *
 * @return string
 */
function claimbox_get_arrow()
{
    return '<svg class="claim-review-arrow" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g><path fill="none" d="M0,0h24v24H0V0z"></path></g><g><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g></svg>';
}


/**
 * Save the metabox claim data
 *
 * @param integer $post_id The post ID we're looking at
 * @param object $post The post object we're using
 * @return mixed              Usually the post ID
 */
function claimbox_save_data($post_id, $post)
{
    /* Get the post type object. */
    $post_type = get_post_type_object($post->post_type);

    /* Check if the current user has permission to edit the post. */
    if (!current_user_can($post_type->cap->edit_post, $post_id)) {
        return $post_id;
    }


    $post_types = get_option('cr-post-types');

    $post_type_string = 'cr-showon' . $post_type->name;

    $isinarray = FALSE;


    foreach ($post_types as $key => $value) {
        if ($key == $post_type_string) {
            $isinarray = TRUE;
            break;
        }
    }

    if (!$isinarray) {
        return $post_id;
    }

    if (array_key_exists('claim', $_POST)) {
        $newclaim = $_POST['claim'];

        if (is_array($newclaim)) {
            $newclaim = array_values($newclaim);
            $sanitizedclaim = array();


            for ($x = 0; $x < sizeof($newclaim); $x++) {

                if (array_key_exists('claimreviewed', $newclaim[$x])) {
                    $sanitizedclaim[$x]['claimreviewed'] = sanitize_text_field($newclaim[$x]['claimreviewed']);
                }

                //wp_die( print_r( $sanitizedclaim ) . print_r( $newclaim[0] ) );

                if (array_key_exists('date', $newclaim[$x])) {
                    $sanitizedclaim[$x]['date'] = sanitize_text_field($newclaim[$x]['date']);
                }

                if (array_key_exists('appearance', $newclaim[$x])) {

                    if (array_key_exists('url', $newclaim[$x]['appearance'])) {
                        for ($y = 0; $y < sizeof($newclaim[$x]['appearance']['url']); $y++) {
                            $sanitizedclaim[$x]['appearance']['url'][$y] = esc_url($newclaim[$x]['appearance']['url'][$y]);
                        }
                    }

                    if (array_key_exists('original', $newclaim[$x]['appearance'])) {
                        $sanitizedclaim[$x]['appearance']['original'] = sanitize_text_field($newclaim[$x]['appearance']['original']);
                    }
                }

                if (array_key_exists('author', $newclaim[$x])) {
                    $sanitizedclaim[$x]['author'] = sanitize_text_field($newclaim[$x]['author']);
                }

                if (array_key_exists('author', $newclaim[$x])) {
                    $sanitizedclaim[$x]['assessment'] = sanitize_text_field($newclaim[$x]['assessment']);
                }

                if (array_key_exists('anchor', $newclaim[$x])) {
                    $sanitizedclaim[$x]['anchor'] = sanitize_title($newclaim[$x]['anchor']);
                }

                if (array_key_exists('location', $newclaim[$x])) {
                    $sanitizedclaim[$x]['location'] = sanitize_text_field($newclaim[$x]['location']);
                }

                if (array_key_exists('job-title', $newclaim[$x])) {
                    $sanitizedclaim[$x]['job-title'] = sanitize_text_field($newclaim[$x]['job-title']);
                }

                if (array_key_exists('image', $newclaim[$x])) {
                    $sanitizedclaim[$x]['image'] = esc_url($newclaim[$x]['image']);
                }

                if (array_key_exists('numeric-rating', $newclaim[$x])) {
                    $sanitizedclaim[$x]['numeric-rating'] = sanitize_text_field($newclaim[$x]['numeric-rating']);
                }

                if (array_key_exists('rating-image', $newclaim[$x])) {
                    $sanitizedclaim[$x]['rating-image'] = esc_url($newclaim[$x]['rating-image']);
                }
            }
        }

        update_post_meta($post_id, '_fullfact_all_claims', $sanitizedclaim);

    }
}

function ee24_save_data($post_id, $post)
{
    if (array_key_exists('article-type', $_POST)) {

        // Save the data
        $data['type'] = $_POST['article-type'];
        // Get the WP public URL of the post
        $data['url'] = get_permalink($post_id);
        $data['headlineNative'] = get_the_title($post_id);
        $data['headline'] = $_POST['headline-english'];
        $data['datePublished'] = get_post_datetime($post_id);
        $data['image'] = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $data['keywords'] = explode(',', $_POST['keywords']);
        $data['inLanguage'] = $_POST['language'];
        $data['topics'] = $_POST['topics'];
        $data['euRelation'] = $_POST['political-eu-relation'] ?: $_POST['debunk-eu-relation'];
        $data['contentLocation'] = $_POST['content-location'] ?? [];
        $data['claimreviewedNative'] = $_POST['political-claim'] ?: $_POST['debunk-claim'];
        $data['claimReviewed'] = $_POST['political-claim-english'] ?: $_POST['debunk-claim-english'];
        $data['reviewRating'] = $_POST['political-rating'] ?: $_POST['debunk-rating'];

        $appearances = array_map(function ($appearance, $index) {
            $appearanceItem = array_filter([
                'url' => $_POST['appearance-url'][$index] ?? null,
                'archivedAt' => $_POST['archived-url'][$index] ?? null,
                'associatedMedia' => str_replace("//wp-content", "/wp-content", $_POST['associatedMedia'][$index]) ?? null,
                'associatedMediaType' => $_POST['associatedMultimediaFormats'][$index] ?? null,
                'format' => $_POST['formats'][$index] ?? null,
                'mediaFormat' => $_POST['associatedMultimediaFormats'][$index] ?? null,
                'platform' => $_POST['platforms'][$index] ?? null,
            ], 'strlen');
            return $appearanceItem;
        }, $_POST['appearance-url'] ?? [], array_keys($_POST['appearance-url'] ?? []));

        $data['itemReviewed'] = [
            'datePublished' => $_POST['claim-date-published'] ?? null,
            'author' => $_POST['author'],
            'politicalParty' => $_POST['party'],
            'appearances' => $appearances,
        ];

        $existingData = get_post_meta($post_id, '_ee24_repository', true) ?: [];
        update_post_meta($post_id, '_ee24_repository', $data);

        if (!wp_is_post_autosave($post)) {

            $api = new EE24Api();

            $headers = [
                'X-API-KEY' => get_option('ee24-apikey'),
                'X-DOMAIN' => get_option('ee24-domain'),
            ];

            // Serialize dates
            $serializedData = $data;
            $serializedData['datePublished'] = $serializedData['datePublished']->format('Y-m-d\TH:i:s.u\Z');

            if ($existingData['externalId'] ?? null) {
                // Already exists in the Repository
                $data['externalId'] = $existingData['externalId'];
                try {
                    $response = $api->sendPatchRequest($data['externalId'], $serializedData, $headers);
                    $data['externalId'] = json_decode($response)->externalId;
                    set_transient("ee24_success", $data['externalId'], 45);
                } catch (\Throwable $e) {
                    set_transient("ee24_error", $e->getMessage(), 45);
                }
            } else {
                // Doesn't exist in the Repository
                try {
                    $response = $api->sendPostRequest($serializedData, $headers);
                    $data['externalId'] = json_decode($response)->externalId;
                    set_transient("ee24_success", $data['externalId'], 45);
                } catch (\Throwable $e) {
                    set_transient("ee24_error", $e->getMessage(), 45);
                }
            }
        }

        update_post_meta($post_id, '_ee24_repository', $data);
    }
}

add_action('save_post', 'claimbox_save_data', 10, 2);
add_action('save_post', 'ee24_save_data', 11, 2);
add_action('admin_notices', 'ee24_admin_notice');

function ee24_admin_notice()
{
    // Get the API response stored earlier
    $error = get_transient('ee24_error');

    if ($error !== false) {
        echo '<div class="notice notice-error is-dismissible">';
        echo 'Error exporting to the EE24 Repository: ' . esc_html($error);
        echo '</div>';
    }

    $success = get_transient('ee24_success');

    if ($success !== false) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>The EE24 Repository has been updated</p>';
        echo '</div>';
    }

    $post = get_post();
    if ($post && !use_block_editor_for_post($post)) {
        if ($error) {
            delete_transient('ee24_error');
        } else if ($success) {
            delete_transient('ee24_success');
        }
    }
}
