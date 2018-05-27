jQuery.fn.dataTable.ext.order['hl-take-first'] = function  ( settings, col )
{
    return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
        return jQuery(td).text().split("(")[0];
    } );
};