<tr>
    <td class="editable" data-id="${trx.nis}" data-column='virtual_account'>${trx.virtual_account}</td>
    <td class="editable" data-id="${trx.nis}" data-column='student_name'>${trx.student_name}</td>
    <td data-id="${trx.nis}" data-column='level'>${trx.level}</td>
    <td class="editable" data-id="${trx.nis}" data-column='parent_phone'>${trx.parent_phone}</td>
    
    <td>${formatToIDR(trx.tagihan)}</td>
    <td class="hl-green">${formatToIDR(trx.penerimaan)}</td>

    <td class="${statusColor(trx.statusJanuari)}" data-id="${trx.nis}" data-month="Januari"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Januari)}</td>
    <td class="${statusColor(trx.statusFebruari)}" data-id="${trx.nis}" data-month="Februari"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Februari)}</td>
    <td class="${statusColor(trx.statusMaret)}" data-id="${trx.nis}" data-month="Maret"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Maret)}</td>
    <td class="${statusColor(trx.statusApril)}" data-id="${trx.nis}" data-month="April"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.April)}</td>
    <td class="${statusColor(trx.statusMei)}" data-id="${trx.nis}" data-month="Mei"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Mei)}</td>
    <td class="${statusColor(trx.statusJuni)}" data-id="${trx.nis}" data-month="Juni"  data-column='trx_amount' data-payment=true>${formatToIDR(trx.Juni)}</td>
    
    <td class="hl-red">${formatToIDR(trx.tunggakan)}</td>
    
    <td class="editable" data-id="${trx.nis}" data-month="Januari"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateJanuari)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Februari"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateFebruari)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Maret"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateMaret)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="April"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateApril)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Mei"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateMei)}</td>
    <td class="editable" data-id="${trx.nis}" data-month="Juni"  data-column='late_bills' data-payment=true>${formatToIDR(trx.LateJuni)}</td>

</tr>