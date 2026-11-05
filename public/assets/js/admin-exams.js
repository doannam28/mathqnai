(function ($) {
    function readJsonFromElement(selector, attrName) {
        var $el = $(selector);
        var raw = '';

        if ($el.length && $el[0].tagName === 'SCRIPT') {
            raw = $el.text();
        } else if ($el.length) {
            raw = $el.attr(attrName);
        }

        if (!raw) {
            return {};
        }

        try {
            return JSON.parse(raw);
        } catch (e) {
            return {};
        }
    }

    function readJson($el, attrName) {
        var raw = $el.attr(attrName);

        if (!raw) {
            return {};
        }

        try {
            return JSON.parse(raw);
        } catch (e) {
            return {};
        }
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildOptions(questionOptions, catId, selectedValue) {
        var items = questionOptions[String(catId)] || [];
        var html = '<option value="">-- Chọn câu hỏi --</option>';

        items.forEach(function (item) {
            var selected = String(item.id) === String(selectedValue) ? ' selected' : '';
            html += '<option value="' + escapeHtml(item.id) + '"' + selected + '>' + escapeHtml(item.title) + '</option>';
        });

        return html;
    }

    function buildTitleOptions(total, selectedValue) {
        var html = '<option value="">-- Chọn title --</option>';

        for (var i = 1; i <= total; i++) {
            var selected = String(i) === String(selectedValue) ? ' selected' : '';
            html += '<option value="' + i + '"' + selected + '>Câu ' + i + '</option>';
        }

        return html;
    }

    function shuffleArray(items) {
        var array = items.slice();

        for (var i = array.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = array[i];
            array[i] = array[j];
            array[j] = temp;
        }

        return array;
    }

    function bootstrapExamBuilder() {
        var $builder = $('#exam-question-builder');

        if (!$builder.length) {
            return;
        }

        var keyConfigs = readJson($builder, 'data-keys-config');
        var questionOptions = readJson($builder, 'data-questions-by-cat');
        var existingExamQuestionsByCat = readJson($builder, 'data-existing-exam-questions-by-cat');
        var groupsCache = [];

        function updateQuestionOptions() {
            var selected = [];

            $('select[name*="[question_id]"]').each(function () {
                var value = $(this).val();
                if (value) {
                    selected.push(String(value));
                }
            });

            $('select[name*="[question_id]"]').each(function () {
                var currentSelect = $(this);
                var currentValue = currentSelect.val();

                currentSelect.find('option').each(function () {
                    var option = $(this);
                    var value = option.val();

                    if (value && String(value) !== String(currentValue)) {
                        option.prop('disabled', selected.indexOf(String(value)) !== -1);
                    } else {
                        option.prop('disabled', false);
                    }
                });
            });
        }

        function updateTitleOptions() {
            var selected = [];

        $('select.title-select').each(function () {
                var value = $(this).val();
                if (value) {
                    selected.push(String(value));
                }
            });

        $('select.title-select').each(function () {
                var currentSelect = $(this);
                var currentValue = currentSelect.val();

                currentSelect.find('option').each(function () {
                    var option = $(this);
                    var value = option.val();

                    if (value && String(value) !== String(currentValue)) {
                        option.prop('disabled', selected.indexOf(String(value)) !== -1);
                    } else {
                        option.prop('disabled', false);
                    }
                });
            });
        }

        function getSelectedQuestionIds(exceptCatId) {
            var selected = [];

            $('select[name*="[question_id]"]').each(function () {
                var currentCatId = String($(this).data('cat-id') || '');
                var value = $(this).val();

                if (String(exceptCatId) !== currentCatId && value) {
                    selected.push(String(value));
                }
            });

            return selected;
        }

        function initSearchableSelects() {
            if ($.fn.select2) {
                $('select.question-select').each(function () {
                    var $select = $(this);

                    if ($select.data('select2')) {
                        $select.select2('destroy');
                    }

                    $select.select2({
                        width: '100%',
                        placeholder: '-- Chọn câu hỏi --'
                    });
                });
            }
        }

        function renderBuilder() {
            var keysId = $('#keys_id').val();
            var groups = keyConfigs[String(keysId)] || [];

            groupsCache = groups;
            $builder.empty();

            if (!keysId) {
                $builder.append('<div class="text-muted">Hãy chọn mẫu đề để hiển thị số câu theo từng dạng toán.</div>');
                return;
            }

            if (!groups.length) {
                $builder.append('<div class="text-muted">Mẫu đề này chưa có cấu hình số câu.</div>');
                return;
            }

            var totalQuestions = 0;
            groups.forEach(function (group) {
                totalQuestions += parseInt(group.number || 0, 10);
            });

            var html = '';

            groups.forEach(function (group) {
                var existingRows = existingExamQuestionsByCat[String(group.taxonomyitem_id)] || [];
                html += '<div class="box box-default exam-question-group" data-cat-id="' + group.taxonomyitem_id + '" style="margin-bottom: 15px;">';
                html += '<div class="box-header with-border"><strong>' + escapeHtml(group.taxonomy_name || 'Danh mục toán') + '</strong>';
                html += ' <span class="text-muted">(' + parseInt(group.number || 0, 10) + ' câu)</span>';
                html += '<button type="button" class="btn btn-xs btn-primary pull-right auto-fill-group" data-cat-id="' + group.taxonomyitem_id + '">Tạo tự động</button>';
                html += '</div>';
                html += '<div class="box-body">';

                var total = parseInt(group.number || 0, 10);
                for (var i = 0; i < total; i++) {
                    var existingRow = existingRows[i] || {};
                    var selectedQuestionId = existingRow.question_id || '';
                    var selectedTitle = existingRow.order || '';

                    html += '<div class="form-group" style="margin-bottom: 12px;">';
                    html += '<label>Câu ' + (i + 1) + '</label>';
                    html += '<select class="form-control question-select" data-cat-id="' + group.taxonomyitem_id + '" name="exam_questions[' + group.taxonomyitem_id + '][' + i + '][question_id]" required>';
                    html += buildOptions(questionOptions, group.taxonomyitem_id, selectedQuestionId);
                    html += '</select>';
                    html += '<div style="height: 8px;"></div>';
                    html += '<select class="form-control title-select" name="exam_questions[' + group.taxonomyitem_id + '][' + i + '][title]" required>';
                    html += buildTitleOptions(totalQuestions, selectedTitle);
                    html += '</select>';
                    html += '</div>';
                }

                html += '</div></div>';
            });

            $builder.append(html);
            initSearchableSelects();

            groups.forEach(function (group) {
                var existingRows = existingExamQuestionsByCat[String(group.taxonomyitem_id)] || [];

                existingRows.forEach(function (row, index) {
                    var $questionSelect = $('select[name="exam_questions[' + group.taxonomyitem_id + '][' + index + '][question_id]"]');
                    var $titleSelect = $('select[name="exam_questions[' + group.taxonomyitem_id + '][' + index + '][title]"]');

                    if ($questionSelect.length) {
                        $questionSelect.val(String(row.question_id)).trigger('change');
                    }

                    if ($titleSelect.length) {
                        $titleSelect.val(String(row.order)).trigger('change');
                    }
                });
            });

            updateQuestionOptions();
            updateTitleOptions();
        }

        $(document).on('click', '.auto-fill-group', function () {
            var catId = String($(this).data('cat-id'));
            var $group = $('.exam-question-group[data-cat-id="' + catId + '"]');
            var $selects = $group.find('select[name*="[question_id]"]');
            var currentSelected = getSelectedQuestionIds(catId);
            var items = (questionOptions[String(catId)] || []).filter(function (item) {
                return currentSelected.indexOf(String(item.id)) === -1;
            });

            if (items.length < $selects.length) {
                alert('Không đủ câu hỏi chưa dùng để tạo tự động cho nhóm này.');
                return;
            }

            items = shuffleArray(items);

            $selects.each(function (index) {
                $(this).val(String(items[index].id)).trigger('change');
            });

            updateQuestionOptions();
        });

        $(document).on('change', '#keys_id', function () {
            renderBuilder();
        });

        $(document).on('change', 'select[name*="[question_id]"]', function () {
            updateQuestionOptions();
        });

        $(document).on('change', 'select.title-select', function () {
            updateTitleOptions();
        });

        if ($builder.find('.exam-question-group').length) {
            updateQuestionOptions();
            updateTitleOptions();
        } else {
            renderBuilder();
            setTimeout(renderBuilder, 200);
        }
    }

    $(bootstrapExamBuilder);
})(jQuery);
