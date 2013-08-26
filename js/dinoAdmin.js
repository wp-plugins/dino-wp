$(document).ready(function () {
    $("div#dinoCaixa .restaurar").on("click", function (event) {
        var p = $(this).closest("div").children("input.padrao").val();
        var t = $(this).closest("div").children("textarea");
        $(t).val(p);
    });
});