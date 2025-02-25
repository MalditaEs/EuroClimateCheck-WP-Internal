<script setup>
import {onMounted, ref, watch} from 'vue';
import {SelectButton} from "primevue";
import {Card} from "primevue";
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import Chip from 'primevue/chip';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import TranslateButton from './components/TranslateButton.vue';

const data = ref({
  type: null,
  url: '',
  headlineNative: '',
  headline: '',
  datePublished: null,
  keywords: [],
  topic: '',
  subtopics: [],
  contentLocation: [],
  inLanguage: null,

  claimReviewed: '',
  claimReviewedNative: '',
  multiclaim: false,
  distortionType: [],
  aiVerification: [],
  harm: false,
  harmEscalation: null,
  reviewRating: '',
  claimAppearances: [],
  evidences: [],
  associatedClaimReview: []
});

const loading = ref(true);
const error = ref(null);

// Añadir refs para la API
const apiConfig = ref({
  apikey: '',
  domain: '',
  endpoint: ''
});

onMounted(async () => {
  try {
    const rawElement = document.getElementById('euroclimatecheck-data');
    if (!rawElement) {
      error.value = "Data element not found";
      console.error(error.value);
      loading.value = false;
      return;
    }

    const rawData = rawElement.innerText;
    if (!rawData) {
      error.value = "No data found in element";
      console.error(error.value);
      loading.value = false;
      return;
    }

    // Get the title from the WP article
    const titleInput = document.getElementById('title');
    const titleH1 = document.querySelector('.editor-post-title');
    const headlineText = titleInput?.value || titleH1?.textContent || '';

    // Add listeners for title changes
    if (titleInput) {
      titleInput.addEventListener('input', (e) => {
        data.value.headlineNative = e.target.value;
      });
    }

    if (titleH1) {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          data.value.headlineNative = titleH1.textContent;
        });
      });

      observer.observe(titleH1, {
        characterData: true,
        childList: true,
        subtree: true
      });
    }

    try {
      const parsedData = JSON.parse(rawData);
      // Guardar la configuración de la API
      apiConfig.value = {
        apikey: parsedData.apikey,
        domain: parsedData.domain,
        language: parsedData.language,
        endpoint: document.getElementById('ee24-data')?.dataset?.endpoint
      };

      // Convertir fechas string a objetos Date en claimAppearances
      if (parsedData.data.claimAppearances) {
        parsedData.data.claimAppearances = parsedData.data.claimAppearances.map(appearance => ({
          ...appearance,
          appearanceDate: appearance.appearanceDate ? new Date(appearance.appearanceDate) : null
        }));
      }

      const transformedData = {
        ...data.value,
        ...parsedData.data,
        inLanguage: languages[parsedData.data.inLanguage] || {
          code: apiConfig.value.language,
          name: languages[apiConfig.value.language] || "Unknown"
        } || null,
        topic: parsedData.data.topic || '',
        headlineNative: parsedData.data.headlineNative || headlineText || '',
        headline: parsedData.data.headline || headlineText || '',
      };

      data.value = transformedData;
      console.log('Loaded data:', data.value);
    } catch (parseError) {
      error.value = "Failed to parse data: " + parseError.message;
      console.error(error.value);
      return;
    }

    loading.value = false;
  } catch (err) {
    error.value = err.message;
    console.error("Error in onMounted:", err);
    loading.value = false;
  }
});

const articleTypes = ['Factcheck', 'Prebunk', 'None'];

const allCountries = {
  "AF": "Afghanistan",
  "AL": "Albania",
  "DZ": "Algeria",
  "AS": "American Samoa",
  "AD": "Andorra",
  "AO": "Angola",
  "AI": "Anguilla",
  "AQ": "Antarctica",
  "AG": "Antigua and Barbuda",
  "AR": "Argentina",
  "AM": "Armenia",
  "AW": "Aruba",
  "AU": "Australia",
  "AT": "Austria",
  "AZ": "Azerbaijan",
  "BS": "Bahamas (the)",
  "BH": "Bahrain",
  "BD": "Bangladesh",
  "BB": "Barbados",
  "BY": "Belarus",
  "BE": "Belgium",
  "BZ": "Belize",
  "BJ": "Benin",
  "BM": "Bermuda",
  "BT": "Bhutan",
  "BO": "Bolivia (Plurinational State of)",
  "BQ": "Bonaire, Sint Eustatius and Saba",
  "BA": "Bosnia and Herzegovina",
  "BW": "Botswana",
  "BV": "Bouvet Island",
  "BR": "Brazil",
  "IO": "British Indian Ocean Territory (the)",
  "BN": "Brunei Darussalam",
  "BG": "Bulgaria",
  "BF": "Burkina Faso",
  "BI": "Burundi",
  "CV": "Cabo Verde",
  "KH": "Cambodia",
  "CM": "Cameroon",
  "CA": "Canada",
  "KY": "Cayman Islands (the)",
  "CF": "Central African Republic (the)",
  "TD": "Chad",
  "CL": "Chile",
  "CN": "China",
  "CX": "Christmas Island",
  "CC": "Cocos (Keeling) Islands (the)",
  "CO": "Colombia",
  "KM": "Comoros (the)",
  "CD": "Congo (the Democratic Republic of the)",
  "CG": "Congo (the)",
  "CK": "Cook Islands (the)",
  "CR": "Costa Rica",
  "HR": "Croatia",
  "CU": "Cuba",
  "CW": "Curaçao",
  "CY": "Cyprus",
  "CZ": "Czechia",
  "CI": "Côte d'Ivoire",
  "DK": "Denmark",
  "DJ": "Djibouti",
  "DM": "Dominica",
  "DO": "Dominican Republic (the)",
  "EC": "Ecuador",
  "EG": "Egypt",
  "SV": "El Salvador",
  "GQ": "Equatorial Guinea",
  "ER": "Eritrea",
  "EE": "Estonia",
  "SZ": "Eswatini",
  "ET": "Ethiopia",
  "FK": "Falkland Islands (the) [Malvinas]",
  "FO": "Faroe Islands (the)",
  "FJ": "Fiji",
  "FI": "Finland",
  "FR": "France",
  "GF": "French Guiana",
  "PF": "French Polynesia",
  "TF": "French Southern Territories (the)",
  "GA": "Gabon",
  "GM": "Gambia (the)",
  "GE": "Georgia",
  "DE": "Germany",
  "GH": "Ghana",
  "GI": "Gibraltar",
  "GR": "Greece",
  "GL": "Greenland",
  "GD": "Grenada",
  "GP": "Guadeloupe",
  "GU": "Guam",
  "GT": "Guatemala",
  "GG": "Guernsey",
  "GN": "Guinea",
  "GW": "Guinea-Bissau",
  "GY": "Guyana",
  "HT": "Haiti",
  "HM": "Heard Island and McDonald Islands",
  "VA": "Holy See (the)",
  "HN": "Honduras",
  "HK": "Hong Kong",
  "HU": "Hungary",
  "IS": "Iceland",
  "IN": "India",
  "ID": "Indonesia",
  "IR": "Iran (Islamic Republic of)",
  "IQ": "Iraq",
  "IE": "Ireland",
  "IM": "Isle of Man",
  "IL": "Israel",
  "IT": "Italy",
  "JM": "Jamaica",
  "JP": "Japan",
  "JE": "Jersey",
  "JO": "Jordan",
  "KZ": "Kazakhstan",
  "KE": "Kenya",
  "KI": "Kiribati",
  "KP": "Korea (the Democratic People's Republic of)",
  "KR": "Korea (the Republic of)",
  "KW": "Kuwait",
  "KG": "Kyrgyzstan",
  "LA": "Lao People's Democratic Republic (the)",
  "LV": "Latvia",
  "LB": "Lebanon",
  "LS": "Lesotho",
  "LR": "Liberia",
  "LY": "Libya",
  "LI": "Liechtenstein",
  "LT": "Lithuania",
  "LU": "Luxembourg",
  "MO": "Macao",
  "MG": "Madagascar",
  "MW": "Malawi",
  "MY": "Malaysia",
  "MV": "Maldives",
  "ML": "Mali",
  "MT": "Malta",
  "MH": "Marshall Islands (the)",
  "MQ": "Martinique",
  "MR": "Mauritania",
  "MU": "Mauritius",
  "YT": "Mayotte",
  "MX": "Mexico",
  "FM": "Micronesia (Federated States of)",
  "MD": "Moldova (the Republic of)",
  "MC": "Monaco",
  "MN": "Mongolia",
  "ME": "Montenegro",
  "MS": "Montserrat",
  "MA": "Morocco",
  "MZ": "Mozambique",
  "MM": "Myanmar",
  "NA": "Namibia",
  "NR": "Nauru",
  "NP": "Nepal",
  "NL": "Netherlands (the)",
  "NC": "New Caledonia",
  "NZ": "New Zealand",
  "NI": "Nicaragua",
  "NE": "Niger (the)",
  "NG": "Nigeria",
  "NU": "Niue",
  "NF": "Norfolk Island",
  "MP": "Northern Mariana Islands (the)",
  "NO": "Norway",
  "OM": "Oman",
  "PK": "Pakistan",
  "PW": "Palau",
  "PS": "Palestine, State of",
  "PA": "Panama",
  "PG": "Papua New Guinea",
  "PY": "Paraguay",
  "PE": "Peru",
  "PH": "Philippines (the)",
  "PN": "Pitcairn",
  "PL": "Poland",
  "PT": "Portugal",
  "PR": "Puerto Rico",
  "QA": "Qatar",
  "MK": "Republic of North Macedonia",
  "RO": "Romania",
  "RU": "Russian Federation (the)",
  "RW": "Rwanda",
  "RE": "Réunion",
  "BL": "Saint Barthélemy",
  "SH": "Saint Helena, Ascension and Tristan da Cunha",
  "KN": "Saint Kitts and Nevis",
  "LC": "Saint Lucia",
  "MF": "Saint Martin (French part)",
  "PM": "Saint Pierre and Miquelon",
  "VC": "Saint Vincent and the Grenadines",
  "WS": "Samoa",
  "SM": "San Marino",
  "ST": "Sao Tome and Principe",
  "SA": "Saudi Arabia",
  "SN": "Senegal",
  "RS": "Serbia",
  "SC": "Seychelles",
  "SL": "Sierra Leone",
  "SG": "Singapore",
  "SX": "Sint Maarten (Dutch part)",
  "SK": "Slovakia",
  "SI": "Slovenia",
  "SB": "Solomon Islands",
  "SO": "Somalia",
  "ZA": "South Africa",
  "GS": "South Georgia and the South Sandwich Islands",
  "SS": "South Sudan",
  "ES": "Spain",
  "LK": "Sri Lanka",
  "SD": "Sudan (the)",
  "SR": "Suriname",
  "SJ": "Svalbard and Jan Mayen",
  "SE": "Sweden",
  "CH": "Switzerland",
  "SY": "Syrian Arab Republic",
  "TW": "Taiwan",
  "TJ": "Tajikistan",
  "TZ": "Tanzania, United Republic of",
  "TH": "Thailand",
  "TL": "Timor-Leste",
  "TG": "Togo",
  "TK": "Tokelau",
  "TO": "Tonga",
  "TT": "Trinidad and Tobago",
  "TN": "Tunisia",
  "TR": "Turkey",
  "TM": "Turkmenistan",
  "TC": "Turks and Caicos Islands (the)",
  "TV": "Tuvalu",
  "UG": "Uganda",
  "UA": "Ukraine",
  "AE": "United Arab Emirates (the)",
  "GB": "United Kingdom of Great Britain and Northern Ireland (the)",
  "UM": "United States Minor Outlying Islands (the)",
  "US": "United States of America (the)",
  "UY": "Uruguay",
  "UZ": "Uzbekistan",
  "VU": "Vanuatu",
  "VE": "Venezuela (Bolivarian Republic of)",
  "VN": "Viet Nam",
  "VG": "Virgin Islands (British)",
  "VI": "Virgin Islands (U.S.)",
  "WF": "Wallis and Futuna",
  "EH": "Western Sahara",
  "YE": "Yemen",
  "ZM": "Zambia",
  "ZW": "Zimbabwe",
  "AX": "Åland Islands"
};
const allowedCountries = [
  "AF", "AL", "DZ", "AD", "AO", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BB", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "BN", "BG", "BF", "BI", "CV", "KH", "CM", "CA", "CF", "TD", "CL", "CN", "CO", "KM", "CD", "CG", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "GQ", "ER", "EE", "SZ", "ET", "EU", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GR", "GD", "GT", "GN", "GW", "GY", "HT", "HN", "HU", "IS", "IN", "ID", "IR", "IQ", "IE", "IL", "IT", "JM", "JP", "JO", "KZ", "KE", "KI", "KP", "KR", "XK", "KW", "KG", "LA", "LV", "LB", "LS", "LR", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MH", "MR", "MU", "MX", "FM", "MD", "MC", "MN", "ME", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "MK", "NO", "OM", "PK", "PW", "PA", "PG", "PY", "PE", "PH", "PL", "PT", "QA", "RO", "RU", "RW", "KN", "LC", "VC", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "SS", "ES", "LK", "SD", "SR", "SE", "CH", "SY", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "TV", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VA", "VE", "VN", "YE", "ZM", "ZW"
];
const reducedCountries = [
  'AL', 'AD', 'AM', 'AT', 'AZ', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ',
  'DK', 'EE', 'EU', 'FI', 'FR', 'GE', 'DE', 'GR', 'HU', 'IS', 'IE',
  'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'MD', 'MC', 'ME', 'NL', 'MK',
  'NO', 'PL', 'PT', 'RO', 'SM', 'RS', 'SK', 'SI', 'ES', 'SE', 'CH',
  'TR', 'UA', 'GB', 'XK', 'BY', 'RU', 'OTHER'
];
const languages = {
  "SQ": "Albanian",
  "HY": "Armenian",
  "AZ": "Azerbaijani",
  "BE": "Belarusian",
  "BS": "Bosnian",
  "BG": "Bulgarian",
  "CA": "Catalan",
  "HR": "Croatian",
  "CS": "Czech",
  "DA": "Danish",
  "NL": "Dutch",
  "EN": "English",
  "ET": "Estonian",
  "FI": "Finnish",
  "FR": "French",
  "GL": "Galician",
  "KA": "Georgian",
  "DE": "German",
  "EL": "Greek",
  "HU": "Hungarian",
  "IS": "Icelandic",
  "GA": "Irish",
  "IT": "Italian",
  "LV": "Latvian",
  "LT": "Lithuanian",
  "LB": "Luxembourgish",
  "MK": "Macedonian",
  "MT": "Maltese",
  "MO": "Moldovan",
  "NO": "Norwegian",
  "PL": "Polish",
  "PT": "Portuguese",
  "RO": "Romanian",
  "RU": "Russian",
  "SR": "Serbian",
  "SK": "Slovak",
  "SL": "Slovenian",
  "ES": "Spanish",
  "SV": "Swedish",
  "TR": "Turkish",
  "UK": "Ukrainian",
  "EU": "Basque",
  "OTHER": "Other"
};

const cleanAllowedCountries = Object.entries(allCountries)
    .filter(([countryCode]) => allowedCountries.includes(countryCode)).map(([countryCode, countryName]) => ({
      name: countryName,
      code: countryCode
    }));

const cleanReducedCountries = ref(Object.entries(allCountries)
    .filter(([countryCode]) => reducedCountries.includes(countryCode)).map(([countryCode, countryName]) => ({
      name: countryName,
      code: countryCode
    })));

const cleanLanguages = Object.entries(languages).map(([languageCode, languageName]) => ({
  name: languageName,
  code: languageCode
}));

const topics = [
  'Extreme weather events',
  'Transport',
  'Renewables',
  'Conspiracy theories',
  'Fossil fuels',
  'Waste',
  'Other'
];
const subTopic = {
  'Extreme weather events': [
    'Increasing temperatures',
    'Heatwaves',
    'Floods',
    'Water scarcity'
  ],
  'Transport': [
    'Electric cars'
  ],
  'Renewables': [
    'Wind energy',
    'Solar PV',
    'Offshore wind energy'
  ],
  'Conspiracy theories': [
    'Chemtrails',
    '2030 agenda',
    '15-minute cities',
    'HAARP'
  ],
  'Fossil fuels': [
    'Natural gas',
    'Oil',
    'Coal'
  ],
  'Waste': [
    'Plastic'
  ],
  'Other': [
    'Climate change denial',
    'Meat consumption'
  ]
};

const newKeyword = ref('');

const addKeyword = () => {
  if (newKeyword.value.includes(',')) {
    const inputKeywords = newKeyword.value
        .split(',')
        .map((k) => k.trim())
        .filter((k) => k);

    data.value.keywords.push(...inputKeywords);
    newKeyword.value = '';
  }
};

const addEvidence = () => {
  data.value.evidences.push({
    question: '',
    answer: '',
    url: '',
    type: ''
  });
};

const ratingOptions = [
  'False',
  'Partly false',
  'Missing Context',
  'Satire',
  'True',
  'AI Generated',
  'Lack of evidence'
];

const yesNoOptions = {true: 'Yes', false: 'No'};

const distortionTypes = [
  'Unproven',
  'Satire believed to be true',
  'Mislabelled, misattributed or misidentified information',
  'Misleading information',
  'Overstated/understated',
  'Conflated',
  'Edited content',
  'Staged content',
  'Transformed content',
  'Fabricated information',
  'Imposter content',
  'Co-ordinated inauthentic behaviour',
  'True'
];

const aiVerificationMethods = [
  'Direct disclosure',
  'Indirect disclosure',
  'AI detection tool',
  'Context',
  'Direct rebuttal evidence'
];

const harmEscalationLevels = [
  'Unlikely to escalate',
  'Plausibly could escalate',
  'Context suggests escalation likely'
];

const evidenceTypes = [
  'Online written source (including statistical data)',
  'Online media source (video, audio, image analysis, and reverse image search)',
  'Reference to other fact-checked articles',
  'Consultation with the claimant (and/or people involved in the claim)',
  'Consultation with experts',
  'Consultation with a government official or department (including local government and public/emergency services)',
  'None the fact checker could find',
  'Other'
];

const platforms = {
  'x': 'X',
  'facebook': 'Facebook',
  'instagram': 'Instagram',
  'tiktok': 'TikTok',
  'youtube': 'YouTube',
  'whatsapp': 'WhatsApp',
  'telegram': 'Telegram',
  'signal': 'Signal',
  'other': 'Other'
};

const diffusionFormats = {
  'text': 'Text',
  'image': 'Image',
  'video': 'Video',
  'audio': 'Audio',
  'other': 'Other'
};

const claimantTypes = [
  'Person',
  'Organization'
];

const claimantInfluenceLevels = {
  'High': 'High (E.g. President/PM of a country, a high-profile MP, a mainstream political party, household names)',
  'Medium': 'Medium (E.g. a community leader, a famous actor, someone famous in the media/social media (with many followers))',
  'Low': 'Low (E.g. random social media users, anonymous sources, a head teacher in a school, etc.'
};

const platformOptions = Object.entries(platforms).map(([value, label]) => ({value, label}));
const formatOptions = Object.entries(diffusionFormats).map(([value, label]) => ({value, label}));
const influenceLevelOptions = Object.entries(claimantInfluenceLevels).map(([value, label]) => ({value, label}));

const addClaimAppearance = () => {
  data.value.claimAppearances.push({
    url: '',
    archivedAt: '',
    difussionFormat: '',
    platform: '',
    appearanceDate: null,
    views: null,
    likes: null,
    comments: null,
    shares: null,
    actionTaken: false,
    appearanceBody: '',
    claimant: '',
    claimantType: '',
    claimantInfluence: ''
  });
};

watch(data, (newData) => {
  const hiddenInput = document.getElementById('ee24-form-data');
  if (hiddenInput) {
    hiddenInput.value = JSON.stringify(newData);
  }
}, {deep: true});

</script>

<template>
  <Toast/>
  <div class="ec:p-4">
    <input
        type="hidden"
        id="ee24-form-data"
        name="ee24-form-data"
        :value="JSON.stringify(data)"
    />

    <!-- Loading state -->
    <div v-if="loading" class="ec:text-center ec:py-4">
      <p>Loading...</p>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="ec:text-red-500 ec:p-4">
      Error: {{ error }}
    </div>

    <!-- Content -->
    <div v-else>

      <header class="ec:mb-4 ec:bg-slate-100 ec:p-4 ec:rounded-lg">
        <img
            alt="EuroClimateCheck logo"
            class="!ec:h-16 !ec:max-w-[200px]"
            style="max-width: 200px !important;"
            src="./assets/euroclimatecheck.png"
        />
      </header>

      <main class="ec:bg-white ec:gap-4 ec:flex ec:flex-col">

        <div>
          <div><span>Type of article</span></div>
          <SelectButton
              v-model="data.type"
              :options="articleTypes"
              class="ec:mb-4"
          />
        </div>

        <template v-if="data.type === 'Factcheck' || data.type === 'Prebunk'">
          <Card>
            <template #title><span
                class="ec:flex ec:items-center ec:bg-emerald-900 ec:rounded-lg ec:p-4 ec:text-slate-50 ec:mb-4"><i
                class="fa-solid fa-file ec:mr-2"></i> Basic fields</span></template>

            <template #content>
              <div class="ec:flex ec:flex-wrap ec:gap-4">

                <div class="ec:w-full">
                  <div><span>Headline</span></div>
                  <InputText
                      class="ec:w-full ec:!border ec:!border-slate-300"
                      v-model="data.headlineNative"
                      placeholder="Headline of the article in native language"
                  />
                </div>

                <div class="ec:w-full">
                  <div class="ec:flex ec:flex-row ec:gap-2 ec:items-center">
                    <span>Headline in English</span>
                  </div>
                  <div class="ec:flex ec:items-center ec:gap-2">
                    <InputText
                        class="ec:!border ec:grow ec:!border-slate-300"
                        v-model="data.headline"
                        placeholder="Headline of the article, translated to English"
                    />
                    <TranslateButton style="width: 20% !important;"
                                     :getSourceText="() => data.headlineNative"
                                     :updateTargetField="(text) => data.headline = text"
                                     :apiConfig="apiConfig"
                    />
                  </div>
                </div>
              </div>

              <div class="ec:flex ec:flex-wrap ec:gap-4 ec:mt-8">
                <div class="ec:w-1/4">
                  <div><span>Language of the article</span></div>
                  <Select v-model="data.inLanguage"
                          :options="cleanLanguages"
                          filter
                          placeholder="Select a language"
                          optionLabel="name"
                          class="ec:w-full"/>
                </div>

                <div class="ec:grow">
                  <div><span>Mentioned countries</span></div>
                  <MultiSelect class="ec:w-full" v-model="data.contentLocation" filter :options="cleanAllowedCountries"
                               placeholder="Select one or more countries"
                               optionLabel="name"></MultiSelect>
                </div>

              </div>

              <div class="ec:flex ec:flex-wrap ec:gap-4 ec:mt-8">

                <div class="ec:w-2/5">
                  <div><span>Topic</span></div>
                  <Select v-model="data.topic" :options="topics" filter
                          placeholder="Select a topic"
                          class="ec:w-full"/>
                </div>

                <div class="ec:flex-grow">
                  <div><span>Subtopics</span></div>
                  <MultiSelect class="ec:w-full" v-model="data.subtopics" filter :options="subTopic[data.topic]"
                               :select-all="false" placeholder="Select one or more subtopics"></MultiSelect>
                </div>
              </div>

              <div class="ec:flex ec:flex-col ec:flex-wrap ec:gap-4 ec:mt-8">

                <div class="ec:flex-grow">
                  <div><span>Add keywords separated by commas</span></div>
                  <InputText
                      class="ec:!border ec:!border-slate-300 ec:w-full"
                      v-model="newKeyword"
                      @input="addKeyword"
                      placeholder="Keyword…"
                  ></InputText>
                </div>

                <div class="ec:w-2/5 ec:transition-all" v-if="data.keywords.length > 0">
                  <div><span>Keywords</span></div>
                  <div class="ec:w-full ec:flex ec:gap-2">
                    <template v-for="keyword in data.keywords">
                      <Chip :label="keyword" removable/>
                    </template>
                  </div>
                </div>
              </div>

            </template>

          </Card>
        </template>

        <template v-if="data.type === 'Factcheck'">

          <Card>
            <template #title><span
                class="ec:flex ec:items-center ec:bg-amber-900 ec:rounded-lg ec:p-4 ec:text-slate-50 ec:mb-4"><i
                class="fa-solid fa-check ec:mr-2"></i> Claim, rating and harm</span></template>
            <template #content>
              <div class="ec:flex ec:flex-wrap ec:gap-4">
                <div class="ec:w-full">
                  <div><span>Claim text in native language</span></div>
                  <InputText class="ec:w-full ec:!border ec:!border-slate-300" v-model="data.claimReviewedNative"
                             placeholder="Original claim text"></InputText>
                </div>

                <div class="ec:w-full">
                  <div><span>Claim text in English</span></div>
                  <div class="ec:flex ec:items-center ec:gap-2">
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300" v-model="data.claimReviewed"
                             placeholder="Claim text, translated to English"></InputText>
                    <TranslateButton style="width: 20% !important;"
                                     :getSourceText="() => data.claimReviewedNative"
                                     :updateTargetField="(text) => data.claimReviewed = text"
                                     :apiConfig="apiConfig"
                    />
                  </div>
                </div>

                <div class="ec:w-1/3">
                  <div><span>Rating</span></div>
                  <Select filter v-model="data.reviewRating" :options="ratingOptions" placeholder="Select rating"
                          class="ec:w-full"/>
                </div>

                <div class="ec:w-1/3">
                  <div><span>Multiple claims?</span></div>
                  <SelectButton v-model="data.multiclaim" :options="yesNoOptions"/>
                </div>

                <div class="ec:w-full">
                  <div><span>Distortion types</span></div>
                  <MultiSelect filter v-model="data.distortionType" :options="distortionTypes"
                               placeholder="Select distortion types" class="ec:w-full" :selectAll="false"/>
                </div>

                <div class="ec:w-full">
                  <div><span>AI verification methods</span></div>
                  <MultiSelect filter v-model="data.aiVerification" :options="aiVerificationMethods"
                               placeholder="Select AI verification methods" class="ec:w-full" :selectAll="false"/>
                </div>

                <div class="ec:flex ec:w-full ec:gap-4">
                  <div>
                    <div><span>Potential for harm?</span></div>
                    <SelectButton v-model="data.harm" :options="yesNoOptions"/>
                  </div>

                  <div class="ec:grow">
                    <div><span>Harm escalation</span></div>
                    <Select filter v-model="data.harmEscalation" :options="harmEscalationLevels"
                            placeholder="Select harm escalation" class="ec:w-full"/>
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <Card>
            <template #title>
              <span class="ec:flex ec:items-center ec:bg-rose-900 ec:rounded-lg ec:p-4 ec:text-slate-50 ec:mb-4">
                <i class="fa-solid fa-binoculars ec:mr-2"></i> Claim appearance(s)
              </span>
            </template>
            <template #content>
              <div class="ec:flex ec:flex-col ec:gap-4">
                <Button @click="addClaimAppearance" severity="secondary" class="ec:w-fit">
                  <i class="fa-solid fa-plus ec:mr-2"></i> Add claim appearance
                </Button>

                <div v-for="(appearance, index) in data.claimAppearances" :key="index"
                     class="ec:flex ec:flex-col ec:gap-4 ec:p-4 ec:ml-4 ec:border-l-2 ec:border-rose-900">

                  <div class="ec:flex ec:gap-4">
                    <div class="ec:w-1/2">
                      <div><span>Platform</span></div>
                      <Select filter v-model="appearance.platform"
                              :options="platformOptions"
                              optionLabel="label"
                              optionValue="value"
                              placeholder="Select platform"
                              class="ec:w-full"/>
                    </div>

                    <div class="ec:w-1/2">
                      <div><span>Diffusion format</span></div>
                      <Select filter v-model="appearance.difussionFormat"
                              :options="formatOptions"
                              optionLabel="label"
                              optionValue="value"
                              placeholder="Select format"
                              class="ec:w-full"/>
                    </div>
                  </div>

                  <div class="ec:w-full">
                    <div><span>Appearance URL</span></div>
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                               v-model="appearance.url"/>
                  </div>

                  <div class="ec:w-full">
                    <div><span>Archive URL</span></div>
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                               v-model="appearance.archivedAt"/>
                  </div>

                  <div class="ec:flex ec:gap-4">
                    <div class="ec:w-1/4">
                      <div><span># views</span></div>
                      <InputText type="number" class="ec:w-full ec:!border ec:!border-slate-300"
                                 v-model="appearance.views"/>
                    </div>

                    <div class="ec:w-1/4">
                      <div><span># likes</span></div>
                      <InputText type="number" class="ec:w-full ec:!border ec:!border-slate-300"
                                 v-model="appearance.likes"/>
                    </div>

                    <div class="ec:w-1/4">
                      <div><span># comments</span></div>
                      <InputText type="number" class="ec:w-full ec:!border ec:!border-slate-300"
                                 v-model="appearance.comments"/>
                    </div>

                    <div class="ec:w-1/4">
                      <div><span># shares</span></div>
                      <InputText type="number" class="ec:w-full ec:!border ec:!border-slate-300"
                                 v-model="appearance.shares"/>
                    </div>
                  </div>

                  <div class="ec:flex ec:gap-4">
                    <div class="ec:w-1/4">
                      <div><span>Appearance date</span></div>
                      <DatePicker v-model="appearance.appearanceDate" showIcon iconDisplay="input"/>
                    </div>

                    <div class="ec:w-1/4 hidden">
                      <div><span>Action taken by platform?</span></div>
                      <SelectButton v-model="appearance.actionTaken" :options="yesNoOptions"/>
                    </div>

                  </div>

                  <div class="ec:w-full hidden">
                    <div><span>Appearance body</span></div>
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                               v-model="appearance.appearanceBody"/>
                  </div>

                  <div class="ec:flex ec:gap-4">
                    <div class="ec:grow">
                      <div><span>Claimant</span></div>
                      <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                                 v-model="appearance.claimant"/>
                    </div>

                    <div class="ec:w-1/4">
                      <div><span>Claimant type</span></div>
                      <Select filter v-model="appearance.claimantType"
                              :options="claimantTypes"
                              placeholder="Select type"
                              class="ec:w-full"/>
                    </div>

                    <div class="ec:w-1/3">
                      <div><span>Claimant influence</span></div>
                      <Select filter v-model="appearance.claimantInfluence"
                              :options="influenceLevelOptions"
                              optionLabel="label"
                              optionValue="value"
                              placeholder="Select influence level"
                              class="ec:w-full"/>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <Card>
            <template #title>
              <span class="ec:flex ec:items-center ec:bg-sky-900 ec:rounded-lg ec:p-4 ec:text-slate-50 ec:mb-4">
                <i class="fa-solid fa-receipt ec:mr-2"></i> Evidence(s)
              </span>
            </template>
            <template #content>
              <div class="ec:flex ec:flex-col ec:gap-4">
                <Button @click="addEvidence" severity="secondary" class="ec:w-fit">
                  <i class="fa-solid fa-plus ec:mr-2"></i> Add evidence
                </Button>

                <div v-for="(evidence, index) in data.evidences" :key="index"
                     class="ec:flex ec:flex-col ec:gap-4 ec:p-4 ec:ml-4 ec:border-l-2 ec:border-sky-900">
                  <div class="ec:w-full">
                    <div><span>Question</span> <span class="ec:italic ec:text-xs ec:text-slate-500">What specific question addresses the claim being evaluated?</span>
                    </div>
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                               v-model="evidence.question"
                    />
                  </div>

                  <div class="ec:w-full">
                    <div><span>Answer</span> <span class="ec:italic ec:text-xs ec:text-slate-500">What is the response or conclusion regarding the specific question?</span></div>
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                               v-model="evidence.answer"
                    />
                  </div>

                  <div class="ec:w-full">
                    <div><span>Source URL</span> <span class="ec:italic ec:text-xs ec:text-slate-500">Where can the evidence supporting this answer be found?</span></div>
                    <InputText class="ec:w-full ec:!border ec:!border-slate-300"
                               v-model="evidence.url"
                    />
                  </div>

                  <div class="ec:w-full">
                    <div><span>Type of evidence provided</span> <span class="ec:italic ec:text-xs ec:text-slate-500">What type of evidence was used to support the claim verification?</span></div>
                    <Select filter v-model="evidence.type"
                            :options="evidenceTypes"
                            placeholder="Select evidence type"
                            class="ec:w-full"/>
                  </div>
                </div>
              </div>
            </template>
          </Card>

        </template>

      </main>
    </div>
  </div>
</template>
