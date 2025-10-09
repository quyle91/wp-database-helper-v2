(function ($) {
    'use strict';

    function escapeForRegex(str) {
        return str.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    function reindexRepeater($repeater) {
        const base = $repeater.data('base');
        if (!base) return;

        const baseEsc = escapeForRegex(base);

        // chỉ lấy item cấp 1 trong .wpdh-repeater-items
        $repeater.find('> .wpdh-repeater-items > .wpdh-repeater-item').each(function (index) {
            const $item = $(this);

            // cập nhật name
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

            // đệ quy cho repeater con
            $item.find('> .wpdh-repeater').each(function () {
                reindexRepeater($(this));
            });
        });

        // debug cấp hiện tại
        $repeater.find('> .wpdh-debug').trigger('click');
    }

    // clone item
    $(document).on('click', '.wpdh-clone', function (e) {
        e.preventDefault();
        const $item = $(this).closest('.wpdh-repeater-item');
        const $repeater = $item.closest('.wpdh-repeater');
        const $container = $repeater.children('.wpdh-repeater-items');

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
        const $item = $(this).closest('.wpdh-repeater-item');
        const $repeater = $item.closest('.wpdh-repeater');
        $item.remove();
        reindexRepeater($repeater);
    });

    // up item
    $(document).on('click', '.wpdh-up', function (e) {
        e.preventDefault();
        const $item = $(this).closest('.wpdh-repeater-item');
        const $repeater = $item.closest('.wpdh-repeater');
        const $prev = $item.prev('.wpdh-repeater-item');

        if ($prev.length) {
            $item.insertBefore($prev);
            reindexRepeater($repeater);
        }
    });

    // test button
    $(document).on('click', '.wpdh-debug', function (e) {
        e.preventDefault();

        // -------------------- Simple Repeater form field names --------------------
        const $repeater = $(this).closest('.wpdh-repeater');
        const data = {};
        $repeater.find('input[name], textarea[name], select[name]').each(function () {
            const $el = $(this);
            const name = $el.attr('name') || '';
            const val = $el.val();
            data[name] = val;
        });
        console.log('Repeater:', $repeater.data('name'), data);

        // -------------------- Simple checksum (synchronous) --------------------
        const jsonString = JSON.stringify(data);
        let hash = 0;
        for (let i = 0; i < jsonString.length; i++) {
            hash = ((hash << 5) - hash) + jsonString.charCodeAt(i);
            hash |= 0; // convert to 32-bit integer
        }

        console.log('Repeater:', $repeater.data('name'), 'Checksum:', hash);
    });

})(jQuery);
