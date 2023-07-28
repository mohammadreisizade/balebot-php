<?php
// اضافه کردن کتابخانه PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// اتصال به پایگاه داده
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bale";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset('utf8');

// بررسی وجود خطا در اتصال
if ($conn->connect_error) {
    die("اتصال به پایگاه داده ناموفق بود: " . $conn->connect_error);
}

// استفاده از کوئری برای دریافت اطلاعات از جدول "persons"
$sql = "SELECT * FROM Requests WHERE is_closed = 1 OR is_closed = 0 ORDER BY date_registered DESC, time_registered DESC LIMIT 1000";
$result = $conn->query($sql);

// ایجاد یک شیء Spreadsheet جدید
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


// اضافه کردن عنوان‌ها به اکسل
$sheet->setCellValue('A1', 'نام');
$sheet->setCellValue('B1', 'عنوان');
$sheet->setCellValue('C1', 'توضیحات');
$sheet->setCellValue('D1', 'نوع قرارداد');
$sheet->setCellValue('E1', 'وضعیت');
$sheet->setCellValue('F1', 'مبلغ');
$sheet->setCellValue('G1', 'پروژه');
$sheet->setCellValue('H1', 'تاریخ ثبت درخواست');
$sheet->setCellValue('I1', 'ساعت ثبت درخواست');
$sheet->setCellValue('J1', 'تاریخ تغییر وضعیت به کارتابل');
$sheet->setCellValue('K1', 'ساعت تغییر وضعیت به کارتابل');
$sheet->setCellValue('L1', 'تاریخ تغییر وضعیت به پرداخت شده');
$sheet->setCellValue('M1', 'ساعت تغییر وضعیت به پرداخت شده');
$sheet->setCellValue('N1', 'دلیل رد درخواست');
$sheet->setCellValue('O1', 'تاریخ رد درخواست');
$sheet->setCellValue('P1', 'ساعت رد درخواست');


if ($result->num_rows != 0) {
    $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
    while ($row = $result->fetch_assoc()) {
        // $content = array("chat_id" => $chat_id, "text" => $row['username']);
        // $bot->sendText($content);
        if ($row['contract_type']=="contract"){
            $contract_type = "قراردادی";
        }else{
            $contract_type = "فاکتوری";
        }

        if ($row['req_status']=="paid"){
            $req_status = "پرداخت شده";
        }elseif ($row['req_status']=="cartable"){
            $req_status = "کارتابل";
        }elseif ($row['req_status']=="rejectacc"){
            $req_status = "رد شده توسط امور مالی";
        }elseif ($row['req_status']=="waitacc"){
            $req_status = "در انتظار بررسی";
        }else{
            $req_status = "---";
        }
        $name = $row['name'];
        $title = $row['title'];
        $description = $row['description'];
        $project = $row['project'];


        $sheet->setCellValue('A' . $rowNumber, $name);
        $sheet->setCellValue('B' . $rowNumber, $title);
        $sheet->setCellValue('C' . $rowNumber, $description);
        $sheet->setCellValue('D' . $rowNumber, $contract_type);
        $sheet->setCellValue('E' . $rowNumber, $req_status);
        $sheet->setCellValue('F' . $rowNumber, $row['price']);
        $sheet->setCellValue('G' . $rowNumber, $project);
        $sheet->setCellValue('H' . $rowNumber, $row['date_registered']);
        $sheet->setCellValue('I' . $rowNumber, $row['time_registered']);
        $sheet->setCellValue('J' . $rowNumber, $row['date_cartable']);
        $sheet->setCellValue('K' . $rowNumber, $row['time_cartable']);
        $sheet->setCellValue('L' . $rowNumber, $row['date_paid']);
        $sheet->setCellValue('M' . $rowNumber, $row['time_paid']);
        $sheet->setCellValue('N' . $rowNumber, $row['reason']);
        $sheet->setCellValue('O' . $rowNumber, $row['date_reject']);
        $sheet->setCellValue('P' . $rowNumber, $row['time_reject']);
        $rowNumber++;
    }
} else {
    // $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
    // $bot->sendText($content);
    echo "خبری نیست";
}
// ذخیره کردن فایل اکسل
$writer = new Xlsx($spreadsheet);
// $filename = 'public_html/requests.xlsx';
$filename = 'requests.xlsx';
$writer->save($filename);