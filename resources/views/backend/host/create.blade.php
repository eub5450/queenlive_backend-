@extends('backend.layouts.main')

@section('title')
Supplier | Host Registration
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* Form Styles */
    .body-content {
        padding: 20px;
    }
    
    .card {
        background: var(--dark-card);
        border: 1px solid var(--dark-border);
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }
    
    .card-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 25px;
        position: relative;
    }
    
    .form-group label {
        color: var(--dark-text);
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-group label i {
        color: #ffc107;
        margin-right: 8px;
        width: 20px;
    }
    
    .form-control {
        background: rgba(0, 0, 0, 0.3);
        border: 2px solid var(--dark-border);
        border-radius: 10px;
        color: var(--dark-text);
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
        height: auto;
    }
    
    .form-control:hover {
        border-color: rgba(255, 193, 7, 0.3);
    }
    
    .form-control:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1);
        background: rgba(0, 0, 0, 0.5);
        color: var(--dark-text);
        outline: none;
    }
    
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ffc107' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
        padding-right: 40px;
    }
    
    /* Image Preview Styles */
    .image-preview-container {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 10px;
    }
    
    .image-preview {
        width: 100px;
        height: 100px;
        border-radius: 10px;
        border: 3px solid rgba(255, 193, 7, 0.3);
        background: rgba(0, 0, 0, 0.2);
        padding: 5px;
        transition: all 0.3s ease;
    }
    
    .image-preview:hover {
        border-color: #ffc107;
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .preview-label {
        color: var(--dark-text-muted);
        font-size: 13px;
        margin-bottom: 5px;
        display: block;
    }
    
    .preview-label i {
        color: #ffc107;
        margin-right: 5px;
    }
    
    /* Button Styles */
    .btn-save {
        background: linear-gradient(145deg, #28a745, #20c997) !important;
        color: white !important;
        padding: 12px 30px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        border: none !important;
        border-radius: 10px !important;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease !important;
        cursor: pointer;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
    }
    
    .btn-save i {
        margin-right: 8px;
    }
    
    /* Loading Spinner */
    .spinner-border {
        width: 20px;
        height: 20px;
        margin-right: 8px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Toast Notifications */
    .toast-success {
        background: linear-gradient(145deg, #28a745, #20c997) !important;
        color: white !important;
    }
    
    .toast-error {
        background: linear-gradient(145deg, #dc3545, #c82333) !important;
        color: white !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .image-preview-container {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .image-preview {
            width: 80px;
            height: 80px;
        }
    }
</style>
@endpush

@section('content')
<div class="body-content">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-white">
                    <i class="fas fa-user-plus text-warning me-2"></i>
                    Host Registration Form
                </h4>
                <span class="badge bg-info">New Registration</span>
            </div>
        </div>
    </div>

    <form action="{{URL::to('/host-store')}}" method="post" enctype="multipart/form-data" id="hostForm">
        @csrf
    
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Host Selection -->
                        <div class="form-group">
                            <label for="host_id">
                                <i class="fas fa-user"></i>
                                Host ID
                            </label>
                            <select name="host_id" class="form-control host_id" id="host_id" required>
                                <option value="">-- Select Host --</option>
                                @foreach($host as $user)
                                <option value="{{$user->id}}">{{$user->id}} - {{$user->name}}</option>
                                @endforeach
                            </select>
                            <small class="text-white-50" id="host-info"></small>
                        </div>

                        <!-- Agency Selection -->
                        <div class="form-group">
                            <label for="agency_id">
                                <i class="fas fa-building"></i>
                                Joining Agency
                            </label>
                            <select name="agency_id" class="form-control agency_id" id="agency_id" required>
                                <option value="">-- Select Agency --</option>
                                @foreach($agencys as $agency)
                                <option value="{{$agency->code}}">{{$agency->code}} - {{$agency->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hosting Type -->
                        <div class="form-group">
                            <label for="hosting_type">
                                <i class="fas fa-broadcast-tower"></i>
                                Hosting Type
                            </label>
                            <select name="hosting_type" class="form-control hosting_type" id="hosting_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="2">Video</option>
                                <option value="1">Audio</option>
                            </select>
                        </div>

                        <!-- NID Number -->
                        <div class="form-group">
                            <label for="nid_number">
                                <i class="fas fa-id-card"></i>
                                NID Number
                            </label>
                            <input type="text" name="nid_number" class="form-control" 
                                   placeholder="Enter NID Number" id="nid_number" required>
                        </div>

                        <!-- Phone Number -->
                        <div class="form-group">
                            <label for="phone_number">
                                <i class="fas fa-phone"></i>
                                Phone Number
                            </label>
                            <input type="text" name="phone_number" class="form-control" 
                                   placeholder="Enter Phone Number" id="phone_number" required>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="row">
                            <!-- Selfie Upload -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="selfie">
                                        <i class="fas fa-camera"></i>
                                        Selfie
                                    </label>
                                    <input type="file" name="selfie" class="form-control" 
                                           id="selfie" accept="image/*" onchange="previewImage(this, 'preview1')" required>
                                    <div class="image-preview-container">
                                        <div class="image-preview">
                                            <img src="https://via.placeholder.com/100x100?text=Selfie" 
                                                 id="preview1" alt="Selfie Preview">
                                        </div>
                                        <small class="text-white-50">Click to upload selfie</small>
                                    </div>
                                </div>
                            </div>

                            <!-- NID Upload -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nid">
                                        <i class="fas fa-id-card"></i>
                                        NID Image
                                    </label>
                                    <input type="file" name="nid" class="form-control" 
                                           id="nid" accept="image/*" onchange="previewImage(this, 'preview2')" required>
                                    <div class="image-preview-container">
                                        <div class="image-preview">
                                            <img src="https://via.placeholder.com/100x100?text=NID" 
                                                 id="preview2" alt="NID Preview">
                                        </div>
                                        <small class="text-white-50">Click to upload NID</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="image">
                                        <i class="fas fa-image"></i>
                                        Additional Image
                                    </label>
                                    <input type="file" name="image" class="form-control" 
                                           id="image" accept="image/*" onchange="previewImage(this, 'preview3')" required>
                                    <div class="image-preview-container">
                                        <div class="image-preview">
                                            <img src="https://via.placeholder.com/100x100?text=Image" 
                                                 id="preview3" alt="Image Preview">
                                        </div>
                                        <small class="text-white-50">Click to upload image</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn-save" id="submitBtn">
                                <i class="fas fa-save"></i>
                                Save Host Information
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Toastr Notifications (optional) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// Configure Toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000
};

$(document).ready(function() {
    console.log('✅ Document ready - jQuery version:', $.fn.jquery);
    
    // Initialize select2 if available (optional)
    if ($.fn.select2) {
        $('.host_id, .agency_id').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    /* ===== HOST SELECTION AJAX ===== */
    $(document).on('change', '#host_id', function() {
        var id = $(this).val();
        
        if (!id) {
            $('#host-info').html('');
            return;
        }
        
        console.log('Fetching host info for ID:', id);
        
        // Show loading state
        $('#host-info').html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ URL::to('get/host_agency_info') }}/" + id,
            type: "GET",
            dataType: "json",
            success: function(data) {
                console.log('AJAX Success:', data);
                
                if (data.success) {
                    // Update agency fields if they exist
                    if ($('#agency_name').length) {
                        $('#agency_name').val(data.data.name || '');
                    }
                    if ($('#agency_code').length) {
                        $('#agency_code').val(data.data.code || '');
                    }
                    
                    // Show host info
                    $('#host-info').html(
                        '<i class="fas fa-check-circle text-success"></i> ' + 
                        'Host: ' + (data.data.name || '') + ' (Code: ' + (data.data.code || '') + ')'
                    );
                    
                    // Success notification
                    toastr.success(data.success || 'Host information loaded successfully!');
                    
                } else if (data.error) {
                    $('#host-info').html('<i class="fas fa-exclamation-circle text-danger"></i> ' + data.error);
                    toastr.error(data.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                
                $('#host-info').html('<i class="fas fa-exclamation-triangle text-danger"></i> Error loading host info');
                toastr.error('Failed to load host information. Please try again.');
            }
        });
    });
    
    /* ===== FORM SUBMIT HANDLER ===== */
    $('#hostForm').on('submit', function(e) {
        console.log('Form submitting...');
        
        var btn = $('#submitBtn');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        // Form will submit normally after this
        return true;
    });
    
    /* ===== INITIAL CHECK ===== */
    console.log('Initialization complete');
    console.log('Host dropdown exists:', $('#host_id').length > 0);
    console.log('Agency dropdown exists:', $('#agency_id').length > 0);
});

/* ===== IMAGE PREVIEW FUNCTIONS ===== */
function previewImage(input, previewId) {
    console.log('Previewing image for:', previewId);
    
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            $('#' + previewId)
                .attr('src', e.target.result)
                .css({
                    'width': '100%',
                    'height': '100%',
                    'object-fit': 'cover'
                });
            
            console.log('Image loaded for:', previewId);
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/* ===== HELPER FUNCTIONS ===== */
function showLoading(selector) {
    $(selector).append('<i class="fas fa-spinner fa-spin ms-2"></i>');
}

function hideLoading(selector) {
    $(selector).find('.fa-spinner').remove();
}
</script>

<!-- Fallback in case jQuery fails -->
<script>
// Double-check jQuery loaded
if (typeof jQuery === 'undefined') {
    console.error('❌ jQuery failed to load!');
    document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
}
</script>
@endpush