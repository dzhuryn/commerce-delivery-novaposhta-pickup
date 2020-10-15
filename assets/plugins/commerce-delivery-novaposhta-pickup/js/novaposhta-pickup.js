function initNpPickup($) {

    var $npPickupCity = $('#np-pickup-city');



    var citySelect2 = $npPickupCity.select2({
        language: deliveryNpConfig.lexiconCode,
        width: "100%",

        ajax: {
            url: "/commerce-delivery-np-pickup",
            dataType: 'json',
            delay: 250,
            minimumInputLength: 1,

            data: function (params) {
                return {
                    action: 'getCities',
                    query: params.term,
                    lang: deliveryNpConfig.langCode
                };
            }
        }
    });

    var $npPickupDepartment = $('#np-pickup-department');

    var departmentSelect = $npPickupDepartment.select2({
        language: deliveryNpConfig.lexiconCode,
        width: "100%",
        disabled: !$npPickupCity.val(),

        ajax: {
            url: "/commerce-delivery-np-pickup",
            dataType: 'json',
            delay: 250,
            minimumInputLength: 1,

            data: function (params) {
                return {
                    action: 'getDepartments',
                    city_ref: citySelect2.val(),
                    query: params.term,
                    lang: deliveryNpConfig.langCode
                };
            }
        }

    });


    citySelect2.on("select2:select", function (e) {
        departmentSelect.val('').trigger('change');
        departmentSelect.prop('disabled', false)
    });
}
initNpPickup(jQuery);

$(document).on('order-data-updated.commerce',function () {
    var interval = setInterval(function () {
        if(!$('#np-pickup-city').hasClass('select2-hidden-accessible')){
            clearInterval(interval);
            initNpPickup(jQuery);
        }
    },50);
})