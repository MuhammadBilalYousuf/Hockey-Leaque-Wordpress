import css from "../css/lsfs.scss";

("use strict");

(function($) {
    $(function() {
        $.fn.wake = function(callback) {
            if (typeof callback === "function") {
                return $(this).on("wake", callback);
            } else {
                return $(this).trigger("wake");
            }
        };

        var LSFS = {
            self: null,

            timeout: lsfs.live_refresh,

            live_intervals: [],

            currentTime: false,

            init: function() {
                self = this;
                this.startLiveEvents();
                this.liveEventForm();
                $( document.body ).on( 'lsfs_trigger_live_events', this.refreshEventResults );
            },

            liveEventForm: function liveEventForm() {
                if ($(".lsfs-form-live-event-results").length > 0) {
                    $(document).on(
                        "click",
                        ".lsfs-form-live-event-results .lsfs-button-live",
                        function(e) {
                            e.preventDefault();
                            var $this = $(this),
                                parent = $(this).parent(),
                                event_id = $this.attr("data-id"),
                                config_id = $this.attr("data-config"),
                                event_type = $this.attr("data-event"),
                                input = $this.attr("data-input"),
                                value = false,
                                action = "lsfs_ajax_live_",
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

                            parent.find(".lsfs-notice").remove();

                            $.ajax({
                                url: lsfs.ajaxurl,
                                dataType: "json",
                                type: "POST",
                                data: ajax_data,
                                success: resp => {
                                    if (resp && resp.success) {
                                        if (
                                            resp.data.hasOwnProperty("type") &&
                                            "live" === resp.data.type
                                        ) {
                                            $this.html(resp.data.message);
                                        } else {
                                            var html =
                                                '<div class="lsfs-notice"><p>';
                                            html += resp.data.message;
                                            html += "</p></div>";
                                            parent.append(html);
                                            setTimeout(() => {
                                                parent
                                                    .find(".lsfs-notice")
                                                    .fadeOut(500);
                                            }, 2000);

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
                                            resp.data.hasOwnProperty(
                                                "disable"
                                            ) &&
                                            resp.data.disable
                                        ) {
                                            $this.attr("disabled", "disabled");
                                        }
                                    } else {
                                        if (
                                            typeof resp.data.message !==
                                            "undefined"
                                        ) {
                                            var html =
                                                '<div class="lsfs-notice error"><p>';
                                            html += resp.data.message;
                                            html += "</p></div>";
                                            parent.append(html);
                                            setTimeout(() => {
                                                parent
                                                    .find(".lsfs-notice")
                                                    .fadeOut(500);
                                            }, 2000);
                                        }
                                    }
                                }
                            });
                        }
                    );
                }
            },

            /**
             * Refresh Event Results
             * @param  object list
             * @return void
             */
            refreshEventResults: function( e, live ) {

                var $this = $(this),
                    $live = $this.attr("data-lsfs-live"),
                    $type = $this.attr("data-lsfs-type"),
                    $data = { nonce: lsfs.nonce };

                if (typeof $type === "undefined" || !$type) {
                    $type = "list";
                }

                if ( typeof live !== "undefined" && live ) {
                    $live = live;
                }

                switch ($type) {
                    case "list":
                        $data.action = "lsfs_event_results";
                        $data.list = $live;
                        break;
                    case "event":
                        $data.action = "lsfs_event_single_result";
                        $data.event = $live;
                        break;
                }

                $.ajax({
                    url: lsfs.ajaxurl,
                    data: $data,
                    type: "GET",
                    dataType: "json",
                    success: function(resp) {
                        if (resp.success) {
                            if (resp.data.events) {
                                $(document.body).trigger(
                                    "lsfs_refresh_events",
                                    [resp.data.events]
                                );
                                var results = [];
                                for (var sp_event in resp.data.events) {
                                    var data = resp.data.events[sp_event];

                                    if (
                                        "" !== data.status ||
                                        "" !== data.results
                                    ) {
                                        var event_el = $this.find(
                                            "[data-live-event=" + sp_event + "]"
                                        );

                                        results[sp_event] = event_el.find(
                                            ".data-live-results"
                                        );
                                        var results_html = results[sp_event];

                                        if (
                                            results[sp_event].find("a").length
                                        ) {
                                            results_html = results[
                                                sp_event
                                            ].find("a");
                                        }

                                        var old_html = results_html.html();
                                        // Manipulate HTML only when needed
                                        if (
                                            old_html !==
                                            data.results + data.status
                                        ) {
                                            results[sp_event].addClass(
                                                "change"
                                            );

                                            results_html.html(
                                                data.results + data.status
                                            );
                                        } else {
                                            delete results[sp_event];
                                        }

                                        if ( event_el.parent().find('.lsfs-live-scorers').length ) {
                                            const liveScorersEl = event_el.parent().find('.lsfs-live-scorers');
                                            // We have the scorers layout.
                                            if ( data.scorers ) {
                                                liveScorersEl.removeClass('lsfs-hidden');
                                                var info = data.scorer_information || 'minutes';
                                                for( var team_id in data.scorers ) {
                                                    liveScorersEl.find('.lsfs-live-team-scorers[data-team=' + team_id + ']').removeClass('lsfs-hidden');
                                                    let ul = liveScorersEl.find('.lsfs-live-team-scorers[data-team=' + team_id + '] ul');
                                                    const scorers = data.scorers[ team_id ];
                                                    ul.find('li').remove();
                                                    for( var i = 0; i < scorers.length; i++ ) {
                                                        let scorer = scorers[ i ];
                                                        let scorer_info = scorer['minutes'];
                                                        if ( 'minutes' !== info ) {
                                                            scorer_info = scorer['points'];
                                                        }
                                                        ul.append('<li>' + scorer['name'] + '<span class="lsfs-scorer-minutes">' + scorer_info + '</span></li>' );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                setTimeout(() => {
                                    for (var sp_event in results) {
                                        results[sp_event].removeClass("change");
                                    }
                                }, 500);
                            }
                        }
                    }
                });
            },

            startLiveEvents: function() {
                var lastTime = new Date().getTime();

                if ($("[data-lsfs-live]").length > 0) {
                    $("[data-lsfs-live]").wake(this.refreshEventResults);

                    $("[data-lsfs-live]").each(function() {
                        var live_id = $(this).attr("data-lsfs-live");

                        self.live_intervals[live_id] = setInterval(() => {
                            self.currentTime = new Date().getTime();
                            $("[data-lsfs-live=" + live_id + "]").wake();
                            lastTime = self.currentTime;
                        }, self.timeout);
                    });
                }
            }
        };

        LSFS.init();
    });
})(jQuery);
