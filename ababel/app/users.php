
<?php
include 'auth.php';
include 'config.php';
include 'authorize.php';
allow_roles(['مدير عام']);

$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الموظفين</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f8f8f8; padding: 30px; }
    h2 { text-align: center; }
    .top-link {
      display: inline-block;
      margin-bottom: 15px;
      padding: 10px 15px;
      background: #711739;
      color: white;
      text-decoration: none;
      border-radius: 6px;
    }
    .filters {
      margin-bottom: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }
    .filters select, .filters input {
      padding: 8px;
      font-size: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      margin-top: 10px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th {
      background: #711739;
      color: white;
    }
    a.action {
      margin: 0 5px;
      text-decoration: none;
      font-weight: bold;
      color: #711739;
    }
    a.action:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<h2>👥 إدارة الموظفين</h2>

<a class="top-link" href="add_user.php">➕ إضافة موظف جديد</a>
<a class="top-link" href="dashboard.php">⬅️ العودة للرئيسية</a>

<div class="filters">
  <input type="text" id="searchInput" placeholder="بحث بالاسم أو اسم المستخدم..." onkeyup="filterTable()">
  <select id="filterRole" onchange="filterTable()">
    <option value="">كل الأدوار</option>
    <option value="مدير عام">مدير عام</option>
    <option value="محاسب">محاسب</option>
    <option value="مدير مكتب">مدير مكتب</option>
  </select>
  <select id="filterOffice" onchange="filterTable()">
    <option value="">كل المكاتب</option>
    <option value="بورتسودان">بورتسودان</option>
    <option value="عطبرة">عطبرة</option>
    <option value="الصين">الصين</option>
  </select>
  <select id="filterStatus" onchange="filterTable()">
    <option value="">كل الحالات</option>
    <option value="مفعل">مفعل</option>
    <option value="غير مفعل">غير مفعل</option>
  </select>
</div>

<table id="usersTable">
  <thead>
    <tr>
      <th>الاسم الكامل</th>
      <th>اسم المستخدم</th>
      <th>الدور</th>
      <th>المكتب</th>
      <th>البريد الإلكتروني</th>
      <th>الحالة</th>
      <th>الإجراءات</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?php echo $row['full_name']; ?></td>
        <td><?php echo $row['username']; ?></td>
        <td><?php echo $row['role']; ?></td>
        <td><?php echo $row['office']; ?></td>
        <td><?php echo $row['email'] ?: '-'; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
          <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="action">تعديل</a>
          <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="action" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<script>
function filterTable() {
  const search = document.getElementById("searchInput").value.toLowerCase();
  const role = document.getElementById("filterRole").value;
  const office = document.getElementById("filterOffice").value;
  const status = document.getElementById("filterStatus").value;
  const rows = document.querySelectorAll("#usersTable tbody tr");

  rows.forEach(row => {
    const name = row.cells[0].textContent.toLowerCase();
    const username = row.cells[1].textContent.toLowerCase();
    const rowRole = row.cells[2].textContent;
    const rowOffice = row.cells[3].textContent;
    const rowStatus = row.cells[5].textContent;

    const matchSearch = name.includes(search) || username.includes(search);
    const matchRole = !role || rowRole === role;
    const matchOffice = !office || rowOffice === office;
    const matchStatus = !status || rowStatus === status;

    row.style.display = (matchSearch && matchRole && matchOffice && matchStatus) ? "" : "none";
  });
}
</script>

</body>
</html>
