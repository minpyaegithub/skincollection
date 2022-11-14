<ul style="background:#161513;" class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" style="height:110px;" href="{{ route('home') }}">
        <div class="">
            <img src="{{asset('logo/logo.jpg')}}" width="79%;">
        </div>
        <!-- <div class="sidebar-brand-text mx-3">Skin Collection</div> -->
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <!-- user management -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#taTpDropDown"
            aria-expanded="true" aria-controls="taTpDropDown">
            <i class="fas fa-user-alt"></i>
            <span>User Management</span>
        </a>
        <div id="taTpDropDown" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">User Management:</h6>
                <a class="collapse-item" href="{{ route('users.index') }}">List</a>
                <a class="collapse-item" href="{{ route('users.create') }}">Add New</a>
                <a class="collapse-item" href="{{ route('users.import') }}">Import Data</a>
            </div>
        </div>
    </li>
    <!-- end user management -->
    <!-- Start Patient -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#patient"
            aria-expanded="true" aria-controls="patient">
            <i class="fa-solid fa-hospital-user"></i>
            <span>Patient</span>
        </a>
        <div id="patient" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Patient:</h6>
                <a class="collapse-item" href="{{ route('patients.index') }}">List</a>
                <a class="collapse-item" href="{{ route('patients.create') }}">Add New</a>
                <a class="collapse-item" href="{{ route('users.import') }}">Import Data</a>
            </div>
        </div>
    </li>
    <!-- end Patient -->
     <!-- Start Appointment -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#appointment"
            aria-expanded="true" aria-controls="appointment">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Appointment</span>
        </a>
        <div id="appointment" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Appointment:</h6>
                <a class="collapse-item" href="{{ route('appointments.index') }}">List</a>
                <a class="collapse-item" href="{{ route('appointments.create') }}">Create Appointment</a>
            </div>
        </div>
    </li>
    <!-- end Appointment -->
     <!-- Start Pharmacy -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#pharmacy"
            aria-expanded="true" aria-controls="pharmacy">
            <i class="fa-sharp fa-solid fa-tablets"></i>
            <span>Pharmacy</span>
        </a>
        <div id="pharmacy" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Pharmacy:</h6>
                <a class="collapse-item" href="{{ route('pharmacy.index') }}">List</a>
                <a class="collapse-item" href="{{ route('pharmacy.create') }}">Add New</a>
                <a class="collapse-item" href="{{ route('pharmacy.import') }}">Import Data</a>
            </div>
        </div>
    </li>
    <!-- end Pharmacy -->

    <!-- Start Purchase -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#purchase"
            aria-expanded="true" aria-controls="purchase">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Purchase</span>
        </a>
        <div id="purchase" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Purchase:</h6>
                <a class="collapse-item" href="{{ route('purchase.index') }}">List</a>
                <a class="collapse-item" href="{{ route('purchase.create') }}">Add New</a>
                <a class="collapse-item" href="{{ route('purchase.import') }}">Import Data</a>
            </div>
        </div>
    </li>
    <!-- end Purchase -->

    <!-- Start Treatment -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#treatment"
            aria-expanded="true" aria-controls="treatment">
            <i class="fa-solid fa-hand-holding-medical"></i>
            <span>Treatment</span>
        </a>
        <div id="treatment" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Treatment:</h6>
                <a class="collapse-item" href="{{ route('treatment.index') }}">Category List</a>
                <a class="collapse-item" href="{{ route('treatment.create') }}">Add Category</a>
            </div>

        </div>

            
    </li>
    <!-- end Pharmacy -->

    <!-- Start Purchase -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#invoice"
            aria-expanded="true" aria-controls="invoice">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Invoice</span>
        </a>
        <div id="invoice" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Invoice:</h6>
                <a class="collapse-item" href="{{ route('invoices.index') }}">List</a>
                <a class="collapse-item" href="{{ route('invoices.create') }}">Create</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    @hasrole('Admin')
        <!-- Heading -->
        <div class="sidebar-heading">
            Admin Section
        </div>

        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                aria-expanded="true" aria-controls="collapsePages">
                <i class="fas fa-fw fa-folder"></i>
                <span>Masters</span>
            </a>
            <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Role & Permissions</h6>
                    <a class="collapse-item" href="{{ route('roles.index') }}">Roles</a>
                    <a class="collapse-item" href="{{ route('permissions.index') }}">Permissions</a>
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">
    @endhasrole

    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>


</ul>