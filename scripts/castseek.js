(function () {
    "use strict";

    function pad(number) {
        return ("00" + number).slice(-2);
    }

    function time_to_seconds(time) {
        var s = time.attributes.datetime.value.split(":");
        return parseInt(s[0] * 3600, 10) + parseInt(s[1] * 60, 10) + parseInt(s[2], 10);
    }

    function seconds_to_time( seconds ) {
        seconds = Number(seconds);
        var h = Math.floor(seconds / 3600);
        var m = Math.floor(seconds % 3600 / 60);
        var s = Math.floor(seconds % 3600 % 60);
        return pad( h ) + ":"  + pad( m ) + ":" + pad( s );
    }

    var highlighter = {

        /*
         * Collect and parse the time tags, annotate them with their time in seconds,
         * and stash them in an array called "nodes".
         */
        init: function () {
            var nodes, i, time, seconds, audio;

            // collect time tags
            nodes = document.querySelectorAll('.shownotes dt time');

            for (i = 0; i < nodes.length; i += 1) {
                time = nodes[i];
                seconds = time_to_seconds(time);

                time.seconds = seconds;
                time.onclick = this.click_handler;
            }

            this.nodes = nodes;

            // listen to audio events
            audio = document.getElementById('castplayer');
            audio.addEventListener("timeupdate", this.timeupdate_handler);
            audio.addEventListener("progress", this.progress_handler);

            this.audio = audio;

            console.log("highlighter initialized!");
        },

        /*
         * A callback that fires constantly while the audio is playing.
         */
        timeupdate_handler: function (event) {
            var secs = event.target.currentTime;
            highlighter.highlight_time(secs);
        },

        /*
         * A callback that fires when the user clicks a time tag on the page.
         */
        click_handler: function () {
            var audio, seconds, seek_once;
            seconds = this.seconds;

            /* assume if user clicks, they want history */
            if (history.pushState) {
                history.pushState( { time: seconds }, "Skipped to" + seconds,
                            "#ts-" + seconds_to_time( seconds ) );
            }
            audio = highlighter.audio;
            if (audio.paused) {
                audio.play();

                // The following mess is needed to seek after the audio has started playing,
                seek_once = function () {
                    console.log("seeking to", seconds);
                    this.currentTime = seconds;
                    this.removeEventListener('canplay', seek_once, false);
                };
                audio.addEventListener('canplay', seek_once, false);
            } else {
                // Seek!
                audio.currentTime = seconds;
            }
        },

        /*
         * Find the time tag at position "n" in the "nodes" array, and
         * highlight it.
         */
        highlight: function (n) {
            // console.log("highlighting", n);

            // unhighlight old node
            if (this.highlighted >= 0) {
                this.nodes[this.highlighted].parentNode.classList.remove("highlighted");
            }

            // highlight new node
            this.nodes[n].parentNode.classList.add("highlighted");

            // remember which node we've highlighted
            this.highlighted = n;
        },

        /*
         * Do a linear search of the "nodes" array for the first time tag that is
         * less than "secs".
         */
        find_node_before: function (secs) {
            var run = true, p = 0;

            // find the node just before "secs"
            while (run) {
                // console.log(p, "checking", secs, ">", this.nodes[p].seconds);

                if (p + 1 >= this.nodes.length) {
                    run = 0;
                } else if (secs < this.nodes[p + 1].seconds) {
                    run = 0;
                }

                p += 1;
                // console.log("p", p)
            }

            return p - 1;
        },

        /*
         * Check if the player is still playing the "this.highlighted" time tag.
         */
        in_range: function (secs) {
            if (secs > this.nodes[this.highlighted].seconds) {
                if (this.highlighted + 1 > this.nodes.length - 1) {
                    return true;
                }
                if (secs < this.nodes[this.highlighted + 1].seconds) {
                    return true;
                }
            }
            return false;
        },


        /*
         * This is the main workhorse function. It's called every time the audio "timeupdate"
         * callback is fired, and constantly checks to make sure the highlighted element
         * matches what's in the player.
         */
        highlight_time: function (secs) {
            if (this.highlighted >= 0) {
                if (!this.in_range(secs)) {
                    var n = this.find_node_before(secs);
                    this.highlight(n);

                    /* whereas here, they probably don’t want history */
                    if (history.replaceState) {
                        history.replaceState( { time: secs }, "Played to" + secs,
                                    "#ts-" + seconds_to_time( secs ) );
                    }
                }
            } else {
                // nothing was highlighted, so highlight the first thing.
                this.highlight(0);
            }
        }

    };


    highlighter.init();
}());
