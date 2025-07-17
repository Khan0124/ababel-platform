<?php
include 'config.php';
$rate_result = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
$exchange_rate = $rate_result ? $rate_result->fetch_assoc()['exchange_rate'] : 1;
$type = $_GET['type'] ?? '';
$clients = $conn->query("SELECT id, name, code FROM clients ORDER BY name");

if (!in_array($type, ['موانئ', 'أرضيات', 'سجل', 'تختيم'])) exit;
?>
<form method="POST" action="save_daily_expense.php">
  <input type="hidden" name="type" value="<?= $type ?>">
  <input type="hidden" name="exchange_rate" value="<?= $exchange_rate ?>">

  <div class="row mb-3">
    <div class="col-md-4">
      <label>اسم العميل:</label>
      <select name="client_id" id="clientSelect" class="form-select" required>
        <option value="">اختر</option>
        <?php while($c = $clients->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>" data-code="<?= $c['code'] ?>"><?= $c['name'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label>رقم العميل:</label>
      <input type="text" name="client_code" id="clientCode" class="form-control" readonly>
    </div>
    <div class="col-md-4">
      <label>رقم الحاوية:</label>
      <select name="container_id" id="containerSelect" class="form-select" required>
        <option value="">اختر</option>
      </select>
    </div>
  </div>

  <hr>

  <table class="table table-bordered" id="itemsTable">
    <thead class="table-dark">
      <tr><th>البيان</th><th>المبلغ بالجنيه</th><th>بالدولار</th><th>خيارات</th></tr>
    </thead>
    <tbody></tbody>
  </table>

  <div class="row mb-3">
    <div class="col-md-4">
      <select id="desc" class="form-select">
        <option value="">اختر البيان</option>
        <option>رسوم <?= $type ?></option>
        <option>ضيافة</option>
        <option>أخرى</option>
      </select>
    </div>
    <div class="col-md-4">
      <input type="number" id="amount" placeholder="المبلغ بالجنيه" class="form-control">
    </div>
    <div class="col-md-4">
      <button type="button" onclick="addItem()" class="btn btn-dark w-100">➕ إضافة بند</button>
    </div>
  </div>

  <input type="hidden" name="items_json" id="items_json">

  <div class="text-end">
    <button class="btn btn-success">💾 حفظ العملية</button>
  </div>
</form>

<script>
let items = [];
const rate = <?= $exchange_rate ?>;

document.getElementById('clientSelect').addEventListener('change', function () {
  const selected = this.options[this.selectedIndex];
  document.getElementById('clientCode').value = selected.dataset.code || '';
  const id = this.value;
  fetch('get_client_containers.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      let containerSelect = document.getElementById('containerSelect');
      containerSelect.innerHTML = '<option value="">اختر</option>';
      data.forEach(c => {
        let opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.container_number;
        containerSelect.appendChild(opt);
      });
    });
});

function addItem() {
  const desc = document.getElementById('desc').value;
  const amount = parseFloat(document.getElementById('amount').value);
  if (!desc || !amount || amount <= 0) return alert("الرجاء إدخال البيان والمبلغ");

  const usd = (amount / rate).toFixed(2);
  items.push({ desc, amount, usd });
  renderTable();
}

function renderTable() {
  let tbody = document.querySelector("#itemsTable tbody");
  tbody.innerHTML = '';
  items.forEach((item, i) => {
    tbody.innerHTML += `
      <tr>
        <td>${item.desc}</td>
        <td>${item.amount}</td>
        <td>${item.usd}</td>
        <td>
          <button type="button" onclick="editItem(${i})" class="btn btn-sm btn-warning">✏️</button>
          <button type="button" onclick="removeItem(${i})" class="btn btn-sm btn-danger">🗑️</button>
        </td>
      </tr>`;
  });
  document.getElementById('items_json').value = JSON.stringify(items);
}

function removeItem(i) {
  items.splice(i, 1);
  renderTable();
}

function editItem(i) {
  const item = items[i];
  document.getElementById('desc').value = item.desc;
  document.getElementById('amount').value = item.amount;
  removeItem(i);
}
</script>