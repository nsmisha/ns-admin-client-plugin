;(function ($) {


    $(document).ready(function () {

        $('#wpbody-content>.notice-error, #wpbody-content>.notice-warning, #wpbody-content>.notice-success,#wpbody-content>.notice-info, #wpbody-content>.updated,.error, #wpbody-content>.update-nag').hide();

        $(document).on('click', '.comment_reply', function (e) {
            e.stopPropagation();
            e.preventDefault();
            $(this).closest('.comment_thread').find('form').toggleClass('visible');
            $(this).text('Reply');
            if ($(this).closest('.comment_thread').find('form').hasClass('visible')) {
                $(this).text('Hide');
            }
        });

        $(document).on('click', '.thread_task_button_submit', function (e) {
            e.preventDefault();

            var parent = $(this).parents('.thread_comment_submit_box ');
            submitComment(parent, 'ns_add_thread_comment');
        });

        $(document).on('opened', '.view_task_container', function () {
            $(this).closest('.remodal-wrapper').scrollTop(0);

            if ($(this).find('.task-card-image').length > 0 && $(this).find('.task-card-image').length !== null) {
                var parent = $('.remodal-wrapper.remodal-is-opened');
                CreateCanvasFromData(parent);

                //triggeg click on circle to hide description
                $(document).on('click', '.circle', function () {
                    $(this).closest('.circle_wrp').find('.description_wrp').toggleClass('hidden');
                });
                $(document).on('click', '.task_circle_num', function () {
                    var commentNum = $(this).attr('data-value');
                    $('.circle_wrp_' + commentNum).find('.description_wrp').toggleClass('hidden');
                })
            }

        });

        $(document).on('click', '.task_approval_wrp .ns_green_button.reject_task', function (e) {
            e.preventDefault();
            var $this = $(this);
            var parent = $(this).parents('.task-card-comment-submit-box');
            if (parent.find('textarea.task-card-textarea').val().length <= 0 && !$(this).hasClass('reopen')) {
                var error = ['Please input reason this ticket is being rejected.'];
                parent.find('.ns-error-box').html(generateErrors(error));
                return;
            }
            submitComment(parent, 'ns_add_comment');
            var taskID = $(this).attr('data-task-id');
            var data = {
                taskId: taskID,
                status: 'ns_pushed_to_live',
                action: 'ns_change_status',
                nonce: ajax_object.nonce
            };
            $.post(ajax_object.ajax_url, data,
                function (response) {
                    if (typeof response.success != 'undefined') {
                        if (response.success == true) {
                            console.log(response.data);
                            var inst = $this.parents('.view_task_container.remodal-is-opened').remodal();
                            if (!$this.hasClass('reopen')) {
                                inst.close();
                            }
                            location.reload();
                        }
                        if (response.success == false && response.data.errors) {
                            console.log(response.data);
                        }
                    }
                }
            );
        });

        $(document).on('click', '.task_approval_wrp .ns_green_button.live_approval', function (e) {
            e.preventDefault();
            if ($(this).hasClass('closed')) {
                return;
            }
            var environment = '';
            if ($(this).hasClass('live_approval')) {
                $(this).text('Closed');
                $(this).css({
                    'background': '#cccccc',
                    'border-color': '#cccccc'
                });
                environment = 'live';
                $(this).addClass('closed');
            }
            var taskID = $(this).attr('data-task-id');
            $.post(
                ajax_object.ajax_url,
                {
                    'approved_environment': environment,
                    'task_id': taskID,
                    'action': 'task_approve'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        console.log(response.data);
                        var data = {
                            taskId: taskID,
                            status: 'ns_closed',
                            action: 'ns_change_status',
                            nonce: ajax_object.nonce
                        };
                        $.post(ajax_object.ajax_url, data,
                            function (response) {
                                if (typeof response.success != 'undefined') {
                                    if (response.success == true) {
                                        console.log(response.data);
                                        location.reload();
                                    }
                                    if (response.success == false && response.data.errors) {
                                        console.log(response.data);
                                    }
                                }
                            }
                        );
                    }
                });

        });

        var HubMessagePadding = $('.hub_site_message_block .contact_buttons').outerHeight();

        $('.hub_site_message_block').css('padding-bottom', HubMessagePadding);

        $(".timeline_management_block .project_header").first().css("border-top", "1px solid #e1e1e1");

        //Google analytics section
        $('#adminmenu>li.wp-menu-separator').remove();

        $(document).on('click', '#submit_access_code', function (e) {
            e.preventDefault();
            var enteredAccessCode = $('input#access_code').val();
            $.post(
                ajax_object.ajax_url,
                {
                    'access_code': enteredAccessCode,
                    'action': 'ns_save_gapi_access_code'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        location.reload();
                    }
                });
        });


        //add scrollbar to task containers
        $('.staging-list-wrap').mCustomScrollbar();
        $('.complete-list-wrap').mCustomScrollbar();
        $('.in_progress_tasks_wrap').mCustomScrollbar();

        // Plugin management custom tabs
        $(function () {
            $('.tabs nav a').on('click', function () {
                show_content($(this).index());
                $("#ns_dashboard .tabs .content.visible").mCustomScrollbar();
            });

            show_content(0);

            function show_content(index) {
                // Make the content visible
                $('.tabs .content.visible').removeClass('visible');
                $('.tabs .content:nth-of-type(' + (index + 1) + ')').addClass('visible');

                // Set the tab to selected
                $('.tabs nav a.selected').removeClass('selected');
                $('.tabs nav a:nth-of-type(' + (index + 1) + ')').addClass('selected');
            }
        });

        //Plugin activating ajax link
        $(document).on('click', '.ns_wp_activation_link', function (e) {
            e.preventDefault();
            if ($(this).attr('clicked') == 'true') {
                return false;
            }
            var pluginFilepath = $(this).attr('data-path'),
                activeStatus = $(this).attr('data-status'),
                clickedPlugin = $(this);
            clickedPlugin.attr('clicked', 'true');
            $.post(
                ajax_object.ajax_url,
                {
                    'activeStatus': activeStatus,
                    'pluginFilepath': pluginFilepath,
                    'action': 'plugin_activation_link'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        if (activeStatus == 'inactive') {
                            clickedPlugin.siblings('.notice').remove();
                            clickedPlugin.find('span').text('Deactivate: ');
                            clickedPlugin.attr('data-status', 'active');
                            clickedPlugin.after(response.data);
                        } else {
                            clickedPlugin.siblings('.notice').remove();
                            clickedPlugin.find('span').text('Activate: ');
                            clickedPlugin.attr('data-status', 'inactive');
                            clickedPlugin.after(response.data);
                        }
                        clickedPlugin.attr('clicked', 'false');
                        location.reload();
                    }
                }
            )
        });

        //Add custom plugin note
        $(document).on('click', '.add_plugin_note', function (e) {
            e.preventDefault();
            var NevNote = $(this).siblings('textarea').val(),
                NoteTexarea = $(this).siblings('textarea'),
                optionName = NoteTexarea.attr('name');
            if (NevNote.length !== 0 && $(this).hasClass('edit')) {
                var Editbuton = $(this);
                $.post(
                    ajax_object.ajax_url,
                    {
                        'optionName': optionName,
                        'NevNote': NevNote,
                        'action': 'update_plugin_note'
                    },
                    function (response) {
                        if (typeof response.success != 'undefined' && response.success == true) {
                            Editbuton.siblings('p.added_note').remove();
                            NoteTexarea.show();
                            Editbuton.text('Save Note');
                            Editbuton.removeClass('edit');
                            Editbuton.addClass('add');
                        }
                    }
                );
                return false;
            }
            if (NevNote.length !== 0 && $(this).hasClass('add')) {
                NoteTexarea.hide();
                $('<p class="added_note">' + $.trim(NevNote) + '</p>').insertBefore($(this).siblings('.plugin_note'));
                $(this).text('Edit');
                $(this).removeClass('add');
                $(this).addClass('edit');
                $.post(
                    ajax_object.ajax_url,
                    {
                        'optionName': optionName,
                        'NevNote': NevNote,
                        'action': 'update_plugin_note'
                    },
                    function (response) {
                        if (typeof response.success != 'undefined' && response.success == true) {

                        }
                    }
                );
                return false;
            }

        });

        //Admin Menu Management
        $('#adminmenuwrap #adminmenu').append('<ul class="collapsed_ns_menu"><div class="wp-menu-name">Hidden Items<div class="wp-menu-image dashicons-before dashicons-arrow-down-alt2"></div></div><div id="hidden_menus"></div></ul>');
        //Create users tabs
        $('ul.user_tabs li:not(.init)').click(function () {
            var tab_id = $(this).attr('data-tab');
            $('ul.user_tabs li').removeClass('current');
            $('.tab-content').removeClass('current');

            $(this).addClass('current');
            $('.sidebar_management_title').siblings('.tab-content[id="' + tab_id + '"]').addClass('current');
            $("div.tab-content.current").mCustomScrollbar();
        });

        $('.wp-menu-separator').remove();

        // DOCUMENTATION STARTS
        //Create documentation tabs
        $(document).on('click', 'ul.documentation_tabs li', function () {
            var tab_id = $(this).attr('data-tab');

            $('ul.documentation_tabs li').removeClass('current');
            $('.doc-content').removeClass('current');

            $(this).addClass('current');
            // wp.editor.initialize( editorId, configObject );
            $('.docs_header').siblings('.doc-content[id="' + tab_id + '"]').addClass('current');
            if ($('.doc-content.current').find('.repeater_item').length <= 0) {
                $('.doc-content.current').mCustomScrollbar("destroy");
            } else {
                $('.doc-content.current').mCustomScrollbar();
            }
        });

        var roles_obj = [];

        function GetRolesObj() {
            roles_obj = [];
            var all_roles = $('.top-level-role-wrp[data-tab-role]');
            all_roles.each(function () {
                var menu_status = {},
                    menu_obj = {};
                $(this).find('input[type="radio"]:checked').each(function () {

                    menu_obj[$(this).attr('data-menu-id')] = $(this).val();

                });
                menu_status.role = $(this).attr('data-tab-role');
                menu_status.menu_obj = menu_obj;
                roles_obj.push(menu_status);
            });
        }

        GetRolesObj();

        $.post(
            ajax_object.ajax_url,
            {
                'action': 'set_menu_dashboard'
            },
            function (response) {
                if (typeof response.success != 'undefined' && response.success == true) {
                    $.each(response.data, function (k, v) {
                        var currentUserRole = response.data.current_user_role;
                        if (v.role == currentUserRole) {
                            $.each(v.menu_obj, function (key, val) {
                                var ReplacedKey = key.replace('=', '-');
                                ReplacedKey = ReplacedKey.replace('?', '-');
                                ReplacedKey = ReplacedKey.replace('/', '-');
                                var menusToHide = $('ul#adminmenu').find('li[id="' + ReplacedKey + '"]');
                                if (val == 'hidden' && val.length > 0) {
                                    $('ul.collapsed_ns_menu').css('display', 'block');
                                    menusToHide.appendTo('ul.collapsed_ns_menu #hidden_menus');
                                    if (menusToHide.find('.wp-has-current-submenu').length > 0 || menusToHide.find('.current').length > 0) {
                                        $('#hidden_menus').show();
                                    }
                                } else if (val == 'removed' && val.length > 0) {
                                    menusToHide.remove();
                                }
                            });
                            $('#hidden_menus .wp-submenu-head').remove();
                        }

                    });
                }
            }
        );

        $(document).on('click', '.collapsed_ns_menu', function () {
            $(this).find('#hidden_menus').slideToggle('fast');
            $(this).find('.dashicons-before:first').toggleClass('dashicons-arrow-up-alt2');
            $(this).find('.dashicons-before:first').toggleClass('dashicons-arrow-down-alt2');

            // if ( $('#adminmenuwrap').outerHeight() < $('#adminmenuback').outerHeight() ) {
            //     $('#adminmenuwrap').removeClass('adminmenuwrap_botunset');
            // } else {
            //     $('#adminmenuwrap').addClass('adminmenuwrap_botunset');
            // }
        });

        // Sticky WP SIDEBAR

        var sticky = $('#adminmenuwrap');
        var stickyrStopper = $('#wpfooter');
        if (!!sticky.offset()) {

            var generalSidebarHeight = sticky.innerHeight();
            var stickyTop = sticky.offset().top;
            var stickOffset = 0;
            var stickyStopperPosition = stickyrStopper.offset().top;
            var stopPoint = stickyStopperPosition - generalSidebarHeight - stickOffset;
            var diff = stopPoint + stickOffset;

            $(window).scroll(function () { // scroll event
                var windowTop = $(window).scrollTop(); // returns number

                if (stopPoint < windowTop) {
                    sticky.css({position: 'absolute', top: diff});
                } else if (stickyTop < windowTop + stickOffset) {
                    sticky.css({position: 'fixed', top: stickOffset + 32});
                } else {
                    sticky.css({position: 'absolute', top: 'initial'});
                }
            });
        }

        $(document).on('click', '.user_management_tabs .menu_capability_button', function () {
            GetRolesObj();
            $.post(
                ajax_object.ajax_url,
                {
                    'roles_obj': roles_obj,
                    'action': 'ajax_set_user_menu_dashboard'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        location.reload();
                    }
                }
            );
        });


        //editable boxes for documentation centre

        $('.doc-content div[data-sortable-id="sortable"]').each(function () {
            $(this).sortable({
                revert: true,
                items: "div.repeater_item",
                cancel: ".ns_doc_edit"
            });
            $("ul, li").disableSelection();
        });

        if ($('.doc-content.current .repeater_item').length <= 0) {
            $('.save_documentation').hide();
        } else {
            $('div.doc-content.current').mCustomScrollbar();
        }


        // Add new documentation
        $(document).on('click', '#add-row', function () {
            $.post(
                ajax_object.ajax_url,
                {
                    'action': 'documentation_repeater'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        $(response.data.created_html).prependTo('.doc-content.current .ui-sortable');
                        $(".doc-content.current").mCustomScrollbar();
                        $('.save_documentation').show();
                        $('.ns_doc_edit').remodal();
                        var inst = $(response.data.created_html).find('.ns_doc_edit').remodal();
                        inst.open();
                        // Save the tinyMCE content to Textarea
                        tinymce.execCommand('mceAddEditor', true, response.data.created_id);
                        var ed = tinymce.activeEditor;
                        ed.theme.resizeTo('100%', 150);
                    }
                }
            );
            return false;
        });

        //Init editors on open edit doc mode
        $(document).on('click', '.ns_doc_title_block', function () {
            // $(this).siblings('.ns_doc_edit').slideToggle('fast');
            var CurrentTabEditors = $(this).siblings('.ns_doc_edit').find('textarea');
            CurrentTabEditors.each(function () {
                // tinyMCE.execCommand( 'mceFocus', false, $(this).attr('id') );
                tinyMCE.execCommand('mceRemoveEditor', false, $(this).attr('id'));
                tinyMCE.execCommand('mceAddEditor', false, $(this).attr('id'));
            });
        });

        //Click to edit already created documentation
        $(document).on('click', '.doc_edit', function (e) {
            e.preventDefault();
            $(this).closest('.ns_doc_edit').find('.saved_content').hide();
            $(this).closest('.ns_doc_edit').find('.editor_area').show();
            $(this).hide();
        });

        //Change Doc List item name on modal title keyup
        $(document).on('keyup', '.documentation_title', function () {
            var EnteredTitle = $(this).val(),
                titleToAppend = $(this).closest('.ns_doc_edit ').attr('data-remodal-id');
            $('.ns_doc_title_block[data-remodal-target="' + titleToAppend + '"]').find('h2').text(EnteredTitle);
            if ($(this).val().length == 0) {

                $(this).parents('div[data-repeater-item]').find('.ns_doc_title_block h2').text('Enter Title');

            }
        });

        function SaveDocumentation() {
            var tabs = {},
                counter = 1;
            $('.doc-content').each(function () {
                if ($(this).find('.repeater_item').length > 0) {
                    var tabContent = {},
                        tabId = $(this).attr('id');

                    $(this).find('.repeater_item').each(function () {
                        var repeaterID = $(this).attr('data-repeater-item'),
                            singleContent = {},
                            thisEditor = $('.ns_doc_edit[data-remodal-id="' + repeaterID + '"]'),
                            textarea = thisEditor.find('textarea'),
                            title = thisEditor.find('input.documentation_title').val();
                        singleContent[title] = textarea.val();
                        tabContent[repeaterID] = singleContent;
                    });
                    tabs[tabId] = tabContent;
                    counter++;
                }
            });
            $.post(
                ajax_object.ajax_url,
                {
                    'content': tabs,
                    'action': 'documentation_block_save'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        if (response.data == 'saved') {
                            $('#repeatable-fieldset-one .updated').remove();
                            $('#repeatable-fieldset-one').prepend('<div class="updated"><p>Doc(s) successfully saved</p></div>');
                            location.reload();
                        } else {
                            $('#repeatable-fieldset-one .updated').remove();
                            $('#repeatable-fieldset-one').prepend('<div class="updated"><p>Doc(s) successfully removed</p></div>');
                        }
                        $('.delete_task_wrp').remove();
                        history.pushState("", document.title, window.location.pathname + window.location.search);
                        // location.reload();
                    }
                }
            );
        }

        $(document).on('click', '.remove-row', function () {

            $('body').append('<div class="delete_task_wrp"><div class="delete_question"><p>Delete this Document?</p><span class="delete_doc">Yes</span><span class="no">No</span></div></div>')

        });

        $(document).on('click', '.delete_doc', function () {
            var docEl = $('.remodal-wrapper.remodal-is-opened .editor_area .remove-row');
            docEl.parents('div[data-repeater-item]').remove();
            var repeaterItemToRemove = docEl.closest('.ns_doc_edit').attr('data-remodal-id');
            $('div[data-repeater-item="' + repeaterItemToRemove + '"]').remove();
            docEl.closest('.remodal-wrapper').remove();
            SaveDocumentation();
            $('.remodal-overlay').hide();
            if ($('.doc-content.current').find('.repeater_item').length <= 0) {
                $('.doc-content.current').mCustomScrollbar("destroy");
                $('.save_documentation').hide();
            } else {
                $('.doc-content.current').mCustomScrollbar("destroy");
                $('.doc-content.current').mCustomScrollbar();
            }
            return false;
        });

        $('div[data-sortable-id="sortable"]').on('sortstart', function (event, ui) {
            var currentDraggableID = ui.item.find('textarea').attr('id');
            tinyMCE.triggerSave();
            tinyMCE.execCommand("mceRemoveEditor", false, currentDraggableID);
        });

        $('div[data-sortable-id="sortable"]').on('sortstop', function (event, ui) {
            var currentDraggableID = ui.item.find('textarea').attr('id');
            tinyMCE.execCommand("mceAddEditor", false, currentDraggableID);
        });

        $(document).on('click', 'button.save_documentation', function (e) {
            e.preventDefault();
            tinyMCE.triggerSave();
            SaveDocumentation();
        });

        // DOCUMENTATION END

        // $(window).scroll(function () {
        //     var scroll = $(window).scrollTop();
        //     //console.log(scroll);
        //     if (scroll >= 270) {
        //         //console.log('a');
        //         $(".sticky-menu #adminmenuwrap").css('position', 'fixed');
        //         $(".sticky-menu #adminmenuwrap").css('top', 32);
        //     } else {
        //         //console.log('a');
        //         $(".sticky-menu #adminmenuwrap").css('position', 'relative');
        //         $(".sticky-menu #adminmenuwrap").css('top', 0);
        //     }
        // });

        $(document).on('click', '.disconnect', function (e) {
            e.preventDefault();
            $('.connect_wrapper').hide();
            $('.disconnect_wrp').show();
        });
        $(document).on('click', '.stay_connected', function () {
            $('.disconnect_wrp').hide();
            $('button.connection_status').show();
        });
        $(document).on('click', '.break_connection', function (e) {
            e.preventDefault();
            $.post(
                ajax_object.ajax_url,
                {
                    'action': 'remove_connection_data'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        location.reload();
                    }
                }
            );
        });

        $(document).on('click', '.connect', function (e) {
            e.preventDefault();
            var enteredUrl = $('input[name="remote_site_url"]').val(),
                enteredKey = $('input[name="remote_site_key"]').val();
            $.post(
                ajax_object.ajax_url,
                {
                    'enteredKey': enteredKey,
                    'enteredUrl': enteredUrl,
                    'action': 'nsc_veryfy_connection_to_remote_site'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        location.reload();
                    } else {
                        alert('Connection Failed');
                        $('.connection_status').html('Not Connected');
                        $('.connect_wrapper').hide();
                        $('.connection_status').show();
                    }
                }
            );

        });


        //TODO refactor all this
        // new task Creation
        function submitTask(e) {
            //TODO wrapper to prepare data before send
            var Body = $('body');
            $('.preloader-wrapper').show();
            Body.addClass('preloader-site');
            var form = $('#ns-task-form');
            form.find('.ns-error-box').html('');
            $.post(ajax_object.ajax_url, (form.serialize() + '&action=ns_add_task&nonce=' + ajax_object.nonce),
                function (response) {
                    if (typeof response.success != 'undefined') {
                        if (response.success == true && response.data.tasks) {
                            $('.new-task-input-container .remodal-close').trigger('click');
                            displayTaskRequest(response.data.tasks);
                            reInitRemodal();
                            removeTaskInput();
                            $('.preloader-wrapper').fadeOut();
                            $('body').removeClass('preloader-site');
                            $('.preloader-wrapper').hide();
                        }
                        if (response.success == false && response.data.errors) {
                            form.find('.ns-error-box').html(generateErrors(response.data.errors));
                            $('.preloader-wrapper').fadeOut();
                            $('body').removeClass('preloader-site');
                            $('.preloader-wrapper').hide();
                        }
                    }
                }
            );
        };

        function generateErrors(errors) {
            var html = '<div class="ns-error-collection">';
            for (var i in errors) {
                html += '<div class="ns-error">' + errors[i] + '</div>';
            }
            html += '</div>';

            return html;
        }

        function displayTaskRequest(data) {
            $('.request-list-wrap').html(data);
            // $(data).insertBefore($('.request-list-wrap .task-item').first());
        };

        function displayCommentRequest(data, post_id) {
            console.log(post_id);
            $('.task-card-comments-wrap.task-id-' + post_id).html(data);
        };

        function reInitRemodal() {
            $('[data-remodal-id]').removeAttr('remodal');
            $('[data-remodal-id]').remodal();
        };

        function removeTaskInput() {
            var form = $('.new-task-input-container');
            form.find('form').trigger('reset');
            form.find('form img').attr('src', '');
            form.find('.image-uploader').show();
        };

        function removeCommentInput() {
            $('.task-card-comment-submit-box').trigger('reset');
        };

        // event listeners

        $(document).on("click", ".new-task-input-container .task-submit", function (e) {
            e.preventDefault();
            submitTask(e);
        });

        $(document).on("click", ".task-card-comment-submit", function (e) {
            e.preventDefault();

            var parent = $(this).parents('.task-card-comment-submit-box');
            submitComment(parent, 'ns_add_comment');
        });

        $(document).on('click', '.load-more', function () {
            loadNextTasks($(this));
        });

        function loadNextTasks(obj) {
            var statuses = obj.data('statuses');
            var offset = obj.data('offset');
            var parent = obj.parents('.task-list-wrap');
            var data = {
                statuses: statuses,
                offset: offset,
                action: 'ns_load_next_tasks',
                nonce: ajax_object.nonce
            };
            $.post(ajax_object.ajax_url, data,
                function (response) {
                    if (typeof response.success != 'undefined') {
                        if (response.success == true && response.data.tasks) {
                            parent.find('.load-more-wrap').remove();
                            parent.append(response.data.tasks);
                            reInitRemodal();
                            $('.in_progress_tasks_wrap').mCustomScrollbar();
                        }
                    }
                }
            );
        }

        $(document).on('change', '.task-edit-wrap .task-statuses-wrap .status-checkbox input', function () {
            var t = $(this);
            var parent = t.parents('.status-checkbox');
            if (parent.hasClass('readonly')) {
                return;
            }
            changeStatus(t);
        });

        function changeStatus(obj) {
            $('.task-edit-wrap').find('.ns-error-box').html('');

            var parent = obj.parents('.status-checkbox');
            parent.nextAll().find('input:checkbox').prop('checked', false);
            parent.prevAll().find('input:checkbox').prop('checked', true);
            var status = obj.data('status');
            var statusPrev = obj.data('statusPrev');
            var taskId = obj.data('taskId');
            var st = obj.is(':checked') ? status : statusPrev;

            var data = {
                taskId: taskId,
                status: st,
                action: 'ns_change_status',
                nonce: ajax_object.nonce
            };
            $.post(ajax_object.ajax_url, data,
                function (response) {
                    if (typeof response.success != 'undefined') {
                        if (response.success == true) {
                            parent.addClass('readonly');
                        }
                        if (response.success == false && response.data.errors) {
                            parent.find('.ns-error-box').html(generateErrors(response.data.errors));
                        }
                    }
                }
            );
        }

        function submitComment(parent, action) {
            //TODO wrapper to prepare data before send
            parent.find('.ns-error-box').html('');
            $('.preloader-wrapper').show();
            $.post(ajax_object.ajax_url, (parent.serialize() + '&action=' + action + '&nonce=' + ajax_object.nonce),
                function (response) {
                    if (typeof response.success != 'undefined') {

                        if (response.success == true && response.data.comments) {
                            displayCommentRequest(response.data.comments, response.data.post_id);
                            removeCommentInput();
                        }
                        if (response.success == false && response.data.errors) {
                            parent.find('.ns-error-box').html(generateErrors(response.data.errors));
                        }
                        $('.preloader-wrapper').hide();
                    }
                }
            );
        };
        $(".ns_sortable_task_container .task-item").wrapAll("<div id='sortable' />");
        $(".request-list-wrap #sortable").sortable({
            handle: ".handle",
            stop(event, ui) {
                let newOrder = [],
                    currentItemOrderStart = ui.item.attr('data-ns-order'),
                    currentItemOrderEnd,
                    prevElId,
                    currentItemId = ui.item.attr('data-ns-id');
                $(this).find('.ui-state-default').each(function (index, row) {
                    $(row).attr( 'data-ns-order', index);
                    currentItemOrderEnd = ui.item.attr('data-ns-order');
                    prevElId = ui.item.prev().attr('data-ns-id');
                    newOrder.push({
                        id: $(row).data('nsId'),
                        order: index,
                    });
                });
                $.post(ajax_object.ajax_url, {
                        newOrder: newOrder,
                        action: 'ns_reorder_tasks',
                        nonce: ajax_object.nonce,
                        item_start: currentItemOrderStart,
                        item_end: currentItemOrderEnd,
                        item_id: currentItemId,
                        prev_item_id: prevElId
                    },
                    function (response) {
                        reInitRemodal();
                    }
                );
            }
        });

        $(document).on("click", ".ns_sorting", function (e) {
            sortTasks($(this));
        });

        function sortTasks(t) {
            if ($('.complete-sort-button').length) {
                $('.complete-sort-button').removeClass('current');
            }
            t.addClass('current');
            var order = t.data('order');
            var orderBy = t.data('order-by');
            var TaskContainer = t.data('task-container');
            $.post(ajax_object.ajax_url, {
                    task_container: TaskContainer,
                    orderBy: orderBy,
                    order: order,
                    action: 'ns_order_tasks',
                    nonce: ajax_object.nonce
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        if (response.data.tasks) {
                            if (t.attr('data-direction') == 'up' && t.hasClass('dashicons-arrow-up-alt2')) {
                                t.removeClass('dashicons-arrow-up-alt2');
                                t.addClass('dashicons-arrow-down-alt2');
                                t.attr('data-direction', 'down');
                            } else {
                                t.removeClass('dashicons-arrow-down-alt2');
                                t.addClass('dashicons-arrow-up-alt2');
                                t.attr('data-direction', 'up');
                            }
                            if (t.siblings('.complete-list-wrap').length > 0) {
                                $('.complete-list-wrap').mCustomScrollbar("destroy");
                                $('.complete-list-wrap').html(response.data.tasks);
                                t.data('order', (order == 'desc' ? 'asc' : 'desc'));
                                reInitRemodal();
                                if (t.siblings('.complete-list-wrap').length > 0) {
                                    $('.complete-list-wrap').mCustomScrollbar();
                                }
                            } else if (t.siblings('.staging-list-wrap').length > 0) {
                                $('.staging-list-wrap').mCustomScrollbar("destroy");
                                $('.staging-list-wrap').html(response.data.tasks);
                                t.data('order', (order == 'desc' ? 'asc' : 'desc'));
                                reInitRemodal();
                                if (t.siblings('.staging-list-wrap').length > 0) {
                                    $('.staging-list-wrap').mCustomScrollbar();
                                }
                            } else if (t.siblings('.task-list-container').find('.in_progress_tasks_wrap').length > 0) {
                                $('.in_progress_tasks_wrap').mCustomScrollbar("destroy");
                                $('.in_progress_tasks_wrap').html(response.data.tasks);
                                t.data('order', (order == 'desc' ? 'asc' : 'desc'));
                                reInitRemodal();
                                if (t.siblings('.task-list-container').find('.in_progress_tasks_wrap').length > 0) {
                                    $('.in_progress_tasks_wrap').mCustomScrollbar();
                                }
                            }
                        }
                    }
                }
            );
        };

        //User management wp menu icons
        $('.user_management_tabs li.ns_menu_item_element .status_checkboxes a').on('click', function () {
            $('.user_management_tabs li.ns_menu_item_element .status_checkboxes a').removeClass('clicked');
            $(this).addClass('clicked');
        });

        $('.menu_icon_remodal .dashicons_modal>div').on('click', function () {
            $('.menu_icon_remodal .dashicons_modal>div').removeClass('selected');
            $('.menu_icon_remodal .dashicons_modal>div').attr('data-selected', ' ');
            $(this).addClass('selected');
            $(this).attr('data-selected', 'selected');
        });

        $(document).on('click', '.menu_info_save', function (e) {
            e.preventDefault();
            var inst = $(this).parent('.menu_icon_remodal').remodal();
            var selected = $('.menu_icon_remodal .dashicons_modal>div.selected'),
                SelectedMenuItem = $('.user_management_tabs li.ns_menu_item_element .status_checkboxes a.clicked').attr('data-remodal-target'),
                editBtn = $('.status_checkboxes a.clicked'),
                menuName = [],
                MenuNameInput = $('.menu_icon_remodal.remodal-is-opened input');
            if (MenuNameInput.attr('class') === $('.user_management_tabs li.ns_menu_item_element .status_checkboxes a.clicked').attr('data-remodal-target') && MenuNameInput.val().length > 0) {
                menuName = MenuNameInput.val();
            } else {
                menuName = $('#adminmenu li#' + SelectedMenuItem).find('.wp-menu-name').text();
                console.log(menuName);
            }

            if (typeof selected.attr('class') !== 'undefined' && selected.attr('class').length > 0) {
                SelectedClass = selected.attr('class')
            } else {
                SelectedClass = '';
            }
            if (selected.length > 0) {
                SelectedClass = SelectedClass.replace('selected', '');
            }
            $.post(
                ajax_object.ajax_url,
                {
                    'action': 'set_menu_icon_and_name',
                    'SelectedClass': SelectedClass,
                    'menu_name': menuName,
                    'SelectedMenuItem': SelectedMenuItem
                },
                function (response) {
                    if (typeof response.success !== 'undefined' && response.success === true) {
                        if (SelectedClass.length > 0) {
                            $('#adminmenu li#' + SelectedMenuItem).find('a .wp-menu-image').html('');
                            $('#adminmenu li#' + SelectedMenuItem).find('a .wp-menu-image').attr('class', 'wp-menu-image dashicons-before ' + SelectedClass);
                            editBtn.attr('class', SelectedClass);
                        }
                        $('.menu_icon_remodal .dashicons_modal>div').removeClass('selected');
                        inst.close();
                    } else {
                        alert('something went wrong');
                    }
                }
            );
        });

        function edit_menu_items() {
            if (typeof menu_items_data !== 'undefined') {
                for (var item in menu_items_data) {
                    if (menu_items_data[item].name.length > 0) {
                        $('#adminmenu li#' + item + ' .wp-menu-name').text(menu_items_data[item].name);
                    }
                    $('#adminmenu li#' + item).find('a .wp-menu-image').html('');
                    $('#adminmenu li#' + item).find('a .wp-menu-image').attr('class', 'wp-menu-image dashicons-before ' + menu_items_data[item].icon);

                    // $('.user_management_tabs .status_checkboxes a').each(function(){
                    //    var $this = $(this);
                    //     if ( $this.attr('data-remodal-target') == item && menu_items_data[item].icon.length > 0 ) {
                    //         $this.attr('class', menu_items_data[item].icon);
                    //         $('#adminmenu li#'+item).find('a .wp-menu-image').html('');
                    //         $('#adminmenu li#'+item).find('a .wp-menu-image').attr( 'class', 'wp-menu-image dashicons-before ' + menu_items_data[item].icon);
                    //     }
                    // });
                }
            }
        }

        edit_menu_items();

        $("#ns_dashboard .tabs nav, div.tab-content.current, div.doc-content.current, #ns_dashboard .tabs .content.visible, .hub_site_message_block .client_message, .notifications_wrp").mCustomScrollbar();

        $(document).on('click', '.notifications_caterories ul li a', function (e) {
            e.preventDefault();
            if ($(this).attr('class') == 'all') {
                $('#ns_dashboard .ns_notifications>div').css('display', 'block');
            }
            if ($(this).attr('class') == 'warnings') {
                $('#ns_dashboard .ns_notifications>div').css('display', 'none');
                $('#ns_dashboard .ns_notifications .notice-warning, .ns_notifications .update-nag').css('display', 'block');
            }
            if ($(this).attr('class') == 'errors') {
                $('#ns_dashboard .ns_notifications>div').css('display', 'none');
                $('#ns_dashboard .ns_notifications .notice-error, .ns_notifications .error').css('display', 'block');
            }
            if ($(this).attr('class') == 'updates') {
                $('#ns_dashboard .ns_notifications>div').css('display', 'none');
                $('#ns_dashboard .ns_notifications .notice-info').css('display', 'block');
            }
        });

        $(document).on('click', '.plugin_management_filter ul li.select', function (e) {
            e.preventDefault();
            var $this = $(this);
            $('#ns_dashboard .tabs nav a').hide();
            if ($('#ns_dashboard .tabs nav a').hasClass($this.attr('data-plugin-status'))) {
                $('#ns_dashboard .tabs nav a.' + $this.attr('data-plugin-status')).show();
            }
        });

        $(document).on('click', '.sidebar_management_filter ul li[data-plugin-status]', function (e) {
            e.preventDefault();
            var $this = $(this);
            $('.tab-content ul li.ns_menu_item_element ').hide();
            if ($('.user_management_tabs .tab-content ul li').hasClass($this.attr('data-plugin-status'))) {
                $('.user_management_tabs .tab-content ul li.' + $this.attr('data-plugin-status')).show();
            }
        });

        $('.gapi_filter').find('.init').append('<div class="dashicons-arrow-down-alt2"></div>');
        $(".gapi_filter").on("click", ".init", function (e) {
            if (!$(this).closest(".analytics_filter").length) {
                e.preventDefault();
            }
            $(this).closest(".gapi_filter").children('li:not(.init)').toggle();
            $(this).closest(".gapi_filter").children('li:not(.init)').first().css({
                'margin-top': '15px',
                'border-top-left-radius': '5px',
                'border-top-right-radius': '5px',
            });
        });

        var allOptions = $(".gapi_filter").children('li:not(.init)');

        $(".gapi_filter").on("click", "li:not(.init)", function (e) {

            if (!$(this).closest(".analytics_filter").length) {
                e.preventDefault();
            }
            allOptions.removeClass('selected');
            $(this).addClass('selected');
            $(this).siblings('.init').html($(this).html());
            // $(".gapi_filter").children('.init').html($(this).html());
            var currentOptions = $(this).parent('.gapi_filter').children('li:not(.init)');
            currentOptions.toggle();
            $(this).siblings('.init').append('<div class="dashicons-arrow-down-alt2"></div>');
        });

        $('#adminmenu li').each(function () {
            var bubble = $(this).find('.wp-menu-name>span');
            if (bubble.length > 0) {
                bubble.appendTo($(this).find('.wp-menu-image'));
            }
        });

        $(document).on('click', '.connection_status', function () {
            $(this).hide();
            $('.connect_wrapper').show();
        });

        $(document).on('click', '.gapi_connect', function () {
            $(this).hide();
            $('.ns_analyitcs_section').show();
        });

        $(document).on('click', '.gapi_disconnect', function (e) {
            e.preventDefault();
            $.post(
                ajax_object.ajax_url,
                {
                    'action': 'ns_gapi_disconnect'
                },
                function (response) {
                    if (typeof response.success != 'undefined' && response.success == true) {
                        location.reload();
                    }
                }
            );
        });

        $(document).on('click', '.task_delete', function (e) {
            e.preventDefault();
            $('body').append('<div class="delete_task_wrp"><div class="delete_question"><p>Delete this task?</p><span class="yes">Yes</span><span class="no">No</span></div></div>')
        });

        $(document).on('click', '.delete_question span', function () {
            if ($(this).hasClass('no')) {
                $('.delete_task_wrp').remove();
            } else {

                var hubTaskID = $('.view_task_container.remodal-is-opened').data('task-hub-id');
                var ClientTaskID = $('.view_task_container.remodal-is-opened').data('task-id');
                var data = {
                    task_hub_id: hubTaskID,
                    client_task_id: ClientTaskID,
                    action: 'ns_task_delete',
                    nonce: ajax_object.nonce
                };
                $.post(ajax_object.ajax_url, data,
                    function (response) {
                        if (typeof response.success != 'undefined') {
                            if (response.success == true) {
                                $('.task-item[data-ns-id="' + ClientTaskID + '"]').remove();
                                $('.view_task_container[data-task-hub-id="' + hubTaskID + '"]').parent('.remodal-wrapper').remove();
                                $('.remodal-overlay').hide();
                                $('.delete_task_wrp').remove();
                            }
                            if (response.success == false && response.data.errors) {
                                console.log(response.data);
                            }
                        }
                    }
                );
            }
        });

        $(document).on('click', '.view_task_container .img_wrp .image_expand', function () {
            $('.view_task_container .img_wrp').addClass('expanded_img');
            $('.img_container .img_wrp .image_expand').hide();
            $('.img_container .img_wrp .image_contract').show();
            var scrollTop = jQuery(window).scrollTop(),
                elementOffset = jQuery('.remodal-is-opened .view_task_container').offset().top,
                distance = (elementOffset - scrollTop);
            $('.view_task_container .img_wrp .img').css('top', -distance);
            var parent = $('.remodal-wrapper.remodal-is-opened');
            parent.find('.task-card-image .circle_wrp').remove();
            CreateCanvasFromData(parent);
        });


        $(document).on('click', '.view_task_container .img_wrp .image_contract ', function (e) {
            if ($('.view_task_container.remodal-is-opened').length > 0 && $('.view_task_container.remodal-is-opened').is(':visible')) {
                if (e.target.nodeName !== 'IMG' && !$(e.target).hasClass('image_expand')) {
                    $('.view_task_container .img_wrp').removeClass('expanded_img');
                    $('.img_container .img_wrp .image_contract').hide();
                    $('.img_container .img_wrp .image_expand').show();
                    $('.view_task_container .img_wrp .img').css('top', 'unset');
                    var parent = $('.remodal-wrapper.remodal-is-opened');
                    parent.find('.task-card-image .circle_wrp').remove();
                    CreateCanvasFromData(parent);
                }
            }
            ;
        });


        $(document).on('opened', '.remodal', function () {
            if ($(this).find('.task_header_title').outerWidth() > $(this).find('.task_title_wrp_to_crop').outerWidth()) {
                $(this).find('.task_title_wrp_to_crop').prepend('<div class="more_url"><span>...</span></div>');
            }
        });

    });

    //darw canvas on tasks edit page
    function CreateCanvasFromData(parent) {
        var backgroundImagerc = parent.find('.task-card-image').attr('data-image-url');

        var task_hub_id = parent.find('.view_task_container').attr('data-task-id');
        var canvas = document.getElementById('task_image_canvas_' + task_hub_id);
        var context = canvas.getContext('2d');

        var strokeWidth = 2,
            drawCount = 1;

        var base_image = new Image();


        base_image.onload = function () {
            canvas.width = base_image.naturalWidth;
            canvas.height = base_image.naturalHeight;
            drawRectangleOnCanvas.drawAll(false);
            drawRectangleOnCanvas.resizePosition();
        };

        var drawRectangleOnCanvas = {
            drawAll: function (resize) {
                context.clearRect(0, 0, canvas.width, canvas.height);

                var CanvasWidthLoaded = canvas.offsetWidth;
                var CanvasHeightLoaded = canvas.offsetHeight;
                canvas.width = CanvasWidthLoaded;
                canvas.height = CanvasHeightLoaded;

                context.drawImage(base_image, 0, 0, base_image.width, base_image.height,     // source rectangle
                    0, 0, canvas.width, canvas.height); // destination rectangle

                var rects_data = parent.find('input[name="rects_data"]').val();
                var rects_descriptions = parent.find('input[name="rects_descriptions"]').val();

                var rects_descriptions = JSON.parse(rects_descriptions);


                var rects_data = JSON.parse(rects_data);

                var rects = rects_data[0].rects,
                    arcs = rects_data[0].arcs;

                if (rects_data.length > 0) {
                    for (var i = 0; i < rects.length; i++) {

                        var r = rects[i];
                        context.strokeStyle = r.color;
                        context.lineWidth = strokeWidth;
                        context.globalAlpha = 1;

                        var left = canvas.width * arcs[i].rectLeftCoef,
                            top = canvas.height * arcs[i].rectTopCoef,
                            right = left + canvas.width * arcs[i].rectWidthCoef,
                            bottom = canvas.height * arcs[i].rectBotCoef,
                            width = canvas.width * arcs[i].rectWidthCoef,
                            height = canvas.height * arcs[i].rectHeightCoef;

                        context.strokeRect(left, top, width, height);


                        context.beginPath();
                        context.arc(right, bottom, 15, 0, Math.PI * 2, true);

                        context.closePath();
                        context.fillStyle = r.color;
                        context.fill();


                        if (resize === false) {
                            parent.find('.task-card-image').append("<div style='top:" + arcs[i].y + "px; left:" + arcs[i].x + "px' class='circle_wrp circle_wrp_" + (i + 1) + "'><div class='circle circle" + i + "'>" + (i + 1) + "</div><div class='description_wrp hidden'><p class='rect_description' >" + rects_descriptions[i] + "</p></div></div>");
                        }

                        parent.find('.task-card-image .circle_wrp_' + (i + 1)).css({
                            'top': bottom,
                            'left': right
                        });

                        var text = drawCount;
                        context.fillStyle = "#fff";
                        var font = "bold " + 2 + "px serif";
                        context.font = font;
                        var width = context.measureText(text).width;
                        var height = context.measureText("w").width; // this is a GUESS of height
                        context.fillText(text, r.left - (width / 2), r.top + (height / 2));
                    }
                }
            },
            resizePosition: function () {
                parent.find('.circle_wrp').each(function (i) {
                    var obj = $(this);
                    var childPos = obj.position();

                    if ((obj.parent().outerWidth() - childPos.left) <= 200) {
                        parent.find('.circle_wrp_' + (i + 1) + ' .description_wrp').addClass('show_left');
                    }
                    if ((obj.parent().outerHeight() - childPos.top) <= 300) {
                        var ElHeight = $(this).find('.description_wrp').outerHeight();
                        parent.find('.circle_wrp_' + (i + 1) + ' .description_wrp').css('top', -ElHeight);
                    }
                });
            }
        }

        window.addEventListener('resize', resizeCanvas, false);

        function resizeCanvas() {
            drawRectangleOnCanvas.drawAll(true);
            drawRectangleOnCanvas.resizePosition();
        }

        base_image.src = backgroundImagerc;
    }

    $(window).on('load', function () {
        var delay = 1000, setTimeoutConst;
        $('#adminmenu>li:nth-child(3)').mouseover(function () {
            $(this).prev().addClass('mouseovered');
        }).mouseout(function () {
            $(this).prev().removeClass('mouseovered');
        });
        // if ( $('#adminmenuwrap').outerHeight() < $('#adminmenuback').outerHeight() ) {
        //     $('#adminmenuwrap').addClass('adminmenuwrap_botunset');
        // }
        $('#ns_dashboard #loader-wrapper').hide();

    });
    $(window).on('resize', function () {
        var HubMessagePaddingMob = $('.hub_site_message_block .contact_buttons').outerHeight();

        $('.hub_site_message_block').css('padding-bottom', HubMessagePaddingMob);

        $(document).on('opened', '.remodal', function () {
            if ($(this).find('.task_header_title').outerWidth() < $(this).find('.task_title_wrp_to_crop').outerWidth()) {
                // $(this).find('.task_title_wrp_to_crop').remove();
            }
            if ($(this).find('.task_header_title').outerWidth() > $(this).find('.task_title_wrp_to_crop').outerWidth()) {
                $(this).find('.task_title_wrp_to_crop').prepend('<div class="more_url"><span>...</span></div>');
            }
        });

    });

}(jQuery));