import css2 from '../css/select2.min.css';
import "../css/select2.min.css";
import css from "../css/admin.scss";
import "jquery";
import "select2";
("use strict");

(function($) {
    $(function() {
        var LSFS_Admin = {
            self: null,

            init: function() {
                self = this;
                this.attachEvents();
            },

            attachEvents: function() {
                $(document).on(
                    "click",
                    ".lsfs-button-live",
                    this.liveButtonClick
                );
                $( document ).on( 'click', '.lsfs-live-match .lsfs-header', function(){ $(this).parent().toggleClass('active'); });
                $( document ).on( 'click', '.button-lsfs-integration-activate', this.integrationActivate);
                $( document ).on( 'click', '.button-lsfs-integration-deactivate', this.integrationDeactivate);
                $( document ).on( 'click', '#lsfs_add_live_part', this.showLivePart );
                $( document ).on( 'click', '#lsfs_save_live_part', this.saveLivePart );
                $( document ).on( 'click', '.lsfs-submit-scorer', this.submitScorer );
                $( document ).on( 'click', '.lsfs-remove-scorer', this.removeScorer );

                $( document ).on( 'change', '.lsfs-live-scorers-form select[name=lsfs_team_player_id]', this.toggleNewPlayerInput );
            },
            removeScorer: function removeScorer( e ) {
                e.preventDefault();
                $('.lsfs-remove-scorer').attr('disabled', 'disabled');
                var $this     = $(this),
                    $row      = $this.attr('data-row'),
                    $team_id  = $this.attr('data-team'),
                    $event_id = $this.attr('data-event');
                $.ajax({
                    url: lsfs.ajaxurl,
                    method: 'POST',
                    data: { action: 'admin_lsfs_ajax_remove_scorer', row: $row, team_id: $team_id, event_id: $event_id },
                    success: function( resp ) {
                        $('.lsfs-remove-scorer').removeAttr('disabled');
                        if( resp.success ) {
                            var table = $this.parents('table'),
                                table_body = table.find('tbody');
                            table_body.html('');
                            if( resp.data.scorers ) {
                                for( var i in resp.data.scorers ) {
                                    var html = '<tr><td>' + resp.data.scorers[ i ]['name'] + '</td><td>' + resp.data.scorers[ i ]['minute'] + '\'</td><td><button type="button" class="button button-default button-small lsfs-remove-scorer" data-row="' + resp.data.scorers[ i ]['row'] + '" data-team="' + $team_id + '" data-event="' + $event_id + '">X</button></td></tr>';
                                    table_body.append( html );
                                }
                            } else {
                                table.parent().addClass('hidden');
                            }
                        } else {
                            alert( resp.data.message );
                        } 
                    }
                });
            },
            submitScorer: function submitScorer( e ) {
                e.preventDefault();
                var $this  = $(this).parent(),
                    action = 'admin_lsfs_ajax_' + $this.find('[name=lsfs_action]').val();
                
                $.ajax({
                    url: lsfs.ajaxurl,
                    method: 'POST',
                    data: { action: action, data: $this.find(':input').serialize() },
                    success: function( resp ) {
                        if( resp.success ) {
                            if( resp.data.player ) {
                                var minute   = resp.data.player.minute,
                                    team_id  = resp.data.player.team_id,
                                    event_id = resp.data.player.event_id,
                                    row      = resp.data.player.row,
                                    name     = resp.data.player.name,
                                    name_arr = name.split(' '),
                                    surname  = name_arr.pop(),
                                    _name    = '';
                                if( name_arr.length > 0 ) {
                                    _name = name_arr.map(function(n){ return n.charAt(0) + '.'; }).join(' ');
                                    _name += ' ';
                                }
                                var html = _name + surname;
                                $this.parent().find('.lsfs-team-scorers-list').removeClass('hidden');
                                $this.parent().find('.lsfs-team-scorers-list table tbody').append( '<tr><td>' + html + '</td><td>' + minute + '\'' + '</td><td><button type="button" class="button button-default button-small lsfs-remove-scorer" data-row="' + row + '" data-team="' + team_id + '" data-event="' + event_id + '">X</button></td></tr>' );
                                $( document ).triggerHandler( 'lsfs_scorers_added_ajax_response', [ resp.data.player ] );
                            }
                        } else {
                            if( resp.data.message ) {
                                alert( resp.data.message );
                            }
                        } 
                    }
                });
            },
            toggleNewPlayerInput: function toggleNewPlayerInput() {
                var value = $(this).val(),
                    form  = $(this).parent();
 
                if( value !== 0 && value !== '0' ) {
                    form.find('[name=lsfs_team_player_new]').addClass('hidden'); 
                } else {
                    form.find('[name=lsfs_team_player_new]').removeClass('hidden'); 
                }
            },
            saveLivePart: function saveLivePart(e) {
                e.preventDefault();
                var serialized = $('.lsfs-add-live-part :input').serialize();
                $.ajax({
                    url: lsfs.ajaxurl,
                    datatype: 'json',
                    type: 'POST',
                    data: { action: 'admin_lsfs_save_live_part', data: serialized },
                    success: function( resp ) {
                        if ( resp.success ) {
                            const type = resp.data.type;
                            let template = '';
                            if ( 'live' === type ) {
                                template = 'live-part-live';
                            } else {
                                template = 'live-part-pause';
                            }
                            const tmpl = wp.template( template );
                            const html = tmpl( resp.data );
                            if ( 'live' === type ) {
                                $('#lsfs-type-live').append( html );
                            } else {
                                $('#lsfs-type-pause').append(html);
                            }
                        } else {
                            const error = '<div class="error notice">' + resp.data + '</div>';
                            $('.lsfs-add-live-part').append( error );
                            setTimeout(() => {
                                $('.lsfs-add-live-part .error').fadeOut();
                            }, 2000);
                            setTimeout(() => {
                                $('.lsfs-add-live-part .error').remove();
                            }, 3000);
                        }
                        console.log(resp);
                    }
                });
            },
            showLivePart: function showLivePart(e) {
                e.preventDefault();
                $('.lsfs-add-live-part').toggleClass('hidden');
            },
            integrationActivate: function integrationActivate(e) {
                e.preventDefault();
                var $this = $(this),
                    integration = $this.attr('data-integration');

                if( integration ) {
                    $.ajax({
                      url: lsfs.ajaxurl,
                      dataType: 'json',
                      type: 'POST',
                      data: { action: 'admin_lsfs_activate_integration', integration: integration, nonce: lsfs.nonce },
                      success: function(resp) {
                        if( resp.success ) {
                          $this.removeClass('button-lsfs-integration-activate')
                            .removeClass('button-primary')
                            .addClass('button-lsfs-integration-deactivate')
                            .addClass('button-default')
                            .html( lsfs.text.deactivate );
                        } 
                      }
                    });
                }
            },

            integrationDeactivate: function integrationDeactivate(e) {
                e.preventDefault();
                var $this = $(this),
                    integration = $this.attr('data-integration');

                if( integration ) {
                    $.ajax({
                      url: lsfs.ajaxurl,
                      dataType: 'json',
                      type: 'POST',
                      data: { action: 'admin_lsfs_deactivate_integration', integration: integration, nonce: lsfs.nonce },
                      success: function(resp) {
                        if( resp.success ) {
                          $this.removeClass('button-lsfs-integration-deactivate')
                            .removeClass('button-default')
                            .addClass('button-lsfs-integration-activate')
                            .addClass('button-primary')
                            .html( lsfs.text.activate );
                        } 
                      }
                    });
                }
            },

            liveButtonClick: function(e) {
                e.preventDefault();
                var $this = $(this),
                    parent = $(this).parent(),
                    event_id = $this.attr("data-id"),
                    config_id = $this.attr("data-config"),
                    event_type = $this.attr("data-event"),
                    input = $this.attr("data-input"),
                    value = false,
                    action = "admin_lsfs_ajax_live_",
                    ajax_data = {};

                if (event_id) {
                    ajax_data["event_id"] = event_id;
                }

                if (config_id) {
                    ajax_data["config_id"] = config_id;
                }

                if (event_type) {
                    ajax_data["type"] = event_type;
                    action += event_type.replace(/-/g, "_");
                }

                if (action) {
                    ajax_data["action"] = action;
                }

                if (input) {
                    if ($(input).length > 1) {
                        value = $(input).serializeArray();
                    } else {
                        value = $(input).val();
                    }

                    ajax_data["value"] = value;
                }

                ajax_data["nonce"] = lsfs.nonce;

                parent.find(".notice").remove();

                $.ajax({
                    url: lsfs.ajaxurl,
                    dataType: "json",
                    type: "POST",
                    data: ajax_data,
                    success: resp => {
                        if ( resp && resp.success) {
                            if (
                                resp.data.hasOwnProperty("type") &&
                                "live" === resp.data.type
                            ) {
                                $this.html(resp.data.message);
                            } else {
                                if ( resp.data.message ) {
                                    var html = '<div class="updated notice"><p>';
                                    html += resp.data.message;
                                    html += "</p></div>";
                                    parent.append(html);
                                    setTimeout(() => {
                                        parent.find(".notice").fadeOut(500);
                                    }, 2000);
                                }

                                if ("pause" === resp.data.type) {
                                    if ("pause" === event_type) {
                                        // We are pausing the event -> Starting this live part
                                        $(
                                            "[data-config=" +
                                                config_id +
                                                "][data-event=start]"
                                        ).removeAttr("disabled");
                                    } else {
                                        $(
                                            "[data-config=" +
                                                config_id +
                                                "][data-event=pause]"
                                        ).removeAttr("disabled");
                                    }
                                }
                            }

                            if (
                                resp.data.hasOwnProperty("disable") &&
                                resp.data.disable
                            ) {
                                $this.attr("disabled", "disabled");
                            }
                        } else {
                            if ( ! resp || ! resp.hasOwnProperty('data') ) { return; }
                            var html = '<div class="error notice"><p>';
                            html += resp.data.message;
                            html += "</p></div>";
                            parent.append(html);
                            setTimeout(() => {
                                parent.find(".notice").fadeOut(500);
                            }, 2000);
                        }
                    }
                });
            }
        };

        LSFS_Admin.init();

        var LSFS_Admin_PRO = {
            self: null,

            init: function() {
                self = this;
                this.prepare();
                this.attachEvents();
            },

            attachEvents: function() {
                $(document).on(
                    "click",
                    "[data-commentary]",
                    this.addCommentary
                );
                $(document).on(
                    "click",
                    ".lsfs-commentary-delete",
                    this.deleteCommentary
                );
                $( document ).on( 'lsfs_scorers_added_ajax_response', this.addCommentaryOnScorer );
            },

            prepare() {
                this.prepareCommentaryIconSelect();
                this.prepareCommentaryPlayerSelect();
            },

            /**
             * Preparing the Commentary Player Select
             */
            prepareCommentaryPlayerSelect() {
                var selectIcon = $(".lsfs-commentary-player");
                selectIcon.select2({
                    templateResult: function(option) {
                        if (option["element"]) {
                            var dataIconAttr = false,
                                dataIconType = "icon",
                                dataIconColor = "";
                                
                            for (var index in option.element["attributes"]) {
                                
                                if (Number.isInteger(Number.parseInt(index))) {
                                    var val =
                                        option.element["attributes"][index];
                                    if (val.name == "data-icon") {
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-image") {
                                        dataIconType = "image";
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-color") {
                                        dataIconColor = val.value;
                                    }
                                }
                            }

                            if (!dataIconAttr) {
                                return option.text;
                            }
                            var ob = "";

                            if ("image" === dataIconType) {
                                ob = '<img src="' + dataIconAttr + '"/>';
                            } else {
                                ob = '<span class="' + dataIconAttr + '"';
                                if ("" !== dataIconColor) {
                                    ob +=
                                        ' style="color:' +
                                        dataIconColor +
                                        ' !important;"';
                                }
                                ob += "></span>";
                            }

                            ob += " " + option.text; // replace image source with option.img (available in JSON)
                            return ob;
                        }

                        return option.text;
                    },
                    templateSelection: option => { 
                        if (option["element"]) {
                            var dataIconAttr = false,
                                dataIconType = "icon",
                                dataIconColor = "";
                            for (var index in option.element["attributes"]) {
                                if (Number.isInteger(Number.parseInt(index))) {
                                    var val =
                                        option.element["attributes"][index];
                                    if (val.name == "data-icon") {
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-image") {
                                        dataIconType = "image";
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-color") {
                                        dataIconColor = val.value;
                                    }
                                }
                            }

                            if (!dataIconAttr) {
                                return option.text;
                            }
                            var ob = "";

                            if ("image" === dataIconType) {
                                ob = '<img src="' + dataIconAttr + '"/>';
                            } else {
                                ob = '<span class="' + dataIconAttr + '"';
                                if ("" !== dataIconColor) {
                                    ob +=
                                        ' style="color:' +
                                        dataIconColor +
                                        ' !important;"';
                                }
                                ob += "></span>";
                            }

                            ob += " " + option.text; // replace image source with option.img (available in JSON)
                            return ob;
                        }
                        return option.text;
                    },
                    escapeMarkup: m => {
                        return m;
                    }
                });
            },

            prepareCommentaryIconSelect() {
                var selectIcon = $(".lsfs-commentary-icon");
                selectIcon.select2({
                    templateResult: function(option) {
                        if (option["element"]) {
                            var dataIconAttr = false,
                                dataIconType = "icon",
                                dataIconColor = "";
                            for (var index in option.element["attributes"]) {
                                if (Number.isInteger(Number.parseInt(index))) {
                                    var val =
                                        option.element["attributes"][index];
                                    if (val.name == "data-icon") {
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-image") {
                                        dataIconType = "image";
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-color") {
                                        dataIconColor = val.value;
                                    }
                                }
                            }

                            if (!dataIconAttr) {
                                return option.text;
                            }
                            var ob = "";

                            if ("image" === dataIconType) {
                                ob = '<img src="' + dataIconAttr + '"/>';
                            } else {
                                ob = '<span class="' + dataIconAttr + '"';
                                if ("" !== dataIconColor) {
                                    ob +=
                                        ' style="color:' +
                                        dataIconColor +
                                        ' !important;"';
                                }
                                ob += "></span>";
                            }

                            ob += " " + option.text; // replace image source with option.img (available in JSON)
                            return ob;
                        }

                        return option.text;
                    },
                    templateSelection: option => {
                        if (option["element"]) {
                            var dataIconAttr = false,
                                dataIconType = "icon",
                                dataIconColor = "";
                            for (var index in option.element["attributes"]) {
                                if (Number.isInteger(Number.parseInt(index))) {
                                    var val =
                                        option.element["attributes"][index];
                                    if (val.name == "data-icon") {
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-image") {
                                        dataIconType = "image";
                                        dataIconAttr = val.value;
                                    }

                                    if (val.name == "data-color") {
                                        dataIconColor = val.value;
                                    }
                                }
                            }

                            if (!dataIconAttr) {
                                return option.text;
                            }
                            var ob = "";

                            if ("image" === dataIconType) {
                                ob = '<img src="' + dataIconAttr + '"/>';
                            } else {
                                ob = '<span class="' + dataIconAttr + '"';
                                if ("" !== dataIconColor) {
                                    ob +=
                                        ' style="color:' +
                                        dataIconColor +
                                        ' !important;"';
                                }
                                ob += "></span>";
                            }

                            ob += " " + option.text; // replace image source with option.img (available in JSON)
                            return ob;
                        }
                        return option.text;
                    },
                    escapeMarkup: m => {
                        return m;
                    }
                });
            },

            deleteCommentary: function(e) {
                e.preventDefault();
                var commentaryID = $(this).attr("data-id"),
                    commentaryItem = $(this).parents("li"),
                    ajax_data = {
                        action: "admin_lsfs_ajax_delete_commentary",
                        commentary_id: commentaryID,
                        nonce: lsfs.nonce
                    };

                $.ajax({
                    url: lsfs.ajaxurl,
                    dataType: "json",
                    type: "POST",
                    data: ajax_data,
                    success: resp => {
                        if (resp.success) {
                            commentaryItem.fadeOut(500);
                            setTimeout(() => {
                                commentaryItem.remove();
                            }, 600);
                        } else {
                            var html = '<div class="error notice"><p>';
                            html += resp.data.message;
                            html += "</p></div>";
                            commentaryItem.append(html);
                            setTimeout(() => {
                                commentaryItem.find(".notice").fadeOut(500);
                            }, 2000);
                        }
                    }
                });
            },

            addCommentaryOnScorer: function addCommentaryOnScorer( e, data ) {
                console.log(e); console.log(data);
                var commentary = data.commentary || false;
                if ( ! commentary ) {
                    return false;
                }
                var template = wp.template("commentary"),
                html = template({
                    icon: commentary.icon,
                    minute: commentary.minute || '',
                    text: commentary.text || '',
                    player: commentary.player,
                    id: commentary.id
                });

                $('.lsfs-commentary-list').prepend(html);
            },

            addCommentary: function(e) {
                e.preventDefault();
                var targetForm = $(this).attr("data-commentary"),
                    $targetForm = $(targetForm),
                    event_id = $(this).attr("data-event");

                var icon = $targetForm.find(".lsfs-commentary-icon").val(),
                    text = $targetForm.find(".lsfs-commentary-text").val(),
                    minute = $targetForm.find(".lsfs-commentary-minute").val(),
                    player = $targetForm.find(".lsfs-commentary-player").val(),
                    ajax_data = {
                        action: "admin_lsfs_ajax_add_commentary",
                        icon: icon,
                        text: text,
                        minute: minute,
                        event_id: event_id,
                        player_id: player,
                        nonce: lsfs.nonce
                    };

                $.ajax({ 
                    url: lsfs.ajaxurl,
                    dataType: "json",
                    type: "POST",
                    data: ajax_data,
                    success: resp => {
                        if (resp.success) {
                            var template = wp.template("commentary"),
                                html = template({
                                    icon: resp.data.icon,
                                    minute: minute,
                                    text: text,
                                    player: resp.data.player,
                                    id: resp.data.id
                                });

                            $targetForm
                                .parent()
                                .find(".lsfs-commentary-list")
                                .prepend(html);
                        } else {
                            var html = '<div class="error notice"><p>';
                            html += resp.data.message;
                            html += "</p></div>";
                            $targetForm.append(html);
                            setTimeout(() => {
                                $targetForm.find(".notice").fadeOut(500);
                            }, 2000);
                        }
                    }
                });
            }
        };

        LSFS_Admin_PRO.init();
    });
})(jQuery);