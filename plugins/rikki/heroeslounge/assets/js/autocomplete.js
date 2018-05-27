jQuery(function () {

    jQuery('.autocomplete').on('focus', function () {
        var selectItem = function (event, ui) 
        {
            jQuery(this).val(ui.item.value);
            return false;
        }

        jQuery(this).autocomplete({
            source: function (request, response) {
                var term = request.term;
                jQuery.request('onAutocomplete', {
                    data: term,
                    success: function (data) {
                        response(data);
                    }
                });
            },
            select: selectItem,
            minLength: 3

        });
    });

});