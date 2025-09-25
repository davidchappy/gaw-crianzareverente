// Improve admin search for Authors taxonomy: ensure display name (term name) is considered
(function ($) {
    $(function () {
        var $input = $('#tag-search-input');
        var $button = $('#search-submit');

        if ($input.length === 0 || $button.length === 0) {
            return;
        }

        var lastValue = ($input.val() || '').trim();
        var timerId = null;

        function maybeSubmit() {
            var current = ($input.val() || '').trim();
            if (current.length === 0 && lastValue.length > 0) {
                $button.trigger('click');
            }
            lastValue = current;
        }

        $input.on('input', function () {
            if (timerId) {
                clearTimeout(timerId);
            }
            timerId = setTimeout(maybeSubmit, 150);
        });

        // Some browsers fire a special 'search' event when the clear (x) is used
        $input.on('search', function () {
            if (timerId) {
                clearTimeout(timerId);
            }
            timerId = setTimeout(maybeSubmit, 0);
        });
    });
})(jQuery);
