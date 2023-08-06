<?php 
include "BaleAPIv2.php";
include "jdf.php";

$token="621029901:jTnqofwUsMtF5wKAB9MQHeyJgCtY863VfsVMXd8q";

// Set session variables

$bot=new balebot($token);
$chat_id=$bot->ChatID();
$user_id=$bot->UserID();
$Text_orgi=$bot->Text();
$callback_data=$bot->CallBack_Data();

$username=$bot->username();

// $today_date = gregorian_to_jalali(date("Y"),date("m"),date("d"), "-");

// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------      DATABASE INFORMATIONS     ----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------

$servername = "localhost";
$usern = "balebtir_dev";
$password = "1P,2Hs!xan).";
// Create connection
$conn = new mysqli($servername, $usern, $password, "balebtir_bale_pro");
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8mb4");



$sql  = "SELECT unique_id FROM Persons WHERE position='CEO' OR position='admin'";
if ($result = $conn->query($sql)) {
    if($result->num_rows!=0){
        while($row = $result->fetch_assoc()) {
            $data[] = $row['unique_id'];
        }
    }else{
        $data = [];
    }
}
foreach ($data as $u) {
    $contenttmp = array('chat_id' => $u,"text"=>"گزارشات روزانه:");
    $bot->sendText($contenttmp);
    $sql = "SELECT * FROM Requests WHERE req_status='cartable'";
    if ($result = $conn->query($sql)){
        if($result->num_rows!=0){
            while($row = $result->fetch_assoc()) {
                $name = $row['name'];
                $title = $row['title'];
                $description = $row['description'];
                $price = $row['price'];
                $date = $row['date_registered'];
                $time = $row['time_registered'];
                $contract_type = $row['contract_type'];
                $date_cartable = $row['date_cartable'];
                $time_cartable = $row['time_cartable'];
                $status = "کارتابل";
                if ($contract_type == 'factor') {
                    $project = $row['project'];
                    $content = array("chat_id" => $u, "text" => "نام : $name\nوضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price ریال\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable");
                } else {
                    $content = array("chat_id" => $u, "text" => "نام : $name\nوضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price ریال\nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nتاریخ تغییر وضعیت به کارتابل : $date_cartable\nساعت تغییر وضعیت به کارتابل : $time_cartable");
                }
                $bot->sendText($content);
                sleep(2);
            }
            $contenttmp = array('chat_id' => $u,"text"=>"پایان پردازش.");
            $bot->sendText($contenttmp);
        }else{
            $contenttmp = array('chat_id' => $u,"text"=>"موردی وجود ندارد.");
            $bot->sendText($contenttmp);
        }
    }
}
?>