$(document).ready(function (){
    // onClick search
    $("#actor-search").on('click', function (){
        let url = '/ajax:search';
        let $target = $("#search_results");
        $target.html('').scrollTop(0);

        $.ajax({
            url: url,
            type: 'POST',
            async: false,
            data: {
                city:       $("input[name='city']").val(),
                district:   $("input[name='district']").val(),
                street:     $("input[name='street']").val(),
                fio:        $("input[name='fio']").val(),
            }
        }).done(function(data){
            $target.html(data).show();
        });
    });
});