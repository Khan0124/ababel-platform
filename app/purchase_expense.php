<?php
include 'config.php';
include 'auth.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📦 مصروفات على المشتريات</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap RTL -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f8f9fa;
      padding: 30px;
    }

    .form-section {
      background: #ffffff;
      padding: 35px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
      max-width: 1100px;
      margin: auto;
    }

    h4 {
      color: #711739;
      font-weight: bold;
      margin-bottom: 30px;
    }

    label {
      font-weight: 600;
      color: #333;
    }

    .form-control:focus {
      border-color: #711739;
      box-shadow: 0 0 0 0.15rem rgba(113, 23, 57, 0.25);
    }

    .btn-primary {
      background-color: #711739;
      border-color: #711739;
    }

    .btn-primary:hover {
      background-color: #5e1230;
      border-color: #5e1230;
    }

    .alert-custom {
      background: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
      padding: 10px 15px;
      border-radius: 6px;
      font-size: 14px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="form-section">
    <h4 class="text-center">📦 مصروفات على المشتريات</h4>

    <form method="POST" action="save_purchase_expense.php" class="row g-4" id="purchaseForm">

      <div class="col-md-4">
  <label for="loading_number">🔢 رقم اللودنق:</label>
  <input type="text" id="loading_number" name="loading_number" class="form-control" required>
  <div id="loadingAlert" class="alert alert-warning mt-2 d-none" role="alert">
    ⚠️ هذا اللودنق تم تسجيله مسبقًا
  </div>
  <div id="loadingSuccess" class="alert alert-success mt-2 d-none" role="alert">
    ✅ هذا اللودنق متاح ويمكن استخدامه
  </div>
  <div id="loadingNotFound" class="alert alert-danger mt-2 d-none" role="alert">
  ⚠️ هذا اللودنق غير مربوط بأي حاوية
</div>

</div>


      <div class="col-md-4">
        <label for="client_code">📇 رقم العميل:</label>
        <input type="text" id="client_code" name="client_code" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="client_name">👤 اسم العميل:</label>
        <input type="text" id="client_name" name="client_name" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="container_number">🚛 رقم الحاوية:</label>
        <input type="text" id="container_number" name="container_number" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="customs_amount">💰 الجمارك:</label>
        <input type="number" step="0.01" id="customs_amount" name="customs_amount" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="customs_additional">➕ قيمة مضافة جمركية:</label>
        <input type="number" step="0.01" name="customs_additional" class="form-control">
      </div>

      <div class="col-md-4">
        <label for="manifesto_amount">📄 المنفستو:</label>
        <input type="number" step="0.01" id="manifesto_amount" name="manifesto_amount" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="manifesto_additional">➕ قيمة مضافة منفستو:</label>
        <input type="number" step="0.01" name="manifesto_additional" class="form-control">
      </div>

      <div class="col-md-4">
        <label for="customs_profit">🏦 أرباح أعمال جمركية:</label>
        <input type="number" step="0.01" name="customs_profit" class="form-control">
      </div>

      <div class="col-md-4">
        <label for="ports_amount">🚢 الموانئ:</label>
        <input type="number" step="0.01" id="ports_amount" name="ports_amount" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="ports_additional">➕ قيمة مضافة موانئ:</label>
        <input type="number" step="0.01" name="ports_additional" class="form-control">
      </div>

      <div class="col-md-4">
        <label for="permission_amount">📝 مبلغ الإذن:</label>
        <input type="number" step="0.01" id="permission_amount" name="permission_amount" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="permission_additional">➕ قيمة مضافة إذن:</label>
        <input type="number" step="0.01" name="permission_additional" class="form-control">
      </div>

      <div class="col-md-4">
        <label for="yard_amount">🏗️ الأرضيات:</label>
        <input type="number" step="0.01" id="yard_amount" name="yard_amount" class="form-control" readonly>
      </div>

      <div class="col-md-4">
        <label for="yard_additional">➕ قيمة مضافة أرضيات:</label>
        <input type="number" step="0.01" name="yard_additional" class="form-control">
      </div>

      <div class="col-12 text-center mt-4">
        <button type="submit" class="btn btn-primary px-5 py-2">💾 حفظ</button>
      </div>

    </form>
  </div>
</div>

<!-- Ajax Script -->
<script>
  function toggleFormFields(disabled) {
    $('#purchaseForm input').not('#loading_number').prop('disabled', disabled);
  }

  let debounceTimer;

  $('#loading_number').on('input', function () {
    clearTimeout(debounceTimer);

    const loading = $(this).val().trim();

    if (loading.length < 1) {
      $('#loadingAlert, #loadingSuccess, #loadingNotFound').addClass('d-none');
      $('#loading_number').removeClass('is-invalid is-valid');
      toggleFormFields(true);
      return;
    }

    debounceTimer = setTimeout(function () {
      $.get('check_purchase_exists.php', { loading_number: loading }, function (check) {
        if (check.exists) {
          $('#loadingAlert').removeClass('d-none');
          $('#loadingSuccess, #loadingNotFound').addClass('d-none');
          $('#loading_number').addClass('is-invalid').removeClass('is-valid');
          toggleFormFields(true);

          $('#client_code, #client_name, #container_number, #customs_amount, #manifesto_amount, #ports_amount, #permission_amount, #yard_amount').val('');
        } else {
          // التحقق من الحاوية
          $.get('fetch_purchase_expense_data.php', { loading_number: loading }, function (data) {
            if (data.status === 'success') {
              $('#loadingAlert, #loadingNotFound').addClass('d-none');
              $('#loadingSuccess').removeClass('d-none');
              $('#loading_number').addClass('is-valid').removeClass('is-invalid');

              setTimeout(() => {
                $('#loadingSuccess').addClass('d-none');
              }, 2000);

              toggleFormFields(false);

              $('#client_code').val(data.client_code);
              $('#client_name').val(data.client_name);
              $('#container_number').val(data.container_number);
              $('#customs_amount').val(data.customs_amount);
              $('#manifesto_amount').val(data.manifesto_amount);
              $('#ports_amount').val(data.ports_amount);
              $('#permission_amount').val(data.permission_amount);
              $('#yard_amount').val(data.yard_amount);
            } else if (data.status === 'not_found') {
              $('#loadingNotFound').removeClass('d-none');
              $('#loadingAlert, #loadingSuccess').addClass('d-none');
              $('#loading_number').addClass('is-invalid').removeClass('is-valid');
              toggleFormFields(true);

              $('#client_code, #client_name, #container_number, #customs_amount, #manifesto_amount, #ports_amount, #permission_amount, #yard_amount').val('');
            }
          }, 'json');
        }
      }, 'json');
    }, 700);
  });
</script>


</body>
</html>
