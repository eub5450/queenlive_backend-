<ul class="sidenav-inner py-1">
  <li class="sidenav-item active open">
    <a href="{{ route('author.dashboard') }}" class="sidenav-link">
      <i class="sidenav-icon feather icon-home"></i>
      <div>Dashboard</div>
    </a>
  </li>

  <li class="sidenav-item">
    <a href="javascript:" class="sidenav-link sidenav-toggle">
      <i class="sidenav-icon feather icon-user"></i>
      <div>Host Tools</div>
    </a>
    <ul class="sidenav-menu">
      <li class="sidenav-item"><a href="{{ route('country.author.host-search') }}" class="sidenav-link"><div>Search Profile</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.host-add') }}" class="sidenav-link"><div>Add Host</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.host-list') }}" class="sidenav-link"><div>Active Host List</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.host-pending') }}" class="sidenav-link"><div>Pending Host</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.host-transfer') }}" class="sidenav-link"><div>Transfer Host</div></a></li>
    </ul>
  </li>

  <li class="sidenav-item">
    <a href="javascript:" class="sidenav-link sidenav-toggle">
      <i class="sidenav-icon feather icon-layers"></i>
      <div>Agency</div>
    </a>
    <ul class="sidenav-menu">
      <li class="sidenav-item"><a href="{{ route('country.author.agency-add') }}" class="sidenav-link"><div>Add Agency</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.agency-list') }}" class="sidenav-link"><div>Agency Details</div></a></li>
    </ul>
  </li>

  <li class="sidenav-item">
    <a href="javascript:" class="sidenav-link sidenav-toggle">
      <i class="sidenav-icon feather icon-credit-card"></i>
      <div>Portal</div>
    </a>
    <ul class="sidenav-menu">
      <li class="sidenav-item"><a href="{{ route('country.author.protal') }}" class="sidenav-link"><div>Portal History</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.protal-list') }}" class="sidenav-link"><div>Portal Users</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.protal-recall') }}" class="sidenav-link"><div>Location ID Recall</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.protal-recall-list') }}" class="sidenav-link"><div>Recall History</div></a></li>
    </ul>
  </li>

  <li class="sidenav-item">
    <a href="javascript:" class="sidenav-link sidenav-toggle">
      <i class="sidenav-icon feather icon-bar-chart-2"></i>
      <div>Location</div>
    </a>
    <ul class="sidenav-menu">
      <li class="sidenav-item"><a href="{{ route('country.author.host-ranking') }}" class="sidenav-link"><div>Location Ranking</div></a></li>
      <li class="sidenav-item"><a href="{{ route('country.author.banner') }}" class="sidenav-link"><div>Add Banner</div></a></li>
    </ul>
  </li>
</ul>
