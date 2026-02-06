(function ($) {


    // for making any tab as default tab for testing and working nicely after refresh.
    // send user to the right tab based on the query string or url
    const QS = window.location.search;
    if (QS.indexOf('allPages') >= 0) {
        setTimeout(function () {
            $('a[href="#allPages"]').trigger('click');
        }, 150);
    } else if (QS.indexOf('editTemplates') >= 0) {
        setTimeout(function () {
            $('a[href="#editTemplates"]').trigger('click');
        }, 150);
    } else if (QS.indexOf('home') >= 0) {
        setTimeout(function () {
            $('a[href="#home"]').trigger('click');
        }, 150);
    } else if (QS.indexOf('createLegalPage') >= 0) {
        setTimeout(function () {
            $('a[href="#createLegalPage"]').trigger('click');
        }, 150);
    } else if (QS.indexOf('support') >= 0) {
        setTimeout(function () {
            $('a[href="#Support"]').trigger('click');
        }, 150);
    }

    // tooltips
    $(function () {
        $('a[title]').tooltip();
    });

    // home tab's tab
    $(".btn-pref .btn").click(function () {
        $(".btn-pref .btn").removeClass("btn-primary").addClass("btn-default");
        $(this).removeClass("btn-default").addClass("btn-primary");
    });

    // notification close button functionality
    $(document).on('click', '#adl_close_it', function (e) {
        $(this).closest('div').hide();
    });

    /*
     * GENERAL INFORMATION TAB
    */
    // save general data of user site
    $('#adl_lp_g_settings').on('click', function (e) {
        e.preventDefault();

        var form = $("#adl_lp_gs_form");
        var formData = form.serialize();

        $("#successResult").remove();

        // Show loader
        if ($('#adl_lp_loader').length === 0) {
            $('body').append('<div id="adl_lp_loader"></div>');
        }
        $('#adl_lp_loader').show();

        adlAjaxHandler(form, 'general_info_handler', formData, function (data) {

            // Hide loader
            $('#adl_lp_loader').fadeOut();

            if (data === 'success') {
                autoCLoseMessage('Success: Data saved. <span id="adl_close_it">&times;</span>', 2000);
            } else {
                autoCLoseMessage('Error: Something went wrong. <span id="adl_close_it">&times;</span>', 2000);
            }
        });
    });



    // Reset general data in the FORM
    $('#adl_lp_g_settings_reset').on('click', function (e) {
        e.preventDefault();
        resetForm($("#adl_lp_gs_form"));

    });


    // Reset general data in the DATABASE
    $('#adl_lp_g_settings_resetdb').on('click', function (e) {
        e.preventDefault();
        var form = $("#adl_lp_gs_form");
        var formData = form.serialize();

        $("#successResult").remove();

        // Show loader
        if ($('#adl_lp_loader').length === 0) {
            $('body').append('<div id="adl_lp_loader"></div>');
        }
        $('#adl_lp_loader').show();

        // get submitted form data and send them to the ajax handler
        var iconBindingElement = jQuery('#adl_ajax_loader');
        adlAjaxHandler(iconBindingElement, 'reset_general_info_handler', formData, function (data) {

            // Hide loader
            $('#adl_lp_loader').fadeOut();

            // Reset the form
            resetForm(form);

            // Show result message
            if (data === 'success') {
                autoCLoseMessage('Success: Data has been reset. <span id="adl_close_it">&times;</span>', 3000);
            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong</p><pre>' + data + '</pre></div>').insertAfter(form);
            }
        });
    });


    /*
     * SOCIAL INFORMATION
    */
    // save social data into the database
    $('#social_info_submit').on('click', function (e) {

        e.preventDefault();
        var form = $("#socialInfoForm");
        var formData = form.serialize();
        $("#successResult").remove();
        // get submitted from data and serialize them and send them to the ajax handler

        adlAjaxHandler(form, 'social_info_handler', formData, function (data) {

            if (data === 'success') {
                autoCLoseMessage('Success: Data saved. <span id="adl_close_it">&times;</span>', 2000);
            } else {
                // for debugging only: add this : <pre>'+data+'</pre> below
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(form);

            }
        });

    });


    // Reset social data in the FORM inputs
    $('#adl_lp_s_settings_reset').on('click', function (e) {
        e.preventDefault();
        resetForm($("#socialInfoForm")); // reset the form inputs
        $("#successResult").remove(); // remove if any messaging div is shown.

    });


    // Reset general data in the database
    $('#adl_lp_s_settings_resetdb').on('click', function (e) {
        e.preventDefault();
        var form = $("#socialInfoForm");
        var formData = form.serialize();

        $("#successResult").remove();

        adlAjaxHandler(form, 'reset_social_info_handler', formData, function (data) {
            resetForm(form);
            if (data === 'success') {
                autoCLoseMessage('Success: Data saved. <span id="adl_close_it">&times;</span>', 2000);
            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong</p><pre>' + data + '</pre></div>').insertAfter(form);

            }
        });
    });


    /*
     * Miscellaneous
    */
    // save Miscellaneous data into the database
    $('#misc_setting_submit').on('click', function (e) {
        e.preventDefault();

        var form = $("#misc_form");
        var formData = form.serialize();

        $("#successResult").remove();

        // Show loader
        if ($('#adl_lp_loader').length === 0) {
            $('body').append('<div id="adl_lp_loader"></div>');
        }
        $('#adl_lp_loader').show();

        // Send AJAX request
        adlAjaxHandler(form, 'misc_info_handler', formData, function (data) {

            // Hide loader
            $('#adl_lp_loader').fadeOut();

            // Show result message
            if (data === 'success') {
                autoCLoseMessage('Success: Data saved. <span id="adl_close_it">&times;</span>', 2000);
            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(form);
            }
        });
    });



    /*
     * CREATE LEGAL PAGES TAB's JS CODES GO HERE
    */
    //Show template list based on selected types
    $('#adl_lp_type').on('change', function (e) {
        var chooseTemplate = $('#ChooseTemplate');
        var formData = $("#showTemplateTypeForm").serialize();
        $("#successResult").remove();

        adlAjaxHandler(chooseTemplate, 'showTemplate_type_handler', formData, function (data) {

            if (data !== 'success') {
                autoCLoseMessage('Template list updated successfully. <span id="adl_close_it">&times;</span>', 2000);
                chooseTemplate.html('<h3>Choose a Template</h3>' + data);

            } else {
                chooseTemplate.html('');
                autoCLoseMessage('<p class="notice notice-error">ERROR: something went wrong</p> <span id="adl_close_it">&times;</span>', 3000);
            }
        });

    });


    // modify the tinyMCE content upon selection of the template
    $('#ChooseTemplate').on('click', 'a', function (e) {
        const form = $("#showTemplateTypeForm");
        e.preventDefault();
        // var type =  data +'&' + $(this).data('type');
        //const id = 'template_id=' + $(this).data('id');
        const lp_title = $('#lp_title');
        var data = 'template_id=' + $(this).data('id');
        data += '&adl_LP_nonce=' + $(this).data('nonce');
        // console.log(id);
        $("#successResult").remove();
        // get submitted from data and serialize them and send them to the ajax handler

        //var iconBindingElement = jQuery('#adl_ajax_loader');
        adlAjaxHandler(form, 'fetch_and_insert_template_data', data, function (data) {
            if (!tinyMCE.activeEditor) jQuery('.wp-editor-wrap .switch-tmce').trigger('click');
           
            if ( data.success !== false ) {
                var jsn = isJson(data);
                if (jsn) {
                    var parsedData = JSON.parse(data);

                    lp_title.val(parsedData[0]);
                    tinyMCE.activeEditor.setContent(parsedData[1]);
                }
                autoCLoseMessage('Page Content Updated Successfully. <span id="adl_close_it">&times;</span>', 2000);
            } else {
                autoCLoseMessage( '<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.</p></div>', 2000 );
            }
        });
    });

// $('#adl_lp_loader').show();
    //Save Legal Page to the Database and modify the tinyMCE content to let user edit option.
    const cbtn = $('#addNewLegalPage');

        cbtn.on('submit', function (e) {
            e.preventDefault();

            // Show loader
            $('#adl_lp_loader').show();

            // Ensure Visual editor is active
            if (!tinyMCE.activeEditor) {
                jQuery('.wp-editor-wrap .switch-tmce').trigger('click');
            }

            // Serialize form + TinyMCE content
            const data =
                $(this).serialize() +
                '&content=' +
                tinyMCE.activeEditor.getContent({ format: 'html' });

            $('#successResult').remove();

            adlAjaxHandler(cbtn, 'addNewLegalPage', data, function (response) {

                // Hide loader
                $('#adl_lp_loader').fadeOut(300);


                // Ensure Visual editor is active again
                if (!tinyMCE.activeEditor) {
                    jQuery('.wp-editor-wrap .switch-tmce').trigger('click');
                }

                if (response !== 'error') {

                    var parsedData = [];

                    if (isJson(response)) {
                        parsedData = JSON.parse(response);
                    }


                    let viewUrl = parsedData[0];
                    let editUrl = parsedData[1];

                    let message =
                        'Page Created Successfully.<br>' +
                        '<a href="' + viewUrl + '" target="_blank" style="color:#fff;text-decoration:underline;margin-right:10px;">View Page</a>' +
                        editUrl;

                    toastr.success(message, 'Success', {
                        timeOut: 8000,
                        closeButton: true,
                        progressBar: true,
                        escapeHtml: false
                    });
                } else {
                    $('<div class="notice notice-error is-dismissible" id="successResult">' +
                        '<p>Error: Something went wrong.<pre>' + response + '</pre></p>' +
                      '</div>').insertAfter(cbtn);
                }
            });
        });


    /*
     * CODES FOR ALL LEGAL PAGES
    */
    // Refresh the page on user click on refresh button
    $('#refreshPage').on('click', function (e) {
        e.preventDefault();
        location.reload();
    });

    // move the page to the trash on user click on trash icon. NEXT ADD CONFIRM AND USE SWEET ALERT JS LIBRARY
    jQuery(document).on('click', '.moveToTrash', function (e) {
        e.preventDefault();

        const $this = jQuery(this);
        const postId = $this.data('id');
        const nonce = $this.data('nonce');
        const pageTitle = $this.closest('tr').find('td:nth-child(2) a').text().trim(); // Get page title from 2nd column

        // Build modal HTML
        const modalHTML = `
            <div id="adl-trash-modal-overlay">
                <div id="adl-trash-modal">
                    <h3>Move Page to Trash</h3>
                    <p>Are you sure you want to move the page "<strong>${pageTitle}</strong>" to the Trash?</p>
                    <button class="btn btn-confirm">Yes, Move</button>
                    <button class="btn btn-cancel">Cancel</button>
                </div>
            </div>
        `;

        // Append to container
        const $container = jQuery('#adl-trash-modal-container');
        $container.html(modalHTML);

        // Show overlay
        jQuery('#adl-trash-modal-overlay').fadeIn();

        // Cancel button
        jQuery('#adl-trash-modal .btn-cancel').on('click', function () {
            jQuery('#adl-trash-modal-overlay').fadeOut(function () {
                $container.empty();
            });
        });

        // Confirm button
        jQuery('#adl-trash-modal .btn-confirm').on('click', function () {
            const data = '&post_id=' + postId + '&adl_LP_nonce=' + nonce;

            adlAjaxHandler(null, 'moveToTrash', data, function (response) {
                jQuery('#adl-trash-modal-overlay').fadeOut(function () {
                    $container.empty(); // Remove modal
                });

                if (response === 'success') {
                    autoCLoseMessage(
                        `Page "<strong>${pageTitle}</strong>" has been moved to the Trash Successfully<span id="adl_close_it">&times;</span>`,
                        3000
                    );
                    $this.closest('tr').fadeOut();
                } else {
                    toastr.error('Something went wrong. Please try again.', 'Error', { timeOut: 5000, closeButton: true });
                }
            });
        });
    });



    // go to create legal page's tab on clicking on a button
    $('#CreateALegalPage').on('click', function (e) {
        e.preventDefault();
        $('a[href="#createLegalPage"]').trigger('click');
    });

    /*
     * CODES FOR CREATE LEGAL PAGE TEMPLATE PAGE
    */
    //Save Legal Page Template to the Database
    const create_lp_template = $('#addNewLegalTemplate');
    create_lp_template.on('submit', function (e) {
        if (!tinyMCE.activeEditor) jQuery('.wp-editor-wrap .switch-tmce').trigger('click');

        const data = $(this).serialize() + '&adl_lp_template=' + tinyMCE.activeEditor.getContent({
            format: 'html'
        }); // get all forms field and then get modified tinymce adl_lp_template and add that to the serialized strings.
        const lp_title = $('#lp_title');
        $('#successResult').remove();

        e.preventDefault();
        adlAjaxHandler(create_lp_template, 'addNewLegalTemplate', data, function (data) {
            // if(!tinyMCE.activeEditor)jQuery('.wp-editor-wrap .switch-tmce').trigger('click');

            if (data == 'success') {
                const msg = 'Legal Page Template has been created Successfully. You can view and edit on All Legal Page Tab under Legal Pages menu</br><span id="adl_close_it">&times;</span>';
                autoCLoseMessage(msg, 10000);
            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(create_lp_template);

            }
        });
    });


    //UPDATE Legal Page Template to the Database
    const edit_lp_template = $('#editLegalTemplate');
    edit_lp_template.on('submit', function (e) {
        if (!tinyMCE.activeEditor) jQuery('.wp-editor-wrap .switch-tmce').trigger('click');

        var data = $(this).serialize();
        data += '&adl_lp_template=' + tinyMCE.activeEditor.getContent({
            format: 'html'
        })
        data += '&id=' + edit_lp_template.data('id'); // get all forms field and then get modified tinymce adl_lp_template and add that to the serialized strings.
        data += '&type=' + edit_lp_template.data('type');
        const lp_title = $('#lp_title');
        $('#successResult').remove();

        e.preventDefault();
        adlAjaxHandler(edit_lp_template, 'editLegalTemplate', data, function (data) {
            // if(!tinyMCE.activeEditor)jQuery('.wp-editor-wrap .switch-tmce').trigger('click');

            if (data == 'success') {
                const msg = 'Legal Page Template has been created Successfully. You can view and edit on All Legal Page Tab under Legal Pages menu</br><span id="adl_close_it">&times;</span>';
                autoCLoseMessage(msg, 10000);
            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(edit_lp_template);

            }
        });
    });

    //DELETE the Legal Page Template on user click on trash icon. NEXT ADD CONFIRM AND USE SWEET ALERT JS LIBRARY
    jQuery(document).on('click', 'a.deleteLegalTemplate', function (e) {
        e.preventDefault();

        const $this = jQuery(this);
        const postId = $this.data('id');
        const nonce = $this.data('nonce');
        const container = jQuery('#adl_legal_template_container');

        // Get page title if available
        let pageTitle = $this.closest('tr').find('td:nth-child(2) a').text().trim() || 'this template';

        // Build confirmation modal
        const modalHTML = `
            <div id="adl-trash-modal-overlay">
                <div id="adl-trash-modal">
                    <h3>Delete Page Template</h3>
                    <p>Are you sure you want to <strong>delete permanently</strong> "<strong>${pageTitle}</strong>"?</p>
                    <button class="btn btn-confirm">Yes, Delete</button>
                    <button class="btn btn-cancel">Cancel</button>
                </div>
            </div>
        `;

        // Append modal to container
        const $modalContainer = jQuery('#adl-trash-modal-container');
        $modalContainer.html(modalHTML);
        jQuery('#adl-trash-modal-overlay').fadeIn();

        // Cancel button
        jQuery('#adl-trash-modal .btn-cancel').on('click', function () {
            jQuery('#adl-trash-modal-overlay').fadeOut(function () {
                $modalContainer.empty();
            });
        });

        // Confirm button
            jQuery('#adl-trash-modal .btn-confirm').on('click', function () {
                jQuery('#adl-trash-modal-overlay').fadeOut(function () {
                    $modalContainer.empty();
                });

                // Remove previous success/error messages
                jQuery("#successResult").remove();

                const postData = '&template_id=' + postId + '&adl_LP_nonce=' + nonce;

                // Your existing AJAX handler
                adlAjaxHandler(container, 'deleteLegalTemplate', postData, function (data) {
                    if (data === 'success') {
                        autoCLoseMessage('The Page Template has been deleted Successfully<span id="adl_close_it">&times;</span>', 3000);
                        $this.closest('tr').fadeOut();
                    } else {
                        jQuery('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(container);
                    }
                });
            });
        });


    /*
     * HELPER FUNCTIONS
    */
    function resetForm($form) {
        $form.find('input:text, input:password, input:file, select, textarea').val('');
        $form.find('input:radio, input:checkbox')
            .removeAttr('checked').removeAttr('selected');
    }

    function isJson(item) {
        item = typeof item !== "string" ?
            JSON.stringify(item) :
            item;

        try {
            item = JSON.parse(item);
        } catch (e) {
            return false;
        }

        if (typeof item === "object" && item !== null) {
            return true;
        }

        return false;
    }

    function adlAjaxHandler(ElementToShowLoadingIconAfter, ActionName, arg, CallBackHandler) {

        if (ActionName) data = "action=" + ActionName;
        if (arg) data = arg + "&action=" + ActionName;
        if (arg && !ActionName) data = arg;
        data = data;

        var n = data.search(adl_lp_obj.nonceAction);
        if (n < 0) {
            data = data + "&" + adl_lp_obj.nonceAction + "=" + adl_lp_obj.nonce;
        }

        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                jQuery("<span class='adl_lp_ajax_loading'></span>").insertAfter(ElementToShowLoadingIconAfter);
            },
            success: function (data) {
                jQuery(".adl_lp_ajax_loading").remove();
                CallBackHandler(data);
            }
        });
    }

    function autoCLoseMessage(msg, duration) {
        var el = document.createElement("div");
        // el.setAttribute('class', 'notice notice-success');
        el.setAttribute('id', 'adl-lp-notification');
        el.innerHTML = msg;
        setTimeout(function () {
            el.parentNode.removeChild(el);
        }, duration);
        document.body.appendChild(el);
    }
    

})(jQuery);

jQuery(document).ready(function($){

    // Function to get cookie
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    // Function to set cookie
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    // On tab click, save active tab
    $('.wplp-top-tab-nav button').on('click', function(e){
        e.preventDefault(); // prevent default button behavior if needed
        var tabId = $(this).attr('href');
        setCookie('activeTab', tabId, 7); // store for 7 days

        // Activate tab immediately
        $('.wplp-top-tab-nav button').removeClass('btn-primary').addClass('btn-default');
        $('.wplp-tab-content .tab-pane').removeClass('active');

        $(this).removeClass('btn-default').addClass('btn-primary');
        $(tabId).addClass('active');
    });

    // On page load
    var activeTab = getCookie('activeTab');

    if(activeTab && $(activeTab).length) {
        // Activate saved tab
        $('.wplp-top-tab-nav button').removeClass('btn-primary').addClass('btn-default');
        $('.wplp-tab-content .tab-pane').removeClass('active');

        $('.wplp-top-tab-nav button[href="' + activeTab + '"]').removeClass('btn-default').addClass('btn-primary');
        $(activeTab).addClass('active');
    } else {
        // No cookie or invalid tab, activate first tab
        var firstTab = $('.wplp-top-tab-nav button').first();
        var firstTabId = firstTab.attr('href');

        $('.wplp-top-tab-nav button').removeClass('btn-primary').addClass('btn-default');
        $('.wplp-tab-content .tab-pane').removeClass('active');

        firstTab.removeClass('btn-default').addClass('btn-primary');
        $(firstTabId).addClass('active');
    }

});



toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "2500",
};

jQuery(document).on('click', '.wplp-copy-shortcode', function () {
    var $btn = jQuery(this);
    var shortcode = $btn.siblings('.wplp-shortcode-text').text().trim();

    // Copy to clipboard
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(shortcode);
    } else {
        var $temp = jQuery("<input>");
        jQuery("body").append($temp);
        $temp.val(shortcode).select();
        document.execCommand("copy");
        $temp.remove();
    }

    // Show ✔
    var $originalContent = $btn.html();
    $btn.html('✔');

    // Revert back after 1.5 seconds
    setTimeout(function () {
        $btn.html($originalContent);
    }, 1500);

    // Show Toastr notification
    if (typeof toastr !== 'undefined') {
        toastr.success( `${shortcode} Shortcode copied `, 'Copied!');
    }
});
