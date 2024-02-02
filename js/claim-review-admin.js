let appearanceIndex = 0;
let appearanceIndexSelected = 0;
let apikey = "";
let domain = "";
let endpoint = "";

jQuery(document).ready(function () {

    apikey = jQuery('#ee24-data').data('apikey');
    domain = jQuery('#ee24-data').data('domain');
    endpoint = jQuery('#ee24-data').data('endpoint');

    let frame = null;
    appearanceIndex = jQuery('.appearance').length + 1;

    jQuery('.allclaims-box').on('click', 'button.claim-more-fields', function (e) {
        e.preventDefault();
        jQuery(this).parent().next(".claim-more-fields-box").toggle();
    });

    jQuery('.cr-add-claim-field').on('click', function (e) {
        e.preventDefault();
        var number = jQuery(this).data('target');
        var newmetabox = metabox.metabox.replace(/%%JS%%/g, number);
        number++;
        jQuery(this).data('target', number);
        jQuery('.allclaims-box').append(newmetabox);
    });

    jQuery('body').on('focus', ".crdatepicker", function () {
        jQuery(this).datepicker({
            dateFormat: "yy-mm-dd"
        });
    });

    jQuery('.allclaims-box').on('click', 'a.add-claim-appearance', function (e) {
        e.preventDefault();
        var arraykey = jQuery(this).data('arraykey');
        var html = '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' + arraykey + '][appearance][url][]" value="" placeholder="" /></td><td style="width:25%;"><button class="button button-secondary cr-remove-row">Remove</button></td></tr>';
        jQuery(this).closest('.claimbox').find('table.claim-appearance > tbody').append(html);
    });

    jQuery('.allclaims-box').on('click', 'button.cr-remove-claim', function (e) {
        e.preventDefault();
        var number = jQuery(this).data('remove-target');
        var claimbox = jQuery(this).closest('.claimbox');
        var box = claimbox.data('box');

        if (number == box) {
            claimbox.remove();
        }
    });

    jQuery('.allclaims-box').on('click', 'button.cr-remove-row', function (e) {
        e.preventDefault();
        var claimrow = jQuery(this).closest('tr');
        claimrow.remove();
    });

    new TomSelect("#input-keywords", {
        persist: false,
        createOnBlur: true,
        create: true
    });

    new TomSelect("#select-country-origin", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#language", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-content-location", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-topics", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-parties", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-rating", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-eu-relation", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-debunk-rating", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    new TomSelect("#select-debunk-eu-relation", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });

    document.querySelectorAll('.tomselect-init').forEach(el => {
        new TomSelect(el, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });

    document.querySelectorAll('.translate-button').forEach((el) => {
        el.onclick = function () {

            let element = jQuery(this);
            let input = element.siblings('.translatable');


            let dataSelector = jQuery(this).data('source');

            let originText = jQuery("." + dataSelector).text();
            if (!originText || originText === "") {
                originText = jQuery("." + dataSelector).val();
            }

            translate(originText, input, element.find('i'));
        }
    });

    showArticleTypeFields();

    jQuery('input[name="article-type"]').on('change', function () {
        showArticleTypeFields();
    });

    jQuery("body").on("click", ".choose-media-button", function (e) {
        e.preventDefault();
        let currentElement = jQuery(this);
        appearanceIndexSelected = currentElement.data('index');

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });

        // When an image is selected in the media frame...
        frame.on('select', function () {
            // Get media attachment details from the frame state
            let attachment = frame.state().get('selection').first().toJSON();

            // Send the attachment URL to our custom image input field.
            jQuery("#associatedMedia-" + appearanceIndexSelected).val(attachment.url)
            // image-attachment currentElement.parent().parent().parent().find('.image-attachment')
            jQuery(`.image-attachment-${appearanceIndexSelected}`).html('<img src="' + attachment.url + '" />');

        });

        // Finally, open the modal on click
        frame.open();
    });

    jQuery("#add-appearance").on('click', function (e) {
        createAppearance();
    });
});

// Wrap your operations into a function that gets the checked input
function showArticleTypeFields() {
    // Get the selected article type
    let articleType = jQuery('input[name="article-type"]:checked').val();

    // Hide all article type fields
    jQuery('.article-type-field').slideUp();

    if (articleType === undefined) {
        return;
    }

    // Show the fields for the selected article type
    jQuery('.article-type-field-' + articleType.toLowerCase()).slideDown();
}

function translate(textToTranslate, element, icon) {

    let authHeaders = new Headers();
    authHeaders.append("X-API-KEY", apikey);
    authHeaders.append("X-DOMAIN", domain);
    authHeaders.append("Content-Type", "application/json");

    let raw = JSON.stringify({
        "text": textToTranslate
    });

    let requestOptions = {
        method: 'POST',
        headers: authHeaders,
        body: raw,
        redirect: 'follow'
    };

    icon.addClass('fa-spin');

    fetch(endpoint + "/translate", requestOptions)
        .then(response => response.text())
        .then(result => {
            element.val(result);
            icon.removeClass('fa-spin');
        })
        .catch(error => {
            console.log('error', error)
            icon.removeClass('fa-spin');
        });
}

function createAppearance() {

    let appearanceUrl = `
    <div>
        <label for="appearance-url-${appearanceIndex}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">URL</label>
        <input id="appearance-url-${appearanceIndex}" class="h-9 px-2 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full" name="appearance-url[]"  value="" autocomplete="off" placeholder="">
    </div>`;

    let archivedAt = `
    <div>
        <label for="archived-url-${appearanceIndex}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Archived URL</label>
        <input id="archived-url-${appearanceIndex}" class="h-9 px-2 border border-{#d0d0d0} text-gray-900 text-sm rounded-lg w-full" name="archived-url[]"  value="" autocomplete="off" placeholder="">
    </div>`;

    let addMediaElement = `
    <div class="flex items-end w-100">
        <div data-index="${appearanceIndex}" role="button" class="choose-media-button flex-grow h-9 text-sm flex justify-center items-center font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
            <div><i class="fa-solid fa-photo-film"></i> Add media element</div>
        </div>
    </div>`;

    let platform = `
    <div>
        <label for="platforms-${appearanceIndex}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Platform</label>
        <select class="platforms" id="platforms-${appearanceIndex}" name="platforms[]" placeholder="Select a platform..." autocomplete="off">
            <option value="">Select a platform...</option>
            <option value="facebook">Facebook</option>
            <option value="youtube">YouTube</option>
            <option value="x">X</option>
            <option value="instagram">Instagram</option>
            <option value="tiktok">Tiktok</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="linkedin">LinkedIn</option>
            <option value="pinterest">Pinterest</option>
            <option value="telegram">Telegram</option>
            <option value="signal">Signal</option>
            <option value="snapchat">Signal</option>
            <option value="other">Other</option>
        </select>
    </div>`;

    let format = `
    <div>
        <label for="formats-${appearanceIndex}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Format</label>
        <select class="formats" id="formats-${appearanceIndex}" name="formats[]" placeholder="Select a format..." autocomplete="off">
            <option value="">Select a format...</option>
            <option value="Image">Image</option>
            <option value="Video">Video</option>
            <option value="Article">Article</option>
            <option value="Audio">Audio</option>
            <option value="Other">Other</option>
        </select>
    </div>`;

    let associatedMultimediaFormat = `
    <div>
        <label for="associatedMultimediaFormat-${appearanceIndex}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Associated Multimedia Format</label>
        <select class="formats" id="associatedMultimediaFormat-${appearanceIndex}" name="associatedMultimediaFormats[]" placeholder="Select a format..." autocomplete="off">
            <option value="">Select a format...</option>
            <option value="image">Image</option>
            <option value="video">Video</option>
            <option value="audio">Audio</option>
            <option value="other">Other</option>
        </select>
    </div>`;

    let associatedMedia = `<input type="hidden" id="associatedMedia-${appearanceIndex}" name="associatedMedia[]"/>`;

    let recurrence = `<div class="appearance pl-4 border-l border-l-4 rounded border-green-400"><div class="image-attachment-${appearanceIndex} w-8"></div><div class="my-4 grid grid-cols-2 gap-4">${appearanceUrl}${archivedAt}</div><div class="my-4 grid grid-cols-4 gap-4">${platform}${format}${associatedMedia}${associatedMultimediaFormat}${addMediaElement}</div></div>`;

    jQuery(".appearances").append(recurrence);

    document.querySelectorAll(`#formats-${appearanceIndex}`).forEach((el) => {
        let settings = {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        };
        new TomSelect(el, settings);
    });

    document.querySelectorAll(`#platforms-${appearanceIndex}`).forEach((el) => {
        let settings = {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        };
        new TomSelect(el, settings);
    });

    document.querySelectorAll(`#associatedMultimediaFormat-${appearanceIndex}`).forEach((el) => {
        let settings = {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        };
        new TomSelect(el, settings);
    });

    appearanceIndex++;
}