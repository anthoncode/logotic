//var id = document.getElementsByClassName("form_logo")[0].id;
function mialerta(clicked_id) {
    //tinyMCE.triggerSave();
    //$('input').attr('name', 'new_name')
    event.preventDefault() //evita redirigir después del get
    alert(clicked_id)
    //obtiene los atributos del segundo form
    //var id = document.getElementsByTagName("form")[1].id; 
    //var id = document.getElementsByClassName("form_logo")[0].id;
    var id = clicked_id;
    //var name = document.getElementsByClassName(clicked_id)[0].id;
    var name = $("input[name='" + clicked_id + "']").val();
    //var description = $("#description").val();
    //var description = $("input[name='desc_gr']").val();
    //var tags = $("input[name='tags_gr']").val();
    $.post("./ajax-update-logo.php", {
        id: id,
        name: name
    }, function(data) {
        $('#results').html(data);
        //$('#upload3')[0].reset();
    });
    return
}