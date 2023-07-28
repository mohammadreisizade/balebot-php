<?php
include "BaleAPIv2.php";
include "jdf.php";

//require 'vendor/autoload.php';
require_once 'Classes/PHPExcel.php';

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// -------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------- FUNCTIONS -----------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------
// کنسل کردن تغییر سمت
function stop_changing($conn)
{
    $sql = "SELECT * FROM Persons WHERE status='changing'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
            $conn->query($q_up);
        }
    }
    $sql = "SELECT * FROM Persons WHERE status='change'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $q_up = "DELETE FROM Persons WHERE status='change'";
            $conn->query($q_up);
        }
    }
}

// کنسل کردن عملیات رد درخواست
function stop_reason_message($conn)
{
    $query_for_reject_message = "SELECT * FROM Requests WHERE (req_status='waitacc' OR req_status='cartable') AND reason='setreasonforreject'";
    if ($result = $conn->query($query_for_reject_message)) {
        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $q_up = "UPDATE Requests SET reason=NULL WHERE id='$id'";
            $conn->query($q_up);
        }
    }

}

// کنسل کردن ساخت کاربر جدید و کنسل کردن تغییر یوزر نیم
function delete_half_made_user($conn)
{
    $sql = "SELECT * FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR status='getpos' OR 
                            status='changeus' OR status='changeuss'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $sql = "DELETE FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR
                          status='getpos' OR status='changeus' OR status='changeuss'";
            $conn->query($sql);
        }
    }
}

// کنسل کردن دریافت تعداد برای نمایش درخواست ها
function delete_get_num($conn, $bb)
{
    $q_exists = "SELECT id FROM Requests WHERE req_status='getnum' AND created_by=$bb";
    if ($result = $conn->query($q_exists)) {
        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $ccc = $row['id'];
            settype($ccc, "integer");
            $d_q = "DELETE FROM Requests WHERE id=$ccc";
            $conn->query($d_q);
        }
    }
}

// حذف درخواست های نیمه کاره
function delete_undone_request($conn, $bb)
{
    $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
    if ($result = $conn->query($q_exists)) {
        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $ccc = $row['id'];
            settype($ccc, "integer");
            $d_q = "DELETE FROM Requests WHERE id=$ccc";
            $conn->query($d_q);
        }
    }
}

function accounting_clipboard($bot, $chat_id)
{
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های باز", '', "openreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '', "everything"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function projectmanager_clipboard($bot, $chat_id)
{
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("ثبت درخواست پرداخت", '', "paymentreq"),
        $bot->buildInlineKeyBoardButton("درخواست های من", '', "myreq"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function admin_clipboard($bot, $chat_id)
{
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("تنظیمات", '', "setting"),
        $bot->buildInlineKeyBoardButton("خروجی اکسل", '', "adminexcel"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function export_excel_all($conn, $bot, $chat_id){

    $sql = "SELECT * FROM Requests WHERE is_closed = 1 OR is_closed = 0 ORDER BY date_registered DESC, time_registered DESC LIMIT 1000";
    $result = $conn->query($sql);

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);



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
         $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
         $bot->sendText($content);
    }
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    $objPHPExcel->setActiveSheetIndex(0);
    $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $temp_path = 'public_html/reports_all_'.$temp_name.'.xlsx';
    $objWriter->save($temp_path);
    $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/'.$temp_path);
    $bot->sendDocument($contentdoc);
    unlink($temp_path);
}

function export_excel_open($conn, $bot, $chat_id){

    $sql = "SELECT * FROM Requests WHERE is_closed = 0 AND (req_status = 'waitacc' OR req_status = 'cartable') ORDER BY date_registered DESC, time_registered DESC LIMIT 1000";
    $result = $conn->query($sql);

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);



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



    if ($result->num_rows != 0) {
        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

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

            $rowNumber++;
        }
    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    $objPHPExcel->setActiveSheetIndex(0);
    $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $temp_path = 'public_html/reports_open_'.$temp_name.'.xlsx';
    $objWriter->save($temp_path);
    $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/'.$temp_path);
    $bot->sendDocument($contentdoc);
    unlink($temp_path);
}

function export_excel_paid($conn, $bot, $chat_id){

    $sql = "SELECT * FROM Requests WHERE is_closed = 1 ORDER BY date_registered DESC, time_registered DESC LIMIT 1000";
    $result = $conn->query($sql);

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);



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



    if ($result->num_rows != 0) {
        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

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

            $rowNumber++;
        }
    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    $objPHPExcel->setActiveSheetIndex(0);
    $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $temp_path = 'public_html/reports_paid_'.$temp_name.'.xlsx';
    $objWriter->save($temp_path);
    $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/'.$temp_path);
    $bot->sendDocument($contentdoc);
    unlink($temp_path);
}


// ---------------------------------------------------------------------------------------------------------------------------

$token = "1324268863:os1PBUvvHX7EVJOfYR3OHC9mH9gLzsaSLRfmxbDW";

// Set session variables

$bot = new balebot($token);
$chat_id = $bot->ChatID();
$user_id = $bot->UserID();
$Text_orgi = $bot->Text();
$callback_data = $bot->CallBack_Data();
$username = $bot->username();


// -----------------------------------------           date and time          --------------------------------------------------

date_default_timezone_set('Asia/Tehran');
$today_date = gregorian_to_jalali(date("Y"), date("m"), date("d"), "-");
$time_now = date('H:i:s');


//----------------------------------------------- حالت های req_status ------------------------------------------------------

//getnum  تعداد درخواست هایی که میخواهیم نمایش داده شود
//waitacc منتظر تأیید حسابداری
//getprice دریافت قیمت درخواست
//gettitle دریافت عنوان درخواست
//getdesc دریافت توضیحات درخواست
//getproj دریافت عنوان پروژه درخواست
//choosing در انتظار ثبت یا لغو درخواست
//paid وضعیت درخواست پرداخت شده
//cartable وضعیت درخواست کارتابل
//gettype دریافت نوع درخواست

//---------------------------------------------------- حالت های status -----------------------------------------------------------

//change دریافت یوزر نیم برای تغییر سمت
//changing  هنگام تغییر سمت برای دریافت سمت مورد نظر یوزری که وارد شده، این وضعیت برای شخص مورد نظر تعیین می شود
//changeus وضعیت برای دریافت یوزر نیم برای تغییر یوزر نیم
//changeuss وضعیت برای دریافت یوزرنیم جدید برای تغییر یوزر نیم
//getname دریافت نام برای ساخت حساب کاربری
//getuser دریافت یوزرنیم برای ساخت حساب کاربری
//getpos وضعیت درخواست پرداخت شده
//choosing تأیید ساخت یک حساب کاربری جدید برای بات

//---------------------------------------------------- حالت های is_closed -----------------------------------------------------------

// 0   درخواست ثبت شده و منتظر تأیید است.
// 1 درخواست پرداخت شده است.
// 2 درخواست در حال ساخته شدن است.

// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------      DATABASE INFORMATIONS     ----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------

$servername = "localhost";
$usern = "balebtir_dev";
$password = "1P,2Hs!xan).";
// Create connection
$conn = new mysqli($servername, $usern, $password, "balebtir_bale_test");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8mb4");

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------UPDATE UNIQUE ID INSTEAD OF USERNAME----------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------


// دریافت همه یوزر نیم های ثبت شده در دیتابیس
$q = "SELECT username FROM Persons";
if ($result = $conn->query($q)) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['username'];
    }
}
// بررسی این که آیا شخصی که وارد بات شده، قبلا یوزرنیم آن ثبت شده یا نه
if (in_array($username, $data)) {
    $sql = "SELECT * FROM Persons WHERE username='$username'";

    if ($result = $conn->query($sql)) {
        {
            $row = $result->fetch_assoc();
            $id = $row['unique_id'];

            if (!isset($id)) {

                // اگر قبلا وارد بات شده باشد آیدی یونیک دریافت شده و گرنه با دستور زیر، آیدی یونیک او را ذخیره می کنیم.
                $query = "UPDATE Persons SET unique_id='$user_id' WHERE username='$username'";
                $result = $conn->query($query);
            }
        }
    }
} else {
    //اگر آیدی یونیک ثبت نشده باشد ، پیغام زیر داده میشود
    $q = "SELECT unique_id FROM Persons";
    if ($result = $conn->query($q)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row['unique_id'];
        }
    }
    if (!in_array($user_id, $data)) {
        $contenttmp = array('chat_id' => $chat_id, "text" => "آیدی شما در ربات تعریف نشده است!");
        $bot->sendText($contenttmp);
    }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------- get current user id in database -------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

// نکته : bb یعنی یوزرنیم آیدی کاربری که دارد استفاده میکند در دیتابیس

$bb = null;
$q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
if ($result = $conn->query($q_id)) {
    $row = $result->fetch_assoc();
    $bb = $row['id'];
    settype($bb, "integer");
}


// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- ADMIN --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// دریافت ادمین
$sql = "SELECT unique_id FROM Persons WHERE position='admin'";
if ($result = $conn->query($sql)) {
      $row = $result->fetch_assoc();
      $admin = $row['unique_id'];

}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- CEOs --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// دریافت مدیر عامل ها
$sql = "SELECT unique_id FROM Persons WHERE position='CEO'";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $ceoceo[] = $row['unique_id'];
    }
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- ACCOUNTINGs ---------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

//دریافت حسابدار ها

$sql = "SELECT unique_id FROM Persons WHERE position='accounting'";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $accc[] = $row['unique_id'];
    }
}

//-------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- PROJECT MANAGERSs ------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------------------------------

//مدیر پروژه ها را دریافت می کند
$sql = "SELECT unique_id FROM Persons WHERE position='project manager'";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $project_managers[] = $row['unique_id'];
    }
}


// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- CODE FOR CEO --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if (in_array($chat_id, $ceoceo)) {
    if ($Text_orgi == "/start") {
        delete_undone_request($conn, $bb);
        $inlineKeyboardoption = [
            $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqceo"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id, "text" => "انتخاب کنید:", 'reply_markup' => $Keyboard);
        $bot->sendText($contenttmp);
    } else {
        $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
        if ($result = $conn->query($q_exists)) {

            if ($result->num_rows != 0) {
                $row = $result->fetch_assoc();
                $ccc = $row['req_status'];

                if ($ccc == "gettitle") {
                    if (strlen($Text_orgi) >= 200) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET title='$Text_orgi', req_status='getprice' WHERE created_by=$bb AND req_status='gettitle'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "مبلغ را وارد کنید_حداکثر30رقم:");
                        $bot->sendText($contenttmp);
                    }

                } elseif ($ccc == "getprice") {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        if (is_numeric($Text_orgi)) {
                            $formatted_num = number_format($Text_orgi, 0, '.', ',');
                            $q_up = "UPDATE Requests SET price='$formatted_num', req_status='getdesc' WHERE created_by=$bb AND req_status='getprice'";
                            $result = $conn->query($q_up);
                            if ($row['contract_type'] == 'factor') {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات(شماره شبا و نام طرف حساب)_حداکثر 300 کاراکتر:");
                                $bot->sendText($contenttmp);
                            } else {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات(صورت وضعیت، علی الحساب، پیش پرداخت، سپرده های قرارداد)_حداکثر 300 کاراکتر:");
                                $bot->sendText($contenttmp);
                            }
                        } else {
                            $contenttmp = array('chat_id' => $chat_id, "text" => "لطفا عدد وارد کنید(زبان کیبورد خود را انگلیسی کنید):");
                            $bot->sendText($contenttmp);
                        }
                    }

                } elseif ($ccc == "getdesc") {
                    if (strlen($Text_orgi) >= 300) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET description='$Text_orgi' WHERE created_by=$bb AND req_status='getdesc'";
                        $result = $conn->query($q_up);
                        if ($row['contract_type'] == 'factor') {
                            $q_st = "UPDATE Requests SET req_status='getproj' WHERE created_by=$bb AND req_status='getdesc'";
                            $r = $conn->query($q_st);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "پروژه_حداکثر 100 کاراکتر:");
                            $bot->sendText($contenttmp);
                        } else {
                            $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
                            if ($result = $conn->query($all)) {
                                $row = $result->fetch_assoc();
                                $t = $row['title'];
                                $p = $row['price'];
                                $d = $row['description'];

                                $content = array("chat_id" => $chat_id, "text" => "شماره قرارداد : $t\nتوضیحات : $d\nمبلغ : $p");
                                $bot->sendText($content);

                                $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                                $result = $conn->query($q_up);

                                $inlineKeyboardoption = [
                                    $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                                    $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                                ];
                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                                $bot->sendText($contenttmp);
                            }
                        }
                    }

                } elseif ($ccc == "getproj") {
                    if (strlen($Text_orgi) >= 100) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET project='$Text_orgi' WHERE created_by=$bb AND req_status='getproj'";
                        $result = $conn->query($q_up);

                        $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
                        if ($result = $conn->query($all)) {
                            $row = $result->fetch_assoc();
                            $t = $row['title'];
                            $p = $row['price'];
                            $d = $row['description'];
                            $pr = $row['project'];

                            $content = array("chat_id" => $chat_id, "text" => "عنوان : $t\nتوضیحات : $d\nپروژه : $pr\nمبلغ : $p");
                            $bot->sendText($content);

                            $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                            $result = $conn->query($q_up);

                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                                $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                            $bot->sendText($contenttmp);
                        }
                    }
                }
            }
        }
    }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- CODE FOR ADMIN --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if ($chat_id==$admin) {
    if ($Text_orgi == "/start") {
        delete_half_made_user($conn);
        stop_changing($conn);
        admin_clipboard($bot, $chat_id);
    } else {
        $q_id = "SELECT * FROM Persons WHERE status='getname' OR status='getuser' OR status='change' OR status='changeus' OR status='changeuss'";
        if ($result = $conn->query($q_id)) {
            if ($result->num_rows != 0) {
                $row = $result->fetch_assoc();
                if ($row['status'] == 'getname') {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Persons SET name='$Text_orgi', status='getuser' WHERE status='getname'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم شخص مورد نظر را بدون علامت @ وارد کنید_حداکثر 30 کاراکتر:");
                        $bot->sendText($contenttmp);
                    }
                } elseif ($row['status'] == 'getuser') {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Persons SET username='$Text_orgi', status='getpos' WHERE status='getuser'";
                        $result = $conn->query($q_up);
                        $inlineKeyboardoption = [
                            $bot->buildInlineKeyBoardButton("مدیر پروژه", '', "ppm"),
                            $bot->buildInlineKeyBoardButton("حسابداری", '', "pacc"),
                            $bot->buildInlineKeyBoardButton("مدیر عامل", '', "pseo"),

                        ];
                        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت شخص مورد نظر را از بین گزینه های زیر انتخاب کنید:", 'reply_markup' => $Keyboard);
                        $bot->sendText($contenttmp);
                    }

                } elseif ($row['status'] == 'change') {
                    $sql = "SELECT * FROM Persons WHERE username='$Text_orgi'";
                    if ($res = $conn->query($sql)) {
                        if ($res->num_rows == 0) {
                            $d_q = "DELETE FROM Persons WHERE status='change'";
                            $result = $conn->query($d_q);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "این یوزرنیم در این ربات تعریف نشده است!");
                            $bot->sendText($contenttmp);
                            accounting_clipboard($bot, $chat_id);

                        } else {
                            $row = $res->fetch_assoc();
                            if ($row['position'] == 'admin') {
                                stop_changing($conn);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "این کار امکان پذیر نیست. یوزرنیم وارد شده متعلق به ادمین است!");
                                $bot->sendText($contenttmp);
                                admin_clipboard($bot, $chat_id);
                            } else {
                                if ($row['position'] == 'project manager') {
                                    $stat = "مدیر پروژه";
                                } elseif ($row['position'] == 'accounting') {
                                    $stat = "حسابداری";
                                } elseif ($row['position'] == 'CEO') {
                                    $stat = "مدیر عامل";
                                }
                                $q_up = "UPDATE Persons SET status='changing' WHERE username='$Text_orgi'";
                                $result = $conn->query($q_up);
                                $sql = "SELECT * FROM Persons WHERE status='change'";
                                if ($result = $conn->query($sql)) {
                                    if ($result->num_rows != 0) {
                                        $d_q = "DELETE FROM Persons WHERE status='change'";
                                        $result = $conn->query($d_q);
                                    }
                                }
                                $inlineKeyboardoption = [
                                    $bot->buildInlineKeyBoardButton("مدیر پروژه", '', "changetopm"),
                                    $bot->buildInlineKeyBoardButton("حسابداری", '', "changetoacc"),
                                    $bot->buildInlineKeyBoardButton("مدیر عامل", '', "changetoceo"),
                                    $bot->buildInlineKeyBoardButton("حذف این شخص", '', "changeremove"),
                                ];
                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "سمت این شخص $stat است.سمت مدنظر خود را برای این شخص انتخاب کنید:", 'reply_markup' => $Keyboard);
                                $bot->sendText($contenttmp);
                            }
                        }
                    }
                } elseif ($row['status'] == 'changeus') {
                    $sql = "SELECT * FROM Persons WHERE username='$Text_orgi'";
                    if ($res = $conn->query($sql)) {
                        if ($res->num_rows == 0) {
                            $d_q = "DELETE FROM Persons WHERE status='changeus'";
                            $result = $conn->query($d_q);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "این یوزرنیم در این ربات تعریف نشده است!");
                            $bot->sendText($contenttmp);
                            accounting_clipboard($bot, $chat_id);

                        } else {
                            $q_up = "UPDATE Persons SET status='changeuss' WHERE username='$Text_orgi'";
                            $result = $conn->query($q_up);
                            $sql = "SELECT * FROM Persons WHERE status='changeus'";
                            if ($result = $conn->query($sql)) {
                                if ($result->num_rows != 0) {
                                    $d_q = "DELETE FROM Persons WHERE status='changeus'";
                                    $result = $conn->query($d_q);
                                }
                            }
                            $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم جدید را بدون علامت @ وارد کنید_حداکثر 30 کاراکتر:");
                            $bot->sendText($contenttmp);
                        }
                    }
                } elseif ($row['status'] == 'changeuss') {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Persons SET username='$Text_orgi', status=NULL WHERE status='changeuss'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم ثبت شده شخص مورد نظر با موفقیت تغییر داده شد.");
                        $bot->sendText($contenttmp);
                    }
                }
            }
        }
    }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------- Callback for ACCOUNTING ------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// بررسی پیغام برای رد درخواست
if (in_array($chat_id, $accc)) {
    if ($Text_orgi != "/start") {
        if (strlen($Text_orgi) >= 250) {
            $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
            $bot->sendText($contenttmp);
        } else {
            $query_for_reject_message = "SELECT * FROM Requests WHERE (req_status='waitacc' OR req_status='cartable') AND reason='setreasonforreject'";
            if ($result = $conn->query($query_for_reject_message)) {
                if ($result->num_rows != 0) {
                    $row = $result->fetch_assoc();
                    $ccc = $row['id'];
                    $title = $row['title'];
                    $q_up = "UPDATE Requests SET req_status='rejectacc', reason='$Text_orgi', date_reject='$today_date', time_reject='$time_now' WHERE id='$ccc'";
                    $result = $conn->query($q_up);
                    $content = array("chat_id" => $chat_id, "text" => "درخواست : $title با موفقیت رد شد.");
                    $bot->sendText($content);
                }
            }
        }
    }
}
//اگر کال بک، حرف اول آن، x باشد، یعنی درخواست مورد نظر ، پرداخت شده است، آیدی درخواست مورد نظر در ادامه x وجود دارد
if ($callback_data[0] == "x") {
    $getcbdata = str_replace("x", "", $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET req_status='paid', is_closed=1, date_paid='$today_date', time_paid='$time_now' WHERE id=$getcbdata";
    if ($result = $conn->query($q_up)) {
        $q = "SELECT title FROM Requests WHERE id=$getcbdata";
        if ($result = $conn->query($q)) {
            $row = $result->fetch_assoc();
            $title = $row['title'];
            $content = array("chat_id" => $chat_id, "text" => "درخواست : $title پرداخت شد.");
            $bot->sendText($content);
        }
    }
    //اگر کال بک، حرف اول آن، y باشد، یعنی درخواست مورد نظر ، کارتابل است، آیدی درخواست مورد نظر در ادامه y وجود دارد
} elseif ($callback_data[0] == 'y') {
    $getcbdata = str_replace('y', '', $callback_data);
    settype($getcbdata, "integer");
    $q_up = "UPDATE Requests SET req_status='cartable', date_cartable='$today_date', time_cartable='$time_now' WHERE id=$getcbdata";
    if ($result = $conn->query($q_up)) {
        $q = "SELECT title FROM Requests WHERE id=$getcbdata";
        if ($result = $conn->query($q)) {
            $row = $result->fetch_assoc();
            $title = $row['title'];
            $content = array("chat_id" => $chat_id, "text" => "درخواست : $title به وضعیت کارتابل تغییر یافت.");
            $bot->sendText($content);
        }
    }
    //اگر کال بک، حرف اول آن، r باشد، یعنی درخواست مورد نظر ، عدم تأیید است، آیدی درخواست مورد نظر در ادامه r وجود دارد
} elseif ($callback_data[0] == 'r') {
    $getcbdata = str_replace('r', '', $callback_data);
    settype($getcbdata, "integer");
    $q_up = "UPDATE Requests SET reason='setreasonforreject' WHERE id=$getcbdata";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "متن توضیح برای عدم تأیید درخواست را وارد کنید(حداکثر 250 کاراکتر):");
        $bot->sendText($content);
    }
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------- FOR ACCOUNTING درخواست های آماده پرداخت -----------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// اگر در بخش درخواست های آماده پرداخت، یک درخواست پرداخت شده انتخاب شود، عملیات با استفاده از کد زیر است
if ($callback_data[0] == 'z') {
    $getcbdata = str_replace('z', '', $callback_data);
    settype($getcbdata, "integer");
    $q_up = "UPDATE Requests SET req_status='paid', is_closed=1, date_paid='$today_date', time_paid='$time_now' WHERE id=$getcbdata";
    if ($result = $conn->query($q_up)) {
        $q = "SELECT title FROM Requests WHERE id=$getcbdata";
        if ($result = $conn->query($q)) {
            $row = $result->fetch_assoc();
            $title = $row['title'];
            $content = array("chat_id" => $chat_id, "text" => "درخواست : $title پرداخت شد.");
            $bot->sendText($content);
        }
    }
}
// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------


// برای حسابداری
// --------------------------------------------------------------------------------------------------------------------------------------------------------------
//اگر start فراخوانی شود، وضعیت درخواست ها و کاربر ها مثل قبل میشود و عملیات کنسل می شود
if (in_array($chat_id, $accc)) {
    if ($Text_orgi == "/start") {
        delete_undone_request($conn, $bb);
        delete_get_num($conn, $bb);
        stop_reason_message($conn);
        accounting_clipboard($bot, $chat_id);
    } else {

        $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
        if ($result = $conn->query($q_exists)) {

            if ($result->num_rows != 0) {
                $row = $result->fetch_assoc();
                $ccc = $row['req_status'];

                if ($ccc == "gettitle") {
                    if (strlen($Text_orgi) >= 200) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET title='$Text_orgi', req_status='getprice' WHERE created_by=$bb AND req_status='gettitle'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "مبلغ را وارد کنید_حداکثر30رقم:");
                        $bot->sendText($contenttmp);
                    }

                } elseif ($ccc == "getprice") {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        if (is_numeric($Text_orgi)) {
                            $formatted_num = number_format($Text_orgi, 0, '.', ',');
                            $q_up = "UPDATE Requests SET price='$formatted_num', req_status='getdesc' WHERE created_by=$bb AND req_status='getprice'";
                            $result = $conn->query($q_up);

                            if ($row['contract_type'] == 'factor') {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات(شماره شبا و نام طرف حساب)_حداکثر 300 کاراکتر:");
                                $bot->sendText($contenttmp);
                            } else {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات(صورت وضعیت، علی الحساب، پیش پرداخت، سپرده های قرارداد)_حداکثر 300 کاراکتر:");
                                $bot->sendText($contenttmp);
                            }
                        } else {
                            $contenttmp = array('chat_id' => $chat_id, "text" => "لطفا عدد وارد کنید(زبان کیبورد خود را انگلیسی کنید):");
                            $bot->sendText($contenttmp);
                        }
                    }
                } elseif ($ccc == "getdesc") {
                    if (strlen($Text_orgi) >= 300) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET description='$Text_orgi' WHERE created_by=$bb AND req_status='getdesc'";
                        $result = $conn->query($q_up);
                        if ($row['contract_type'] == 'factor') {
                            $q_st = "UPDATE Requests SET req_status='getproj' WHERE created_by=$bb AND req_status='getdesc'";
                            $r = $conn->query($q_st);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "پروژه_حداکثر 100 کاراکتر:");
                            $bot->sendText($contenttmp);
                        } else {
                            $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
                            if ($result = $conn->query($all)) {
                                $row = $result->fetch_assoc();
                                $t = $row['title'];
                                $p = $row['price'];
                                $d = $row['description'];

                                $content = array("chat_id" => $chat_id, "text" => "شماره قرارداد : $t\nتوضیحات : $d\nمبلغ : $p");
                                $bot->sendText($content);

                                $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                                $result = $conn->query($q_up);

                                $inlineKeyboardoption = [
                                    $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                                    $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                                ];
                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                                $bot->sendText($contenttmp);
                            }
                        }
                    }

                } elseif ($ccc == "getproj") {
                    if (strlen($Text_orgi) >= 100) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET project='$Text_orgi' WHERE created_by=$bb AND req_status='getproj'";
                        $result = $conn->query($q_up);

                        $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
                        if ($result = $conn->query($all)) {
                            $row = $result->fetch_assoc();
                            $t = $row['title'];
                            $p = $row['price'];
                            $d = $row['description'];
                            $pr = $row['project'];

                            $content = array("chat_id" => $chat_id, "text" => "عنوان : $t\nتوضیحات : $d\nپروژه : $pr\nمبلغ : $p");
                            $bot->sendText($content);

                            $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                            $result = $conn->query($q_up);

                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                                $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                            $bot->sendText($contenttmp);
                        }
                    }
                } elseif ($ccc == "getnum") {

                    $sql = "SELECT * FROM Requests WHERE req_status='getnum' AND created_by=$bb";
                    if ($result = $conn->query($sql)) {
                        if ($result->num_rows != 0) {
                            $thenum = $Text_orgi;

                            if (is_numeric($thenum)) {
                                settype($thenum, "integer");

                                $q_exists = "SELECT * FROM Requests WHERE req_status='waitacc' AND is_closed=0 ORDER BY date_registered DESC LIMIT $thenum";
                                if ($result = $conn->query($q_exists)) {
                                    if ($result->num_rows == 0) {
                                        $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                                        $bot->sendText($contenttmp);
                                    } else {
                                        $num = $result->num_rows;
                                        $contenttmp = array('chat_id' => $chat_id, "text" => "در حال پردازش...");
                                        $bot->sendText($contenttmp);

                                        while ($row = $result->fetch_assoc()) {
                                            $title = $row['title'];
                                            $price = $row['price'];
                                            $date = $row['date_registered'];
                                            $time = $row['time_registered'];
                                            $description = $row['description'];
                                            $created_by = $row['created_by'];
                                            $name = $row['name'];
                                            $id = $row['id'];
                                            $contract_type = $row['contract_type'];
                                            $cbdatareject = "y$id";
                                            $cbdataaccept = "x$id";
                                            $rejectreq = "r$id";
                                            if ($contract_type == 'factor') {
                                                $project = $row['project'];
                                                $inlineKeyboardoption = [
                                                    $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                                    $bot->buildInlineKeyBoardButton("کارتابل", '', "$cbdatareject"),
                                                    $bot->buildInlineKeyBoardButton("پرداخت شده", '', "$cbdataaccept"),
                                                ];
                                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                                $content = array("chat_id" => $chat_id, "text" => "نام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date\nساعت درخواست : $time", 'reply_markup' => $Keyboard);
                                                $bot->sendText($content);
                                            } else {
                                                $inlineKeyboardoption = [
                                                    $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                                    $bot->buildInlineKeyBoardButton("کارتابل", '', "$cbdatareject"),
                                                    $bot->buildInlineKeyBoardButton("پرداخت شده", '', "$cbdataaccept"),
                                                ];
                                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                                $content = array("chat_id" => $chat_id, "text" => "نام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date\nساعت درخواست : $time", 'reply_markup' => $Keyboard);
                                                $bot->sendText($content);
                                            }
                                            sleep(1);
                                        }
                                        $contenttmp = array('chat_id' => $chat_id, "text" => "پایان پردازش");
                                        $bot->sendText($contenttmp);
                                    }
                                }

                                $d_q = "DELETE FROM Requests WHERE req_status='getnum' AND created_by=$bb ";
                                $result = $conn->query($d_q);
                            } else {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "لطفا عدد وارد کنید:");
                                $bot->sendText($contenttmp);
                            }
                        }
                    }
                }
            }
        }
    }
}


// برای مدیر پروژه
// ------------------------------------------------------------------------------------------------------------------------------------
if (in_array($chat_id, $project_managers)) {

    if ($Text_orgi == "/start") {
        delete_undone_request($conn, $bb);

        projectmanager_clipboard($bot, $chat_id);
    } else {
        $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
        if ($result = $conn->query($q_exists)) {
            if ($result->num_rows != 0) {
                $row = $result->fetch_assoc();
                $ccc = $row['req_status'];
                if ($ccc == "gettitle") {
                    if (strlen($Text_orgi) >= 200) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET title='$Text_orgi', req_status='getprice' WHERE created_by=$bb AND req_status='gettitle'";
                        $result = $conn->query($q_up);

                        $contenttmp = array('chat_id' => $chat_id, "text" => "مبلغ را وارد کنید_حداکثر 30 رقم:");
                        $bot->sendText($contenttmp);
                    }
                } elseif ($ccc == "getprice") {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        if (is_numeric($Text_orgi)) {
                            $formatted_num = number_format($Text_orgi, 0, '.', ',');

                            $q_up = "UPDATE Requests SET price='$formatted_num', req_status='getdesc' WHERE created_by=$bb AND req_status='getprice'";
                            $result = $conn->query($q_up);

                            if ($row['contract_type'] == 'factor') {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات(شماره شبا و نام طرف حساب)_حداکثر 300 کاراکتر:");
                                $bot->sendText($contenttmp);
                            } else {
                                $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات(صورت وضعیت، علی الحساب، پیش پرداخت، سپرده های قرارداد)_حداکثر 300 کاراکتر:");
                                $bot->sendText($contenttmp);
                            }
                        } else {
                            $contenttmp = array('chat_id' => $chat_id, "text" => "لطفا عدد وارد کنید(زبان کیبورد خود را انگلیسی کنید):");
                            $bot->sendText($contenttmp);
                        }
                    }

                } elseif ($ccc == "getdesc") {
                    if (strlen($Text_orgi) >= 300) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET description='$Text_orgi' WHERE created_by=$bb AND req_status='getdesc'";
                        $result = $conn->query($q_up);
                        if ($row['contract_type'] == 'factor') {
                            $q_st = "UPDATE Requests SET req_status='getproj' WHERE created_by=$bb AND req_status='getdesc'";
                            $r = $conn->query($q_st);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "پروژه_حداکثر 100 کاراکتر:");
                            $bot->sendText($contenttmp);
                        } else {
                            $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
                            if ($result = $conn->query($all)) {
                                $row = $result->fetch_assoc();
                                $t = $row['title'];
                                $p = $row['price'];
                                $d = $row['description'];

                                $content = array("chat_id" => $chat_id, "text" => "شماره قرارداد : $t\nتوضیحات : $d\nمبلغ : $p");
                                $bot->sendText($content);

                                $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                                $result = $conn->query($q_up);

                                $inlineKeyboardoption = [
                                    $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                                    $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                                ];
                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                                $bot->sendText($contenttmp);
                            }
                        }
                    }
                } elseif ($ccc == "getproj") {
                    if (strlen($Text_orgi) >= 100) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Requests SET project='$Text_orgi' WHERE created_by=$bb AND req_status='getproj'";
                        $result = $conn->query($q_up);

                        $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
                        if ($result = $conn->query($all)) {
                            $row = $result->fetch_assoc();
                            $t = $row['title'];
                            $p = $row['price'];
                            $d = $row['description'];
                            $pr = $row['project'];

                            $content = array("chat_id" => $chat_id, "text" => "عنوان : $t\nتوضیحات : $d\nپروژه : $pr\nمبلغ : $p");
                            $bot->sendText($content);

                            $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                            $result = $conn->query($q_up);

                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                                $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                            $bot->sendText($contenttmp);
                        }
                    }
                }
            }
        }
    }
}

switch ($callback_data) {
//    درخواست پرداخت
    case "paymentreq":
        if (in_array($chat_id, $project_managers)) {
            delete_undone_request($conn, $bb);

            $q_id = "SELECT id FROM Persons WHERE unique_id='$user_id'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $cb = $row['id'];
                settype($cb, "integer");
            }
            $qu = "INSERT INTO Requests (is_closed, req_status, created_by)
      VALUES (2, 'gettype',$cb)";
            if ($result = $conn->query($qu)) {

            } else {
                $content = array("chat_id" => $chat_id, "text" => "مشکلی پیش آمد!");
                $bot->sendText($content);
            }
            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("قراردادی", '', "contract"),
                $bot->buildInlineKeyBoardButton("فاکتوری", '', "factor"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "نوع قرارداد را مشخص کنید:", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
//  درخواست های من
    case "myreq":
        if (in_array($chat_id, $project_managers)) {
            $q_exists = "SELECT * FROM Requests WHERE (created_by = $bb AND is_closed = 0) OR (created_by = $bb AND is_closed = 1)";
            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows == 0) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                    $bot->sendText($contenttmp);
                } else {
                    while ($row = $result->fetch_assoc()) {
                        if ($row['is_closed'] == 1) {
                            $date_paid = $row['date_paid'];

                            // تفکیک سال، ماه و روز از تاریخ جلالی
                            list($year, $month, $day) = explode('-', $date_paid);

                            $year = intval($year);
                            $month = intval($month);
                            $day = intval($day);
                            $date_paid = jalali_to_gregorian($year, $month, $day, "-");

                            $time_paid = $row['time_paid'];
                            $timestampFromDB = strtotime($date_paid . " " . $time_paid);
                            // محاسبه فاصله زمانی بین زمان کنونی و زمان دریافتی از دیتابیس (به صورت ثانیه)
                            $timeDiffInSeconds = time() - $timestampFromDB;

                            // محاسبه فاصله زمانی به صورت ساعت
                            $timeDiffInHours = $timeDiffInSeconds / 3600;
                            if ($timeDiffInHours <= 48) {
                                $title = $row['title'];
                                $price = $row['price'];
                                $contract_type = $row['contract_type'];
                                $description = $row['description'];
                                $reason = $row['reason'];

                                $date = $row['date_registered'];
                                $time = $row['time_registered'];

                                $date_cartable = $row['date_cartable'];
                                $time_cartable = $row['time_cartable'];

                                $date_reject = $row['date_reject'];
                                $time_reject = $row['time_reject'];

                                $state = $row['req_status'];
                                if ($state == "paid") {
                                    $status = "پرداخت شده";
                                    $cartable = "-";
                                    $paid = "+";
                                } else {
                                    $content = array("chat_id" => $chat_id, "text" => "اشتباهی رخ داده است!");
                                    $bot->sendText($content);
                                }
                                if ($state == "paid") {
                                    if ($date_cartable == "") {
                                        $date_cartable = "ثبت نشده";
                                    }
                                    if ($time_cartable == "") {
                                        $time_cartable = "ثبت نشده";
                                    }
                                    if ($contract_type == 'factor') {
                                        $project = $row['project'];
                                        $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nتاریخ تغییر وضعیت به پرداخت شده : $date_paid\nساعت تغییر وضعیت به پرداخت شده : $time_paid\nکارتابل : $cartable\nپرداخت شده : $paid");
                                    } else {
                                        $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nتاریخ تغییر وضعیت به پرداخت شده : $date_paid\nساعت تغییر وضعیت به پرداخت شده : $time_paid\nکارتابل : $cartable\nپرداخت شده : $paid");
                                    }
                                    $bot->sendText($content);
                                }
                            } else {
                                continue;
                            }
                        } else {
                            $title = $row['title'];
                            $price = $row['price'];
                            $contract_type = $row['contract_type'];
                            $description = $row['description'];
                            $reason = $row['reason'];

                            $date = $row['date_registered'];
                            $time = $row['time_registered'];

                            $date_cartable = $row['date_cartable'];
                            $time_cartable = $row['time_cartable'];

                            $date_reject = $row['date_reject'];
                            $time_reject = $row['time_reject'];

                            $state = $row['req_status'];
                            if ($state == "waitacc") {
                                $status = "در انتظار بررسی واحد مالی";
                                $cartable = "-";
                                $paid = "-";
                            } elseif ($state == "cartable") {
                                $status = "کارتابل";
                                $cartable = "+";
                                $paid = "-";
                            } elseif ($state == "rejectacc") {
                                $status = "رد شده توسط واحد مالی";
                                $cartable = "-";
                                $paid = "-";
                            } else {
                                $content = array("chat_id" => $chat_id, "text" => "اشتباهی رخ داد!");
                                $bot->sendText($content);
                            }
                            if ($state == "rejectacc") {
                                if ($contract_type == 'factor') {
                                    $project = $row['project'];
                                    $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nدلیل رد شدن درخواست : $reason\nتاریخ رد شدن درخواست : $date_reject\nساعت رد شدن درخواست : $time_reject");
                                } else {
                                    $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nدلیل رد شدن درخواست : $reason\nتاریخ رد شدن درخواست : $date_reject\nساعت رد شدن درخواست : $time_reject");
                                }
                                $bot->sendText($content);
                            } elseif ($state == "cartable") {
                                if ($contract_type == 'factor') {
                                    $project = $row['project'];
                                    $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nکارتابل : $cartable\nپرداخت شده : $paid");
                                } else {
                                    $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nکارتابل : $cartable\nپرداخت شده : $paid");
                                }
                                $bot->sendText($content);
                            } elseif ($state == "waitacc") {
                                if ($contract_type == 'factor') {
                                    $project = $row['project'];
                                    $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nکارتابل : $cartable\nپرداخت شده : $paid");
                                } else {
                                    $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nکارتابل : $cartable\nپرداخت شده : $paid");
                                }
                                $bot->sendText($content);
                            }
                        }
                        sleep(2);
                    }
                }
            }
            projectmanager_clipboard($bot, $chat_id);
        }
        break;
//انتخاب نوع درخواست قراردادی
    case "contract":
        if (in_array($chat_id, $project_managers) || in_array($chat_id, $accc) || in_array($chat_id, $ceoceo)) {
            $q_up = "UPDATE Requests SET contract_type='contract', req_status='gettitle' WHERE created_by=$bb AND req_status='gettype'";
            $result = $conn->query($q_up);

            $content = array("chat_id" => $chat_id, "text" => "شماره قرارداد را وارد کنید_حداکثر 200 کاراکتر:");
            $bot->sendText($content);
        }
        break;
//انتخاب نوع درخواست فاکتوری
    case "factor":
        if (in_array($chat_id, $project_managers) || in_array($chat_id, $accc) || in_array($chat_id, $ceoceo)) {

            $q_up = "UPDATE Requests SET contract_type='factor', req_status='gettitle' WHERE created_by=$bb AND req_status='gettype'";
            $result = $conn->query($q_up);

            $content = array("chat_id" => $chat_id, "text" => "عنوان را وارد کنید_حداکثر 200 کاراکتر:");
            $bot->sendText($content);
        }
        break;
//تأیید درخواست پرداخت
    case "conf_pm":
        if (in_array($chat_id, $project_managers) || in_array($chat_id, $accc) || in_array($chat_id, $ceoceo)) {
            $q_id = "SELECT * FROM Persons WHERE unique_id=$user_id";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $bb = $row['id'];
                $n = $row['name'];
            }
            $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows != 0) {
                    $row = $result->fetch_assoc();
                    if ($row['req_status'] == 'choosing') {

                        $req_id = $row['id'];
                        $q_up = "UPDATE Requests SET req_status='waitacc', name='$n', date_registered='$today_date', is_closed=0,
                    time_registered='$time_now' WHERE created_by=$bb AND is_closed=2";
                        $result = $conn->query($q_up);

                        // دریافت کسانی که حسابدار هستند
                        $sql = "SELECT unique_id FROM Persons WHERE position='accounting'";
                        if ($result = $conn->query($sql)) {
                            if ($result->num_rows != 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $data[] = $row['unique_id'];
                                }
                            } else {
                                $data = [];
                            }
                        }
                        $content = array("chat_id" => $chat_id, "text" => "درخواست شما با موفقیت ثبت شد.");
                        $bot->sendText($content);
                        // فرستادن درخواست به حسابداران
                        foreach ($data as $u) {
                            $q_exists = "SELECT * FROM Requests WHERE id='$req_id'";
                            if ($result = $conn->query($q_exists)) {
                                $row = $result->fetch_assoc();
                                $title = $row['title'];
                                $price = $row['price'];
                                $date = $row['date_registered'];
                                $description = $row['description'];
                                $created_by = $row['created_by'];
                                $name = $row['name'];
                                $id = $row['id'];
                                $contract_type = $row['contract_type'];
                                $cbdatareject = "y$id";
                                $cbdataaccept = "x$id";
                                $rejectreq = "r$id";
                                if ($contract_type == 'factor') {
                                    $project = $row['project'];
                                    $inlineKeyboardoption = [
                                        $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                        $bot->buildInlineKeyBoardButton("کارتابل", '', "$cbdatareject"),
                                        $bot->buildInlineKeyBoardButton("پرداخت شده", '', "$cbdataaccept"),
                                    ];
                                    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                    $content = array("chat_id" => $u, "text" => "درخواست جدید:\nنام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date", 'reply_markup' => $Keyboard);
                                    $bot->sendText($content);
                                } else {
                                    $inlineKeyboardoption = [
                                        $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                        $bot->buildInlineKeyBoardButton("کارتابل", '', "$cbdatareject"),
                                        $bot->buildInlineKeyBoardButton("پرداخت شده", '', "$cbdataaccept"),
                                    ];
                                    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                    $content = array("chat_id" => $u, "text" => "درخواست جدید:\nنام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date", 'reply_markup' => $Keyboard);
                                    $bot->sendText($content);
                                }
                            }
                            sleep(1);
                        }
                    } else {
                        $content = array("chat_id" => $chat_id, "text" => "درخواست ناقص است!");
                        $bot->sendText($content);
                    }
                } else {
                    $content = array("chat_id" => $chat_id, "text" => "موردی وجود ندارد");
                    $bot->sendText($content);
                }
            }
            if (in_array($chat_id, $project_managers)) {
                projectmanager_clipboard($bot, $chat_id);
            } elseif (in_array($chat_id, $ceoceo)) {
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqceo"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
//لغو درخواست پرداخت
    case "cancle_pm":
        if (in_array($chat_id, $project_managers) || in_array($chat_id, $accc) || in_array($chat_id, $ceoceo)) {
            $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
            if ($result = $conn->query($q_exists)) {
                $row = $result->fetch_assoc();
                if (isset($row)) {
                    $ccc = $row['id'];
                    settype($ccc, "integer");
                    $d_q = "DELETE FROM Requests WHERE id=$ccc";
                    if ($conn->query($d_q) === TRUE) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "درخواست شما لغو شد.");
                        $bot->sendText($contenttmp);
                        if (in_array($chat_id, $project_managers)) {
                            projectmanager_clipboard($bot, $chat_id);
                        } elseif (in_array($chat_id, $accc)) {
                            accounting_clipboard($bot, $chat_id);
                        } elseif (in_array($chat_id, $ceoceo)) {
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqceo"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' => $Keyboard);
                            $bot->sendText($contenttmp);
                        }
                    }
                }
            }
        }
        break;
//درخواست پرداخت برای حسابداری
    case "newreqacc":
        if (in_array($chat_id, $accc)) {
            delete_get_num($conn, $bb);
            delete_undone_request($conn, $bb);

            $q_id = "SELECT id FROM Persons WHERE unique_id='$user_id'";
            $result = $conn->query($q_id);
            $row = $result->fetch_assoc();
            $cb = $row['id'];
            settype($cb, "integer");

            $qu = "INSERT INTO Requests (is_closed, req_status, created_by)
      VALUES (2, 'gettype',$cb)";
            if ($result = $conn->query($qu)) {

            } else {
                $content = array("chat_id" => $chat_id, "text" => "مشکلی پیش آمد!");
                $bot->sendText($content);
            }
            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("قراردادی", '', "contract"),
                $bot->buildInlineKeyBoardButton("فاکتوری", '', "factor"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "نوع قرارداد را مشخص کنید:", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
//نمایش دو گزینه درخواست های باز حسابداری
    case "openreqacc":
        if (in_array($chat_id, $accc)) {
            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("درخواست های باز حسابداری", '', "openreqaccc"),
                $bot->buildInlineKeyBoardButton("آماده پرداخت", '', "payready"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
    //نمایش درخواست های باز حسابداری
    case "openreqaccc":
        if (in_array($chat_id, $accc)) {
            $q_exists = "SELECT * FROM Requests WHERE req_status='waitacc' AND is_closed=0 ORDER BY date_registered DESC LIMIT 15";
            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows == 0) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                    $bot->sendText($contenttmp);
                } else {
                    $num = $result->num_rows;
                    $contenttmp = array('chat_id' => $chat_id, "text" => "در حال پردازش...");
                    $bot->sendText($contenttmp);
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد درخواست های قابل نمایش (حداکثر 15)  : $num");
                    $bot->sendText($contenttmp);

                    while ($row = $result->fetch_assoc()) {
                        $title = $row['title'];
                        $price = $row['price'];
                        $date = $row['date_registered'];
                        $description = $row['description'];
                        $created_by = $row['created_by'];
                        $name = $row['name'];
                        $id = $row['id'];
                        $contract_type = $row['contract_type'];
                        $cbdatareject = "y$id";
                        $cbdataaccept = "x$id";
                        $rejectreq = "r$id";

                        if ($contract_type == 'factor') {
                            $project = $row['project'];
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                $bot->buildInlineKeyBoardButton("کارتابل", '', "$cbdatareject"),
                                $bot->buildInlineKeyBoardButton("پرداخت شده", '', "$cbdataaccept"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $content = array("chat_id" => $chat_id, "text" => "نام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date", 'reply_markup' => $Keyboard);
                            $bot->sendText($content);
                        } else {
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                $bot->buildInlineKeyBoardButton("کارتابل", '', "$cbdatareject"),
                                $bot->buildInlineKeyBoardButton("پرداخت شده", '', "$cbdataaccept"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $content = array("chat_id" => $chat_id, "text" => "نام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date", 'reply_markup' => $Keyboard);
                            $bot->sendText($content);
                        }
                        sleep(1);
                    }
                    $contenttmp = array('chat_id' => $chat_id, "text" => "پایان پردازش");
                    $bot->sendText($contenttmp);
                }
            }
        }
        break;
//نمایش درخواست های آماده پرداخت
    case "payready":
        if (in_array($chat_id, $accc)) {
            $q_exists = "SELECT * FROM Requests WHERE req_status='cartable' AND is_closed=0";
            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows == 0) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                    $bot->sendText($contenttmp);
                } else {
                    $num = $result->num_rows;
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد : $num");
                    $bot->sendText($contenttmp);
                    while ($row = $result->fetch_assoc()) {
                        $title = $row['title'];
                        $price = $row['price'];
                        $date = $row['date_registered'];
                        $description = $row['description'];
                        $created_by = $row['created_by'];
                        $name = $row['name'];
                        $id = $row['id'];
                        $contract_type = $row['contract_type'];
                        $cbdataaccept = "z$id";
                        $rejectreq = "r$id";

                        if ($contract_type == 'factor') {
                            $project = $row['project'];
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                $bot->buildInlineKeyBoardButton("پرداخت", '', "$cbdataaccept"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $content = array("chat_id" => $chat_id, "text" => "نام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date", 'reply_markup' => $Keyboard);
                            $bot->sendText($content);
                        } else {
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                $bot->buildInlineKeyBoardButton("پرداخت", '', "$cbdataaccept"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $content = array("chat_id" => $chat_id, "text" => "نام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date", 'reply_markup' => $Keyboard);
                            $bot->sendText($content);
                        }
                    }
                }
            }
        }
        break;
//نمایش تمام درخواست ها
    case "everything":
        if (in_array($chat_id, $accc)) {
            delete_get_num($conn, $bb);
            $q_exists = "SELECT * FROM Requests WHERE is_closed=1 OR is_closed=0";
            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows == 0) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                    $bot->sendText($contenttmp);
                } else {
                    $num = $result->num_rows;
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد درخواست ها : $num");
                    $bot->sendText($contenttmp);
                    while ($row = $result->fetch_assoc()) {

                        $title = $row['title'];
                        $price = $row['price'];
                        $contract_type = $row['contract_type'];
                        $description = $row['description'];
                        $reason = $row['reason'];

                        $date = $row['date_registered'];
                        $time = $row['time_registered'];

                        $date_cartable = $row['date_cartable'];
                        $time_cartable = $row['time_cartable'];

                        $date_reject = $row['date_reject'];
                        $time_reject = $row['time_reject'];

                        $date_paid = $row['date_paid'];
                        $time_paid = $row['time_paid'];

                        $state = $row['req_status'];
                        if ($state == "waitacc") {
                            $status = "در انتظار بررسی واحد مالی";
                            $cartable = "-";
                            $paid = "-";
                        } elseif ($state == "cartable") {
                            $status = "کارتابل";
                            $cartable = "+";
                            $paid = "-";
                        } elseif ($state == "rejectacc") {
                            $status = "رد شده توسط واحد مالی";
                            $cartable = "-";
                            $paid = "-";
                        } elseif ($state == "paid") {
                            $status = "پرداخت شده";
                            $cartable = "-";
                            $paid = "+";
                        } else {
                            $content = array("chat_id" => $chat_id, "text" => "اشتباهی رخ داد!");
                            $bot->sendText($content);
                        }
                        if ($state == "rejectacc") {
                            if ($contract_type == 'factor') {
                                $project = $row['project'];
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nدلیل رد شدن درخواست : $reason\nتاریخ رد شدن درخواست : $date_reject\nساعت رد شدن درخواست : $time_reject");
                            } else {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nدلیل رد شدن درخواست : $reason\nتاریخ رد شدن درخواست : $date_reject\nساعت رد شدن درخواست : $time_reject");
                            }
                            $bot->sendText($content);
                        } elseif ($state == "cartable") {
                            if ($contract_type == 'factor') {
                                $project = $row['project'];
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nکارتابل : $cartable\nپرداخت شده : $paid");
                            } else {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nکارتابل : $cartable\nپرداخت شده : $paid");
                            }
                            $bot->sendText($content);
                        } elseif ($state == "waitacc") {
                            if ($contract_type == 'factor') {
                                $project = $row['project'];
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nکارتابل : $cartable\nپرداخت شده : $paid");
                            } else {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nکارتابل : $cartable\nپرداخت شده : $paid");
                            }
                            $bot->sendText($content);
                        } elseif ($state == "paid") {
                            if ($date_cartable == "") {
                                $date_cartable = "ثبت نشده";
                            }
                            if ($time_cartable == "") {
                                $time_cartable = "ثبت نشده";
                            }
                            if ($contract_type == 'factor') {
                                $project = $row['project'];
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nتاریخ تغییر وضعیت به پرداخت شده : $date_paid\nساعت تغییر وضعیت به پرداخت شده : $time_paid\nکارتابل : $cartable\nپرداخت شده : $paid");
                            } else {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable\nتاریخ تغییر وضعیت به پرداخت شده : $date_paid\nساعت تغییر وضعیت به پرداخت شده : $time_paid\nکارتابل : $cartable\nپرداخت شده : $paid");
                            }
                            $bot->sendText($content);
                        }

                        sleep(3);
                    }
                    $content = array("chat_id" => $chat_id, "text" => "پایان پردازش");
                    $bot->sendText($content);
                }
            }
        }
        break;
//مدیریت حساب ها(تنظیمات)
    case "setting":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("افزودن سمت", '', "newpost"),
                $bot->buildInlineKeyBoardButton("تغییر سمت", '', "changepost"),
                $bot->buildInlineKeyBoardButton("تغییر یوزرنیم", '', "changeusername"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
//افزودن یک شخص جدید به ربات
    case "newpost":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);

            $contenttmp = array('chat_id' => $chat_id, "text" => "نام کامل شخص مورد نظر را وارد کنید");
            $bot->sendText($contenttmp);
            $qu = "INSERT INTO Persons (status) VALUES ('getname')";
            $result = $conn->query($qu);
        }
        break;
//تغییر سمت شخص مورد نظر
    case "changepost":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $qu = "INSERT INTO Persons (status) VALUES ('change')";
            $result = $conn->query($qu);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم شخص مورد نظر را بدون علامت @ وارد کنید_حداکثر 30 کاراکتر:");
            $bot->sendText($contenttmp);
        }
        break;
//تغییر یوزرنیم یک شخص
    case "changeusername":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $qu = "INSERT INTO Persons (status) VALUES ('changeus')";
            $result = $conn->query($qu);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم قبلی شخص مورد نظر را بدون علامت @ وارد کنید:");
            $bot->sendText($contenttmp);
        }
        break;
//نمایش وضعیت مدیر پروژه که در حال اضافه شدن است
    case "ppm":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position='project manager' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : مدیر پروژه");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }

        break;
//نمایش وضعیت حسابدار که در حال اضافه شدن است
    case "pacc":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position='accounting' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : حسابداری");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
//نمایش وضعیت مدیر عامل که در حال اضافه شدن است
    case "pseo":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position='CEO' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : مدیر عامل");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
// تأیید ساخت حساب
    case "confcreate":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $row = $result->fetch_assoc();
                    $q_up = "UPDATE Persons SET status=NULL WHERE status='choosing'";
                    $result = $conn->query($q_up);
                    $contenttmp = array('chat_id' => $chat_id, "text" => "کاربر با موفقیت اضافه شد.");
                    $bot->sendText($contenttmp);
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;
//لغو ساخت حساب جدید
    case "canclecreate":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            $contenttmp = array('chat_id' => $chat_id, "text" => "درخواست شما لغو شد.");
            $bot->sendText($contenttmp);
            admin_clipboard($bot, $chat_id);
        }
        break;
//حذف یک کاربر موجود در ربات
    case "changeremove":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $sql = "DELETE FROM Persons WHERE status='changing'";
                    $result = $conn->query($sql);
                    $sql = "DELETE FROM Persons WHERE status='change'";
                    $result = $conn->query($sql);
                }
            }
            $contenttmp = array('chat_id' => $chat_id, "text" => "کاربر مورد نظر از لیست کاربران مجاز این ربات حذف شد.");
            $bot->sendText($contenttmp);
            admin_clipboard($bot, $chat_id);
        }
        break;
//تغییر سمت به مدیر عامل
    case "changetoceo":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position='CEO' WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به مدیر عامل تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            accounting_clipboard($bot, $chat_id);
        }
        break;
//تغییر سمت به مدیر پروژه
    case "changetopm":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position='project manager' WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به مدیر پروژه تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            accounting_clipboard($bot, $chat_id);
        }
        break;
//تغییر سمت به کنترل پروژه
    case "changetocp":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position='control project' WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به کنترل پروژه تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            accounting_clipboard($bot, $chat_id);

        }
        break;
//تغییر سمت به حسابداری
    case "changetoacc":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position='accounting' WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به حسابداری تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            accounting_clipboard($bot, $chat_id);
        }
        break;
//ثبت درخواست جدید مدیر عامل
    case "newreqceo":
        if (in_array($chat_id, $ceoceo)) {
            delete_undone_request($conn, $bb);
            $q_id = "SELECT id FROM Persons WHERE unique_id='$user_id'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $cb = $row['id'];
                settype($cb, "integer");
            }
            $qu = "INSERT INTO Requests (is_closed, req_status, created_by)
      VALUES (2, 'gettype',$cb)";

            $result = $conn->query($qu);

            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("قراردادی", '', "contract"),
                $bot->buildInlineKeyBoardButton("فاکتوری", '', "factor"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "نوع قرارداد را مشخص کنید:", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
//        خروجی گرفتن اکسل از گزارشات
    case "adminexcel":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("پرداخت شده ها", '', "adminexcelpaid"),
                $bot->buildInlineKeyBoardButton("درخواست های باز", '', "adminexcelopen"),
                $bot->buildInlineKeyBoardButton("تمام درخواست ها", '', "adminexcelall"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "گزارش مورد نظر خود را انتخاب کنید(1000 مورد آخر خروجی گرفته میشود):", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;

    case "adminexcelopen":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_open($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;

    case "adminexcelpaid":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_paid($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;

    case "adminexcelall":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_all($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;
}
$conn->close();
?>

