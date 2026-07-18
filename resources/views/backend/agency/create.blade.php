@extends('backend.layouts.main')

@section('title')
Create New Agency
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    /* Dark Theme (keeping your existing styles) */
    body {
        background: #0a0c0f;
        color: #e0e0e0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    /* Main Card */
    .agency-card {
        border: none;
        border-radius: 24px;
        background: #1a1e24;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        margin: 20px 0;
        overflow: hidden;
        border: 1px solid rgba(255, 215, 0, 0.1);
    }
    
    .card-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        padding: 1.8rem 2.5rem;
    }
    
    .card-header h4 {
        color: #ffc107;
        font-weight: 800;
        margin: 0;
        font-size: 1.8rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .card-header h4 i {
        color: #ffc107;
        font-size: 2rem;
    }
    
    .card-body {
        padding: 2.5rem;
        background: #1a1e24;
    }
    
    /* Alert */
    .custom-alert {
        background: #252b33;
        border-left: 4px solid #dc3545;
        border-radius: 12px;
        padding: 1.2rem 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
    }
    
    .custom-alert i {
        color: #dc3545;
        font-size: 1.5rem;
    }
    
    .custom-alert ul {
        margin: 0;
        padding-left: 20px;
        color: #ffc107;
    }
    
    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-group.full-width {
        grid-column: span 2;
    }
    
    .form-label {
        color: #ffc107;
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-label i {
        color: #ffc107;
        width: 20px;
    }
    
    .form-control {
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 16px;
        padding: 14px 18px;
        color: #fff;
        width: 100%;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .form-control:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
        background: #2f3740;
    }
    
    .form-control[readonly] {
        background: #1a1e24;
        border-color: #3a4450;
        color: #a0a8b5;
        cursor: not-allowed;
    }
    
    /* Image Upload Section */
    .image-upload-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin: 25px 0;
    }
    
    .image-upload-card {
        background: #252b33;
        border-radius: 20px;
        padding: 20px;
        border: 2px dashed #2f3740;
        transition: all 0.3s ease;
    }
    
    .image-upload-card:hover {
        border-color: #ffc107;
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.1);
    }
    
    .image-preview {
        width: 100%;
        height: 150px;
        background: #1a1e24;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        overflow: hidden;
        border: 2px solid #2f3740;
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s ease;
    }
    
    .image-preview img:hover {
        transform: scale(1.05);
    }
    
    /* Default placeholder styling */
    .image-preview img[src=""], 
    .image-preview img:not([src]) {
        object-fit: contain;
        padding: 20px;
        background: #2f3740;
    }
    
    .image-upload-label {
        background: #2f3740;
        border-radius: 50px;
        padding: 10px 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ffc107;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
        border: 1px solid transparent;
    }
    
    .image-upload-label:hover {
        background: #ffc107;
        color: #1a1e24;
        border-color: #ffc107;
    }
    
    .image-upload-label i {
        font-size: 1rem;
    }
    
    .image-upload-input {
        display: none;
    }
    
    /* Submit Button */
    .btn-submit {
        background: linear-gradient(145deg, #ffc107, #ffaa00);
        color: #1a1e24;
        border: none;
        padding: 16px 40px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1.1rem;
        letter-spacing: 1px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
        text-transform: uppercase;
        margin-top: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .btn-submit:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 20px 40px rgba(255, 193, 7, 0.4);
        background: linear-gradient(145deg, #ffaa00, #ffc107);
    }
    
    .btn-submit i {
        font-size: 1.2rem;
    }
    
    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .button-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #2f3740;
    }
    
    /* Info Tooltip */
    .info-tooltip {
        display: inline-block;
        margin-left: 8px;
        color: #a0a8b5;
        cursor: help;
        position: relative;
    }
    
    .info-tooltip:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #252b33;
        color: #ffc107;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        white-space: nowrap;
        border: 1px solid #ffc107;
        margin-bottom: 10px;
        z-index: 1000;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .image-upload-grid {
            grid-template-columns: 1fr;
        }
        
        .card-header h4 {
            font-size: 1.3rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .button-wrapper {
            justify-content: center;
        }
    }
    
    /* Loading State */
    .loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }
    
    .btn-submit.loading i {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Auto-generated badge */
    .auto-badge {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: #ffc107;
        color: #1a1e24;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        pointer-events: none;
    }
    
    .input-wrapper {
        position: relative;
    }
    
    /* File name display */
    .file-name {
        color: #a0a8b5;
        font-size: 0.8rem;
        margin-top: 8px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Error styling */
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 5px;
        display: block;
    }
    
    /* Toast customization */
    .toast-success {
        background-color: #28a745 !important;
    }
    
    .toast-error {
        background-color: #dc3545 !important;
    }
    
    .toast-info {
        background-color: #ffc107 !important;
    }
</style>
@endpush

@section('content')
<div class="body-content">
    @if ($errors->any())
        <div class="custom-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="agency-card">
        <div class="card-header">
            <h4>
                <i class="fas fa-building"></i>
                Create New Agency
            </h4>
        </div>
        
        <div class="card-body">
            <form action="{{ URL::to('agency_store') }}" method="POST" enctype="multipart/form-data" id="agencyForm">
                @csrf
                
                <div class="form-grid">
                    <!-- Member ID -->
                    <div class="form-group">
                        <label class="form-label" for="agency_user_id">
                            <i class="fas fa-id-card"></i>
                            Member ID
                            <span class="info-tooltip" data-tooltip="Enter member ID">ⓘ</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="user_id" 
                                   class="form-control @error('user_id') is-invalid @enderror" 
                                   placeholder="Enter member ID" 
                                   value="{{ old('user_id') }}" 
                                   id="agency_user_id" 
                                   maxlength="10"
                                   required>
                            <span id="has_order_text"></span>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- User Name -->
                    <div class="form-group">
                        <label class="form-label" for="name">
                            <i class="fas fa-user"></i>
                            User Name
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   placeholder="Auto-filled from ID" 
                                   value="{{ old('name') }}" 
                                   id="name" 
                                   readonly>
                        </div>
                    </div>
                    
                    <!-- Agency Name -->
                    <div class="form-group">
                        <label class="form-label" for="agency_name">
                            <i class="fas fa-tag"></i>
                            Agency Name
                        </label>
                        <input type="text" 
                               name="agency_name" 
                               class="form-control @error('agency_name') is-invalid @enderror" 
                               placeholder="Enter agency name" 
                               value="{{ old('agency_name') }}" 
                               id="agency_name" 
                               required>
                        @error('agency_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Agency Code -->
                    <div class="form-group">
                        <label class="form-label" for="agencycode">
                            <i class="fas fa-qrcode"></i>
                            Agency Code
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="agency_code" 
                                   readonly 
                                   class="form-control @error('agency_code') is-invalid @enderror" 
                                   placeholder="Auto-generated" 
                                   value="{{ old('agency_code') }}" 
                                   id="agencycode" 
                                   required>
                            <span class="auto-badge">Auto</span>
                            @error('agency_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div class="form-group full-width">
                        <label class="form-label" for="phone">
                            <i class="fas fa-phone"></i>
                            Phone Number
                        </label>
                        <input type="tel" 
                               name="phone" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               placeholder="Enter phone number" 
                               value="{{ old('phone') }}" 
                               required>
                        @error('phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                    <!-- NID (2026-07-18: agencies.nid was never collected by this form) -->
                    <div class="form-group full-width">
                        <label class="form-label" for="nid">
                            <i class="fas fa-id-badge"></i>
                            NID Number
                        </label>
                        <input type="text"
                               name="nid"
                               class="form-control @error('nid') is-invalid @enderror"
                               id="nid"
                               placeholder="Enter NID number"
                               value="{{ old('nid') }}"
                               required>
                        @error('nid')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                <!-- Image Upload Section -->
                <div class="image-upload-grid">
                    <!-- Photo ID -->
                    <div class="image-upload-card">
                        <div class="image-preview" id="photoIdPreview">
                            <img src="{{ asset('public/backend/placeholder.png') }}" 
                                 id="photoIdImg" 
                                 alt="Photo ID Preview"
                                 onerror="this.src='{{ asset('public/backend/placeholder.png') }}'">
                        </div>
                        <label for="photoId" class="image-upload-label">
                            <i class="fas fa-camera"></i>
                            Choose Photo ID
                        </label>
                        <input type="file" 
                               name="photo_id" 
                               class="image-upload-input @error('photo_id') is-invalid @enderror" 
                               id="photoId" 
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" 
                               onchange="previewImage(this, 'photoIdImg')" 
                               required>
                        <div class="file-name" id="photoIdFileName"></div>
                        @error('photo_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small style="color: #a0a8b5; display: block; margin-top: 8px; text-align: center;">
                            <i class="fas fa-info-circle"></i> Only JPG, JPEG, PNG or WEBP. Max 2MB.
                        </small>
                    </div>
                    
                    <!-- Selfie -->
                    <div class="image-upload-card">
                        <div class="image-preview" id="selfiePreview">
                            <img src="{{ asset('public/backend/placeholder.png') }}" 
                                 id="selfieImg" 
                                 alt="Selfie Preview"
                                 onerror="this.src='{{ asset('public/backend/placeholder.png') }}'">
                        </div>
                        <label for="selfie" class="image-upload-label">
                            <i class="fas fa-user-circle"></i>
                            Choose Selfie
                        </label>
                        <input type="file" 
                               name="selfie" 
                               class="image-upload-input @error('selfie') is-invalid @enderror" 
                               id="selfie" 
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" 
                               onchange="previewImage(this, 'selfieImg')" 
                               required>
                        <div class="file-name" id="selfieFileName"></div>
                        @error('selfie')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small style="color: #a0a8b5; display: block; margin-top: 8px; text-align: center;">
                            <i class="fas fa-info-circle"></i> Only JPG, JPEG, PNG or WEBP. Max 2MB.
                        </small>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="button-wrapper">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-save"></i>
                        Create Agency
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000",
    };

    $(document).ready(function() {
        // Member ID lookup
        $(document).on('keyup change','#agency_user_id', function() {
            var number = $(this).val();
            var check_number = number.toString().length;
            $('#has_order_text').text('');
            
            if (check_number >= 4) {
                $.ajax({
                    url: "{{ URL::to('get/user_info') }}/" + number,
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        $('#name').val('Loading...');
                        $('#name').prop('readonly', true);
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
                            } else {
                                toastr.error('User not found');
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
                $('#agencycode').val('');
            }
        });

        // Form submission validation
        $('#agencyForm').on('submit', function(e) {
            var photoId = $('#photoId')[0].files.length;
            var selfie = $('#selfie')[0].files.length;
            
            if (photoId === 0) {
                e.preventDefault();
                toastr.error('Please select a Photo ID');
                return false;
            }
            
            if (selfie === 0) {
                e.preventDefault();
                toastr.error('Please select a Selfie');
                return false;
            }
            
            // Show loading state
            $('#submitBtn').addClass('loading').prop('disabled', true);
            $('#submitBtn').html('<i class="fas fa-spinner fa-pulse"></i> Creating...');
        });
    });

    // Image preview function
    function previewImage(input, imgId) {
        var fileNameSpan = input.id === 'photoId' ? 'photoIdFileName' : 'selfieFileName';
        var preview = document.getElementById(imgId);
        var fileName = document.getElementById(fileNameSpan);
        
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            var allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            var extension = file.name.split('.').pop().toLowerCase();
            
            // Validate file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size must be 2MB or less.');
                toastr.error('File size must be less than 2MB');
                input.value = '';
                fileName.textContent = '';
                preview.src = '{{ asset('public/backend/placeholder.png') }}';
                return;
            }
            
            // Validate file type
            if (allowedTypes.indexOf(file.type) === -1 || allowedExtensions.indexOf(extension) === -1) {
                alert('Only JPG, JPEG, PNG or WEBP files are allowed.');
                toastr.error('Only JPG, JPEG, PNG or WEBP files are allowed');
                input.value = '';
                fileName.textContent = '';
                preview.src = '{{ asset('public/backend/placeholder.png') }}';
                return;
            }
            
            // Show file name
            fileName.textContent = file.name;
            
            // Preview image
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.objectFit = 'cover';
            };
            reader.readAsDataURL(file);
        } else {
            // Reset preview
            preview.src = '{{ asset('public/backend/placeholder.png') }}';
            fileName.textContent = '';
        }
    }

    // Manual agency code generation if needed (fallback)
    $('#agency_name').on('input', function() {
        var name = $(this).val();
        var currentCode = $('#agencycode').val();
        
        // Only generate if no code exists (as fallback)
        if (!currentCode && name) {
            var timestamp = Date.now().toString().slice(-4);
            var code = name.toUpperCase().replace(/\s+/g, '') + timestamp;
            $('#agencycode').val(code);
        }
    });

    // Input validation for member ID (only numbers)
    $('#agency_user_id').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Phone number validation
    $('#phone').on('input', function() {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });
</script>
@endsection
