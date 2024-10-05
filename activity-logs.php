<?php
require_once './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();
$_SESSION['username'] != 'admin' ? returnError() : null;

include './headers/admin.php';
?>

<body>
    <div class="container">
        <div class="position-fixed d-none" id="loader">
            <span class="loader"></span>
        </div>
        <div class="position-fixed" style="bottom: 20px; right: 20px; z-index:1000;">
            <div class="btn-group dropup">
                <button type="button" class="btn btn-outline-primary btn-floating rounded-circle" data-mdb-ripple-init
                    data-mdb-ripple-color="dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php include_once __DIR__ . '\icons\gear.svg'; ?>
                </button>
                <ul class="dropdown-menu mb-2">
                    <li><a class="dropdown-item" id="m-check-bills" onclick="checkBills()" href="#">Check
                            Bills</a></li>
                    <li><a class="dropdown-item" id="m-create-bills" onclick="createBills()" href="#">Create
                            Bills</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#" onclick="createCharge()">Create
                            Charge</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('first_day')">Nofity 1st</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('day_before')">Nofity 2nd</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('day_after')">Nofity 3rd</a></li>
                </ul>
            </div>
        </div>
        <div class="row h-screen">
            <div class="row h-half">
                <div class="col-12">
                    <?php include './headers/nav-admin.php'; ?>
                </div>
                <div class="col-12">
                    <h2 class="my-4">
                        Activity Logs
                    </h2>
                </div>
            </div>
            <div class="col h-half main-content">
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-striped custom-table" id="log-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>User</th>
                                <th>Activity</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="log-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const getLogs = () => {
            let url = "/api/get-logs.php";

            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                success: function (response) {
                    let data = response;
                    let html = "";
                    data.forEach((log) => {
                        html += `<tr>
                            <td>${log.activity_by}</td>
                            <td>${log.activity}</td>
                            <td>${log.created_at}</td>
                        </tr>`;
                    });
                    $("#log-body").html(html);
                }
            });
        }
        $(document).ready(() => {getLogs()})
        const refreshData = () => {getLogs()}
    </script>
</body>
