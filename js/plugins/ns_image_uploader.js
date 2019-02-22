(function($, window, document) {
    $(function() {
        var file_frame;

        $('#image-uploader').on( 'click', function( event ) {
            event.preventDefault();

            if ( file_frame ) {
                file_frame.open();
                return;
            }

            file_frame = wp.media.frames.file_frame = wp.media({
                title: $(this).data( 'uploader_title' ),
                button: {
                    text: $(this).data( 'uploader_button_text' )
                },
                multiple: false
            });

            file_frame.on( 'select', function() {
                attachment = file_frame.state().get('selection').first().toJSON();
                $('#image-uploader').hide();
                $('#image-uploader-image').attr('src', attachment.url);

                if( $('#_thumbnail_id').length > 0){
                    $('#_thumbnail_id').val(attachment.id);
                }
            });

            file_frame.open();
        });
    });
}(window.jQuery, window, document));