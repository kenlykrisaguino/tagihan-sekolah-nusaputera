<?php
require_once './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();
$_SESSION['username'] != 'subadmin' ? returnError() : null;

include './headers/admin.php';

$username = $_SESSION['username'];
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
                        Biaya Tambahan
                    </h2>
                </div>
            </div>
            <div class="col h-half main-content">
                <div class="form-group col-12 mb-3">
                    <label for="nis">Nama</label>
                    <select name="nis" id="nis" onchange="getAdditionalFee()">
                    </select>
                </div>

                <hr class="mb-2">

                <form id="additional-fee-form" method="post">

                </form>
                <div id="additional-buttons">

                </div>
            </div>
        </div>
    </div>

    <script>
        let id = 0;
        let active_id = 0;

        let type = [];
        let years = [];
        let months = [{
            numeric: 1,
            text: "Januari"
        }, {
            numeric: 2,
            text: "Februari"
        }, {
            numeric: 3,
            text: "Maret"
        }, {
            numeric: 4,
            text: "April"
        }, {
            numeric: 5,
            text: "Mei"
        }, {
            numeric: 6,
            text: "Juni"
        }, {
            numeric: 7,
            text: "Juli"
        }, {
            numeric: 8,
            text: "Agustus"
        }, {
            numeric: 9,
            text: "September"
        }, {
            numeric: 10,
            text: "Oktober"
        }, {
            numeric: 11,
            text: "November"
        }, {
            numeric: 12,
            text: "Desember"
        }]
        const filterUsers = () => {
            const nis = $('#nis').val();

            var url = "/api/get-users.php"

            $.ajax({
                url: url,
                type: 'GET',
                success: (data) => {
                    var $nis = $('#nis');
                    $nis.empty();
                    $nis.append("<option value='' selected>Pilih Siswa/i</option>");

                    data.forEach((u) => {
                        if (u.name != null) {
                            $nis.append(
                                `<option value="${u.nis}" ${u.nis == nis? 'selected' : ''}>${u.name}</option>`
                            );
                        }
                    });
                    if ($nis.hasClass('ts-control')) {
                        $nis[0].tomselect.clearOptions();
                        $nis[0].tomselect.load(function(callback) {
                            callback(data.data.map((l) => ({
                                value: l.nis,
                                text: l.name
                            })));
                        });
                    } else {
                        new TomSelect("#nis", {
                            create: false,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                        });
                    }
                }
            })

        }

        const feeTypes = () => {
            var url = "/api/fee-list.php"
            $.ajax({
                url: url,
                type: 'GET',
                success: (data) => {
                    type = data;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(errorThrown);
                }
            })
        }

        const getYears = () => {
            var url = "/api/get-tahun-ajaran.php"
            $.ajax({
                url: url,
                type: 'GET',
                success: (data) => {
                    years = data;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(errorThrown);
                }
            })
        }

        const getAdditionalFee = () => {
            var url = "/api/get-additional-fee.php";
            const nis = $('#nis').val();

            $('#additional-buttons').empty();

            var params = new URLSearchParams({
                nis: nis,
            });
            url += '?' + params.toString();

            $.ajax({
                url: url,
                type: 'GET',
                success: (data) => {
                    $('#additional-fee-form').empty();
                    $('#additional-buttons').empty();
                    id = 0

                    if (data.length == 0) {
                        $('#additional-fee-form').append(`<?php include './tables/input-additional-fees.php'; ?>`);
                        $(`#type-${id}`).append(`<option value='' selected>Pilih Jenis Biaya</option>`)
                        type.forEach((fee) => {
                            $(`#type-${id}`).append(
                                `<option value="${fee.id}">${fee.category_name}</option>`
                            )
                        })
                        new TomSelect(`#type-${id}`, {
                            create: false,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                            maxOptions: 50, // Optional: adjust based on your needs
                        });
                        years.forEach((year) => {
                            $(`#year-${id}`).append(
                                `<option value="${year.period}">${year.period}</option>`
                            )
                        })
                        new TomSelect(`#year-${id}`, {
                            create: false,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                            maxOptions: 50,
                        });

                        months.forEach((month) => {
                            $(`#month-${id}`).append(
                                `<option value="${month.numeric}">${month.text}</option>`
                            )
                        })
                        new TomSelect(`#month-${id}`, {
                            create: false,
                            multiple: true,
                            sortField: {
                                field: "numeric",
                                direction: "asc"
                            },
                            maxOptions: 12,
                        });
                        active_id++

                    } else {
                        data.forEach((fees) => {
                            $('#additional-fee-form').append(`<?php include './tables/input-additional-fees.php'; ?>`);

                            $(`#type-${id}`).append(
                                `<option value='' selected>Pilih Jenis Biaya</option>`)
                            type.forEach((fee) => {
                                $(`#type-${id}`).append(
                                    `<option value="${fee.id}" ${fee.id == fees.type ? 'selected' : ''}>${fee.category_name}</option>`
                                )
                            })
                            new TomSelect(`#type-${id}`, {
                                create: false,
                                sortField: {
                                    field: "text",
                                    direction: "asc"
                                },
                                maxOptions: 50,
                            });

                            years.forEach((year) => {
                                $(`#year-${id}`).append(
                                    `<option value="${year.period}" ${fees.years.includes(year.period) ? 'selected' : ''}>${year.period}</option>`
                                )
                            })
                            new TomSelect(`#year-${id}`, {
                                create: false,
                                sortField: {
                                    field: "text",
                                    direction: "asc"
                                },
                                maxOptions: 50,
                            });

                            months.forEach((month) => {
                                $(`#month-${id}`).append(
                                    `<option value="${month.numeric}" ${fees.months.includes(month.numeric.toString()) ? 'selected' : ''}>${month.text}</option>`
                                )
                            })
                            new TomSelect(`#month-${id}`, {
                                create: false,
                                multiple: true,
                                sortField: {
                                    field: "numeric",
                                    direction: "asc"
                                },
                                maxOptions: 12,
                            });
                            $(`#amount-${id}`).val(fees.amount);
                            active_id++
                            id++;
                        })
                    }

                    $('#additional-buttons').append(`
                        <div class="form-group row mb-3">
                            <button class="btn btn-outline-secondary ml-4 mr-3" onclick="addInput()">Add Fees</button>
                            <button class="btn btn-primary" onclick="updateAdditionalFee()">Submit</button>
                        </div>
                    `)
                },
                error: (error) => {
                    console.error(error);
                }
            })
        }

        const updateAdditionalFee = () => {
            const nis = $('#nis').val();
            const additional_fee = [];
            var updated_by = "<?php echo $username ?>";

            invalid = [];
            for ($i = 0; $i <= id; $i++) {
                const type = $(`#type-${$i}`).val() ?? '';
                const amount = $(`#amount-${$i}`).val() ?? '';
                const years = $(`#year-${$i}`).val() ?? [];
                const months = $(`#month-${$i}`).val() ?? [];
                
                let all_filled = type !== "" && amount !== "" && years.length > 0 && months.length > 0;

                let some_empty = (type !== "" || amount !== "" || years.length > 0 || months.length > 0) &&
                     !(type !== "" && amount !== "" && years.length > 0 && months.length > 0);

                
                
                if (all_filled) {
                    additional_fee.push({
                        type,
                        amount,
                        years,
                        months
                    });
                }

                if (some_empty) {
                    invalid.push({
                        index: $i,
                        message: 'Harap isi semua input yang diperlukan'
                    })
                }
            }
            if (invalid.length > 0) {
                let message = '';
                invalid.forEach((item) => {
                    message += `<p>${item.message}</p>`;
                })
                $.toast({
                    heading: 'Invalid',
                    text: message,
                    showHideTransition: 'plain',
                    icon: 'warning'
                })
                return;
            }
            if (additional_fee.length == 0) {
                $.toast({
                    heading: 'Gagal',
                    text: `Biaya tambahan tidak ditemukan`,
                    showHideTransition: 'plain',
                    icon: 'error'
                })
                return;
            }
            console.log(additional_fee.length)
            var url = '/api/update-additional-fee.php'
            $.ajax({
                url: url,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    updated_by: updated_by,
                    nis: nis,
                    additional_fee: additional_fee,
                }),
                success: (response) => {
                    $.toast({
                        heading: 'Berahsil',
                        text: `Berhasil mengupdate biaya tambahan`,
                        showHideTransition: 'plain',
                        icon: 'success'
                    })
                },
                error: (error) => {
                    console.error(error.responseText);
                    $.toast({
                        heading: 'Gagal',
                        text: `Gagal mengupdate biaya tambahan : ${error}`,
                        showHideTransition: 'plain',
                        icon: 'error'
                    })
                }
            })
        }

        const addInput = () => {
            id++
            $('#additional-fee-form').append(`<?php include './tables/input-additional-fees.php'; ?>`);

            $(`#type-${id}`).append(`<option value='' selected>Pilih Jenis Biaya</option>`)
            type.forEach((fee) => {
                $(`#type-${id}`).append(
                    `<option value="${fee.id}">${fee.category_name}</option>`
                )
            })

            new TomSelect(`#type-${id}`, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                maxOptions: 50,
            });

            years.forEach((year) => {
                $(`#year-${id}`).append(
                    `<option value="${year.period}">${year.period}</option>`
                )
            })
            new TomSelect(`#year-${id}`, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                maxOptions: 50,
            });

            months.forEach((month) => {
                $(`#month-${id}`).append(
                    `<option value="${month.numeric}">${month.text}</option>`
                )
            })
            new TomSelect(`#month-${id}`, {
                create: false,
                multiple: true,
                sortField: {
                    field: "numeric",
                    direction: "asc"
                },
                maxOptions: 12,
            });

            active_id++
        }

        const deleteInput = (id) => {
            if (active_id > 1) {
                $(`#additional-fee-form #input-${id}`).remove();
                active_id--;
            }
        }

        $(document).ready(() => {
            getYears()
            feeTypes()
            filterUsers()
        })
        const refreshData = () => {
            getYears()
            feeTypes()
            filterUsers()
        }
    </script>
</body>
