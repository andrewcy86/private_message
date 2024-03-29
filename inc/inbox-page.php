<?php

function rwpm_inbox() {
    global $wpdb, $current_user;

    $pm_id = $_GET['id'];
    
    $total_message_count = $wpdb->get_row("SELECT COUNT(a.id) as total_message_count
    FROM " . $wpdb->prefix . "pm a 
    INNER JOIN " . $wpdb->prefix . "pm_users b ON a.id = b.pm_id 
    WHERE b.recipient = " . $current_user->ID . " AND b.deleted != '2' ");
    $n = $total_message_count->total_message_count;
    
    $unread_message_count = $wpdb->get_row("SELECT COUNT(a.id) as unread_message_count
    FROM " . $wpdb->prefix . "pm a 
    INNER JOIN " . $wpdb->prefix . "pm_users b ON a.id = b.pm_id 
    WHERE b.recipient = " . $current_user->ID . " AND b.deleted != '2' AND b.viewed = 0");
    $num_unread = $unread_message_count->unread_message_count;
    
    echo '<p>', sprintf( _n( 'You have %d private message (%d unread).', 'You have %d private messages (%d unread).', $n, 'pm4wp' ), $n, $num_unread ), '</p>';
?>
<style>
body {
    margin-right:1.5%;
}

div.dataTables_filter, div.dataTables_length {
  padding: 0.5%;
}

div.dataTables_wrapper {
        width: 100%;
    }

.datatable_header {
background-color: rgb(66, 73, 73) !important; 
color: rgb(255, 255, 255) !important;
width: 100%;
}

.text_highlight_messages a:link, a:visited {
  color:#1d4289;
  text-decoration: underline;
}

a:link {
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
/*
#pm_sort_button {
  background-color: #FFFFFF !important;
  color: #000000 !important;
  margin-right: 30px;
  padding: 5px 10px;
  font-size: 12px;
  line-height: 1.5;
  border-radius: 3px;
  display: inline-block;
  font-weight: normal;
  text-align: center;
  vertical-align: middle;
  cursor: pointer;
  border: 1px solid transparent;
  white-space: nowrap;
}
*/

.message-details {
    font-size: 13px;
}

#modal-close:focus {
    outline: none !important;
    border:4px solid black !important;
    box-shadow: 0 0 10px #719ECE !important;
}
</style>

<button type="button" class="button" id="pm_refresh_btn"><i class="fas fa-retweet"></i> Reset </i></button>
<button type="button" class="button" id="pm_sort_btn"><i class="fas fa-sort"></i> Group Messages </i></button>
<br /><br />
<div class="table-responsive" style="overflow-x:auto;">
<form id="frm-messages" method="POST">

<select id="message_action" name="message_action" aria-label="Bulk Message Action">
  <option value="">Bulk actions</option>
  <option value="read">Mark as Read</option>
  <option value="delete">Mark as Deleted</option>
</select>
<button type="button" class="button" id="pm_apply_bulk_action">Apply</button>

<table id="tbl_templates_messages" class="display nowrap" cellspacing="5" cellpadding="5" width="100%">
        <thead>
            <tr>
                <th class="datatable_header" scope="col"></th>
                <th class="datatable_header" scope="col">Identifier</th>
                <th class="datatable_header" scope="col">Subject</th>
                <th class="datatable_header" scope="col">Content</th>
                <th class="datatable_header" scope="col">Date</th>
            </tr>
        </thead>
    </table>
<input type='hidden' id='user_id' value='<?php echo $current_user->ID; ?>' />
</form>
</div>
<div class="modal fade" id="DescModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                 <h3 class="modal-title">Message Details</h3>

            </div>
            <div class="modal-body">
                 <h5 class="text-left"></h5>

            </div>
            <div class="modal-footer">
                <button type="button" id="modal-close" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __DIR__ ) ?>/css/bootstrap-modal.css"/>

<link rel="stylesheet" type="text/css" href="<?php echo dirname(plugin_dir_url( __DIR__ )) ?>/pattracking/asset/lib/DataTables/datatables.min.css"/>
<script type="text/javascript" src="<?php echo dirname(plugin_dir_url( __DIR__ )) ?>/pattracking/asset/lib/DataTables/datatables.min.js"></script>

<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css" rel="stylesheet" />
<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script>


<link href="https://cdn.datatables.net/rowgroup/1.0.2/css/rowGroup.dataTables.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/rowgroup/1.0.2/js/dataTables.rowGroup.min.js"></script>


<script>

jQuery(document).ready(function() {
 var collapsedGroups = {};

    var table = jQuery('#tbl_templates_messages').DataTable({
	     "autoWidth": true,
	     "scrollX" : true,
	     "processing" : true,
	     "serverSide": true,
	     "serverMethod": 'post',
	     "stateSave": true,
         "paging" : true,
         "responsive" : true,
		 "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
		 		'drawCallback': function (settings) { 
	        // Here the response
	        var response = settings.json;
	        console.log(response);
    	},
	  'ajax': {
       'url':'<?php echo plugin_dir_url( __DIR__ ) ?>/inc/scripts/message_processing.php',
       'data': function(data){
          // Read values
          var uid = jQuery('#user_id').val();
          data.userID = uid;
       }
    },
	  'columnDefs': [	
         {
            'width': '5px',
            'targets': 0,	
            'checkboxes': {	
               'selectRow': true	
            },
         },

            { 'width' : '10%', "visible": false, targets: 1 },
            { 'width' : '50%', targets: 2 },
            { 'width' : '10%', "visible": false, targets: 3 },
            { 'width' : '50%', targets: 4 }
      ],
            'order': [[4, 'desc']],
      'columns': [
       { data: 'id', 'title': 'Select All Checkbox'},
       { data: 'identifier' },
       { data: 'subject', 'class' : 'text_highlight_messages' }, 
       { data: 'content' },
       { data: 'sent_date' },
    ],
      
      rowGroup: {
        // Uses the 'row group' plugin
        dataSrc: 'identifier',
        startRender: function (rows, group) {
            var collapsed = !!collapsedGroups[group];

            rows.nodes().each(function (r) {
                r.style.display = collapsed ? 'none' : '';
            });    

            // Add category name to the <tr>. NOTE: Hardcoded colspan
            return jQuery('<tr/>')
                .append('<td colspan="5">' + group + ' (' + rows.count() + ')</td>')
                .attr('data-name', group)
                .toggleClass('collapsed', collapsed);
        }
      },
    });

function htmlDecode(input){
  var e = document.createElement('textarea');
  e.innerHTML = input;
  // handle case of empty input
  return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}

        jQuery('#tbl_templates_messages').on('click', '.detailsmodal', function (event) {
        event.preventDefault();

        var row = jQuery(this).closest("tr").get(0);
        var position = table.row( row ).index();
        
        var full_row = htmlDecode(table.cell( position , 3 ).data());

        var id_row = table.cell( position , 0 ).data();
        
        console.log(htmlDecode(full_row));
        
    jQuery.post(
   '<?php echo plugin_dir_url( __DIR__ ) ?>/inc/scripts/mark_read.php',{
    postvarsmessageid : id_row
    }); 
        jQuery('#DescModal').modal("show");
        jQuery(".text-left").html("<div class='message-details'>" +full_row + "</div>");
    
            jQuery("#DescModal").attr("tabindex",-1).focus();

    var tabbable = jQuery("#DescModal").find('select, input, textarea, button, a').filter(':visible');
    
    var firstTabbable = tabbable.first();
    /*set focus on first input*/
    firstTabbable.focus();
    
jQuery(document).on('keydown', function(e) {
    var target = e.target;
    var shiftPressed = e.shiftKey;
    // If TAB key pressed
    if (e.keyCode == 9) {
        // If inside a Modal dialog (determined by attribute id=wpsc_popup)
        if (jQuery(target).parents('[class=modal-dialog]').length) {                            
            // Find first or last input element in the dialog parent (depending on whether Shift was pressed). 
            // Input elements must be visible, and can be Input/Select/Button/Textarea.
            var borderElem = shiftPressed ?
                                jQuery(target).closest('[class=modal-dialog]').find('a:visible,input:visible,select:visible,button:visible,textarea:visible').first() 
                             :
                                jQuery(target).closest('[class=modal-dialog]').find('a:visible,input:visible,select:visible,button:visible,textarea:visible').last();
            if (jQuery(borderElem).length) {
                if (jQuery(target).is(jQuery(borderElem))) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }
    return true;
});

    table.ajax.reload( null, false );
    });


        jQuery('#frm-messages').on('click', '#pm_apply_bulk_action', function (event) {
        //event.preventDefault();
    var rows_selected = table.column(0).checkboxes.selected();
    var bulk_action = jQuery('#message_action').val();

    jQuery.post(
   '<?php echo plugin_dir_url( __DIR__ ) ?>/inc/scripts/bulk_update.php',{
    postvaraction : bulk_action,
    postvarselection : rows_selected.join(",")
    },
    function (response) {
        alert(response);
        table.ajax.reload( null, false );
        table.column(0).checkboxes.deselectAll();
    });


    //table.ajax.reload( null, false );
    //window.location.reload();
    });
    
    jQuery('#pm_sort_btn').on('click', function(){
        
        table
    .order( [ 1, 'desc' ] )
    .draw();
    });
    
    jQuery('#pm_refresh_btn').on('click', function(){

        table
    .order( [ 4, 'desc' ] )
    .search('')
    .draw();
    });

});

</script>


<?php    
}
?>