@extends('backend.layouts.main')

@section('title')
Banner Management
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Dark Theme */
    body {
        background: #0a0c0f;
        color: #e0e0e0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    /* Main Card */
    .banner-card {
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
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .card-header h4 {
        color: #ffc107;
        font-weight: 700;
        margin: 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-header h4 i {
        color: #ffc107;
    }
    
    /* Add Button */
    .btn-add {
        background: linear-gradient(145deg, #ffc107, #ffaa00);
        color: #1a1e24;
        border: none;
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .btn-add:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.5);
        background: linear-gradient(145deg, #ffaa00, #ffc107);
    }
    
    .btn-add i {
        font-size: 1.1rem;
    }
    
    .card-body {
        padding: 2rem;
        background: #1a1e24;
    }
    
    /* Table Styles */
    .table-responsive {
        border-radius: 20px;
        overflow: hidden;
        background: #1a1e24;
    }
    
    .banner-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        margin: 0;
    }
    
    .banner-table thead tr {
        background: #252b33;
    }
    
    .banner-table thead th {
        color: #ffc107;
        font-weight: 700;
        padding: 18px 20px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        border-bottom: 2px solid #ffc107;
    }
    
    .banner-table tbody tr {
        background: #252b33;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .banner-table tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        background: #2f3740;
        box-shadow: 0 15px 30px rgba(255, 193, 7, 0.15);
    }
    
    .banner-table tbody td {
        padding: 20px;
        border: none;
        vertical-align: middle;
        color: #e0e0e0;
    }
    
    /* Banner Image */
    .banner-wrapper {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transition: all 0.5s ease;
        max-width: 400px;
    }
    
    .banner-wrapper:hover {
        transform: scale(1.02);
        box-shadow: 0 20px 40px rgba(255, 193, 7, 0.3);
    }
    
    .banner-img {
        width: 100%;
        height: auto;
        max-height: 150px;
        object-fit: cover;
        border-radius: 16px;
        border: 2px solid #ffc107;
        transition: all 0.5s ease;
        display: block;
    }
    
    .banner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), transparent);
        opacity: 0;
        transition: all 0.3s ease;
        border-radius: 16px;
    }
    
    .banner-wrapper:hover .banner-overlay {
        opacity: 1;
    }
    
    .banner-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ffc107;
        color: #1a1e24;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
    }
    
    /* Action Buttons */
    .action-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .btn-action {
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }
    
    .btn-action::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn-action:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-action:hover {
        transform: translateY(-3px) scale(1.05);
    }
    
    .btn-remove {
        background: linear-gradient(145deg, #dc3545, #c82333);
        color: white;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }
    
    .btn-remove:hover {
        box-shadow: 0 10px 25px rgba(220, 53, 69, 0.5);
    }
    
    .btn-view {
        background: linear-gradient(145deg, #17a2b8, #138496);
        color: white;
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
    }
    
    /* Modal Styles - Dark */
    .modal-dark .modal-content {
        background: #1a1e24;
        border: 2px solid #ffc107;
        border-radius: 24px;
        box-shadow: 0 30px 60px rgba(255, 193, 7, 0.2);
    }
    
    .modal-dark .modal-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        padding: 1.8rem;
        border-radius: 22px 22px 0 0;
    }
    
    .modal-dark .modal-title {
        color: #ffc107;
        font-weight: 700;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-dark .modal-title i {
        color: #ffc107;
        font-size: 1.6rem;
    }
    
    .modal-dark .close {
        color: #ffc107;
        opacity: 1;
        font-size: 2rem;
        text-shadow: none;
    }
    
    .modal-dark .modal-body {
        padding: 2rem;
        background: #1a1e24;
    }
    
    .modal-dark .modal-footer {
        background: #252b33;
        border-top: 2px solid #ffc107;
        padding: 1.8rem;
        border-radius: 0 0 22px 22px;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.8rem;
    }
    
    .form-label {
        color: #ffc107;
        font-weight: 600;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }
    
    .form-label i {
        color: #ffc107;
    }
    
    .form-control {
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 16px;
        padding: 14px 18px;
        color: #fff;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
        background: #2f3740;
    }
    
    .form-control[type="file"] {
        padding: 12px;
        background: #2f3740;
        cursor: pointer;
    }
    
    .form-control[type="file"]::-webkit-file-upload-button {
        background: #ffc107;
        color: #1a1e24;
        padding: 8px 15px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        margin-right: 15px;
        cursor: pointer;
    }
    
    /* Modal Buttons */
    .btn-modal {
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-modal-close {
        background: #2f3740;
        color: #fff;
    }
    
    .btn-modal-close:hover {
        background: #3a4450;
        transform: translateY(-2px);
    }
    
    .btn-modal-save {
        background: linear-gradient(145deg, #ffc107, #ffaa00);
        color: #1a1e24;
        font-weight: 700;
    }
    
    .btn-modal-save:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 10px 20px rgba(255, 193, 7, 0.4);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #252b33;
        border-radius: 20px;
        margin: 20px 0;
    }
    
    .empty-icon {
        width: 100px;
        height: 100px;
        background: #2f3740;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 3px dashed #ffc107;
    }
    
    .empty-icon i {
        font-size: 40px;
        color: #ffc107;
    }
    
    .empty-title {
        color: #fff;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .empty-text {
        color: #a0a8b5;
        margin-bottom: 25px;
    }
    
    /* DataTables Customization */
    .dataTables_wrapper {
        padding: 20px 0;
        color: #e0e0e0;
    }
    
    .dataTables_filter {
        margin-bottom: 25px;
    }
    
    .dataTables_filter label {
        color: #ffc107;
        font-weight: 600;
    }
    
    .dataTables_filter input {
        border: 2px solid #2f3740;
        border-radius: 50px;
        padding: 10px 20px;
        background: #252b33;
        color: #fff;
        width: 250px;
        margin-left: 10px;
    }
    
    .dataTables_filter input:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 20px rgba(255, 193, 7, 0.2);
    }
    
    .dataTables_length select {
        background: #252b33;
        color: #fff;
        border: 2px solid #2f3740;
        border-radius: 8px;
        padding: 5px 10px;
        margin: 0 5px;
    }
    
    .dataTables_paginate .paginate_button {
        background: #252b33;
        color: #e0e0e0 !important;
        border: 2px solid #2f3740;
        border-radius: 10px;
        padding: 8px 15px;
        margin: 0 3px;
        transition: all 0.3s;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107 !important;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    .dataTables_paginate .paginate_button:hover {
        background: #ffc107;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    .dataTables_info {
        color: #a0a8b5;
        margin-top: 15px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
        }
        
        .btn-add {
            width: 100%;
            justify-content: center;
        }
        
        .banner-img {
            max-width: 100%;
        }
        
        .action-group {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }
    
    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1e24;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #2f3740;
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ffc107;
    }
    
    /* Animation */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .banner-table tbody tr {
        animation: slideIn 0.5s ease forwards;
    }
</style>
@endpush

@section('content')
<div class="body-content">
    <div class="banner-card">
        <div class="card-header">
            <h4>
                <i class="fas fa-images"></i>
                Banner Management
            </h4>
            <button type="button" class="btn-add" data-toggle="modal" data-target="#addBannerModal">
                <i class="fas fa-plus-circle"></i>
                Add New Banner
            </button>
        </div>
        
        <div class="card-body">
            @if(count($sliders) > 0)
                <div class="table-responsive">
                    <table class="banner-table" id="bannerTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Banner Preview</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 0; @endphp
                            @foreach($sliders as $item)
                            <tr>
                                <td style="font-weight: 700; color: #ffc107; font-size: 1.2rem;">#{{ ++$i }}</td>
                                
                                <td>
                                    <div class="banner-wrapper">
                                        <img src="{{ \App\Support\MediaPathHelper::publicUrl($item->image) }}" class="banner-img" alt="Banner">
                                        <div class="banner-overlay"></div>
                                        <span class="banner-badge">Banner {{ $i }}</span>
                                    </div>
                                </td>
                                
                                <td>
                                    <div style="background: #2f3740; padding: 15px; border-radius: 16px;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                            <i class="fas fa-info-circle" style="color: #ffc107;"></i>
                                            <span style="color: #fff; font-weight: 600;">Banner Information</span>
                                        </div>
                                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                            <div>
                                                <small style="color: #a0a8b5; display: block;">Uploaded</small>
                                                <span style="color: #ffc107;">{{ date('d M Y', strtotime($item->created_at ?? now())) }}</span>
                                            </div>
                                            <div>
                                                <small style="color: #a0a8b5; display: block;">Path</small>
                                                <span style="color: #fff; font-size: 0.8rem;">{{ basename($item->image) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="action-group">
                                        <a href="{{ \App\Support\MediaPathHelper::publicUrl($item->image) }}" target="_blank" class="btn-action btn-view">
                                            <i class="fas fa-eye"></i>
                                            Preview
                                        </a>
                                        <a href="{{ URL::to('admin-slider-removed', $item->id) }}" 
                                           class="btn-action btn-remove" 
                                           onclick="return confirm('Are you sure you want to remove this banner?')">
                                            <i class="fas fa-trash-alt"></i>
                                            Remove
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h4 class="empty-title">No Banners Found</h4>
                    <p class="empty-text">Get started by adding your first banner image.</p>
                    <button type="button" class="btn-add" data-toggle="modal" data-target="#addBannerModal" style="display: inline-flex; width: auto;">
                        <i class="fas fa-plus-circle"></i>
                        Add First Banner
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Banner Modal -->
<div class="modal fade modal-dark" id="addBannerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload New Banner
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ URL::to('admin/slider-store') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image"></i>
                            Banner Image
                        </label>
                        <input type="file" name="image" class="form-control" required accept="image/*">
                        <small style="color: #a0a8b5; margin-top: 8px; display: block;">
                            <i class="fas fa-info-circle"></i>
                            Recommended size: 1920x600 pixels. Max size: 2MB
                        </small>
                    </div>
                    
                    <div class="preview-area" style="display: none; margin-top: 20px;">
                        <label class="form-label">Preview</label>
                        <div style="border: 2px dashed #ffc107; border-radius: 16px; padding: 20px; text-align: center;">
                            <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 12px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-modal btn-modal-save">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Upload Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable if there are banners
    @if(count($sliders) > 0)
    $('#bannerTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        language: {
            search: "🔍 Search:",
            searchPlaceholder: "Search banners...",
            lengthMenu: "Show _MENU_ banners",
            info: "Showing _START_ to _END_ of _TOTAL_ banners",
            infoEmpty: "Showing 0 to 0 of 0 banners",
            infoFiltered: "(filtered from _MAX_ total banners)",
            paginate: {
                first: "⟪",
                last: "⟫",
                next: "→",
                previous: "←"
            }
        }
    });
    @endif
    
    // Image preview functionality
    $('input[name="image"]').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
                $('.preview-area').fadeIn();
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Reset preview when modal is closed
    $('#addBannerModal').on('hidden.bs.modal', function() {
        $('input[name="image"]').val('');
        $('.preview-area').hide();
    });
});
</script>
@endpush
