$(document).ready(function() {
    var loading = false;
    var no_more = false;
    var page_num = 1;

    // carga página 1 automáticamente al entrar
    load_page(page_num);

    $("#load-more").on("click", function() {
        if (loading || no_more) return;
        page_num++;
        load_page(page_num);
    });

    function load_page(num) {
        loading = true;
        $('#load-more').hide();
        $('#ajax-loader').show();

        $.ajax({
            url: window.SITE_URL + '/logo-post.php',
            type: "post",
            data: { page_num: num }
        }).done(function(data) {
            loading = false;
            $('#ajax-loader').hide();

            if ($.trim(data) === '') {
                no_more = true;
                $('#load-more').hide(); // no hay más, oculta definitivamente
            } else {
                $("#dynamic-posts2").append(data);
                $('#load-more').show(); // hay más, muestra el botón
            }
        }).fail(function() {
            loading = false;
            $('#ajax-loader').hide();
            $('#load-more').show();
        });
    }
});