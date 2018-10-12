(function ($) {

    $(document).ready(function () {
        $($("form")[0]).submit(function (e) {
            let val = $("form")[0][0].value;
            if (!val) {
                e.preventDefault();
                alert("Upload a Lighthouse Audit JSON file first");
            }
        });
    })


})(jQuery);