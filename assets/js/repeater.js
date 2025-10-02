(function ($) {
    'use strict';

    function escapeForRegex(str) {
        return str.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    function reindexRepeater($repeater) {
        const base = $repeater.data('base');
        if (!base) return;

        const baseEsc = escapeForRegex(base);

        $repeater.find('.wpdh-repeater-item').each(function (index) {
            const $item = $(this);
            $item.find('[name]').each(function () {
                const $el = $(this);
                let name = $el.attr('name');

                const regex = new RegExp('^' + baseEsc + '\\[\\d+\\]');
                if (regex.test(name)) {
                    const remainder = name.replace(regex, '');
                    const newName = base + '[' + index + ']' + remainder;
                    $el.attr('name', newName);
                }
            });

            $item.attr('data-index', index);
        });
    }

    // clone item
    $(document).on('click', '.wpdh-clone', function (e) {
        e.preventDefault();
        const $item = $(this).closest('.wpdh-repeater-item');
        const $repeater = $item.closest('.wpdh-repeater');

        const $clone = $item.clone();
        $clone.find('input,select,textarea').each(function () {
            const $el = $(this);
            $el.val($el.val()); // giữ nguyên giá trị
        });

        $clone.insertAfter($item);
        reindexRepeater($repeater);
    });

    // remove item
    $(document).on('click', '.wpdh-remove', function (e) {
        e.preventDefault();
        const $repeater = $(this).closest('.wpdh-repeater');
        $(this).closest('.wpdh-repeater-item').remove();
        reindexRepeater($repeater);
    });

    // $(document).ready(function () {
    //     $('.wpdh-repeater').each(function () {
    //         reindexRepeater($(this));
    //     });
    // });

    // up item
    $(document).on('click', '.wpdh-up', function (e) {
        e.preventDefault();
        const $item = $(this).closest('.wpdh-repeater-item');
        const $prev = $item.prev('.wpdh-repeater-item');
        const $repeater = $item.closest('.wpdh-repeater');

        if ($prev.length) {
            $item.insertBefore($prev);
            reindexRepeater($repeater);
        }
    });

    function collectRepeaterData($repeater) {
        const data = {};
        const base = $repeater.data('base');

        $repeater.find('input[name], textarea[name], select[name]').each(function () {
            const $el = $(this);
            const name = $el.attr('name') || '';
            const val = $el.val();

            // giữ nguyên toàn bộ name, không cắt
            data[name] = val;
        });

        return data;
    }

    // test button
    $(document).on('click', '.wpdh-debug', function (e) {
        e.preventDefault();
        const $repeater = $(this).closest('.wpdh-repeater');
        const data = collectRepeaterData($repeater);
        console.log('Repeater:', $repeater.data('name'), data);
    });

})(jQuery);
