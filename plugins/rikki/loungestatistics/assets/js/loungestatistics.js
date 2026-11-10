jQuery.fn.dataTable.ext.order['hl-take-first'] = function  ( settings, col )
{
    return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
        return jQuery(td).text().split("(")[0];
    } );
};

/*
 * DataTables setup for the team and sloth statistics. Called after the statistics are
 * rendered, both on page load and after they are (re)loaded through AJAX.
 */
window.HLStatistics = (function ($) {
    var dom = "<'row'<'col-sm-6'<'mt-2'l>><'col-sm-6'<'mt-2' f>>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>";
    var takeFirst = { 'orderDataType': 'hl-take-first', 'type': 'num' };

    function initMapTable() {
        $('#mapStatisticsToggle>table').DataTable({
            destroy: true,
            searching: false,
            paging: false,
            autoWidth: false,
            order: [[3, 'desc']],
            info: false
        });
    }

    function initHeroTable(columns) {
        $('#heroStatisticsToggle>table').DataTable({
            destroy: true,
            columns: columns,
            order: [[1, 'desc']],
            dom: dom
        });
        $('.dataTables_wrapper').removeClass('container-fluid');
    }

    return {
        initTeamTables: function () {
            initMapTable();
            initHeroTable([null, takeFirst, null, takeFirst, takeFirst]);
        },
        initSlothTables: function () {
            initMapTable();
            initHeroTable([null, takeFirst, null, takeFirst, takeFirst, null, null, null, null, null, null, null, null]);
        }
    };
})(jQuery);
