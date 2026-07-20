// Call the dataTables jQuery plugin
$(document).ready(function() {
  if ($.fn.DataTable.isDataTable('#dataTable')) {
    $('#dataTable').DataTable().destroy();
  }

  var $table = $('#dataTable');
  var searchLabel = $table.data('search-label') || 'Cari data:';
  var lengthMenuLabel = $table.data('length-label') || 'Tampilkan _MENU_ data';
  var infoLabel = $table.data('info-label') || 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data';
  var prevLabel = $table.data('prev-label') || 'Previous';
  var nextLabel = $table.data('next-label') || 'Next';

  $table.DataTable({
    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-right'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    language: {
      search: searchLabel,
      lengthMenu: lengthMenuLabel,
      info: infoLabel,
      paginate: {
        previous: prevLabel,
        next: nextLabel
      }
    }
  });
});
