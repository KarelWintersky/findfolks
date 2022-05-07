;$(document).ready(function (){
    // onClick search
    $(document).on('click', "#actor-search",function (){
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
                day:        $("select[name='day']").val()
            }
        }).done(function(data){
            $target.html(data).show();
        });
    });

    $(document).on('click', '#actor-export', function (){
        let url = '/admin/export';

    });

    $(document).on('click', '.action-delete-ticket', function () {
        let id = $(this).data('id');
        let url = `/admin/ticket.delete/${id}`;
        $.ajax({
            url: url,
            type: 'GET',
            async: false,
        }).done(function (){
            window.location.reload();
        });
    });
});