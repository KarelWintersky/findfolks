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

    $(document).on('click', '#actor-export', function (){
        let dataset = {
            'city': $(`input[name="hidden_search_city"]`).val(),
            'district': $(`input[name="hidden_search_district"]`).val(),
            'street': $(`input[name="hidden_search_street"]`).val(),
            'fio': $(`input[name="hidden_search_fio"]`).val(),
            'day': $(`input[name="hidden_search_day"]`).val(),
        };
        let url = '/admin/download_pdf';

        let filename = dataset.day !== "" ? `export_${dataset.day}.pdf` : `export.pdf`;

        $.ajax({
            url: url,
            cache: false,
            data: dataset,
            type: 'POST',
            xhr: function () {
                let xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 2) {
                        if (xhr.status == 200) {
                            xhr.responseType = "blob";
                        } else {
                            xhr.responseType = "text";
                        }
                    }
                };
                return xhr;
            },
            success: function (data) {
                let blob = new Blob([data], {type: "application/octetstream"});
                let url = window.URL || window.webkitURL;
                let $body = $("body");
                let link = url.createObjectURL(blob);
                let a = $("<a />");
                a.attr("download", filename);
                a.attr("href", link);
                $body.append(a);
                a[0].click();
                $body.remove(a);
            }
        });

    });

    $(document).on('change', '.action-save-hidden-data', function (){
        let target = $(this).data('target-field');
        let value = $(this).val();
        $(`input[name="hidden_search_${target}"]`).val(value);
    });
});