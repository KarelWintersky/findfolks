function _DownloadFile(url, data, fileName) {
    $.ajax({
        url: url,
        cache: false,
        data: data,
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
            //Convert the Byte Data to BLOB object.
            const blob = new Blob([data], {type: "application/octetstream"});
            const url = window.URL || window.webkitURL;
            let link = url.createObjectURL(blob);
            let a = $("<a />");
            a.attr("download", fileName);
            a.attr("href", link);
            $("body").append(a);
            a[0].click();
            $("body").remove(a);

            //Check the Browser type and download the File.
            /*const isIE = false || !!document.documentMode;
            if (isIE) {
                window.navigator.msSaveBlob(blob, fileName);
            } else {
                var url = window.URL || window.webkitURL;
                link = url.createObjectURL(blob);
                var a = $("<a />");
                a.attr("download", fileName);
                a.attr("href", link);
                $("body").append(a);
                a[0].click();
                $("body").remove(a);
            }*/
        }
    });
}

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
        let url = '/admin/download_pdf';
        _DownloadFile(url, {}, 'export.pdf');
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