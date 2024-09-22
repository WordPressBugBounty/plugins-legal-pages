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

        adlAjaxHandler(form, 'general_info_handler', formData, function (data) {
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

        // get submitted from data and serialize them and send them to the ajax handler

        var iconBindingElement = jQuery('#adl_ajax_loader');
        adlAjaxHandler(iconBindingElement, 'reset_general_info_handler', formData, function (data) {
            resetForm(form);
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
        // get submitted from data and serialize them and send them to the ajax handler
        adlAjaxHandler(form, 'misc_info_handler', formData, function (data) {
            if (data === 'success') {
                autoCLoseMessage('Success: Data saved. <span id="adl_close_it">&times;</span>', 2000)
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


    //Save Legal Page to the Database and modify the tinyMCE content to let user edit option.
    const cbtn = $('#addNewLegalPage');
    cbtn.on('submit', function (e) {
        if (!tinyMCE.activeEditor) jQuery('.wp-editor-wrap .switch-tmce').trigger('click');

        const data = $(this).serialize() + '&content=' + tinyMCE.activeEditor.getContent({
            format: 'html'
        }); // get all forms field and then get modified tinymce content and add that to the serialized strings.
        const lp_title = $('#lp_title');
        $('#successResult').remove();

        e.preventDefault();
        adlAjaxHandler(cbtn, 'addNewLegalPage', data, function (data) {
            if (!tinyMCE.activeEditor) jQuery('.wp-editor-wrap .switch-tmce').trigger('click');

            if (data != 'error') {
                var jsn = isJson(data); // check if the data is in JSON format.
                if (jsn) {
                    var parsedData = JSON.parse(data); // parsed JSON Object retuned from the database.


                }
                const msg = '<span style="flex: 0 0 100%">Page Created Successfully. You can view and edit page as normal page under WordPress Pages menu</span>' + parsedData[0] + ' ' + parsedData[1] + '<span id="adl_close_it">&times;</span>';
                autoCLoseMessage(msg, 8000);

            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(cbtn);

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
    $(document).on('click', '.moveToTrash', function (e) {
        e.preventDefault();
        const $this = $(this);
        const container = $('#legalPageContainer');
        var data = '&post_id=' + $this.data('id');
        data += '&adl_LP_nonce=' + $this.data('nonce');
        // console.dir($this.closest('tr'));
        $("#successResult").remove();

        adlAjaxHandler(container, 'moveToTrash', data, function (data) {

            if (data === 'success') {
                autoCLoseMessage('Page has been moved to the Trash Successfully<span id="adl_close_it">&times;</span>', 3000);
                $this.closest('tr').fadeOut();

            } else {
                // for debugging only: add this : <pre>'+data+'</pre> below
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(container);

            }
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
    $(document).on('click', 'a.deleteLegalTemplate', function (e) {
        e.preventDefault();
        const $this        = $(this);
        const container    = $('#adl_legal_template_container');
        const postID       = '&template_id=' + $this.data('id') + '&adl_LP_nonce=' + $this.data('nonce');
        $("#successResult").remove();

        adlAjaxHandler(container, 'deleteLegalTemplate', postID, function (data) {
            if (data === 'success') {
                autoCLoseMessage('The Page Template has been deleted Successfully<span id="adl_close_it">&times;</span>', 3000);
                $this.closest('tr').fadeOut();

            } else {
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter(container);
            }
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