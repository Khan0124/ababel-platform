<?php
include 'config.php';
include 'auth.php';

$rate_result = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
$exchange_rate = $rate_result ? $rate_result->fetch_assoc()['exchange_rate'] : 1;

$clients = $conn->query("SELECT id, name, code FROM clients ORDER BY name ASC");
$registers = $conn->query("SELECT id, name FROM registers ORDER BY name ASC");
$date_today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>يوميات الصرف</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 20px; }
    .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 5px #ccc; }
    .form-label { font-weight: bold; }
  </style>
</head>
<body>
<div class="container">
  <div class="text-end mb-3">
    <a href="dashboard.php" class="btn btn-secondary">↩️ الرئيسية</a>
  </div>

  <div class="card">
    <h4 class="mb-4">إضافة يومية صرف</h4>
    <form id="expenseForm">
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">نوع اليومية</label>
          <select name="type" class="form-select" required>
            <option value="">اختر</option>
            <option>صرف سجل</option>
            <option>صرف موانئ</option>
            <option>صرف تختيم</option>
            <option>صرف أرضيات</option>
            <option>صرف منفستو</option> <!-- تمت إضافة هذا الخيار -->
          </select>
        </div>
        <div class="col-md-4">
  <label class="form-label">كود العميل</label>
  <input type="text" id="clientCode" name="client_code" class="form-control" required>
</div>
<div class="col-md-4">
  <label class="form-label">اسم العميل</label>
  <input type="text" id="clientName" class="form-control" readonly>
</div>

      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">رقم الحاوية</label>
          <select name="container_id" id="containerSelect" class="form-select" required>
            <option value="">اختر</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">تاريخ العملية</label>
          <input type="date" name="created_at" class="form-control" value="<?= $date_today ?>">
        </div>
      </div>

      <hr>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">البيان</label>
          <div id="descContainer">
            <input type="text" id="descInput" class="form-control" placeholder="مثال: معاينة، كشف...">
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">المبلغ (جنيه)</label>
          <input type="number" id="amountInput" class="form-control" step="0.01">
        </div>
        <div class="col-md-4 d-grid">
          <label class="form-label">&nbsp;</label>
          <button type="button" class="btn btn-dark" id="addItemBtn">➕ إضافة بند</button>
        </div>
      </div>

      <table class="table table-bordered text-center">
        <thead class="table-dark">
          <tr><th>البيان</th><th>المبلغ (جنيه)</th><th>المبلغ ($)</th><th>خيارات</th></tr>
        </thead>
        <tbody id="itemsTableBody"></tbody>
      </table>

      <input type="hidden" name="items_json" id="itemsJson">
      <div class="text-end">
        <button class="btn btn-success">💾 حفظ اليومية</button>
      </div>
    </form>
  </div>
</div>
<div style="text-align: center; margin-top: 30px;">
  <a href="daily_expense_list.php" class="btn btn-dark">📋 عرض قائمة اليوميات</a>
</div>

<script>
const exchangeRate = <?= $exchange_rate ?>;
let items = [];

const clientCodeGroup = document.getElementById('clientCode').closest('.col-md-4');
const clientNameGroup = document.getElementById('clientName').closest('.col-md-4');
const containerGroup = document.getElementById('containerSelect').closest('.col-md-6');

document.getElementById('clientCode').addEventListener('blur', function() {
  const code = this.value.trim();
  if (!code) return;

  fetch('get_client_by_code.php?code=' + code)
    .then(res => res.json())
    .then(data => {
      if (data && data.id) {
        document.getElementById('clientName').value = data.name;

        fetch('get_client_containers.php?id=' + data.id)
          .then(res => res.json())
          .then(containers => {
            const select = document.getElementById('containerSelect');
            select.innerHTML = '<option value="">اختر</option>';
            containers.forEach(c => {
              const opt = document.createElement('option');
              opt.value = c.id;
              opt.textContent = c.container_number;
              select.appendChild(opt);
            });
          });

        let hidden = document.querySelector('input[name="client_id"]');
        if (!hidden) {
          hidden = document.createElement('input');
          hidden.type = "hidden";
          hidden.name = "client_id";
          document.getElementById('expenseForm').appendChild(hidden);
        }
        hidden.value = data.id;

      } else {
        alert("لم يتم العثور على العميل");
        document.getElementById('clientName').value = '';
        document.getElementById('containerSelect').innerHTML = '<option value="">اختر</option>';
      }
    });
});

document.getElementById('addItemBtn').addEventListener('click', () => {
  const desc = document.getElementById('descInput').value.trim();
  const amount = parseFloat(document.getElementById('amountInput').value);
  if (!desc || isNaN(amount) || amount <= 0) return alert("أدخل البيان والمبلغ بشكل صحيح");

  const usd = +(amount / exchangeRate).toFixed(2);
  items.push({ desc, amount, usd });
  renderItems();
  document.getElementById('descInput').value = '';
  document.getElementById('amountInput').value = '';
});

function renderItems() {
  const body = document.getElementById('itemsTableBody');
  body.innerHTML = '';
  items.forEach((item, i) => {
    body.innerHTML += `<tr>
      <td>${item.desc}</td>
      <td>${item.amount}</td>
      <td>${item.usd}</td>
      <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${i})">🗑️</button></td>
    </tr>`;
  });
  document.getElementById('itemsJson').value = JSON.stringify(items);
}

function removeItem(index) {
  items.splice(index, 1);
  renderItems();
}

document.getElementById('expenseForm').addEventListener('submit', function(e) {
  e.preventDefault();
  if (items.length === 0) return alert("أضف على الأقل بندًا واحدًا");

  const formData = new FormData(this);
  fetch('save_cashbox.php', {
    method: 'POST',
    body: formData
  }).then(res => res.text()).then(data => {
    alert(data);
    items = [];
    renderItems();
    this.reset();
    document.getElementById('clientCode').value = '';
    document.getElementById('clientName').value = '';
    document.getElementById('containerSelect').innerHTML = '<option value="">اختر</option>';
  }).catch(() => alert("حدث خطأ في الحفظ"));
});

const typeSelect = document.querySelector('select[name="type"]');
const descContainer = document.getElementById('descContainer');

typeSelect.addEventListener('change', function () {
  const type = this.value;

  if (type === 'صرف موانئ') {
    descContainer.innerHTML = `
      <select id="descInput" class="form-select">
        <option value="">اختر البيان</option>
        <option value="مبلغ الإذن">مبلغ الإذن</option>
        <option value="موانئ">موانئ</option>
        <option value="كشف">كشف</option>
        <option value="حراسة">حراسة</option>
        <option value="فورمات">فورمات</option>
        <option value="أتعاب">أتعاب</option>
        <option value="طرناطة">طرناطة</option>
        <option value="ختم">ختم</option>
        <option value="تأمين">تأمين</option>
      </select>`;
    clientCodeGroup.style.display = 'block';
    clientNameGroup.style.display = 'block';
    containerGroup.style.display = 'block';

  } else if (type === 'صرف سجل') {
    descContainer.innerHTML = `
      <select id="descInput" class="form-select">
        <option value="">اختر السجل</option>
        <?php
        $registers->data_seek(0);
        while($r = $registers->fetch_assoc()):
        ?>
          <option value="<?= $r['name'] ?>"><?= $r['name'] ?></option>
        <?php endwhile; ?>
      </select>`;
    clientCodeGroup.style.display = 'none';
    clientNameGroup.style.display = 'none';
    containerGroup.style.display = 'none';

  } else if (type === 'صرف منفستو') {
    // إضافة حالة لصرف منفستو
    descContainer.innerHTML = `<input type="text" id="descInput" class="form-control" placeholder="مثال: معاينة، كشف...">`;
    clientCodeGroup.style.display = 'block';
    clientNameGroup.style.display = 'block';
    containerGroup.style.display = 'block';
  } else {
    descContainer.innerHTML = `<input type="text" id="descInput" class="form-control" placeholder="مثال: معاينة، كشف...">`;
    clientCodeGroup.style.display = 'block';
    clientNameGroup.style.display = 'block';
    containerGroup.style.display = 'block';
  }
});
</script>

</body>
</html>