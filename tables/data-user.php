<tr>
    <?php if($_SESSION['role'] == 'ADMIN'): ?>
    <td data-id="${u.nis}" style="cursor: pointer;" onclick="deleteStudent(this)">
        <svg xmlns="http://www.w3.org/2000/svg" height="20" width="24" viewBox="0 0 640 512">
            <path fill="#991111" d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM472 200l144 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-144 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/>
        </svg>
    </td>
    <?php endif; ?>
    <td>${u.nis}</td>
    <td>${u.name}</td>
    <td>${u.level ?? '-'}</td>
    <td>${u.class ?? '-'}</td>
    <td>${u.major ?? '-'}</td>
    <td>${u.phone_number ?? '-'}</td>
    <td>${u.email_address ?? '-'}</td>
    <td>${u.parent_phone}</td>
    <td>${u.virtual_account}</td>
    <td>${u.latest_payment ?? '-'}</td>
    <td>${u.status}</td>
</tr>
