<?php
include 'auth.php';
include 'config.php';

// البيانات التجريبية
$clients = $conn->query("SELECT * FROM clients LIMIT 10");
$containers = $conn->query("SELECT * FROM containers LIMIT 10");
$registers = $conn->query("SELECT r.name, 
  SUM(CASE WHEN t.type = 'مطالبة' THEN t.amount ELSE 0 END) AS debit,
  SUM(CASE WHEN t.type = 'قبض' THEN t.amount ELSE 0 END) AS credit
  FROM registers r LEFT JOIN transactions t ON r.id = t.register_id GROUP BY r.id LIMIT 10");
$expenses = $conn->query("SELECT * FROM cashbox WHERE type='صرف' AND source='مصروفات مكتب' ORDER BY created_at DESC LIMIT 10");
$transactions = $conn->query("SELECT t.*, c.name as client_name FROM transactions t LEFT JOIN clients c ON t.client_id = c.id ORDER BY t.created_at DESC LIMIT 10");

// معالجة طلبات التصدير
if (isset($_GET['export'])) {
    $report = $_GET['report'];
    $format = $_GET['format'];
    
    if ($format == 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $report . '_report.csv');
        
        $output = fopen('php://output', 'w');
        
        switch ($report) {
            case 'clients':
                fputcsv($output, array('#', 'الاسم', 'الكود', 'الرصيد', 'رصيد التأمين'));
                $i = 1;
                while ($c = $clients->fetch_assoc()) {
                    fputcsv($output, array(
                        $i++,
                        $c['name'],
                        $c['code'],
                        number_format($c['balance'], 2),
                        number_format($c['insurance_balance'], 2)
                    ));
                }
                break;
                
            // حالات التصدير الأخرى...
        }
        
        fclose($output);
        exit;
    } elseif ($format == 'pdf') {
        require_once('tcpdf/tcpdf.php');
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetTitle($report . ' Report');
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 12);
        
        $html = '<h1>تقرير ' . $report . '</h1>';
        
        switch ($report) {
            case 'clients':
                $html .= '<table border="1" cellpadding="5">
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الكود</th>
                        <th>الرصيد</th>
                        <th>رصيد التأمين</th>
                    </tr>';
                $i = 1;
                while ($c = $clients->fetch_assoc()) {
                    $html .= '<tr>
                        <td>' . $i++ . '</td>
                        <td>' . $c['name'] . '</td>
                        <td>' . $c['code'] . '</td>
                        <td>' . number_format($c['balance'], 2) . '</td>
                        <td>' . number_format($c['insurance_balance'], 2) . '</td>
                    </tr>';
                }
                $html .= '</table>';
                break;
                
            // حالات التصدير الأخرى...
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($report . '_report.pdf', 'D');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📊 تقارير النظام - شركة أبابيل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #711739;
      --secondary-color: #8d1e44;
      --light-color: #f8f9fa;
      --dark-color: #343a40;
    }
    
    body {
      font-family: 'Cairo', sans-serif;
      background: #f4f4f4;
      padding-top: 20px;
      padding-bottom: 50px;
    }
    
    .top-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 10px;
      margin-bottom: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .top-header img {
      height: 60px;
      margin-left: 15px;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }
    
    .company-name {
      font-size: 20px;
      font-weight: bold;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .report-section {
      background: white;
      padding: 25px;
      border-radius: 12px;
      margin-bottom: 35px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      border-top: 4px solid var(--primary-color);
      transition: all 0.3s ease;
    }
    
    .report-section:hover {
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
      transform: translateY(-5px);
    }
    
    .report-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }
    
    .report-title {
      font-size: 22px;
      font-weight: bold;
      color: var(--primary-color);
      display: flex;
      align-items: center;
    }
    
    .report-title i {
      margin-left: 12px;
      font-size: 26px;
    }
    
    .report-actions {
      display: flex;
      gap: 12px;
    }
    
    .btn-action {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.2s;
    }
    
    .btn-print {
      background: #2c3e50;
      color: white;
      border: none;
    }
    
    .btn-excel {
      background: #1d6f42;
      color: white;
      border: none;
    }
    
    .btn-pdf {
      background: #c7162b;
      color: white;
      border: none;
    }
    
    .btn-action:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .filters-container {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      border: 1px solid #e9ecef;
    }
    
    .filter-row {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    
    .filter-group {
      flex: 1;
      min-width: 250px;
    }
    
    .table-container {
      overflow-x: auto;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      min-width: 800px;
    }
    
    .table th {
      background: var(--primary-color);
      color: white;
      font-weight: 600;
      padding: 16px 12px;
      text-align: center;
      vertical-align: middle;
      position: sticky;
      top: 0;
    }
    
    .table td {
      padding: 14px 12px;
      text-align: center;
      vertical-align: middle;
      border-bottom: 1px solid #e9ecef;
    }
    
    .table tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    
    .table tr:hover {
      background-color: #f1f1f1;
    }
    
    .results-count {
      background: #e9ecef;
      padding: 12px 20px;
      border-radius: 6px;
      font-weight: 600;
      margin-top: 20px;
      display: inline-block;
    }
    
    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 30px;
    }
    
    .pagination .page-item .page-link {
      color: var(--primary-color);
      border: 1px solid #dee2e6;
      margin: 0 4px;
      border-radius: 6px;
      min-width: 40px;
      text-align: center;
      transition: all 0.2s;
    }
    
    .pagination .page-item.active .page-link {
      background: var(--primary-color);
      border-color: var(--primary-color);
      color: white;
    }
    
    .pagination .page-item .page-link:hover {
      background: #f8f9fa;
      border-color: #dee2e6;
    }
    
    .footer {
      text-align: center;
      padding: 25px;
      margin-top: 40px;
      background: var(--dark-color);
      color: white;
      border-radius: 10px;
    }
    
    .print-only {
      display: none;
    }
    
    @media print {
      body * {
        visibility: hidden;
      }
      .print-only, .print-only * {
        visibility: visible;
      }
      .print-only {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
  <script>
    function toggleReport(sectionId) {
      const allSections = document.querySelectorAll('.report-section');
      allSections.forEach(s => s.classList.add('hidden'));
      if (sectionId !== 'all') {
        document.getElementById(sectionId).classList.remove('hidden'));
      } else {
        allSections.forEach(s => s.classList.remove('hidden'));
      }
    }
    
    function printReport(reportId) {
      const printContent = document.getElementById(reportId).innerHTML;
      const originalContent = document.body.innerHTML;
      
      document.body.innerHTML = `
        <div class="print-only">
          <div class="text-center mb-4">
            <h2>شركة أبابيل للتنمية والاستثمار المحدودة</h2>
            <h3>${document.getElementById(reportId).querySelector('.report-title').innerText}</h3>
            <p>تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG')}</p>
          </div>
          ${printContent}
        </div>
      `;
      
      window.print();
      document.body.innerHTML = originalContent;
    }
    
    function exportReport(report, format) {
      window.location.href = `reports.php?export=1&report=${report}&format=${format}`;
    }
  </script>
</head>
<body>
  <div class="top-header">
    <div class="d-flex align-items-center">
      <img src="logo.png" alt="شعار">
      <div class="company-name">
        شركة أبابيل للتنمية والاستثمار المحدودة<br>
        Ababeel Development & Investment Co. Ltd
      </div>
    </div>
    <div>
      <a href="dashboard.php" class="btn btn-light btn-lg">
        <i class="fas fa-home"></i> الرجوع للرئيسية
      </a>
    </div>
  </div>

  <div class="container">
    <div class="report-toggle text-center mb-5">
      <label class="form-label fw-bold fs-5">عرض تقرير:</label>
      <select class="form-select w-auto d-inline-block fs-5" onchange="toggleReport(this.value)" style="min-width: 250px;">
        <option value="all">📊 كل التقارير</option>
        <option value="report-clients">📋 العملاء</option>
        <option value="report-containers">📦 الحاويات</option>
        <option value="report-registers">📁 السجلات</option>
        <option value="report-expenses">💸 المصروفات</option>
        <option value="report-transactions">📑 المعاملات</option>
      </select>
    </div>

    <!-- تقرير العملاء -->
    <div id="report-clients" class="report-section">
      <div class="report-header">
        <h3 class="report-title"><i class="fas fa-users"></i> تقرير العملاء</h3>
        <div class="report-actions">
          <button class="btn btn-action btn-print" onclick="printReport('report-clients')">
            <i class="fas fa-print"></i> طباعة
          </button>
          <button class="btn btn-action btn-excel" onclick="exportReport('clients', 'csv')">
            <i class="fas fa-file-excel"></i> Excel
          </button>
          <button class="btn btn-action btn-pdf" onclick="exportReport('clients', 'pdf')">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
        </div>
      </div>
      
      <div class="filters-container">
        <h5><i class="fas fa-filter"></i> تصفية النتائج</h5>
        <div class="filter-row mt-3">
          <div class="filter-group">
            <label class="form-label">بحث بالاسم أو الكود:</label>
            <input type="text" class="form-control" placeholder="ابحث هنا...">
          </div>
          <div class="filter-group">
            <label class="form-label">ترتيب حسب:</label>
            <select class="form-select">
              <option>الاسم (أ-ي)</option>
              <option>الاسم (ي-أ)</option>
              <option>الرصيد (من أعلى)</option>
              <option>الرصيد (من أقل)</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="table-container">
        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>الاسم</th>
              <th>الكود</th>
              <th>الرصيد</th>
              <th>رصيد التأمين</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; while($c = $clients->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= $c['name'] ?></td>
                <td><?= $c['code'] ?></td>
                <td><?= number_format($c['balance'], 2) ?></td>
                <td><?= number_format($c['insurance_balance'], 2) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="results-count">
          <i class="fas fa-chart-bar"></i> عدد النتائج: 10
        </div>
        
        <div class="pagination-container">
          <ul class="pagination">
            <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">التالي</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- تقرير الحاويات -->
    <div id="report-containers" class="report-section">
      <div class="report-header">
        <h3 class="report-title"><i class="fas fa-box"></i> تقرير الحاويات</h3>
        <div class="report-actions">
          <button class="btn btn-action btn-print" onclick="printReport('report-containers')">
            <i class="fas fa-print"></i> طباعة
          </button>
          <button class="btn btn-action btn-excel" onclick="exportReport('containers', 'csv')">
            <i class="fas fa-file-excel"></i> Excel
          </button>
          <button class="btn btn-action btn-pdf" onclick="exportReport('containers', 'pdf')">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
        </div>
      </div>
      
      <div class="filters-container">
        <h5><i class="fas fa-filter"></i> تصفية النتائج</h5>
        <div class="filter-row mt-3">
          <div class="filter-group">
            <label class="form-label">من تاريخ:</label>
            <input type="date" class="form-control">
          </div>
          <div class="filter-group">
            <label class="form-label">إلى تاريخ:</label>
            <input type="date" class="form-control">
          </div>
          <div class="filter-group">
            <label class="form-label">حالة الحاوية:</label>
            <select class="form-select">
              <option>جميع الحالات</option>
              <option>لم تفرغ</option>
              <option>فرغت</option>
              <option>في الترحيل</option>
              <option>في الميناء</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="table-container">
        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>رقم العميل</th>
              <th>رقم الحاوية</th>
              <th>الحالة</th>
              <th>تاريخ الدخول</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; while($c = $containers->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= $c['code'] ?></td>
                <td><?= $c['container_number'] ?></td>
                <td><?= $c['status'] ?? '-' ?></td>
                <td><?= $c['entry_date'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="results-count">
          <i class="fas fa-chart-bar"></i> عدد النتائج: 10
        </div>
        
        <div class="pagination-container">
          <ul class="pagination">
            <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">التالي</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- تقرير السجلات -->
    <div id="report-registers" class="report-section">
      <div class="report-header">
        <h3 class="report-title"><i class="fas fa-folder"></i> تقرير السجلات</h3>
        <div class="report-actions">
          <button class="btn btn-action btn-print" onclick="printReport('report-registers')">
            <i class="fas fa-print"></i> طباعة
          </button>
          <button class="btn btn-action btn-excel" onclick="exportReport('registers', 'csv')">
            <i class="fas fa-file-excel"></i> Excel
          </button>
          <button class="btn btn-action btn-pdf" onclick="exportReport('registers', 'pdf')">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
        </div>
      </div>
      
      <div class="table-container">
        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>اسم السجل</th>
              <th>مطالبات</th>
              <th>مقبوضات</th>
              <th>الرصيد</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; while($r = $registers->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= $r['name'] ?></td>
                <td><?= number_format($r['debit'], 2) ?></td>
                <td><?= number_format($r['credit'], 2) ?></td>
                <td><?= number_format($r['debit'] - $r['credit'], 2) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="results-count">
          <i class="fas fa-chart-bar"></i> عدد النتائج: 10
        </div>
        
        <div class="pagination-container">
          <ul class="pagination">
            <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">التالي</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- تقرير المصروفات -->
    <div id="report-expenses" class="report-section">
      <div class="report-header">
        <h3 class="report-title"><i class="fas fa-money-bill-wave"></i> تقرير المصروفات الإدارية</h3>
        <div class="report-actions">
          <button class="btn btn-action btn-print" onclick="printReport('report-expenses')">
            <i class="fas fa-print"></i> طباعة
          </button>
          <button class="btn btn-action btn-excel" onclick="exportReport('expenses', 'csv')">
            <i class="fas fa-file-excel"></i> Excel
          </button>
          <button class="btn btn-action btn-pdf" onclick="exportReport('expenses', 'pdf')">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
        </div>
      </div>
      
      <div class="filters-container">
        <h5><i class="fas fa-filter"></i> تصفية النتائج</h5>
        <div class="filter-row mt-3">
          <div class="filter-group">
            <label class="form-label">من تاريخ:</label>
            <input type="date" class="form-control">
          </div>
          <div class="filter-group">
            <label class="form-label">إلى تاريخ:</label>
            <input type="date" class="form-control">
          </div>
          <div class="filter-group">
            <label class="form-label">نوع المصروف:</label>
            <select class="form-select">
              <option>جميع الأنواع</option>
              <option>مصروفات مكتب</option>
              <option>مصروفات نقل</option>
              <option>مصروفات جمركية</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="table-container">
        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>النوع</th>
              <th>المبلغ</th>
              <th>ملاحظات</th>
            </tr>
          </thead>
          <tbody>
            <?php while($e = $expenses->fetch_assoc()): ?>
              <tr>
                <td><?= $e['created_at'] ?></td>
                <td><?= $e['description'] ?></td>
                <td><?= number_format($e['amount'], 2) ?></td>
                <td><?= $e['notes'] ?? '-' ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="results-count">
          <i class="fas fa-chart-bar"></i> عدد النتائج: 10
        </div>
        
        <div class="pagination-container">
          <ul class="pagination">
            <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">التالي</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- تقرير المعاملات -->
    <div id="report-transactions" class="report-section">
      <div class="report-header">
        <h3 class="report-title"><i class="fas fa-exchange-alt"></i> تقرير المعاملات المالية</h3>
        <div class="report-actions">
          <button class="btn btn-action btn-print" onclick="printReport('report-transactions')">
            <i class="fas fa-print"></i> طباعة
          </button>
          <button class="btn btn-action btn-excel" onclick="exportReport('transactions', 'csv')">
            <i class="fas fa-file-excel"></i> Excel
          </button>
          <button class="btn btn-action btn-pdf" onclick="exportReport('transactions', 'pdf')">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
        </div>
      </div>
      
      <div class="filters-container">
        <h5><i class="fas fa-filter"></i> تصفية النتائج</h5>
        <div class="filter-row mt-3">
          <div class="filter-group">
            <label class="form-label">من تاريخ:</label>
            <input type="date" class="form-control">
          </div>
          <div class="filter-group">
            <label class="form-label">إلى تاريخ:</label>
            <input type="date" class="form-control">
          </div>
          <div class="filter-group">
            <label class="form-label">نوع المعاملة:</label>
            <select class="form-select">
              <option>جميع الأنواع</option>
              <option>قبض</option>
              <option>مطالبة</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="table-container">
        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>النوع</th>
              <th>البيان</th>
              <th>المبلغ</th>
              <th>العميل</th>
            </tr>
          </thead>
          <tbody>
            <?php while($t = $transactions->fetch_assoc()): ?>
              <tr>
                <td><?= $t['created_at'] ?></td>
                <td><?= $t['type'] ?></td>
                <td><?= $t['description'] ?></td>
                <td><?= number_format($t['amount'], 2) ?></td>
                <td><?= $t['client_name'] ?? '-' ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="results-count">
          <i class="fas fa-chart-bar"></i> عدد النتائج: 10
        </div>
        
        <div class="pagination-container">
          <ul class="pagination">
            <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">التالي</a></li>
          </ul>
        </div>
      </div>
    </div>
    
    <div class="footer no-print">
      <p>جميع الحقوق محفوظة &copy; شركة أبابيل للتنمية والاستثمار المحدودة <?= date('Y') ?></p>
      <p>تم التطوير بواسطة قسم تكنولوجيا المعلومات</p>
    </div>
  </div>
</body>
</html>