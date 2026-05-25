// Asigna el evento clic al bot¨®n por su identificador
$(document).ready(function() {
    var loading = false;
    var page_num = 1;
    load_page(page_num, false);


    $("#load-more").on("click", function() {
        // Llama a la funci¨®n load_page solo si no est¨¢ en proceso de carga
        page_num++;
        load_page(page_num, false)
    });
});

function load_page(page_num, loading) {
    if (loading == false) {
        loading = true;
        $.ajax({
            url: window.location.href + 'logo-post.php', //url de paginaci¨®n infinita
            type: "post",
            data: {
                page_num: page_num
            },
            beforeSend: function() {
                $('#ajax-loader').show();
                //alert(window.location.href + 'logo-post.php');
                return;
            }
        }).done(function(data) {
            $('#ajax-loader').hide();
            loading = false;
            $("#dynamic-posts2").append(data);
        }).fail(function(jqXHR, ajaxOptions, thrownError) {
            $('#ajax-loader').hide();
        });

    }

}