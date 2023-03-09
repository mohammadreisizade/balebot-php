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

$today_date = gregorian_to_jalali(date("Y"),date("m"),date("d"), "-");

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

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------UPDATE UNIQUE ID INSTEAD OF USERNAME----------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------


$q = "SELECT username FROM Persons";
  if ($result = $conn->query($q)) {
    while ($row = $result->fetch_assoc()) {
          $data[] = $row['username'];
      }
  }
  if (in_array($username, $data)){
    $sql  = "SELECT * FROM Persons WHERE username='$username'";

    if ($result = $conn->query($sql)) {{
      $row = $result->fetch_assoc();
      $id = $row['unique_id'];
    
      if(!isset($id)){
        $query = "UPDATE Persons SET unique_id='$user_id' WHERE username='$username'";
        $result = $conn->query($query);
      }
    }
  }  
}
else{
    $contenttmp = array('chat_id' => $chat_id,"text"=>"آیدی شما در ربات تعریف نشده است!");
    $bot->sendText($contenttmp);
  }




// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------ FOR CONTROL PROJECT ----------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if ($callback_data[0]=="a"){
  $getcbdata = str_replace("a", "", $callback_data);

  settype($getcbdata, "integer");
  $q_up = "UPDATE Requests SET req_status='waitacc' WHERE id=$getcbdata";
  if($result = $conn->query($q_up)){
    $q = "SELECT title FROM Requests WHERE id=$getcbdata";
    if($result = $conn->query($q)){
      $row = $result->fetch_assoc();
      $title = $row['title'];
      $content=array("chat_id" =>$chat_id,"text" =>"درخواست : $title تأیید شد.");
      $bot->sendText($content);
    }
  }
  
}elseif($callback_data[0]=='r'){
  $getcbdata = str_replace('r', '', $callback_data);
  settype($getcbdata, "integer");
  $q_up = "UPDATE Requests SET req_status='rejectcp' WHERE id=$getcbdata";
  if($result = $conn->query($q_up)){
    $q = "SELECT title FROM Requests WHERE id=$getcbdata";
    if($result = $conn->query($q)){
      $row = $result->fetch_assoc();
      $title = $row['title'];
      $content=array("chat_id" =>$chat_id,"text" =>"درخواست : $title رد شد.");
      $bot->sendText($content);
    }
  }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------- FOR ACCOUNTING ------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if ($callback_data[0]=="x"){
  $getcbdata = str_replace("x", "", $callback_data);

  settype($getcbdata, "integer");
  $q_up = "UPDATE Requests SET req_status='paid' WHERE id=$getcbdata";
  if($result = $conn->query($q_up)){
    $q_up = "UPDATE Requests SET is_closed=1 WHERE id=$getcbdata";
    $result = $conn->query($q_up);
    $q = "SELECT title FROM Requests WHERE id=$getcbdata";
    if($result = $conn->query($q)){
      $row = $result->fetch_assoc();
      $title = $row['title'];
      $content=array("chat_id" =>$chat_id,"text" =>"درخواست : $title پرداخت شد.");
      $bot->sendText($content);
    }
  }
  
}elseif($callback_data[0]=='y'){
  $getcbdata = str_replace('y', '', $callback_data);
  settype($getcbdata, "integer");
  $q_up = "UPDATE Requests SET req_status='cartable' WHERE id=$getcbdata";
  if($result = $conn->query($q_up)){
    $q = "SELECT title FROM Requests WHERE id=$getcbdata";
    if($result = $conn->query($q)){
      $row = $result->fetch_assoc();
      $title = $row['title'];
      $content=array("chat_id" =>$chat_id,"text" =>"درخواست : $title به وضعیت کارتابل تغییر یافت.");
      $bot->sendText($content);
    }
  }
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------- FOR ACCOUNTING READY TO PAY -----------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if($callback_data[0]=='z'){
  $getcbdata = str_replace('z', '', $callback_data);
  settype($getcbdata, "integer");
  $q_up = "UPDATE Requests SET req_status='paid' WHERE id=$getcbdata";
  if($result = $conn->query($q_up)){
    $q_up = "UPDATE Requests SET is_closed=1 WHERE id=$getcbdata";
    $result = $conn->query($q_up);
    $q = "SELECT title FROM Requests WHERE id=$getcbdata";
    if($result = $conn->query($q)){
      $row = $result->fetch_assoc();
      $title = $row['title'];
      $content=array("chat_id" =>$chat_id,"text" =>"درخواست : $title پرداخت شد.");
      $bot->sendText($content);
    }
  }
}
// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------

// برای حسابداری
// ------------------------------------------------------------- QUERY FOR ACCOUNTING ---------------------------------------------------------------------------

$sql  = "SELECT unique_id FROM Persons WHERE position='accounting'";
if ($result = $conn->query($sql)) {
   while ($row = $result->fetch_assoc()) {
        $accc[] = $row['unique_id'];
    }
}

// --------------------------------------------------------------------------------------------------------------------------------------------------------------

if (in_array($chat_id, $accc)){
  if ($Text_orgi=="/start"){
    $sql = "SELECT * FROM Persons WHERE status='changing'";
    if ($result = $conn->query($sql)){
      if($result->num_rows!=0){
        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
        $result = $conn->query($q_up); 
      }
    }
    $sql = "SELECT * FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR status='getpos' OR status='change'";
    if ($result = $conn->query($sql)) {
      if($result->num_rows!=0){
        $row = $result->fetch_assoc();
        $sql = "DELETE FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR status='getpos' OR status='change'";
        $result = $conn->query($sql);
      }
    }

    $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
    if ($result = $conn->query($q_id)) {
      $row = $result->fetch_assoc();
      $bb = $row['id'];
    }
    $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
    if ($result = $conn->query($q_exists)) {
      if($result->num_rows!=0){
        $row = $result->fetch_assoc();
        $ccc = $row['id'];
        settype($ccc, "integer");
        $d_q = "DELETE FROM Requests WHERE id=$ccc";
        if ($conn->query($d_q) === TRUE) {
          echo "Record deleted successfully";
        }
      }
    }

    $inlineKeyboardoption =	[
      $bot->buildInlineKeyBoardButton("ثبت درخواست", '',"newreqacc"),
      $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqacc"),
      $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '',"everything"),
      $bot->buildInlineKeyBoardButton("تنظیمات", '',"setting"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' =>$Keyboard);
    $bot->sendText($contenttmp);
  }else{
    $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
    if ($result = $conn->query($q_id)) {
      $row = $result->fetch_assoc();
      $bb = $row['id'];
    }
    $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
    if ($result = $conn->query($q_exists)) {
      
      if($result->num_rows!=0){
        $row = $result->fetch_assoc();
        $ccc = $row['req_status'];
        if ($ccc == "gettitle"){
          $q_up = "UPDATE Requests SET title='$Text_orgi' WHERE created_by=$bb AND req_status='gettitle'";
          $result = $conn->query($q_up);
          $q_st = "UPDATE Requests SET req_status='getprice' WHERE created_by=$bb AND req_status='gettitle'";
          $r = $conn->query($q_st);
          $contenttmp = array('chat_id' => $chat_id,"text"=>"مبلغ را وارد کنید:");
          $bot->sendText($contenttmp);
        }elseif($ccc == "getprice"){
          $q_up = "UPDATE Requests SET price='$Text_orgi' WHERE created_by=$bb AND req_status='getprice'";
          $result = $conn->query($q_up);
          $q_st = "UPDATE Requests SET req_status='getdesc' WHERE created_by=$bb AND req_status='getprice'";
          $r = $conn->query($q_st);
          if($row['contract_type'] == 'factor'){
            $contenttmp = array('chat_id' => $chat_id,"text"=>"توضیحات:");
            $bot->sendText($contenttmp);
          }else{
            $contenttmp = array('chat_id' => $chat_id,"text"=>"توضیحات(صورت وضعیت، علی الحساب، پیش پرداخت، سپرده های قرارداد):");
            $bot->sendText($contenttmp);
          }
        }elseif($ccc == "getdesc"){
          $q_up = "UPDATE Requests SET description='$Text_orgi' WHERE created_by=$bb AND req_status='getdesc'";
          $result = $conn->query($q_up);
          if($row['contract_type'] == 'factor'){
            $q_st = "UPDATE Requests SET req_status='getproj' WHERE created_by=$bb AND req_status='getdesc'";
            $r = $conn->query($q_st);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"پروژه:");
            $bot->sendText($contenttmp);
          }else{
            $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
            if ($result = $conn->query($all)) {
              $row = $result->fetch_assoc();
              $t = $row['title'];
              $p = $row['price'];
              $d = $row['description'];

              $content=array("chat_id" =>$chat_id,"text" =>"شماره قرارداد : $t\nتوضیحات : $d\nمبلغ : $p");
              $bot->sendText($content);

              $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
              $result = $conn->query($q_up);

              $inlineKeyboardoption =	[
                $bot->buildInlineKeyBoardButton("تأیید", '',"conf_pm"),
                $bot->buildInlineKeyBoardButton("انصراف", '',"cancle_pm"),
              ];
              $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
              $contenttmp = array('chat_id' => $chat_id,"text"=>"ثبت درخواست", 'reply_markup' =>$Keyboard);
              $bot->sendText($contenttmp); 
            }
          }

        }elseif($ccc == "getproj"){
          $q_up = "UPDATE Requests SET project='$Text_orgi' WHERE created_by=$bb AND req_status='getproj'";
          $result = $conn->query($q_up);

          $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
          if ($result = $conn->query($all)) {
            $row = $result->fetch_assoc();
            $t = $row['title'];
            $p = $row['price'];
            $d = $row['description'];
            $pr = $row['project'];

            $content=array("chat_id" =>$chat_id,"text" =>"عنوان : $t\nتوضیحات : $d\nپروژه : $pr\nمبلغ : $p");
            $bot->sendText($content);

            $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
            $result = $conn->query($q_up);

            $inlineKeyboardoption =	[
              $bot->buildInlineKeyBoardButton("تأیید", '',"conf_pm"),
              $bot->buildInlineKeyBoardButton("انصراف", '',"cancle_pm"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"ثبت درخواست", 'reply_markup' =>$Keyboard);
            $bot->sendText($contenttmp); 
          }
        }
      }
    }
      $q_id = "SELECT * FROM Persons WHERE status='getname' OR status='getuser' OR status='change'";
      if ($result = $conn->query($q_id)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          if ($row['status']=='getname'){
            $q_up = "UPDATE Persons SET name='$Text_orgi' WHERE status='getname'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='getuser' WHERE status='getname'";
            $result = $conn->query($q_up);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"یوزرنیم شخص مورد نظر را بدون علامت @ وارد کنید:");
            $bot->sendText($contenttmp); 
          }elseif($row['status']=='getuser'){
            $q_up = "UPDATE Persons SET username='$Text_orgi' WHERE status='getuser'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='getpos' WHERE status='getuser'";
            $result = $conn->query($q_up);
            $inlineKeyboardoption =	[
              $bot->buildInlineKeyBoardButton("مدیر پروژه", '',"ppm"),
              $bot->buildInlineKeyBoardButton("حسابداری", '',"pacc"),
              $bot->buildInlineKeyBoardButton("کنترل پروژه", '',"pcp"),
              $bot->buildInlineKeyBoardButton("مدیر عامل", '',"pseo"),
              
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"سمت شخص مورد نظر را از بین گزینه های زیر انتخاب کنید:", 'reply_markup' =>$Keyboard);
            $bot->sendText($contenttmp); 
          }elseif($row['status']=='change'){
            $sql = "SELECT * FROM Persons WHERE username='$Text_orgi'";
            if ($res = $conn->query($sql)){
              if($res->num_rows==0){
                $d_q = "DELETE FROM Persons WHERE status='change'";
                $result = $conn->query($d_q);
                $contenttmp = array('chat_id' => $chat_id,"text"=>"این یوزرنیم در این ربات تعریف نشده است!");
                $bot->sendText($contenttmp);
                $inlineKeyboardoption =	[
                  $bot->buildInlineKeyBoardButton("ثبت درخواست", '',"newreqacc"),
                  $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqacc"),
                  $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '',"everything"),
                  $bot->buildInlineKeyBoardButton("تنظیمات", '',"setting"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' =>$Keyboard);
                $bot->sendText($contenttmp);
                  
              }else{

                $row = $result->fetch_assoc();
                if($row['position']=='project manager'){
                  $stat = "مدیر پروژه";
                }elseif($row['position']=='control project'){
                  $stat = "کنترل پروژه";
                }elseif($row['position']=='accounting'){
                  $stat = "حسابداری";
                }elseif($row['position']=='CEO'){
                  $stat = "مدیر عامل";
                }
                $q_up = "UPDATE Persons SET status='changing' WHERE username='$Text_orgi'";
                $result = $conn->query($q_up);
                $sql = "SELECT * FROM Persons WHERE status='change'";
                if ($result = $conn->query($sql)){
                  if($result->num_rows!=0){
                    $d_q = "DELETE FROM Persons WHERE status='change'";
                    $result = $conn->query($d_q);
                  }
                }


                $inlineKeyboardoption =	[
                  $bot->buildInlineKeyBoardButton("مدیر پروژه", '',"changetopm"),
                  $bot->buildInlineKeyBoardButton("حسابداری", '',"changetoacc"),
                  $bot->buildInlineKeyBoardButton("کنترل پروژه", '',"changetocp"),
                  $bot->buildInlineKeyBoardButton("مدیر عامل", '',"changetoceo"),
                  $bot->buildInlineKeyBoardButton("حذف این شخص", '',"changeremove"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id,"text"=>"سمت این شخص $stat است.سمت مدنظر خود را برای این شخص انتخاب کنید:", 'reply_markup' =>$Keyboard);
                $bot->sendText($contenttmp); 
              }
            }
          }
        }
      }
    }
  }


// برای کنترل پروژه
// -------------------------------------------------------------QUERY FOR CONTROL PROJECT------------------------------------------------------------------------

$sql  = "SELECT unique_id FROM Persons WHERE position='control project'";
if ($result = $conn->query($sql)) {
   while ($row = $result->fetch_assoc()) {
        $control_project[] = $row['unique_id'];
    }
}

// --------------------------------------------------------------------------------------------------------------------------------------------------------------

if (in_array($chat_id, $control_project)){
  if ($Text_orgi=="/start"){
    $inlineKeyboardoption =	[
      $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqcp"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id,"text"=>"برای نمایش درخواست ها کلیک کنید.", 'reply_markup' =>$Keyboard);
    $bot->sendText($contenttmp);
  }
}





// برای مدیر پروژه
// -------------------------------------------------------------QUERY FOR PROJECT MANAGERS------------------------------------------------------------------------
$sql  = "SELECT unique_id FROM Persons WHERE position='project manager'";
if ($result = $conn->query($sql)) {
   while ($row = $result->fetch_assoc()) {
        $project_managers[] = $row['unique_id'];
    }
}
if (in_array($chat_id, $project_managers)){

  if ($Text_orgi=="/start"){

      // ----------------------------------------------------------- QUERIES --------------------------------------------------------------------------------------


      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $bb = $row['id'];
      }
      $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
      if ($result = $conn->query($q_exists)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          $ccc = $row['id'];
          settype($ccc, "integer");
          $d_q = "DELETE FROM Requests WHERE id=$ccc";
          if ($conn->query($d_q) === TRUE) {
            echo "Record deleted successfully";
          }
        }
      }

    $inlineKeyboardoption =	[
      $bot->buildInlineKeyBoardButton("ثبت درخواست پرداخت", '',"paymentreq"),
      $bot->buildInlineKeyBoardButton("درخواست های من", '',"myreq"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' =>$Keyboard);
    $bot->sendText($contenttmp);
  }else{
      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $bb = $row['id'];
      }
      $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
      if ($result = $conn->query($q_exists)) {
        
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          $ccc = $row['req_status'];
          if ($ccc == "gettitle"){
            $q_up = "UPDATE Requests SET title='$Text_orgi' WHERE created_by=$bb AND req_status='gettitle'";
            $result = $conn->query($q_up);
            $q_st = "UPDATE Requests SET req_status='getprice' WHERE created_by=$bb AND req_status='gettitle'";
            $r = $conn->query($q_st);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"مبلغ را وارد کنید:");
            $bot->sendText($contenttmp);
          }elseif($ccc == "getprice"){
            $q_up = "UPDATE Requests SET price='$Text_orgi' WHERE created_by=$bb AND req_status='getprice'";
            $result = $conn->query($q_up);
            $q_st = "UPDATE Requests SET req_status='getdesc' WHERE created_by=$bb AND req_status='getprice'";
            $r = $conn->query($q_st);
            if($row['contract_type'] == 'factor'){
              $contenttmp = array('chat_id' => $chat_id,"text"=>"توضیحات:");
              $bot->sendText($contenttmp);
            }else{
              $contenttmp = array('chat_id' => $chat_id,"text"=>"توضیحات(صورت وضعیت، علی الحساب، پیش پرداخت، سپرده های قرارداد):");
              $bot->sendText($contenttmp);
            }
          }elseif($ccc == "getdesc"){
            $q_up = "UPDATE Requests SET description='$Text_orgi' WHERE created_by=$bb AND req_status='getdesc'";
            $result = $conn->query($q_up);
            if($row['contract_type'] == 'factor'){
              $q_st = "UPDATE Requests SET req_status='getproj' WHERE created_by=$bb AND req_status='getdesc'";
              $r = $conn->query($q_st);
              $contenttmp = array('chat_id' => $chat_id,"text"=>"پروژه:");
              $bot->sendText($contenttmp);
            }else{
              $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
              if ($result = $conn->query($all)) {
                $row = $result->fetch_assoc();
                $t = $row['title'];
                $p = $row['price'];
                $d = $row['description'];

                $content=array("chat_id" =>$chat_id,"text" =>"شماره قرارداد : $t\nتوضیحات : $d\nمبلغ : $p");
                $bot->sendText($content);

                $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
                $result = $conn->query($q_up);

                $inlineKeyboardoption =	[
                  $bot->buildInlineKeyBoardButton("تأیید", '',"conf_pm"),
                  $bot->buildInlineKeyBoardButton("انصراف", '',"cancle_pm"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id,"text"=>"ثبت درخواست", 'reply_markup' =>$Keyboard);
                $bot->sendText($contenttmp); 
              }
            }

          }elseif($ccc == "getproj"){
            $q_up = "UPDATE Requests SET project='$Text_orgi' WHERE created_by=$bb AND req_status='getproj'";
            $result = $conn->query($q_up);

            $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
            if ($result = $conn->query($all)) {
              $row = $result->fetch_assoc();
              $t = $row['title'];
              $p = $row['price'];
              $d = $row['description'];
              $pr = $row['project'];

              $content=array("chat_id" =>$chat_id,"text" =>"عنوان : $t\nتوضیحات : $d\nپروژه : $pr\nمبلغ : $p");
              $bot->sendText($content);

              $q_up = "UPDATE Requests SET req_status='choosing' WHERE created_by=$bb AND is_closed=2";
              $result = $conn->query($q_up);

              $inlineKeyboardoption =	[
                $bot->buildInlineKeyBoardButton("تأیید", '',"conf_pm"),
                $bot->buildInlineKeyBoardButton("انصراف", '',"cancle_pm"),
              ];
              $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
              $contenttmp = array('chat_id' => $chat_id,"text"=>"ثبت درخواست", 'reply_markup' =>$Keyboard);
              $bot->sendText($contenttmp); 
            }
          }
        }
      }
  }
}



switch ($callback_data) {
  case "paymentreq":
    if (in_array($chat_id, $project_managers)){

      // ----------------------------------------------------------- QUERIES -------------------------------------------------------------------------------


      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $bb = $row['id'];
        settype($bb, "integer");
      }
      $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
      if ($result = $conn->query($q_exists)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          $ccc = $row['id'];
          settype($ccc, "integer");
          $d_q = "DELETE FROM Requests WHERE id=$ccc";
          if ($conn->query($d_q) === TRUE) {
            echo "Record deleted successfully";
          }
        }
      }

      $q_id = "SELECT id FROM Persons WHERE unique_id='$user_id'";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $cb = $row['id'];
        settype($cb, "integer");
      }
      $qu = "INSERT INTO Requests (is_closed, req_status, created_by)
      VALUES (2, 'gettype',$cb)";
      if ($result = $conn->query($qu)) {

      }else{
        $content=array("chat_id" =>$chat_id,"text" =>"مشکلی پیش آمد!");
        $bot->sendText($content);
      }
      $inlineKeyboardoption =	[
        $bot->buildInlineKeyBoardButton("قراردادی", '',"contract"),
        $bot->buildInlineKeyBoardButton("فاکتوری", '',"factor"),
      ];
      $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"نوع قرارداد را مشخص کنید:", 'reply_markup' =>$Keyboard);
      $bot->sendText($contenttmp);      
    }
    break;


  case "myreq":
    if (in_array($chat_id, $project_managers)){
      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
    if ($result = $conn->query($q_id)) {
      $row = $result->fetch_assoc();
      $bb = $row['id'];
    }
    $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=0";
    if ($result = $conn->query($q_exists)) {
      if($result->num_rows==0){
        $contenttmp = array('chat_id' => $chat_id,"text"=>"موردی وجود ندارد.");
        $bot->sendText($contenttmp);
    }else{
      while ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $state = $row['req_status'];
        $price = $row['price'];
        $date = $row['date_registered'];
        $contract_type = $row['contract_type'];
        $description = $row['description'];
        if($state=="wait"){
          $status = "در انتظار تأیید کنترل پروژه";
          $control_p = "در انتظار";
          // $accounting = "در انتظار";
          $cartable = "-";
          $paid = "-";
        }elseif($state=="waitacc"){
          $status = "در انتظار تأیید حسابداری";
          $control_p = "تأیید";
          // $accounting = "در انتظار";
          $cartable = "-";
          $paid = "-";
        }elseif($state=="cartable"){
            $status = "کارتابل";
            $control_p = "تأیید";
            // $accounting = "تأیید";
            $cartable = "+";
            $paid = "-";
        }elseif($state=="paid"){
          $status = "پرداخت شده";
          $control_p = "تأیید";
          // $accounting = "تأیید";
          $cartable = "-";
          $paid = "+";
        }elseif($state=="rejectcp"){
          $status = "رد شده توسط کنترل پروژه";
          $control_p = "عدم تأیید";
          // $accounting = "در انتظار";
          $cartable = "-";
          $paid = "-";
        }elseif($state=="rejectacc"){
          $status = "رد شده توسط حسابداری";
          $control_p = "تأیید";
          // $accounting = "عدم تأیید";
          $cartable = "-";
          $paid = "-";
        }else{
          $content=array("chat_id" =>$chat_id,"text" =>"اشتباهی رخ داد!");
          $bot->sendText($content);
        }
        if($contract_type=='factor'){
          $project = $row['project'];
          $content=array("chat_id" =>$chat_id,"text" =>"وضعیت : $status\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date\nکنترل پروژه : $control_p\nحسابداری : $accounting\nکارتابل : $cartable\nپرداخت شده : $paid");
          $bot->sendText($content);
        }else{
          $content=array("chat_id" =>$chat_id,"text" =>"وضعیت : $status\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date\nکنترل پروژه : $control_p\nحسابداری : $accounting\nکارتابل : $cartable\nپرداخت شده : $paid");
          $bot->sendText($content);
        }
        sleep(3);
    }
  }
}

      // $content=array("chat_id" =>$chat_id,"text" =>"this is myreq");
      // $bot->sendText($content);
    }
    break;



  case "contract":
    if (in_array($chat_id, $project_managers) || in_array($chat_id, $accc)){


      // ----------------------------------------------------------- QUERIES -------------------------------------------------------------------------------

      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $bb = $row['id'];
      }
      $q_up = "UPDATE Requests SET contract_type='contract' WHERE created_by=$bb AND req_status='gettype'";
      $result = $conn->query($q_up);
      $q_st = "UPDATE Requests SET req_status='gettitle' WHERE created_by=$bb AND req_status='gettype'";
      $r = $conn->query($q_st);

      
      $content=array("chat_id" =>$chat_id,"text" =>"شماره قرارداد را وارد کنید:");
      $bot->sendText($content);
    }
    break;
  case "factor":
    if (in_array($chat_id, $project_managers) || in_array($chat_id, $accc)){

      // ----------------------------------------------------------- QUERIES -------------------------------------------------------------------------------

      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $bb = $row['id'];
      }
      $q_up = "UPDATE Requests SET contract_type='factor' WHERE created_by=$bb AND req_status='gettype'";
      $result = $conn->query($q_up);
      $q_st = "UPDATE Requests SET req_status='gettitle' WHERE created_by=$bb AND req_status='gettype'";
      $r = $conn->query($q_st);

      
      $content=array("chat_id" =>$chat_id,"text" =>"عنوان را وارد کنید:");
      $bot->sendText($content);
    }
    break;


  case "conf_pm":
    if(in_array($chat_id, $project_managers) || in_array($chat_id, $accc)){
    $q_id = "SELECT * FROM Persons WHERE unique_id=$user_id";
    if ($result = $conn->query($q_id)) {
      $row = $result->fetch_assoc();
      $bb = $row['id'];
      $n = $row['name'];
    }
    $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=2";
    if ($result = $conn->query($q_exists)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();

          if($row['req_status']=='choosing'){
            $q_up = "UPDATE Requests SET req_status='wait' WHERE created_by=$bb AND is_closed=2";
            $result = $conn->query($q_up);

            $q_st = "UPDATE Requests SET date_registered='$today_date' WHERE created_by=$bb AND is_closed=2";
            $r = $conn->query($q_st);

            $q_st = "UPDATE Requests SET name='$n' WHERE created_by=$bb AND is_closed=2";
            $r = $conn->query($q_st);

            $q_st = "UPDATE Requests SET is_closed=0 WHERE created_by=$bb AND is_closed=2";
            $r = $conn->query($q_st);


            $content=array("chat_id" =>$chat_id,"text" =>"درخواست شما با موفقیت ثبت شد.");
            $bot->sendText($content);
          }
          else{
            $content=array("chat_id" =>$chat_id,"text" =>"درخواست ناقص است!");
            $bot->sendText($content);
          }
        }else{
          $content=array("chat_id" =>$chat_id,"text" =>"موردی وجود ندارد");
          $bot->sendText($content);
        }
      }
      if(in_array($chat_id, $project_managers)){
        $inlineKeyboardoption =	[
          $bot->buildInlineKeyBoardButton("ثبت درخواست پرداخت", '',"paymentreq"),
          $bot->buildInlineKeyBoardButton("درخواست های من", '',"myreq"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' =>$Keyboard);
        $bot->sendText($contenttmp);
      }else{
        $inlineKeyboardoption =	[
          $bot->buildInlineKeyBoardButton("ثبت درخواست", '',"newreqacc"),
          $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqacc"),
          $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '',"everything"),
          $bot->buildInlineKeyBoardButton("تنظیمات", '',"setting"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' =>$Keyboard);
        $bot->sendText($contenttmp);
      }
    }
    break;
  case "cancle_pm":
    if(in_array($chat_id, $project_managers) || in_array($chat_id, $accc)){
    $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
    if ($result = $conn->query($q_id)) {
      $row = $result->fetch_assoc();
      $bb = $row['id'];
    }
    $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
    if ($result = $conn->query($q_exists)) {
      $row = $result->fetch_assoc();
      if(isset($row)){
        $ccc = $row['id'];
        settype($ccc, "integer");
        $d_q = "DELETE FROM Requests WHERE id=$ccc";
        if ($conn->query($d_q) === TRUE) {
          $contenttmp = array('chat_id' => $chat_id,"text"=>"درخواست شما لغو شد.");
          $bot->sendText($contenttmp);
          if(in_array($chat_id, $project_managers)){
            $inlineKeyboardoption =	[
              $bot->buildInlineKeyBoardButton("ثبت درخواست پرداخت", '',"paymentreq"),
              $bot->buildInlineKeyBoardButton("درخواست های من", '',"myreq"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' =>$Keyboard);
            $bot->sendText($contenttmp);
          }else{
            $inlineKeyboardoption =	[
              $bot->buildInlineKeyBoardButton("ثبت درخواست", '',"newreqacc"),
              $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqacc"),
              $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '',"everything"),
              $bot->buildInlineKeyBoardButton("تنظیمات", '',"setting"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' =>$Keyboard);
            $bot->sendText($contenttmp);
          }
        }
      }
    }
  }
    break;
  case "openreqcp":
    if(in_array($chat_id, $control_project)){

      $q_exists = "SELECT * FROM Requests WHERE req_status='wait' AND is_closed=0 ORDER BY date_registered DESC LIMIT 15";
      if ($result = $conn->query($q_exists)) {
        if($result->num_rows==0){
          $contenttmp = array('chat_id' => $chat_id,"text"=>"موردی وجود ندارد.");
          $bot->sendText($contenttmp);
        }else{
        $num = $result->num_rows;
        $contenttmp = array('chat_id' => $chat_id,"text"=>"تعداد درخواست های قابل نمایش (حداکثر 15)  : $num");
        $bot->sendText($contenttmp);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"در حال پردازش...");
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
          $cbdatareject = "r$id";
          $cbdataaccept = "a$id";

          if($contract_type=='factor'){
            $project = $row['project'];
            $inlineKeyboardoption =	[
              $bot->buildInlineKeyBoardButton("تأیید", '',"$cbdataaccept"),
              $bot->buildInlineKeyBoardButton("عدم تأیید", '',"$cbdatareject"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $content=array("chat_id" =>$chat_id,"text" =>"نام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date", 'reply_markup' =>$Keyboard);
            $bot->sendText($content);
          }else{
            $inlineKeyboardoption =	[
              $bot->buildInlineKeyBoardButton("تأیید", '',"$cbdataaccept"),
              $bot->buildInlineKeyBoardButton("عدم تأیید", '',"$cbdatareject"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $content=array("chat_id" =>$chat_id,"text" =>"نام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date", 'reply_markup' =>$Keyboard);
            $bot->sendText($content);
          }
          sleep(1);
      }
      $contenttmp = array('chat_id' => $chat_id,"text"=>"پایان پردازش");
      $bot->sendText($contenttmp);
    }
  }

}

    break;
  case "newreqacc":
    if (in_array($chat_id, $accc)){

      // ----------------------------------------------------------- QUERIES -------------------------------------------------------------------------------

      $q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $bb = $row['id'];
        settype($bb, "integer");
      }
      $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=2";
      if ($result = $conn->query($q_exists)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          $ccc = $row['id'];
          settype($ccc, "integer");
          $d_q = "DELETE FROM Requests WHERE id=$ccc";
          if ($conn->query($d_q) === TRUE) {
            echo "Record deleted successfully";
          }
        }
      }

      $q_id = "SELECT id FROM Persons WHERE unique_id='$user_id'";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $cb = $row['id'];
        settype($cb, "integer");
      }
      $qu = "INSERT INTO Requests (is_closed, req_status, created_by)
      VALUES (2, 'gettype',$cb)";
      if ($result = $conn->query($qu)) {

      }else{
        $content=array("chat_id" =>$chat_id,"text" =>"مشکلی پیش آمد!");
        $bot->sendText($content);
      }
      $inlineKeyboardoption =	[
        $bot->buildInlineKeyBoardButton("قراردادی", '',"contract"),
        $bot->buildInlineKeyBoardButton("فاکتوری", '',"factor"),
      ];
      $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"نوع قرارداد را مشخص کنید:", 'reply_markup' =>$Keyboard);
      $bot->sendText($contenttmp); 
    }
    break;
  case "openreqacc":
    if (in_array($chat_id, $accc)){

      $inlineKeyboardoption =	[
        $bot->buildInlineKeyBoardButton("درخواست های باز حسابداری", '',"openreqaccc"),
        $bot->buildInlineKeyBoardButton("آماده پرداخت", '',"payready"),
      ];
      $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' =>$Keyboard);
      $bot->sendText($contenttmp); 
    }
    break;
    case "openreqaccc":
      if (in_array($chat_id, $accc)){
        $q_exists = "SELECT * FROM Requests WHERE req_status='waitacc' AND is_closed=0 ORDER BY date_registered DESC LIMIT 15";
        if ($result = $conn->query($q_exists)) {
          if($result->num_rows==0){
            $contenttmp = array('chat_id' => $chat_id,"text"=>"موردی وجود ندارد.");
            $bot->sendText($contenttmp);
          }else{
          $num = $result->num_rows;
          $contenttmp = array('chat_id' => $chat_id,"text"=>"در حال پردازش...");
          $bot->sendText($contenttmp);
          $contenttmp = array('chat_id' => $chat_id,"text"=>"تعداد درخواست های قابل نمایش (حداکثر 15)  : $num");
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
  
            if($contract_type=='factor'){
              $project = $row['project'];
              $inlineKeyboardoption =	[
                $bot->buildInlineKeyBoardButton("کارتابل", '',"$cbdatareject"),
                $bot->buildInlineKeyBoardButton("پرداخت شده", '',"$cbdataaccept"),
              ];
              $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
              $content=array("chat_id" =>$chat_id,"text" =>"نام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date", 'reply_markup' =>$Keyboard);
              $bot->sendText($content);
            }else{
              $inlineKeyboardoption =	[
                $bot->buildInlineKeyBoardButton("کارتابل", '',"$cbdatareject"),
                $bot->buildInlineKeyBoardButton("پرداخت شده", '',"$cbdataaccept"),
              ];
              $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
              $content=array("chat_id" =>$chat_id,"text" =>"نام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date", 'reply_markup' =>$Keyboard);
              $bot->sendText($content);
            }
            sleep(1);
          }
          $contenttmp = array('chat_id' => $chat_id,"text"=>"پایان پردازش");
          $bot->sendText($contenttmp);
        }
      }
    }
    break;

    case "payready":
      if (in_array($chat_id, $accc)){
        $q_exists = "SELECT * FROM Requests WHERE req_status='cartable' AND is_closed=0";
        if ($result = $conn->query($q_exists)) {
          if($result->num_rows==0){
            $contenttmp = array('chat_id' => $chat_id,"text"=>"موردی وجود ندارد.");
            $bot->sendText($contenttmp);
          }else{
          $num = $result->num_rows;
          $contenttmp = array('chat_id' => $chat_id,"text"=>"تعداد : $num");
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
  
            if($contract_type=='factor'){
              $project = $row['project'];
              $inlineKeyboardoption =	[
                $bot->buildInlineKeyBoardButton("پرداخت", '',"$cbdataaccept"),
              ];
              $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
              $content=array("chat_id" =>$chat_id,"text" =>"نام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date", 'reply_markup' =>$Keyboard);
              $bot->sendText($content);
            }else{
              $inlineKeyboardoption =	[
                $bot->buildInlineKeyBoardButton("پرداخت", '',"$cbdataaccept"),
              ];
              $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
              $content=array("chat_id" =>$chat_id,"text" =>"نام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date", 'reply_markup' =>$Keyboard);
              $bot->sendText($content);
            }
          }
        }
      }
    }
  break;
  case "everything":
    if(in_array($chat_id, $accc)){

      $q_exists = "SELECT * FROM Requests WHERE is_closed=1 OR is_closed=0";
      if ($result = $conn->query($q_exists)) {
        if($result->num_rows==0){
          $contenttmp = array('chat_id' => $chat_id,"text"=>"موردی وجود ندارد.");
          $bot->sendText($contenttmp);
        }else{
        $num = $result->num_rows;
        $contenttmp = array('chat_id' => $chat_id,"text"=>"تعداد درخواست ها : $num");
        $bot->sendText($contenttmp);
        while ($row = $result->fetch_assoc()) {
          $title = $row['title'];
          $price = $row['price'];
          $date = $row['date_registered'];
          $description = $row['description'];
          $created_by = $row['created_by'];
          $name = $row['name'];
          $contract_type = $row['contract_type'];
          $state = $row['req_status'];
          if($state=="wait"){
            $status = "در انتظار تأیید کنترل پروژه";

          }elseif($state=="waitacc"){
            $status = "در انتظار تأیید حسابداری";

          }elseif($state=="cartable"){
            $status = "کارتابل";

          }elseif($state=="paid"){
            $status = "پرداخت شده";

          }elseif($state=="rejectcp"){
            $status = "رد شده توسط کنترل پروژه";

          }elseif($state=="rejectacc"){
            $status = "رد شده توسط حسابداری";
          }
          if($row['is_closed']==1){
            $s = 'بسته';
          }else{
            $s = 'باز';
          }

          if($contract_type=='factor'){
            $project = $row['project'];

            $content=array("chat_id" =>$chat_id,"text" =>"وضعیت درخواست : $s\nوضعیت : $status\nنام : $name\nعنوان : $title\nتوضیحات : $description\nپروژه : $project\nمبلغ : $price\nتاریخ درخواست : $date");
            $bot->sendText($content);
          }else{
            $content=array("chat_id" =>$chat_id,"text" =>"وضعیت درخواست : $s\nوضعیت : $status\nنام : $name\nشماره قرارداد : $title\nمبلغ : $price\nتوضیحات : $description\nتاریخ درخواست : $date");
            $bot->sendText($content);
          }
          sleep(3);
      }
      $content=array("chat_id" =>$chat_id,"text" =>"پایان پردازش");
      $bot->sendText($content);
    }
  }

}
  break;
  case "setting":
    if (in_array($chat_id, $accc)){

      $inlineKeyboardoption =	[
        $bot->buildInlineKeyBoardButton("افزودن سمت", '',"newpost"),
        $bot->buildInlineKeyBoardButton("تغییر سمت", '',"changepost"),
      ];
      $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' =>$Keyboard);
      $bot->sendText($contenttmp); 
    }
  break;
  case "newpost":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='change' OR status='getpos' OR status='getuser' OR status='getname'";
      if ($result = $conn->query($sql)){
        if($result->num_rows!=0){
          $d_q = "DELETE FROM Persons WHERE status='change' OR status='getpos' OR status='getuser' OR status='getname'";
          $result = $conn->query($d_q);
        }
      }
      $contenttmp = array('chat_id' => $chat_id,"text"=>"نام کامل شخص مورد نظر را وارد کنید");
      $bot->sendText($contenttmp); 
      $qu = "INSERT INTO Persons (status) VALUES ('getname')";
      $result = $conn->query($qu);
    }
  break;
  case "changepost":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='change' OR status='getpos' OR status='getuser' OR status='getname'";
      if ($result = $conn->query($sql)){
        if($result->num_rows!=0){
          $d_q = "DELETE FROM Persons WHERE status='change' OR status='getpos' OR status='getuser' OR status='getname'";
          $result = $conn->query($d_q);
        }
      }

      $sql = "SELECT * FROM Persons WHERE status='changing'";
      if ($result = $conn->query($sql)){
      if($result->num_rows!=0){
        $q_up = "UPDATE Requests SET status=NULL WHERE status='changing'";
        $result = $conn->query($q_up); 
      }
    }
      $qu = "INSERT INTO Persons (status) VALUES ('change')";
      $result = $conn->query($qu);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"یوزرنیم شخص مورد نظر را بدون علامت @ وارد کنید:");
      $bot->sendText($contenttmp);

    }
  break;
  case "ppm":
    if (in_array($chat_id, $accc)){
      $q_up = "UPDATE Persons SET position='project manager' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_id = "SELECT * FROM Persons WHERE status='choosing'";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $nam = $row['name'];
        $un = $row['username'];
        $content=array("chat_id" =>$chat_id,"text" =>"نام : $nam\nیوزرنیم : $un\nسمت : مدیر پروژه");
        $bot->sendText($content);
        $inlineKeyboardoption =	[
          $bot->buildInlineKeyBoardButton("تأیید", '',"confcreate"),
          $bot->buildInlineKeyBoardButton("انصراف", '',"canclecreate"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"تأیید می کنید؟", 'reply_markup' =>$Keyboard);
        $bot->sendText($contenttmp);
      }
    }

  break;
  case "pacc":
    if (in_array($chat_id, $accc)){
      $q_up = "UPDATE Persons SET position='accounting' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_id = "SELECT * FROM Persons WHERE status='choosing'";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $nam = $row['name'];
        $un = $row['username'];
        $content=array("chat_id" =>$chat_id,"text" =>"نام : $nam\nیوزرنیم : $un\nسمت : حسابداری");
        $bot->sendText($content);
        $inlineKeyboardoption =	[
          $bot->buildInlineKeyBoardButton("تأیید", '',"confcreate"),
          $bot->buildInlineKeyBoardButton("انصراف", '',"canclecreate"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"تأیید می کنید؟", 'reply_markup' =>$Keyboard);
        $bot->sendText($contenttmp);
      }
    }
  break;
  case "pseo":
    if (in_array($chat_id, $accc)){
      $q_up = "UPDATE Persons SET position='CEO' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_id = "SELECT * FROM Persons WHERE status='choosing'";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $nam = $row['name'];
        $un = $row['username'];
        $content=array("chat_id" =>$chat_id,"text" =>"نام : $nam\nیوزرنیم : $un\nسمت : مدیر عامل");
        $bot->sendText($content);
        $inlineKeyboardoption =	[
          $bot->buildInlineKeyBoardButton("تأیید", '',"confcreate"),
          $bot->buildInlineKeyBoardButton("انصراف", '',"canclecreate"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"تأیید می کنید؟", 'reply_markup' =>$Keyboard);
        $bot->sendText($contenttmp);
      }
    }
  break;
  case "pcp":
    if (in_array($chat_id, $accc)){
      $q_up = "UPDATE Persons SET position='control project' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
      $result = $conn->query($q_up);
      $q_id = "SELECT * FROM Persons WHERE status='choosing'";
      if ($result = $conn->query($q_id)) {
        $row = $result->fetch_assoc();
        $nam = $row['name'];
        $un = $row['username'];
        $content=array("chat_id" =>$chat_id,"text" =>"نام : $nam\nیوزرنیم : $un\nسمت : کنترل پروژه");
        $bot->sendText($content);
        $inlineKeyboardoption =	[
          $bot->buildInlineKeyBoardButton("تأیید", '',"confcreate"),
          $bot->buildInlineKeyBoardButton("انصراف", '',"canclecreate"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id,"text"=>"تأیید می کنید؟", 'reply_markup' =>$Keyboard);
        $bot->sendText($contenttmp);
      }
    }
  break;

  case "confcreate":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='choosing'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          $q_up = "UPDATE Persons SET status=NULL WHERE status='choosing'";
          $result = $conn->query($q_up);
          $contenttmp = array('chat_id' => $chat_id,"text"=>"کاربر با موفقیت اضافه شد.");
          $bot->sendText($contenttmp);
        }
      }
      $inlineKeyboardoption =	[
        $bot->buildInlineKeyBoardButton("ثبت درخواست", '',"newreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '',"everything"),
        $bot->buildInlineKeyBoardButton("تنظیمات", '',"setting"),
      ];
      $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' =>$Keyboard);
      $bot->sendText($contenttmp); 
    }
  break;
  case "canclecreate":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR status='getpos'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $row = $result->fetch_assoc();
          $sql = "DELETE FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR status='getpos'";
          if ($result = $conn->query($sql)) {
    
          $contenttmp = array('chat_id' => $chat_id,"text"=>"درخواست شما لغو شد.");
          $bot->sendText($contenttmp);
            
          } 
        }
      }
      $inlineKeyboardoption =	[
        $bot->buildInlineKeyBoardButton("ثبت درخواست", '',"newreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های باز", '',"openreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های هر پروژه", '',"everything"),
        $bot->buildInlineKeyBoardButton("تنظیمات", '',"setting"),
      ];
      $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
      $contenttmp = array('chat_id' => $chat_id,"text"=>"یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' =>$Keyboard);
      $bot->sendText($contenttmp);
    }
  break;
  case "changeremove":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='changing'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $sql = "DELETE FROM Persons WHERE status='changing'";
          if ($result = $conn->query($sql)) {
            $sql = "DELETE FROM Persons WHERE status='change'";
            if ($result = $conn->query($sql)){
              $contenttmp = array('chat_id' => $chat_id,"text"=>"کاربر مورد نظر از لیست کاربران مجاز این ربات حذف شد.");
              $bot->sendText($contenttmp);
            }

          } 
        }
      }
    }
  break;
  case "changetoceo":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='changing'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $q_up = "UPDATE Persons SET position='CEO' WHERE status='changing'";
          if ($result = $conn->query($q_up)) {
            $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
            $result = $conn->query($q_up);
            $contenttmp = array('chat_id' => $chat_id,"text"=>"سمت کاربر مورد نظر به مدیر عامل تغییر یافت.");
            $bot->sendText($contenttmp);
            $sql = "DELETE FROM Persons WHERE status='change'";
            $result = $conn->query($sql);
          } 
        }
      }
    }
  break;
  case "changetopm":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='changing'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $q_up = "UPDATE Persons SET position='project manager' WHERE status='changing'";
          if ($result = $conn->query($q_up)) {
            $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
            $result = $conn->query($q_up);
          $contenttmp = array('chat_id' => $chat_id,"text"=>"سمت کاربر مورد نظر به مدیر پروژه تغییر یافت.");
          $bot->sendText($contenttmp);
          $sql = "DELETE FROM Persons WHERE status='change'";
          $result = $conn->query($sql);
          } 
        }
      }
    }
  break;
  case "changetocp":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='changing'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $q_up = "UPDATE Persons SET position='control project' WHERE status='changing'";
          if ($result = $conn->query($q_up)) {
            $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
            $result = $conn->query($q_up);
          $contenttmp = array('chat_id' => $chat_id,"text"=>"سمت کاربر مورد نظر به کنترل پروژه تغییر یافت.");
          $bot->sendText($contenttmp);
          $sql = "DELETE FROM Persons WHERE status='change'";
          $result = $conn->query($sql);
          } 
        }
      }
    }
  break;
  case "changetoacc":
    if (in_array($chat_id, $accc)){
      $sql = "SELECT * FROM Persons WHERE status='changing'";
      if ($result = $conn->query($sql)) {
        if($result->num_rows!=0){
          $q_up = "UPDATE Persons SET position='accounting' WHERE status='changing'";
          if ($result = $conn->query($q_up)) {
            $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
            $result = $conn->query($q_up);
          $contenttmp = array('chat_id' => $chat_id,"text"=>"سمت کاربر مورد نظر به حسابداری تغییر یافت.");
          $bot->sendText($contenttmp);
          $sql = "DELETE FROM Persons WHERE status='change'";
          $result = $conn->query($sql);
          } 
        }
      }
    }
  break;
}
$conn->close(); 
?>