<tr>
<td class="editable" data-id="${trx.nis}" data-column='virtual_account'>${trx.virtual_account}</td>
    <td class="editable" data-id="${trx.nis}" data-column='student_name'>${trx.student_name}</td>
    <td data-id="${trx.nis}" data-column='level'>${trx.level}</td>
    <td class="editable" data-id="${trx.nis}" data-column='parent_phone'>${trx.parent_phone}</td>
    
    <td class="hl-green">${formatToIDR(trx.penerimaan)}</td>

    <td class="editable ${statusColor(trx.statusJuli)}" data-id="${trx.nis}" data-month="Juli"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Juli)}</td>
    <td class="editable ${statusColor(trx.statusAgustus)}" data-id="${trx.nis}" data-month="Agustus"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Agustus)}</td>
    <td class="editable ${statusColor(trx.statusSeptember)}" data-id="${trx.nis}" data-month="September"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.September)}</td>
    <td class="editable ${statusColor(trx.statusOktober)}" data-id="${trx.nis}" data-month="Oktober"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Oktober)}</td>
    <td class="editable ${statusColor(trx.statusNovember)}" data-id="${trx.nis}" data-month="November"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.November)}</td>
    <td class="editable ${statusColor(trx.statusDesember)}" data-id="${trx.nis}" data-month="Desember"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Desember)}</td>

    <td class="hl-red">${formatToIDR(trx.tunggakan)}</td>

    <td class="editable" data-id="${trx.nis}" data-month="Juli"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateJuli)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Agustus"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateAgustus)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="September"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateSeptember)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Oktober"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateOktober)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="November"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateNovember)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Desember"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateDesember)}</td>

</tr>