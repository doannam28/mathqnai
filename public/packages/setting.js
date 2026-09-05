function initEditor() {
    tinymce.remove();
    tinymce.init({
        selector: '.editor',
        plugins: 'link image code autoresize table lists',
        toolbar: 'blocks | code | undo redo | link image | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | removeformat | table',
        min_height: 300,
        max_height: 400,
        placeholder: 'Nhập nội dung bài viết',
        resize: true,
        forced_root_block: 'p',
        autoresize_bottom_margin: 100,
        menu: {
            file: { title: 'File', items: 'newdocument restoredraft | preview | print' },
            edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace' },
            view: { title: 'View', items: 'code | visualaid visualchars visualblocks | preview fullscreen' },
            insert: { title: 'Insert', items: 'image link media inserttable hr' },
            format: { title: 'Format', items: 'blocks | bold italic underline strikethrough | forecolor backcolor | removeformat' },
            tools: { title: 'Tools', items: 'code wordcount' },
            table: { title: 'Table', items: 'inserttable | cell row column | tableprops deletetable' },
            help: { title: 'Help', items: 'help' }
        },
        block_formats: 'Đoạn văn=p; Tiêu đề 1=h1; Tiêu đề 2=h2; Tiêu đề 3=h3; Tiêu đề 4=h4; Tiêu đề 5=h5; Tiêu đề 6=h6',
        automatic_uploads: true,
        document_base_url: '/',
        relative_urls: false,        // giữ nguyên dấu / ở đầu
        remove_script_host: true,    // KHÔNG thêm domain
        images_file_types: 'jpeg,jpg,jpe,jfi,jif,jfif,png,gif,bmp,webp',
        images_upload_url: '/api/upload',
    });
}
