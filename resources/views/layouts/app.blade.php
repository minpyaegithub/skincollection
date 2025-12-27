<!DOCTYPE html>
<html lang="en">

{{-- Include Head --}}
@include('common.head')
@livewireStyles

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @include('common.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                @include('common.header')
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                @yield('content')
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('common.footer')
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    @include('common.logout-modal')
    <!-- Bootstrap core JavaScript-->
    <script src="{{asset('js/app.js')}}"></script>


    <!-- Custom scripts for all pages-->
    <script src="{{asset('admin/js/sb-admin-2.min.js')}}"></script>

    @yield('scripts')
    @livewireScripts
    <script>

        function logClinicContextRefreshed(eventName, event) {
            var selectEl = document.getElementById('clinic_switcher');
            var detail = (event && event.detail) ? event.detail : {};

            console.log('[clinic] context refreshed', {
                eventName: eventName,
                selectedValue: selectEl ? selectEl.value : null,
                clinicId: (detail && typeof detail.clinicId !== 'undefined') ? detail.clinicId : null,
                viewingAllClinics: (detail && typeof detail.viewingAllClinics !== 'undefined') ? detail.viewingAllClinics : null,
                rawEvent: event,
            });

            setTimeout(function () {
                window.location.reload();
            }, 250);
        }

        // Browser events (from Livewire)
        window.addEventListener('clinic-context-refreshed', function (event) {
            logClinicContextRefreshed('clinic-context-refreshed', event);
        });

    </script>
</body>

</html>