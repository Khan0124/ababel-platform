<?php
include 'config.php';
include 'auth.php';
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>طلب سجل جديد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Cairo', sans-serif;
      padding: 20px;
    }
    .form-section {
      background: white;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h4 {
      margin-bottom: 20px;
      color: #711739;
    }
    label {
      font-weight: bold;
    }
    /* رسالة الخطأ */
    #loading_number_error {
      display: none;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="form-section">
    <h4>📝 بيانات مطالبة سجل</h4>
    <form method="POST" action="save_register_request.php" id="registerForm">
      <div class="row g-3">
        <div class="col-md-6">
          <label>اسم السجل:</label>
          <select name="register_id" id="register_id" class="form-select" required>
            <option value="">اختر</option>
          </select>
        </div>
        <div class="col-md-6">
          <label>رقم العميل:</label>
          <input type="text" name="client_code" id="client_code" class="form-control" required />
        </div>
        <div class="col-md-6">
          <label>اسم العميل:</label>
          <input type="text" name="client_name" id="client_name" class="form-control" readonly />
        </div>
        <div class="col-md-6">
          <label>رقم اللودنق:</label>
          <input type="text" name="loading_number" id="loading_number" class="form-control" required />
          <div id="loading_number_error" class="alert alert-danger"></div>
        </div>
        <!-- باقي الحقول -->
        <div class="col-md-4">
          <label>عدد الكراتين:</label>
          <input type="text" name="carton_count" id="carton_count" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>المحطة الجمركية:</label>
          <input type="text" name="custom_station" id="custom_station" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>نوع البضاعة:</label>
          <input type="text" name="category" id="category" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>رقم الحاوية:</label>
          <input type="text" name="container_number" id="container_number" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>المشتريات:</label>
          <input type="number" name="purchase_amount" class="form-control" step="0.01" />
        </div>
        <div class="col-md-4">
          <label>رقم الشهادة:</label>
          <input type="text" name="certificate_number" class="form-control" />
        </div>
        <div class="col-md-4">
          <label>مبلغ الجمارك:</label>
          <input type="number" name="customs_amount" class="form-control" step="0.01" />
        </div>
        <div class="col-md-4">
          <label>قيمة المطالبة:</label>
          <input type="number" name="claim_amount" id="claim_amount" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>مكان التفريغ:</label>
          <input type="text" name="unloading_place" id="unloading_place" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>الشركة الناقلة:</label>
          <input type="text" name="carrier" id="carrier" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>رقم البوليصة:</label>
          <input type="text" name="bill_number" id="bill_number" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>قيمة المستردات:</label>
          <input type="number" name="refund_value" class="form-control" step="0.01" />
        </div>
        <div class="col-md-4">
          <label>نوع المستردات:</label>
          <select name="refund_type" class="form-select">
            <option value="">اختر</option>
            <option value="جزء من حاوية">جزء من حاوية</option>
            <option value="حاوية كاملة">حاوية كاملة</option>
          </select>
        </div>
        <hr class="my-4" />
        <h5>🚚 بيانات المنفستو</h5>
        <div class="col-md-4">
          <label>رقم المنفستو:</label>
          <input type="text" name="manifesto_number" class="form-control" />
        </div>
        <div class="col-md-4">
          <label>اسم السائق:</label>
          <input type="text" name="driver_name" id="driver_name" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>رقم السائق:</label>
          <input type="text" name="driver_phone" id="driver_phone" class="form-control" readonly />
        </div>
        <div class="col-md-4">
          <label>اسم المرحل:</label>
          <input type="text" name="transporter_name" class="form-control" />
        </div>
        <div class="col-md-4">
          <label>النولون:</label>
          <input type="number" name="transport_fee" class="form-control" step="0.01" />
        </div>
        <div class="col-md-4">
          <label>العمولة:</label>
          <input type="number" name="commission" class="form-control" step="0.01" />
        </div>
        <div class="col-12 text-center mt-4">
          <button type="submit" class="btn btn-primary px-5">💾 حفظ الطلب</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // دالة تفعيل/تعطيل حقول النموذج عدا حقل اللودنق
  function toggleFormFields(disabled) {
    $('#registerForm input, #registerForm select').not('#loading_number').prop('disabled', disabled);
  }

  // تعطيل الحقول عند تحميل الصفحة (انتظر إدخال رقم لودنق صحيح)
  toggleFormFields(true);

  $('#loading_number').on('blur', function () {
    let loadingNumber = $(this).val().trim();
    let $errorDiv = $('#loading_number_error');

    if (loadingNumber.length === 0) {
      $errorDiv.hide();
      toggleFormFields(true);
      return;
    }

    $.get('fetch_loading_data.php', { loading_number: loadingNumber }, function (data) {
      console.log(data);
      if (data.status === 'success') {
        // ملء الحقول
        $('#client_code').val(data.client_code);
        $('#client_name').val(data.client_name);
        if (data.register_id && data.register_name) {
          $('#register_id').html(`<option value="${data.register_id}" selected>${data.register_name}</option>`);
        } else {
          $('#register_id').html(`<option value="">اختر</option>`);
        }
        $('#carton_count').val(data.carton_count);
        $('#custom_station').val(data.custom_station);
        $('#category').val(data.category);
        $('#container_number').val(data.container_number);
        $('#unloading_place').val(data.unloading_place);
        $('#carrier').val(data.carrier);
        $('#bill_number').val(data.bill_number);
        $('#driver_name').val(data.driver_name);
        $('#driver_phone').val(data.driver_phone);
        $('#claim_amount').val(data.claim_amount);

        // إخفاء رسالة الخطأ وتمكين الحقول
        $errorDiv.hide();
        toggleFormFields(false);

      } else if (data.status === 'exists') {
        $errorDiv.text('⚠️ رقم اللودنق مستخدم مسبقًا - يرجى مراجعة القائمة').show();
        toggleFormFields(true);
        clearFieldsExceptLoading();

      } else if (data.status === 'error') {
        $errorDiv.text('❌ لم يتم العثور على الحاوية بهذا الرقم').show();
        toggleFormFields(true);
        clearFieldsExceptLoading();
      }
    }, 'json')
    .fail(function() {
      $errorDiv.text('❌ خطأ في الاتصال بالخادم، حاول لاحقًا').show();
      toggleFormFields(true);
      clearFieldsExceptLoading();
    });
  });

  function clearFieldsExceptLoading() {
    $('#registerForm input, #registerForm select').not('#loading_number').val('');
    $('#register_id').html(`<option value="">اختر</option>`);
  }
</script>

</body>
</html>
