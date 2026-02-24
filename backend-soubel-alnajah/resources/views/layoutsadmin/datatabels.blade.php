<script>


    $(function () {
        "use strict";

        $('#example1').DataTable();
        $('#example2').DataTable({
          'paging'      : true,
          'lengthChange': false,
          'searching'   : false,
          'ordering'    : true,
          'info'        : true,
          'autoWidth'   : false
        });
      
      
      $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
          'copy', 'csv', 'excel', 'pdf', 'print'
        ]
      } );
      
      $('#tickets').DataTable({
        'paging'      : true,
        'lengthChange': true,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false,
      });
      
      $('#productorder').DataTable({
        'paging'      : true,
        'lengthChange': true,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false,
      });
      

      $('#complex_header').DataTable();
      
         



      // Setup - add a text input to each footer cell
      $('#example5 tfoot th').each( function () {
            
            var title = $(this).text();
            $(this).html( '<input type="text" placeholder=" '+title+'" />' );
            
        } );
          // DataTable
          var table = $('#example5').DataTable({
            "language": {
              "paginate": {
                "next": "{{ trans('inscription.next') }}",
                "last": "Last page",
                "previous": "{{ trans('inscription.Previous') }}",
              },
              "info": "{{ trans('inscription.show') }} _START_ {{ trans('inscription.to') }} _END_ {{ trans('inscription.of') }} _TOTAL_ {{ trans('inscription.entries') }}",
              "infoEmpty": "{{ trans('inscription.noentries') }}",
              "emptyTable": "{{ trans('inscription.nodata') }}",
              "search": "{{ trans('inscription.search') }}",
              "infoFiltered": " - {{ trans('inscription.filteringfrom') }} _MAX_ {{ trans('inscription.records') }}",
              "lengthMenu": "{{ trans('inscription.show') }} _MENU_ {{ trans('inscription.records') }}",
              "zeroRecords": "{{ trans('inscription.norecords') }}",
              

            }
          });
  
        // Apply the search
        table.columns().every( function () {
            var that = this;
    
            $( 'input', this.footer() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that
                        .search( this.value )
                        .draw();
                }
                
            } );
            
        } );





      
        for (let i = 0; i < 10; i++) {

         // Setup - add a text input to each footer cell
         $('#example5'+i+' tfoot th').each( function () {
            
            var title = $(this).text();
            $(this).html( '<input type="text" placeholder=" '+title+'" />' );
            
        } );
          // DataTable
          var table = $('#example5'+i+'').DataTable({
            "language": {
              "paginate": {
                "next": "{{ trans('inscription.next') }}",
                "last": "Last page",
                "previous": "{{ trans('inscription.Previous') }}",
              },
              "info": "{{ trans('inscription.show') }} _START_ {{ trans('inscription.to') }} _END_ {{ trans('inscription.of') }} _TOTAL_ {{ trans('inscription.entries') }}",
              "infoEmpty": "{{ trans('inscription.noentries') }}",
              "emptyTable": "{{ trans('inscription.nodata') }}",
              "search": "{{ trans('inscription.search') }}",
              "infoFiltered": " - {{ trans('inscription.filteringfrom') }} _MAX_ {{ trans('inscription.records') }}",
              "lengthMenu": "{{ trans('inscription.show') }} _MENU_ {{ trans('inscription.records') }}",
              "zeroRecords": "{{ trans('inscription.norecords') }}"

            }
          });
  
        // Apply the search
        table.columns().every( function () {
            var that = this;
    
            $( 'input', this.footer() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that
                        .search( this.value )
                        .draw();
                }
                
            } );
            
        } );

      }


        
      
      
      //---------------Form inputs
      var table = $('#example6').DataTable();
    
        $('button').click( function() {
            var data = table.$('input, select').serialize();
            alert(
                "The following data would have been submitted to the server: \n\n"+
                data.substr( 0, 120 )+'...'
            );
            return false;
        } );
      
      
      
      
      }); // End of use strict
</script>