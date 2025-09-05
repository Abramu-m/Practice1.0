{{-- Nav for variable components/functionalities --}}
<li class="nav-item has-treeview">
  <a href="#" class="nav-link nav-header">
    <i class="nav-icon bi bi-person-check-fill text-info"></i>
    <p class="text-bold">
      Learning Ground
      <i class="nav-arrow bi bi-chevron-right"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ route('learn.ajax') }}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-person-plus-fill text-success"></i>
        <p>
          AJAX
        </p>               
      </a>
    </li>
    <li class="nav-item">
      <a href="{{route('learn.dropdown')}}" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-calendar-check-fill text-primary"></i>
        <p>
          Searchable Dropdown 
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="" class="nav-link nav-sub-item">
        <i class="nav-icon bi bi-file-earmark-check text-success"></i>
        <p>
            Routes
        </p>
      </a>
    </li>
  </ul>
</li>