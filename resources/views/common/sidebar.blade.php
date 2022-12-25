<ul style="background:#161513;" class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" style="height:110px;" href="{{ route('home') }}">
        <div class="">
            <img src="{{asset('logo/logo-gold.png')}}" width="79%;">
        </div>
        <!-- <div class="sidebar-brand-text mx-3">Skin Collection</div> -->
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active" id="home">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <li class="nav-item" id="inventory-home">
        <a class="nav-link" href="{{ route('inventory-home') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Inventory Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    @hasrole('Admin')
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
                <!-- <a class="collapse-item" href="{{ route('users.import') }}">Import Data</a> -->
            </div>
        </div>
    </li>
    <!-- end user management -->
    @endhasrole
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
                <!-- <a class="collapse-item" href="{{ route('users.import') }}">Import Data</a> -->
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
                <!-- <a class="collapse-item" href="{{ route('pharmacy.import') }}">Import Data</a> -->
            </div>
        </div>
    </li>
    <!-- end Pharmacy -->
    @hasrole('Admin')
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
                <!-- <a class="collapse-item" href="{{ route('purchase.import') }}">Import Data</a> -->
            </div>
        </div>
    </li>
    <!-- end Purchase -->
    @endhasrole

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
            <i class="fa-solid fa-file-invoice-dollar"></i>
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

     <!-- Start Expense -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#expense"
            aria-expanded="true" aria-controls="expense">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Expense</span>
        </a>
        <div id="expense" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Expense:</h6>
                @hasrole('Admin')
                    <a class="collapse-item" href="{{ route('expense.index') }}">List</a>
                @endhasrole
                <a class="collapse-item" href="{{ route('expense.create') }}">Create</a>
            </div>
        </div>
    </li>

    <!-- Start Patient Weight -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#weight"
            aria-expanded="true" aria-controls="weight">
            <i class="fa-solid fa-weight-scale"></i>
            <span>Patient Weight</span>
        </a>
        <div id="weight" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Patient Weight:</h6>
                <a class="collapse-item" href="{{ route('weight.index') }}">List</a>
                <a class="collapse-item" href="{{ route('weight.create') }}">Create</a>
            </div>
        </div>
    </li>

    <!-- Start Patient Photo -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#photo"
            aria-expanded="true" aria-controls="photo">
            <i class="fa-solid fa-record-vinyl"></i>
            <span>Patient Record</span>
        </a>
        <div id="photo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Patient Photo:</h6>
                @hasrole('Admin')
                <a class="collapse-item" href="{{ route('photo.index') }}">List</a>
                @endhasrole
                <a class="collapse-item" href="{{ route('photo.create') }}">Create</a>
            </div>
        </div>
    </li>

    @hasrole('Admin')
    <!-- Start Report-->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#report"
            aria-expanded="true" aria-controls="report">
            <i class="fa-solid fa-chart-line"></i>
            <span>Report</span>
        </a>
        <div id="report" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Report:</h6>
                <a class="collapse-item" href="{{ route('report.index') }}">Profit & Loss</a>
            </div>
        </div>
    </li>
    @endhasrole
    <!-- Start Patient Record -->
    <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#record"
            aria-expanded="true" aria-controls="record">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Patient Record</span>
        </a>
        <div id="record" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Patient Record:</h6>
                <a class="collapse-item" href="{{ route('record.index') }}">List</a>
                <a class="collapse-item" href="{{ route('record.create') }}">Create</a>
            </div>
        </div>
    </li> -->

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- @hasrole('Admin')
        <div class="sidebar-heading">
            Admin Section
        </div>

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

        <hr class="sidebar-divider d-none d-md-block">
    @endhasrole -->

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