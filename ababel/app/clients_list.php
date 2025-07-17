<?php
include 'config.php';
include 'auth.php';
$res = $conn->query("SELECT * FROM clients ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>قائمة العملاء</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f9f9f9;
      padding: 20px;
    }

    h2 {
      color: #711739;
      text-align: center;
      margin-bottom: 20px;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }

    #clientSearch {
      padding: 10px;
      width: 300px;
      max-width: 100%;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .btn {
      background: #711739;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
    }

    .btn:hover {
      background: #5a1134;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
    }

    th, td {
      border: 1px solid #eee;
      padding: 10px;
      text-align: center;
    }

    th {
      background: #711739;
      color: white;
    }

    tbody tr:nth-child(even) {
      background-color: #f7f7f7;
    }

    a.view-link {
      color: #711739;
      font-weight: bold;
      text-decoration: none;
    }

    a.view-link:hover {
      text-decoration: underline;
    }

    @media(max-width: 600px) {
      .top-bar {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
      }
    }
  </style>
</head>
<body>

<h2>📋 قائمة العملاء</h2>

<div class="top-bar">
  <input type="text" id="clientSearch" placeholder="🔍 ابحث باسم العميل أو الكود..." onkeyup="filterClients()">
  <div>
    <a href="insert_client.php" class="btn">➕ إضافة عميل</a>
    <a href="dashboard.php" class="btn">🏠 العودة للرئيسية</a>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>الاسم</th>
      <th>الكود</th>
      <th>الهاتف</th>
      <th>الملف</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1; while($c = $res->fetch_assoc()): ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
      <td><?= htmlspecialchars($c['code'] ?? '') ?></td>
      <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
      <td><a href="profile.php?id=<?= $c['id'] ?>" class="view-link">👁️ عرض</a></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<script>
function filterClients() {
  const input = document.getElementById("clientSearch").value.toLowerCase();
  const rows = document.querySelectorAll("table tbody tr");
  rows.forEach(row => {
    const name = row.children[1].textContent.toLowerCase();
    const code = row.children[2].textContent.toLowerCase();
    row.style.display = (name.includes(input) || code.includes(input)) ? "" : "none";
  });
}
</script>

</body>
</html>