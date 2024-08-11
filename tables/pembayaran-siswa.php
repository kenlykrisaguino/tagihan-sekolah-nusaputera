<tr>
    <td>${trx.month}</td>
    <td>${formatToIDR(trx.bills)}</td>
    <td>${formatToIDR(trx.late_bills)}</td>
    <td>${formatToIDR(trx.payment_amount)}</td>
    <td>${trx.trx_status.charAt(0).toUpperCase() + trx.trx_status.slice(1) ?? '-'}</td>
    <td>${trx.paid_at ?? '-'}</td>
</tr>