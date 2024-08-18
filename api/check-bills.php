<?php

include_once '../config/app.php';
header('Content-Type: application/json');

$level_query = "SELECT name, late_bills FROM levels";
$late_bill_list = read($level_query);

foreach ($late_bill_list as $late_bill){
    $late_bills[$late_bill['name']] = $late_bill['late_bills'];
}

$sql_create_temp_table = "
    CREATE TEMPORARY TABLE temp_bills AS
    SELECT b.id, next_b.id AS next_b_id, b.level, b.payment_due
    FROM bills b
    LEFT JOIN bills next_b ON next_b.nis = b.nis 
        AND next_b.payment_due = (
            SELECT MIN(nb.payment_due)
            FROM bills nb
            WHERE nb.nis = b.nis 
            AND nb.payment_due > b.payment_due
            AND nb.trx_status != 'waiting'
        )
    WHERE 
        b.trx_status = 'waiting' 
        AND b.payment_due < DATE_SUB(NOW(), INTERVAL 24 HOUR)
";

crud($sql_create_temp_table);

$sql_update = "
    UPDATE bills b
    LEFT JOIN temp_bills t ON b.id = t.id
    LEFT JOIN bills next_b ON next_b.id = t.next_b_id
    LEFT JOIN levels l ON l.name = t.level
    SET 
        b.trx_status = 'not paid', 
        b.late_bills = COALESCE(l.late_bills, 0),
        b.payment_due = t.payment_due,
        next_b.trx_status = 'waiting'
    WHERE 
        b.trx_status = 'waiting' 
        AND b.payment_due < DATE_SUB(NOW(), INTERVAL 24 HOUR)
";

$result = crud($sql_update);

$sql_drop_temp_table = "DROP TEMPORARY TABLE temp_bills";
crud($sql_drop_temp_table);

echo json_encode([
    'status' => true,
    'message' => 'Tagihan berhasil dicek', 
    'data' => $late_bills
]);

?>
