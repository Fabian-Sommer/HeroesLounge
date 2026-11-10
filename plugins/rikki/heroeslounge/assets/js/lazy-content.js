/*
 * Lazy loads content through the AJAX framework.
 *
 * A placeholder like
 *     <div data-lazy-handler="component::onLoadSomething" data-request-data="key: 'value'"></div>
 * calls its handler as soon as it is visible: on page load, or when the (sub)tab containing it
 * is shown. The handler is expected to replace the placeholder (e.g. by updating its parent),
 * which also prevents it from loading twice. Other data-request-* attributes (like
 * data-request-success) work as with data-request.
 */
(function ($) {
    'use strict';

    function load($placeholder) {
        if ($placeholder.data('lazyLoading')) {
            return;
        }
        $placeholder.data('lazyLoading', true);
        $placeholder.one('ajaxError', function (event) {
            // Show the failure in place instead of the framework's alert().
            event.preventDefault();
            $placeholder.data('lazyLoading', false);
            $placeholder.text('Loading failed. ').append(
                $('<a href="#">Try again</a>').on('click', function (e) {
                    e.preventDefault();
                    load($placeholder);
                })
            );
        });
        $placeholder.request($placeholder.data('lazy-handler'));
    }

    function loadVisible(scope) {
        $(scope).find('[data-lazy-handler]').filter(':visible').each(function () {
            load($(this));
        });
    }

    $(document).on('shown.bs.tab', function (event) {
        loadVisible($(event.target).attr('href'));
    });

    $(function () {
        loadVisible(document);
    });
})(jQuery);
