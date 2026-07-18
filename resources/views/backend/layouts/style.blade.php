 <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('public/backend/it-solutionsbd/assets/dist/img/favicon.png')}}">
        <!--Global Styles(used by all pages)-->
        <link href="{{asset('public/backend/it-solutionsbd/ui.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/metisMenu/metisMenu.min.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/fontawesome/css/all.min.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/typicons/src/typicons.min.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/themify-icons/themify-icons.min.css')}}" rel="stylesheet">
        <!--Third party Styles(used by this page)--> 
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
          {{-- <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/toastr/toastr.css')}}" rel="stylesheet"> --}}
           <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet">

   
        <!--Start Your Custom Style Now-->
        <link href="{{asset('public/backend/it-solutionsbd/assets/dist/css/style_v1.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/queenlive-admin-modern.css')}}?v={{ @filemtime(public_path('backend/it-solutionsbd/queenlive-admin-modern.css')) ?: time() }}" rel="stylesheet">
         <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/summernote/summernote.css')}}" rel="stylesheet">
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/summernote/summernote-bs4.css')}}" rel="stylesheet"> 
        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/modals/component.css')}}" rel="stylesheet">
         <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/dropzone/dist/min/dropzone.min.css')}}" rel="stylesheet">

          <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/select2/dist/css/select2.min.css')}}" rel="stylesheet">

        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/select2-bootstrap4/dist/select2-bootstrap4.min.css')}}" rel="stylesheet">

        <link href="{{asset('public/backend/it-solutionsbd/assets/plugins/jquery.sumoselect/sumoselect.min.css')}}" rel="stylesheet">
        
        <link href="{{asset('public/backend/it-solutionsbd/assets/dist/css/select.html')}}" rel="stylesheet" type="text/css"/>
        <!--<link href="{{asset('public/backend/it-solutionsbd/mui.css')}}" rel="stylesheet" type="text/css"/>-->
         <style>
          span.select2-selection__arrow {
            display: none;
          }
          .form-control:disabled, .form-control[readonly] {
    background-color: #141a24;
    opacity: 1;
}
        </style>
        @stack('styles')