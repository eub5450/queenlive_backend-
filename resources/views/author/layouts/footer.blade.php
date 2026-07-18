	<!-- [ Layout footer ] Start -->
  <nav class="layout-footer footer footer-light">
   <div class="container-fluid d-flex flex-wrap justify-content-between text-center container-p-x pb-3">
    <div class="pt-3">
     <span class="float-md-right d-none d-lg-block">&copy; BP Live &amp; Made with <i class="fas fa-heart text-danger mr-2"></i></span>
   </div>
   <div>
     <a href="javascript:" class="footer-link pt-3">About Us</a>
     <a href="javascript:" class="footer-link pt-3 ml-4">Help</a>
     <a href="javascript:" class="footer-link pt-3 ml-4">Contact</a>
     <a href="javascript:" class="footer-link pt-3 ml-4">Terms &amp; Conditions</a>
   </div>
 </div>
</nav>
<!-- [ Layout footer ] End -->

</div>
<!-- [ Layout content ] Start -->

</div>
<!-- [ Layout container ] End -->
</div>
<!-- Overlay -->
<div class="layout-overlay layout-sidenav-toggle"></div>
</div>
<!-- [ Layout wrapper] End -->

<!-- Core scripts -->
<script src="{{asset('public/author/assets/js/pace.js')}}"></script>
<script
src="https://code.jquery.com/jquery-3.4.1.min.js"
integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
crossorigin="anonymous"></script>
<script src="{{asset('public/author/assets/libs/popper/popper.js')}}"></script>
<script src="{{asset('public/author/assets/js/bootstrap.js')}}"></script>
<script src="{{asset('public/author/assets/js/sidenav.js')}}"></script>
<script src="{{asset('public/author/assets/js/layout-helpers.js')}}"></script>
<script src="{{asset('public/author/assets/js/material-ripple.js')}}"></script>

<!-- Libs -->
<script src="{{asset('public/author/assets/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>
<script src="{{asset('public/author/assets/libs/eve/eve.js')}}"></script>
<script src="{{asset('public/author/assets/libs/raphael/raphael.js')}}"></script>
<script src="{{asset('public/author/assets/libs/morris/morris.js')}}"></script>
<script src="{{asset('public/author/assets/js/analytics.js')}}"></script>
<!-- Demo -->

<script src="{{asset('public/author/assets/js/pages/dashboards_project.js')}}"></script>
<script src="{{asset('public/author/assets/libs/bootstrap-markdown/bootstrap-markdown.js')}}"></script>
<script src="{{asset('public/author/assets/libs/markdown/markdown.js')}}"></script>
<script src="{{asset('public/author/assets/js/pages/forms_editors.js')}}"></script>
<script src="{{asset('public/author/assets/libs/datatables/datatables.js')}}"></script>
<script src="{{asset('public/author/assets/js/demo.js')}}"></script>
<script src="{{asset('public/author/assets/js/pages/pages_chat.js')}}"></script>
<script>
        // DataTable start
        $('#report-table').DataTable();
        // DataTable end
      </script>
      <script type="text/javascript">
       $(function() {
        $('#bs-markdown2').markdown({
          iconlibrary: 'fa',
          footer: '<div id="md-character-footer"></div><small id="md-character-counter" class="text-muted">350 character left</small>',

          onChange: function(e) {
            var contentLength = e.getContent().length;

            if (contentLength > 350) {
              $('#md-character-counter')
              .removeClass('text-muted')
              .addClass('text-danger')
              .html((contentLength - 350) + ' character surplus.');
            } else {
              $('#md-character-counter')
              .removeClass('text-danger')
              .addClass('text-muted')
              .html((350 - contentLength) + ' character left.');
            }
          },
        });

  // Update character counter
  $('#markdown').trigger('change');
})
       $(function() {
        $('#bs-markdown3').markdown({
          iconlibrary: 'fa',
          footer: '<div id="md-character-footer"></div><small id="md-character-counter" class="text-muted">350 character left</small>',

          onChange: function(e) {
            var contentLength = e.getContent().length;

            if (contentLength > 350) {
              $('#md-character-counter')
              .removeClass('text-muted')
              .addClass('text-danger')
              .html((contentLength - 350) + ' character surplus.');
            } else {
              $('#md-character-counter')
              .removeClass('text-danger')
              .addClass('text-muted')
              .html((350 - contentLength) + ' character left.');
            }
          },
        });

  // Update character counter
  $('#markdown').trigger('change');
})
       $(function() {
        $('#bs-markdown4').markdown({
          iconlibrary: 'fa',
          footer: '<div id="md-character-footer"></div><small id="md-character-counter" class="text-muted">350 character left</small>',

          onChange: function(e) {
            var contentLength = e.getContent().length;

            if (contentLength > 350) {
              $('#md-character-counter')
              .removeClass('text-muted')
              .addClass('text-danger')
              .html((contentLength - 350) + ' character surplus.');
            } else {
              $('#md-character-counter')
              .removeClass('text-danger')
              .addClass('text-muted')
              .html((350 - contentLength) + ' character left.');
            }
          },
        });

  // Update character counter
  $('#markdown').trigger('change');
})
</script>


@yield('script')
