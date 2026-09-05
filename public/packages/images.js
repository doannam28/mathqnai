$(document).ready(function() {
    $(document).on('click', '.add-more-image', function() {
        const templateId = $(this).data('action-id');
        let html = $('#template_'+templateId).html();
        //get current milliseconds
        const key = new Date().getTime();
        html = html.replace(/__key_images__/g, key);
        $('#list_file_'+templateId).append(html);
    });
    $(document).on('click', '.browse-image', function() {
        $(this).parent().prev().click();
    });
    $(document).on('change', '.file-image', function() {
        const file = $(this)[0].files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            $(this).parent().prev().show();
            $(this).parent().prev().find('img').attr('src', e.target.result).show();
            $(this).prev().val(file.name);
        }.bind(this);
        reader.readAsDataURL(file);
    });

    $(document).on('click', '.remove-images', function() {
        $(this).parent().parent().hide();
        $(this).parent().prev().find('.remove-image').val(1);
    });
});
