<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);
// date_default_timezone_set('Asia/Tehran');

// if (PHP_SAPI == 'cli')
// 	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once 'Classes/PHPExcel.php';

$servername = "localhost";
$username = "balebtir_dev";
$password = "1P,2Hs!xan).";
$dbname = "balebtir_bale_test";


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset('utf8');

if ($conn->connect_error) {
    die("اتصال به پایگاه داده ناموفق بود: " . $conn->connect_error);
}


$sql = "SELECT * FROM Requests WHERE is_closed = 1 OR is_closed = 0 ORDER BY date_registered DESC, time_registered DESC LIMIT 1000";
$result = $conn->query($sql);


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
// $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
// 							 ->setLastModifiedBy("Maarten Balliauw")
// 							 ->setTitle("Office 2007 XLSX Test Document")
// 							 ->setSubject("Office 2007 XLSX Test Document")
// 							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
// 							 ->setKeywords("office 2007 openxml php")
// 							 ->setCategory("Test result file");


// Add some data
$sheet = $objPHPExcel->setActiveSheetIndex(0);

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
		if ($row['project']==""){
            $project = "---";
        }

        $sheet->setCellValue('A' . $rowNumber, $name);
        $sheet->setCellValue('B' . $rowNumber, $title);
        $sheet->setCellValue('C' . $rowNumber, $description);
        $sheet->setCellValue('D' . $rowNumber, $contract_type);
        $sheet->setCellValue('E' . $rowNumber, $req_status);
        $sheet->setCellValue('F' . $rowNumber, $row['price']);
        $sheet->setCellValue('G' . $rowNumber, $project);
        $sheet->setCellValue('H' . $rowNumber, $row['date_registered']);
        $sheet->setCellValue('I' . $rowNumber, $row['time_registered']);
        $sheet->setCellValue('J' . $rowNumber, $row['date_cartable']?? "---");
        $sheet->setCellValue('K' . $rowNumber, $row['time_cartable']?? "---");
        $sheet->setCellValue('L' . $rowNumber, $row['date_paid']?? "---");
        $sheet->setCellValue('M' . $rowNumber, $row['time_paid']?? "---");
        $sheet->setCellValue('N' . $rowNumber, $row['reason']?? "---");
        $sheet->setCellValue('O' . $rowNumber, $row['date_reject']?? "---");
        $sheet->setCellValue('P' . $rowNumber, $row['time_reject']?? "---");
        $rowNumber++;
    }
} else {
    // $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
    // $bot->sendText($content);
    echo "خبری نیست";
}


// $objPHPExcel->setActiveSheetIndex(0)
//             ->setCellValue('A1', 'Hello')
//             ->setCellValue('B2', 'world!')
//             ->setCellValue('C1', 'Hello')
//             ->setCellValue('D2', 'world!');

// // Miscellaneous glyphs, UTF-8
// $objPHPExcel->setActiveSheetIndex(0)
//             ->setCellValue('A4', 'Miscellaneous glyphs')
//             ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Disposition: attachment;filename="01simple.xlsx"');
// header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
// header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
// header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
// header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
// header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
// header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('x.xlsx');
// unlink('x.xlsx');
exit;
