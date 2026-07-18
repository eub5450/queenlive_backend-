<footer class="footer-content">
    <div class="footer-text d-flex align-items-center justify-content-between">
        <div class="copy">© 2021 Dashboard</div>
        <div class="credit">Designed by: <a href="#">Minhaz</a></div>
    </div>
</footer><!--/.footer content-->
<div class="overlay"></div>

<!-- Core Scripts - Load these FIRST from CDN -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

<!-- jQuery Plugins - Load these AFTER jQuery -->
<script src="https://cdn.jsdelivr.net/npm/metismenu@3.0.7/dist/metisMenu.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.6/handlebars.min.js"></script>

<!-- DataTables from CDN -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<!-- DataTables active script needs to be local or custom -->

<!-- Chart and Sparkline from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js"></script>

<!-- Summernote from CDN -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<!-- Summernote active script needs to be local or custom -->

<!-- Form & Modal Scripts from CDN -->
<script src="https://cdn.jsdelivr.net/npm/classie@1.0.1/classie.min.js"></script>
<!-- modalEffects.js might need to be local -->
<script src="https://cdn.jsdelivr.net/npm/dropzone@5.7.2/dist/min/dropzone.min.js"></script>
<!-- dropzone.active.js needs to be local -->
<script src="https://cdn.jsdelivr.net/npm/jquery.backstretch@2.1.18/jquery.backstretch.min.js"></script>
<!-- form.scripts.js needs to be local -->

<!-- Select2 & SumoSelect from CDN -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.sumoselect@3.4.9/jquery.sumoselect.min.js"></script>

<!-- SweetAlert & Toastr from CDN -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

<!-- Your custom scripts that must be local -->

<script src="{{ asset('public/backend/it-solutionsbd/assets/dist/js/sidebar.js') }}?v={{ @filemtime(public_path('backend/it-solutionsbd/assets/dist/js/sidebar.js')) ?: time() }}"></script>


@yield('script')
@stack('scripts')

<!-- Consolidated Select2 Initialization -->
<script>
$(document).ready(function() {
    // Initialize all selects with proper classes
    if ($.fn.select2) {
        $('.select, .select_host_id, .select_agency_id').select2({
            placeholder: "Select an option",
            allowClear: true
        });
    } else {
        console.error('Select2 not loaded');
    }
});
</script>

<!-- Consolidated Delete Function with SweetAlert -->
<script>  
$(document).on("click", "#delete", function(e){
    e.preventDefault();
    var link = $(this).attr("href");
    swal({
        title: "Are you sure?",
        text: "Once deleted, this will be permanently removed!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            window.location.href = link;
        } else {
            swal("Your data is safe!");
        }
    });
});
</script>

<!-- Consolidated Toastr Notifications -->
<script>
// Toastr configuration
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000",
};

// Session messages
@if(Session::has('messege'))
    var type = "{{ Session::get('alert-type', 'info') }}";
    var message = "{{ Session::get('messege') }}";
    
    switch(type) {
        case 'info':
            toastr.info(message);
            break;
        case 'success':
            toastr.success(message);
            break;
        case 'warning':
            toastr.warning(message);
            break;
        case 'error':
            toastr.error(message);
            break;
        default:
            toastr.info(message);
    }
@endif
</script>

<!-- Consolidated User Info AJAX -->
<script>
$(document).ready(function() {
    // User info lookup
    $(document).on('keyup change', '#user_id', function() {
        var number = $(this).val();
        var check_number = number.toString().length;
        
        if (check_number == 5) {
            $.ajax({
                url: "{{ URL::to('get/user_info') }}/" + number,
                type: "GET",
                dataType: "json",
                beforeSend: function() {
                    $('#name').val('Loading...');
                },
                success: function(data) {
                    if (data.user) {
                        $('#name').val(data.user.name);
                        if (data.next_agency_code) {
                            $('#agencycode').val(data.next_agency_code);
                        }
                        
                        if (data.success) {
                            toastr.success(data.success);
                        }
                    } else {
                        $('#name').val('');
                        if (data.error) {
                            toastr.error(data.error);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $('#name').val('');
                    toastr.error('Error fetching user data');
                }
            });
        } else {
            $('#name').val('');
        }
    });

    // User recall info
    $(document).on('keyup change', '#user_id', function() {
        var number = $(this).val();
        
        if (number.length >= 3) {
            $.ajax({
                url: "{{ URL::to('get/user_recall_info') }}/" + number,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.data && data.data.balance) {
                        $('#deposit').val(data.data.balance);
                    }
                    
                    if (data.success) {
                        toastr.success(data.success);
                    } else if (data.error) {
                        toastr.error(data.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Recall AJAX Error:', error);
                }
            });
        }
    });
});
</script>

<!-- Print Function -->
<script>
function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>