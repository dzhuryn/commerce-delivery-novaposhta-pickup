(function ($){
    function updateNpPickupFieldsVisible(){
        var value = $('[name="order[fields][delivery_method]"]').val();

        var $fields = $('#np-pickup-city, #np-pickup-department')

        if(value === 'novaposhta-pickup'){
            $fields.closest('tr').show();
        }
        else{
            $fields.closest('tr').hide();
        }
    }

    $(document)
        .ready(updateNpPickupFieldsVisible)
        .on('change','[name="order[fields][delivery_method]"]',updateNpPickupFieldsVisible)
})(jQuery)