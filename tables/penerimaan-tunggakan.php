<table class="table table-bordered my-4 text-center">
    <thead>
        <tr class="">
            <th>Total Penerimaan</th>
            <th>Total Denda</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>${formatToIDR(total.penerimaan ?? 0)}</td>
            <td>${formatToIDR(total.tunggakan ?? 0)}</td>
        </tr>
    </tbody>
</table>