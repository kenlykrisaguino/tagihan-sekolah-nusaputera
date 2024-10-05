<div class="w-100 d-flex flex-wrap" id="input-${id}">
    <div class="col-11 d-flex flex-wrap">
        <div class="form-group col-6">
            <label for="type[${id}]">Tipe Biaya</label>
            <select name="type[${id}]" id="type-${id}">
        
            </select>
        </div>
        <div class="form-group col-6">
            <label for="amount[${id}]">Biaya Tambahan</label>
            <input type="number" class="form-control" id="amount-${id}" name="amount[${id}]" required>
        </div>
        <div class="form-group col-6">
            <label for="year[${id}]">Tahun Ajaran</label>
            <select name="year[${id}]" id="year-${id}" multiple >
        
            </select>
        </div>
        <div class="form-group col-6">
            <label for="month[${id}]">Bulan</label>
            <select name="month[${id}]" id="month-${id}" multiple >
        
            </select>
        </div>
    </div>
    <div class="action-btn d-flex col-1 text-danger" style="align-items: center; cursor: pointer;" onclick="deleteInput('${id}')">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 7l16 0" />
            <path d="M10 11l0 6" />
            <path d="M14 11l0 6" />
            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
        </svg>
    </div>
</div>
