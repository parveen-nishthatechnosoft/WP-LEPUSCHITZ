(function ($) {

    $(document).ready(function () {

        $("#catalogs").hide();
        $("#catalogs").children().hide()
        $("#progressBar").hide();

        var catalog = "none";

        $("#selectCatalog").change(function () {
            $("#catalogs").show();
            catalog = "anda"
            if ($("#selectCatalog option:selected").attr('data-type') === "anda"){
                $("#catAnda").show();
                $("#catAnda").siblings().hide();
            }
        });

        if ($('.anda-choice:selected').attr('value') === "Datei"){
            $("#andaFiles").show();
            $("#andaLinks").hide();
        }

        $("#uploadAnda").click(function () {
            $("#progressBar").show();

            $.ajax({
                url:"/wp-content/themes/lepuschitz_underscore/assets/lib/ajax_catalog_anda.php",
                method:"post",
                data:{catalog:catalog},
                success:function(data){
                    $("#progress").html(data);
                }
            });
        });

        $('#progress').bind('DOMSubtreeModified', function () {
            var total = $('#progress .total').html();
            var finished = $('#progress .finished').html().length;

            $("#progressBar .done").width((finished * 100 / total) + "%");
            $("#progressBar .procent span").html(Number(finished * 100 / total).toFixed(0));
        });

    });

})(jQuery);
