/**
 * Cascading category selects (root → sub → optional third) using a flat list
 * with parent_id (same shape as PHP buildCategoryTree: id, name, parent_id, level).
 */
(function ($) {
    'use strict';

    function fillChildSelect($sel, categories, parentId, placeholder, preselect) {
        $sel.empty();
        $sel.append($('<option></option>').val('').text(placeholder));
        if (!parentId) {
            $sel.prop('disabled', true);
            return;
        }
        var n = 0;
        $.each(categories, function (_, c) {
            if (c.parent_id != null && String(c.parent_id) === String(parentId)) {
                n++;
                var opt = $('<option></option>').attr('value', c.id).text(c.name);
                if (String(c.id) === String(preselect || '')) {
                    opt.prop('selected', true);
                }
                $sel.append(opt);
            }
        });
        $sel.prop('disabled', n === 0);
    }

    /**
     * @param {object} opts
     * @param {string} opts.rootSelect
     * @param {string} opts.subSelect
     * @param {string} [opts.thirdSelect]
     * @param {Array} opts.categories
     * @param {string|number} [opts.preselectRoot]
     * @param {string|number} [opts.preselectSub]
     * @param {string|number} [opts.preselectThird]
     * @param {string} [opts.subPlaceholder]
     * @param {string} [opts.thirdPlaceholder]
     */
    window.initCategoryCascade = function (opts) {
        var categories = opts.categories || [];
        var $root = $(opts.rootSelect);
        var $sub = $(opts.subSelect);
        var $third = opts.thirdSelect ? $(opts.thirdSelect) : $();
        var subPh = opts.subPlaceholder || 'Select subcategory';
        var thirdPh = opts.thirdPlaceholder || 'Further subcategory (optional)';

        $root.on('change', function () {
            var rid = $(this).val();
            fillChildSelect($sub, categories, rid, subPh, '');
            if ($third.length) {
                fillChildSelect($third, categories, '', thirdPh, '');
            }
        });

        if ($third.length) {
            $sub.on('change', function () {
                fillChildSelect($third, categories, $(this).val(), thirdPh, '');
            });
        }

        if ($root.val()) {
            fillChildSelect($sub, categories, $root.val(), subPh, opts.preselectSub || '');
            if ($third.length) {
                fillChildSelect($third, categories, $sub.val(), thirdPh, opts.preselectThird || '');
            }
        }
    };
})(jQuery);
