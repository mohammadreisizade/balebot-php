<?php 
include "BaleAPIv2.php";
include "jdf.php";

$token="804113617:F6iDCkhZIWXnGbjkFIPVVz3JgtDS6BmpB6FFU5Al";

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
$conn = new mysqli($servername, $usern, $password, "balebtir_balebot");
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}



$sql  = "SELECT unique_id FROM Persons WHERE position='CEO'";
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
                $contract_type = $row['contract_type'];

                if($contract_type=='project'){
                    $project = $row['project'];
                    $content=array("chat_id" =>$u,"text" =>"وضعیت : کارتابل\nنام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date");
                    $bot->sendText($content);
                }
                $content=array("chat_id" =>$u,"text" =>"وضعیت : کارتابل\nنام : $name\nعنوان : $title\nتوضیحات : $description\nمبلغ : $price\nتاریخ درخواست : $date");
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