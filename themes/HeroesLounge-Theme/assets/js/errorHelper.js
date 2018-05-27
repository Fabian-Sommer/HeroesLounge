  jQuery(document).ready(function () {
        if (jQuery(".errorMSG") == null)
            return;
        jQuery(".errorMSG").alert();
        jQuery(".errorMSG").fadeTo(4000, 500).slideUp(500, function () {
            jQuery(".errorMSG").slideUp(500);
        });

    });