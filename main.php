<?php
date_default_timezone_set('Europe/Berlin');


include('Parsedown.php');

function umlaute($post){
    $post=trim(preg_replace('/\t/', ' ', $post));
    $post=str_replace('ä', '&auml;', $post);
    $post=str_replace('ü', '&uuml;', $post);
    $post=str_replace('ö', '&ouml;', $post);
    $post=str_replace('Ä', '&Auml;', $post);
    $post=str_replace('Ü', '&Uuml;', $post);
    $post=str_replace('Ö', '&Ouml;', $post);
    $post=str_replace('ß', '&#223;', $post);
    $post=str_replace('ẞ', '&#7838;', $post);

    $emojis=json_decode(shell_exec("cat github_emojis.json"));
    
    foreach( $emojis AS $emoji_txt => $emoji_url){
        $post=str_replace(':'.$emoji_txt.':', '<img src="'.$emoji_url.'" class="emoji">', $post);
    }
    
    return $post;
}

function dlt_html($post){
    $post=str_replace('>', '&gt;', $post);
    $post=str_replace('<', '&lt;', $post);
    return $post;
}

$clickable_txt="onmouseover=\"this.style.color='#00ff00';\" onmouseout=\"this.style.color='#ffffff';\"";
$clickable_greentxt="onmouseover=\"this.style.color='#ffffff';\" onmouseout=\"this.style.color='#00ff00';\"";
$clickable_btn="onmouseover=\"this.style.backgroundColor='#00ff00';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"";
$clickable_field="onmouseover=\"this.style.backgroundColor='rgb(8%,8%,8%)';this.style.color='#00ff00';\" onmouseout=\"this.style.backgroundColor='transparent';this.style.color='#ffffff';\"";
$clickable_grey="onmouseover=\"this.style.color='#ffffff';\" onmouseout=\"this.style.color='rgb(60%, 60%, 60%)';\"";
$clickable_post="onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(8%,8%,8%)';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='#000000';\"";

$title='MAINPAGE';

# VARIABLES [POST]
$userid=$_POST['userid'];

$username=$_POST['username'];
$password=$_POST['password'];
$site=$_POST['site'];
$catsite=$_POST['catsite'];

if($site == '')
    $site=0;
if($catsite == '')
    $catsite=0;

$host = "localhost";
$benutzer = 'MAINPAGE';
$passwort = 'MAINPAGE';
$bindung = mysqli_connect($host, $benutzer, $passwort) or die("Connection is fuckin' broken.");
$db = 'MAINPAGE';

function mdq($bindung, $query)
{
    mysqli_select_db($bindung, 'MAINPAGE');
    return mysqli_query($bindung, $query);
}

if($_POST['deletecookies'] == 1){
    foreach($_COOKIE AS $safeduser => $safedpasswd){
        setcookie($safeduser, $safedpasswd, time() - 1, "/");
        $cookiesdeleted=1;
    }
}


if($password == 'NONE'){
    $sql = "SELECT timeout FROM user WHERE id=$userid;";
    $out = mdq($bindung, $sql);
    while ($row = mysqli_fetch_row($out)) {
        $sec=($row[0]+(60*60))-time();
        $timeout=sprintf('%02d:%02d:%02d', ($sec/3600),($sec/60%60), $sec%60);
        $bodyaddon='onload="setTimeout( function(){ document.mainpage.submit(); }, '.($sec+1).'000 );"';
        if($sec <= 0){
            $userid='';
            $username='';
            $password='';
        }
    }
}

$sql = "SELECT id FROM user WHERE timeout+60*60-".time()."<=0 AND timeout!=0;";
$out = mdq($bindung, $sql);
while ($row = mysqli_fetch_row($out)) {
    $did=$row[0];
    
    $sql2 = "DELETE FROM timestamps WHERE user=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "DELETE FROM subscriptions WHERE user=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "DELETE FROM comments WHERE user=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "DELETE FROM votes WHERE user=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "DELETE FROM hearts WHERE user=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "SELECT id FROM blogs WHERE owner=$did;";
    $out2 = mdq($bindung, $sql2);
    while ($row2 = mysqli_fetch_row($out2)) {
        $sql3 = "DELETE FROM blogposts WHERE blog=".$row2[0].";";
        $out3 = mdq($bindung, $sql3);
        $sql3 = "DELETE FROM subscriptions WHERE blog=".$row2[0].";";
        $out3 = mdq($bindung, $sql3);
    }
    $sql2 = "DELETE FROM blogs WHERE owner=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "DELETE FROM user WHERE id=$did;";
    $out2 = mdq($bindung, $sql2);
}    


# HEAD
echo "<!DOCTYPE html>
<html>
<head>
<script src='jquery.min.js'></script>
<link href='font.css' rel='stylesheet'>
<link rel='stylesheet' type='text/css' href='stylesheet.css'>
<link rel='shortcut icon' type='image/x-icon' href='DATA/icon.ico'>

<script>
$(window).scroll(function() {
  sessionStorage.scrollTop = $(this).scrollTop();
});

$(document).ready(function() {
  if (sessionStorage.scrollTop != 'undefined') {
    $(window).scrollTop(sessionStorage.scrollTop);
  }
});
</script>

<title>$title</title>
</head>

<body $bodyaddon>
<form autocomplete='off' name='mainpage' id='mainpage' method='POST' action='main.php'>
<!-- Prevent implicit submission of the form -->
<button type='submit' disabled style='display: none' aria-hidden='true'></button>

";

$URL=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];


if(isset($_POST['createano'])){
    $stop=0;
    while( $stop == 0 ){
        $username=STRTOUPPER(SUBSTR(md5(rand(111111,999999)), 0, 16));
        $password='NONE';
        $stop=1;
        $sql = "SELECT id FROM user WHERE username='$username';";
        $out = mdq($bindung, $sql);
        while ($row = mysqli_fetch_row($out)) {
            $stop=0;
        }
    }
    
    $sql = "INSERT INTO user SET username='$username', password='$password', password_orig='$password', timeout=".time().";";
    $out = mdq($bindung, $sql);
    $sec=(time()+(60*60))-time();
    $timeout=sprintf('%02d:%02d:%02d', ($sec/3600),($sec/60%60), $sec%60);
    $bodyaddon='onload="setTimeout( function(){ document.mainpage.submit(); }, '.($sec+1).'000 );"';
}


if(isset($_POST['register'])){
    $username=$_POST['register_username'];
    $password=$_POST['register_password'];

    if($username != '' and $password != ''){
        if(ctype_alnum($username)){
            $stop=0;
            $sql = "SELECT id FROM user WHERE username='$username';";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $stop=1;
            }

            if( $stop != 1 ){
                $password_orig=md5($password);
                $password=md5('#'.$password.'#');
                $sql = "INSERT INTO user SET username='$username', password='$password', password_orig='$password_orig';";
                $out = mdq($bindung, $sql);
            }
            else
                $ERROR_register='Username exists';
        }
        else
            $ERROR_register='The username must consist only of characters and/or digits';
    }
    else
        $ERROR_register='Please fill out all fields';
}

if(isset($_POST['login']) and ctype_alnum($_POST['login_username'])){
    $username=$_POST['login_username'];
    $password=md5('#'.$_POST['login_password'].'#');
}

$userexist=0;
$sql = "SELECT id, username, password_orig FROM user WHERE password='$password' AND username='$username';";
$out = mdq($bindung, $sql);
while ($row = mysqli_fetch_row($out)) {
    $userid=$row[0];
    $username=$row[1];
    $password_orig=$row[2];
    $userexist=1;
}

if($userexist == 0){
    $userid='';
    $username='';
    $password_orig='';
}


# LOGIN
if( $userid == '' or $ERROR_register != ''){

    $login_dis='block';
    $autologin_dis='block';
    $register_errordis='none';
    $register_errorspan='none';
    $login_errordis='none';
    $login_errorspan='none';
    
    if(isset($_POST['login'])){
        $login_dis='none';
        $autologin_dis='none';
        $ERROR_login='Wrong username or wrong password';
        $login_errordis='block';
        $login_errorspan='block';
    }
    
    if($ERROR_register != ''){
        $login_dis='none';
        $autologin_dis='none';
        $register_errordis='block';
        $register_errorspan='block';
    }

    if($cookiesdeleted != 1){
        $autologincode='';
        foreach($_COOKIE AS $safeduser => $safedpasswd){
            $in=1;
            $login_dis='none';
            $autologincode.="<div class='login_btn' onclick=\"username.value='$safeduser';password.value='$safedpasswd';document.mainpage.submit();\" $clickable_btn>$safeduser</div>";
        }
    }

        
    echo "<a href='$URL'><img id='login_logo' src='DATA/logo_v1.png'></a>";
    if($in == 1){
        echo "
<input type='hidden' name='deletecookies' id='deletecookies' value='0'>
<div class='box' id='autobtns' style='display:$autologin_dis'><div class='title'><span class='white'>></span> Safed Users</div>$autologincode<br><span class='greytxt' onclick=\"login_btns.style.display='block';autobtns.style.display='none';\" $clickable_txt>Not listed?</span><span class='right greytxt' style='padding-top:0px;' onclick=\"deletecookies.value=1;document.mainpage.submit();\" ".str_replace('#00ff00', '#ff0000', $clickable_txt).">Delete all</span></div>";
    }
    echo "<div id='login_btns' class='box' style='display:$login_dis'>
<div class='title'><span class='white'>></span> Welcome!</div>
<div class='login_btn' onclick=\"login_btns.style.display='none'; anomsg.style.display='block';\" $clickable_btn>Anonymous (1 hour)</div>
<div class='login_btn' onclick=\"login_btns.style.display='none'; login.style.display='block';\" $clickable_btn>Login</div>
<div class='login_btn' onclick=\"login_btns.style.display='none'; register.style.display='block';\" $clickable_btn>Register</div>
</div>

<div id='login' class='box' style='display:$login_errordis;'>
<div class='title'><span class='white'>></span> Login</div>
<div class='errorspan' style='display:$login_errorspan;'><b class='errorspan_arz'>!</b> $ERROR_login</div>
<input type='text' name='login_username' class='text' placeholder='Username'>
<input type='password' name='login_password' class='text' placeholder='Password'>
<input type='hidden' name='cookiesafe' id='cookiesafe' value='0'>
<div class='checkfield' onmouseover=\"field1.style.color='#00ff00';\" onmouseout=\"field1.style.color='#ffffff';\" onclick=\"if(cookiesafe.value == 0){field1.innerHTML='[x]';cookiesafe.value=1;}else{field1.innerHTML='[ ]';cookiesafe.value=0;}\"><span id='field1'>[ ]</span> safe on this device</div>
<p>
<b>#</b> <i>Stay respectful.</i><br>
<b>#</b> <i>Stay anonymous.</i><br>
<b>#</b> <i>Stay community-friendly.</i></p>
<input type='submit' name='login' class='btn' value='login' $clickable_btn>
</div>

<div id='register' class='box' style='display:$register_errordis;'>
<div class='title'><span class='white'>></span> Register</div>
<div class='errorspan' style='display:$register_errorspan;'><b class='errorspan_arz'>!</b> $ERROR_register</div>
<input type='text' name='register_username' class='text' maxlength='16' placeholder='Username'>
<input type='password' name='register_password' class='text' placeholder='Password'>
<input type='hidden' name='cookiesafe2' id='cookiesafe2' value='0'>
<div class='checkfield' onmouseover=\"field2.style.color='#00ff00';\" onmouseout=\"field2.style.color='#ffffff';\" onclick=\"if(cookiesafe2.value == 0){field2.innerHTML='[x]';cookiesafe2.value=1;}else{field2.innerHTML='[ ]';cookiesafe2.value=0;}\"><span id='field2'>[ ]</span> safe on this device</div>
<p>
<b>#</b> <i>Stay respectful.</i><br>
<b>#</b> <i>Stay anonymous.</i><br>
<b>#</b> <i>Stay community-friendly.</i></p>
<input type='submit' name='register' class='btn' value='register' $clickable_btn>
</div>

<div id='anomsg' class='box'>
<div class='title'><span class='white'>></span> Temporary Access</div>
An anonymous account will be generated with which you can use all functions.<br>
But after an hour, all activity will be deleted.<p>
<b>#</b> <i>Stay respectful.</i><br>
<b>#</b> <i>Stay anonymous.</i><br>
<b>#</b> <i>Stay community-friendly.</i><br><br>
<input type='submit' name='createano' class='btn' value='enter' $clickable_btn>
</div>
";
}else{

    # ==== BEGINNING FNCTNS ==== #

    if($_POST['deleteblog'] != 0){
        $sql = "DELETE FROM blogs WHERE id=".$_POST['deleteblog']." and owner=$userid;";
        $out = mdq($bindung, $sql);
        $catsite=1;
    }


    # ==== START ==== #

    $blogpx='0';
    $forumpx='0';
    $grouppx='0';
    $userpx='0';

    $clickable_txt1=$clickable_txt;
    $clickable_txt2=$clickable_txt;
    $clickable_txt3=$clickable_txt;
    $clickable_txt4=$clickable_txt;

    $catsitepx0='0';
    $catsitepx1='0';
    $catsitepx2='0';
    $catsitepx3='0';
    $catsitepx4='0';

    $clickable_txt_cat0=$clickable_txt;
    $clickable_txt_cat1=$clickable_txt;
    $clickable_txt_cat2=$clickable_txt;
    $clickable_txt_cat3=$clickable_txt;
    $clickable_txt_cat4=$clickable_txt;

    if($catsite == '0'){
        $catsitepx0='2';
        $clickable_txt_cat0='';
    }elseif($catsite == '1'){
        $catsitepx1='2';
        $clickable_txt_cat1='';
    }elseif($catsite == '2'){
        $catsitepx2='2';
        $clickable_txt_cat2='';
    }elseif($catsite == '3'){
        $catsitepx3='2';
        $clickable_txt_cat3='';
    }elseif($catsite == '4'){
        $catsitepx4='2';
        $clickable_txt_cat4='';
    }
    
    switch($site){
    default:
        $chsites="
<div class='catheader_child' id='ch0' onclick=\"catsite.value=0;document.mainpage.submit();\" $clickable_txt_cat0><span style='border-bottom:${catsitepx0}px solid #00ff00'>Trend</span></div>
<div class='catheader_child' id='ch1' onclick=\"catsite.value=1;document.mainpage.submit();\" $clickable_txt_cat1><span style='border-bottom:${catsitepx1}px solid #00ff00'>Votings</span></div>
<div class='catheader_child' id='ch2' onclick=\"catsite.value=2;document.mainpage.submit();\" $clickable_txt_cat2><span style='border-bottom:${catsitepx2}px solid #00ff00'>About</span></div>
";
        break;
    case 4:
        $chsites="
<div class='catheader_child' id='ch0' onclick=\"catsite.value=0;document.mainpage.submit();\" $clickable_txt_cat0><span style='border-bottom:${catsitepx0}px solid #00ff00'>Subscriptions</span></div>
<div class='catheader_child' id='ch1' onclick=\"catsite.value=1;document.mainpage.submit();\" $clickable_txt_cat1><span style='border-bottom:${catsitepx1}px solid #00ff00'>My Blogs</span></div>
<div class='catheader_child' id='ch2' onclick=\"catsite.value=2;document.mainpage.submit();\" $clickable_txt_cat2><span style='border-bottom:${catsitepx2}px solid #00ff00'>Search</span></div>
";
        $blogpx='2';
        $clickable_txt4='';
        break;
    case 3:
        $chsites="
<div class='catheader_child' id='ch0' onclick=\"catsite.value=0;document.mainpage.submit();\" $clickable_txt_cat0><span style='border-bottom:${catsitepx0}px solid #00ff00'>All</span></div>
<div class='catheader_child' id='ch1' onclick=\"catsite.value=1;document.mainpage.submit();\" $clickable_txt_cat1><span style='border-bottom:${catsitepx1}px solid #00ff00'>My Threads</span></div>
";
        $forumpx='2';
        $clickable_txt3='';
        break;
    case 2:
        $chsites="
<div class='catheader_child' id='ch0' onclick=\"catsite.value=0;document.mainpage.submit();\" $clickable_txt_cat0><span style='border-bottom:${catsitepx0}px solid #00ff00'>Private</span></div>
<div class='catheader_child' id='ch1' onclick=\"catsite.value=1;document.mainpage.submit();\" $clickable_txt_cat1><span style='border-bottom:${catsitepx1}px solid #00ff00'>Public</span></div>
";
        $grouppx='2';
        $clickable_txt2='';
        break;

    case 1:
        $chsites="
<div class='catheader_child' id='ch0' onclick=\"catsite.value=0;document.mainpage.submit();\" $clickable_txt_cat0><span style='border-bottom:${catsitepx0}px solid #00ff00'>My Profile</span></div>
<div class='catheader_child' id='ch1' onclick=\"catsite.value=1;document.mainpage.submit();\" $clickable_txt_cat1><span style='border-bottom:${catsitepx1}px solid #00ff00'>Hashtags</span></div>
<div class='catheader_child' id='ch2' onclick=\"catsite.value=2;document.mainpage.submit();\" $clickable_txt_cat2><span style='border-bottom:${catsitepx2}px solid #00ff00'>Search</span></div>
";
        $userpx='2';
        $clickable_txt1='';
        break;
    }

    
    if((isset($_POST['login']) or isset($_POST['register'])) and ($_POST['cookiesafe'] == 1 or $_POST['cookiesafe2'] == 1)){
        setcookie($username, $password, time() + (86400 * 30 * 356), "/");
    }

    
    # START
    echo "
<div id='header'>
<img id='logo' src='DATA/logo_v1.png' class='clickable' onclick=\"catsite.value=0;site.value=0;document.mainpage.submit();\">
<!-- SITE NO. 1 --><div class='header_child' ".$clickable_txt1." onclick=\"catsite.value=0;site.value=1;document.mainpage.submit();\"><span style='border-bottom:${userpx}px solid #00ff00'>Users</span></div>
<!-- SITE NO. 2 --><div class='header_child' ".$clickable_txt2." onclick=\"catsite.value=0;site.value=2;document.mainpage.submit();\"><span style='border-bottom:${grouppx}px solid #00ff00'>Groups</span></div>
<!-- SITE NO. 3 --><div class='header_child' ".$clickable_txt3." onclick=\"catsite.value=0;site.value=3;document.mainpage.submit();\"><span style='border-bottom:${forumpx}px solid #00ff00'>Forum</span></div>
<!-- SITE NO. 4 --><div class='header_child' ".$clickable_txt4." onclick=\"catsite.value=0;site.value=4;document.mainpage.submit();\"><span style='border-bottom:${blogpx}px solid #00ff00'>Blogs</span></div>
</div>
<div id='catheader'>
<div id='charrow'>></div>$chsites
</div>
<div style='height:250px;'></div>
";

    # ===============  ALL FNCTNS  =============== #


    
    if(isset($_POST['editprofile']) and $_POST['editusername'] != ""){
        $editusername=$_POST['editusername'];
        $editpassword=$_POST['editpassword'];
        
        if(ctype_alnum($editusername)){
            $stop=0;
            $sql = "SELECT id FROM user WHERE username='$editusername' and id!=$userid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $stop=1;
            }

            if( $stop != 1 ){

                $oldpassword=$password;
                
                if($editpassword != ""){
                    $password_orig=md5($editpassword);
                    $password=md5('#'.$editpassword.'#');
                }

                if($_COOKIE["$username"] != ""){
                    setcookie($username, $oldpassword, time() - 1, "/");
                    setcookie($editusername, $password, time() + (86400 * 30 * 356), "/");
                }

                $username=$editusername;
                
                $sql = "UPDATE user SET username='$username', password='$password', password_orig='$password_orig' WHERE id=$userid;";
                $out = mdq($bindung, $sql);
                
            }
            else
                $ERROR_edit='Username exists';
        }
        else
            $ERROR_edit='The username must consist only of characters and/or digits';
    }

    $showdisplay='block';
    $editdisplay='none';
    $editerrordisplay='none';
    
    if($ERROR_edit != ""){
        $showdisplay='none';
        $editdisplay='block';
        $editerrordisplay='block';
    }

    if(isset($_POST['createblog']) and $_POST['createblog_name'] != ""){
        $createblog_name=$_POST['createblog_name'];
        if(ctype_alnum(str_replace('-', 'A', $createblog_name))){
            $stop=0;
            $sql = "SELECT id FROM blogs WHERE owner=$userid and name='$createblog_name';";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $stop=1;
            }
            if( $stop != 1 ){
                $sql = "INSERT INTO blogs SET name='$createblog_name', owner=$userid;";
                $out = mdq($bindung, $sql);
                $sql = "SELECT LAST_INSERT_ID();";
                $out = mdq($bindung, $sql);
                while ($row = mysqli_fetch_row($out)) {
                    $bloginsertid=$row[0];
                }
                $sql = "INSERT INTO subscriptions SET blog=$bloginsertid, user=0;";
                $out = mdq($bindung, $sql);
                $sql = "INSERT INTO blogposts SET title='', post='', blog=$bloginsertid;";
                $out = mdq($bindung, $sql);
                $sql = "INSERT INTO votes SET type=0, type_id=LAST_INSERT_ID(), vote=2, user=$userid;";
                $out = mdq($bindung, $sql);
            }
            else
                $ERROR_createblog='Blog exists';
        }
        else
            $ERROR_createblog='The name must consist only of characters, minuses and/or digits';
    }

    $blogsdisplay='block';
    $createblogdisplay='none';
    $createblogerrordisplay='none';
    
    if($ERROR_createblog != ""){
        $blogsdisplay='none';
        $createblogdisplay='block';
        $createblogerrordisplay='block';
    }
    

    
    
    # ===============  ALL SITES   =============== #

    if($site == 0){
        if($catsite == 2){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> About";
            echo "<div class='linkbox'><img src='DATA/jcobs-engine_logo.png' class='link_icon'><div class='link_text'>MAINPAGE<img src='DATA/discord_verified.png' class='link_verified_symbol'></div><div class='link_invite_link' onclick=\"window.open('https://github.com/jcobs-engine/MAINPAGE');\" onmouseover=\"this.style.backgroundColor='#2ba06b';\" onmouseout=\"this.style.backgroundColor='#43b581';\">Open</div><img src='DATA/github_logo.png' class='link_logo'><div class='link_title'>GitHub:</div></div>";
            echo "<div class='linkbox' style='margin-bottom:0px;'><img src='DATA/discord_icon.png' class='link_icon'><div class='link_text'>MAINPAGE<img src='DATA/discord_verified.png' class='link_verified_symbol'></div><div class='link_invite_link' onclick=\"window.open('https://discord.gg/tbwgRDh','targetWindow',`resizable=no,width=500,height=650`);return false;\" onmouseover=\"this.style.backgroundColor='#2ba06b';\" onmouseout=\"this.style.backgroundColor='#43b581';\">Join</div><img src='DATA/discord_logo.png' class='link_logo'><div class='link_title'>Discord:</div></div>";
            echo "</div>";
        }
    }
    
    if($site == 1){
        if($catsite == '0'){
            echo "<div class='box' id='showprofile' style='display:$showdisplay'><div class='title'><span class='white'>></span> My Profile</div><div class='bold text'>$username</div>";

            if($password != 'NONE'){
                echo "<div class='text'>".$password_orig."</div><span class='grey'>(The password shown is MD5 hashed.)</span><br><span class='right greytxt' $clickable_txt onclick=\"showprofile.style.display='none';editprofile.style.display='block';\">edit</span><br>";
            }

echo "</div><div class='box' id='editprofile' style='display:$editdisplay;'><div class='title'><span class='white'>></span> Edit Profile</div><div class='errorspan' style='display:$editerrordisplay;'><b class='errorspan_arz'>!</b> $ERROR_edit</div><input type='text' class='text' name='editusername' value='$username' placeholder='Username'><input type='password' placeholder='New password (optional)' name='editpassword' class='text'><input type='submit' name='editprofile' class='btn' value='edit' $clickable_btn></div>";
        }
    }

    if($site == 4){
        if($catsite == '0'){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Subscriptions</div><div class='rightbtn' $clickable_btn onclick=\"catsite.value=2;document.mainpage.submit();\">Search</div>";

            $in=0;
            $sql = "SELECT blogs.id, blogs.name, user.username, ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT(DISTINCT blogposts.id)), 0) AS zahl, IF((MAX(blogposts.time)-MAX(timestamps.time))>0,1,0) AS reddit FROM blogs, user, blogposts, votes, subscriptions, timestamps WHERE blogposts.id=votes.type_id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND blogposts.title!='' AND subscriptions.blog=blogs.id AND subscriptions.user=$userid AND timestamps.type=0 AND timestamps.type_id=blogs.id AND timestamps.user=$userid GROUP by blogs.id ORDER BY reddit desc, blogposts.id desc;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogid=$row[0];
                $searchname=$row[1];
                $searchuser=$row[2];
                $vote=$row[3];
                if($vote == ''){
                    $vote=0;
                }
                $styleaddon='';
                if($row[4] == 1){
                $styleaddon='font-weight:bold;';
                }

                if($in == 0){
                    $styleaddon.="border-top:2px solid #ffffff;";
                }

                echo "<div class='listfield' $clickable_field style='$styleaddon' onclick=\"catsite.value='blog#$blogid';document.mainpage.submit();\">$searchname <span class='grey'>by <span $clickable_grey class='clickable'>$searchuser</span></span><span class='vote right bold' style='padding-top:0px;'>$vote</span></div>";
                $in=1;
            }

            if($in != 1)
                echo "<center>=== NO BLOGS ===</center>";

            echo "</div>";
        }
        if($catsite == '1'){
            echo "<div class='box' id='createblog' style='display:$createblogdisplay'><div class='title'><span class='white'>></span> Create Blog</div><div class='errorspan' style='display:$createblogerrordisplay;'><b class='errorspan_arz'>!</b> $ERROR_createblog</div><input type='text' maxlength='32' class='text' name='createblog_name' placeholder='Name'><input type='submit' name='createblog' class='btn' $clickable_btn value='create'></div><div class='artikel' id='myblogs' style='display:$blogsdisplay'><div class='title'><span class='white'>></span> My Blogs</div><div class='rightbtn' $clickable_btn onclick=\"createblog.style.display='block';myblogs.style.display='none';\">Create New</div>";

            $in=0;
            $sql = "SELECT blogs.id, blogs.name, ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT(DISTINCT blogposts.id)-COUNT(CASE WHEN blogposts.title='' THEN 1 END)), 0) AS zahl FROM blogs, user, blogposts, votes WHERE blogposts.id=votes.type_id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND user.id=$userid GROUP by blogs.id ORDER BY zahl desc, blogposts.id desc;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogid=$row[0];
                if($in == 0){
                    $fieldlistaddon="style='border-top:2px solid #ffffff;'";
                }
                else{
                    $fieldlistaddon="";
                }
                echo "<div class='listfield' $clickable_field $fieldlistaddon onclick=\"catsite.value='blog#$blogid';document.mainpage.submit();\">$row[1]<span class='vote right bold' style='padding-top:0px;'>$row[2]</span></div>";
                $in=1;
            }

            if($in == 0)
                echo "<center>=== NO BLOGS ===</center>";

            echo "</div>";
        }
        if($catsite == '2'){
            $search=$_POST['search'];
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Search</div><input type='text' class='text' name='search' placeholder='Blog, Post or User' value='$search' autofocus><input type='submit' name='createblog' class='btn' $clickable_btn value='search' style='margin-bottom:40px;'>";

            $end='';
            $searchstr='1=0';
            $searchstr2='1=0';
            $search_split=explode(' ', $search);
            foreach($search_split AS $search_now){
                if($search_now != ""){
                    $searchstr.=" OR LOWER(blogs.name) LIKE LOWER('%$search_now%') OR LOWER(user.username) LIKE LOWER('%$search_now%') OR LOWER(blogposts.title) LIKE LOWER('%$search_now%')";
                    $searchstr2.=" OR LOWER(blogposts.title) LIKE LOWER('%$search_now%')";
                }
            }

            if($searchstr == '1=0'){
                $searchstr='1=1';
                $end=' LIMIT 10';
            }

            $in=0;
            $sql = "SELECT blogs.id, blogs.name, user.username, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) AS zahl FROM blogs, user, blogposts, votes, subscriptions WHERE blogs.id=votes.type_catid AND votes.type_id=blogposts.id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND blogposts.title!='' AND subscriptions.blog=blogs.id AND ($searchstr) GROUP by blogs.id ORDER BY zahl desc, blogposts.id desc$end;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogid=$row[0];
                $searchname=$row[1];
                $searchuser=$row[2];
                
                $sql = "SELECT ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT( DISTINCT blogposts.id)), 0) AS zahl FROM blogs, blogposts, votes WHERE blogs.id=$blogid AND blogs.id=votes.type_catid AND votes.type_id=blogposts.id AND votes.type=0 AND blogposts.blog=blogs.id AND blogposts.title!='' GROUP by blogs.id;";
                $out3 = mdq($bindung, $sql);
                while ($row3 = mysqli_fetch_row($out3)) {
                    $vote=$row3[0];
                    if($vote == ''){
                        $vote=0;
                    }
                }

                if($in == 0){
                    $fieldlistaddon="style='border-top:2px solid #ffffff;'";
                }
                else{
                    $fieldlistaddon="";
                }

                echo "<div class='listfield' $clickable_field $fieldlistaddon onclick=\"catsite.value='blog#$blogid';document.mainpage.submit();\">$searchname <span class='grey'>by <span $clickable_grey class='clickable'>$searchuser</span></span><span class='vote right bold' style='padding-top:0px;'>$vote</span>";

                // THEMES
                $themes='';
                $sql = "SELECT blogposts.title, blogposts.id, ROUND(COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END)) AS zahl FROM blogposts, votes WHERE blogposts.blog=$blogid AND blogposts.id=votes.type_id AND votes.type=0 AND ($searchstr2) GROUP by blogposts.id ORDER BY zahl desc, blogposts.id desc;";
                $out2 = mdq($bindung, $sql);
                while ($row2 = mysqli_fetch_row($out2)) {
                    $themes.="<div class='title posttitle little searchtitle' style='margin-bottom:0px;' $clickable_greentxt onclick=\"gt_blogpost.value='{$blogid}_{$row2[1]}';\"><span class='white'>#</span> $row2[0] <span class='grey'>($row2[2])</span></div>";
                }

                if($themes != ""){
                    echo "<div class='themes'>$themes</div>";
                }
                
                echo "</div>";
                
                $in=1;
            }
            
            if($in == 0)
                echo "<center>=== NO RESULTS ===</center>";
            
            echo "</div>";
        }
    }    
    
    if(strpos($catsite,"blog")!==false){
        $blogid=explode('#', $catsite);
        $blogid=$blogid[1];

        $drinn=0;
        $sql = "SELECT id FROM timestamps WHERE type=0 AND type_id=$blogid AND user=$userid;";
        $out = mdq($bindung, $sql);
        while ($row = mysqli_fetch_row($out)) {
            $drinn=1;
        }
        if($drinn == 1){
            $sql = "UPDATE timestamps SET time=".time()." WHERE type=0 AND type_id=$blogid AND user=$userid;";
            $out = mdq($bindung, $sql);
        }
        else
        {
            $sql = "INSERT INTO timestamps SET time=".time().", type=0, type_id=$blogid, user=$userid;";
            $out = mdq($bindung, $sql);
        }
        $sql = "SELECT owner FROM blogs WHERE id=$blogid;";
        $out = mdq($bindung, $sql);
        while ($row = mysqli_fetch_row($out)) {
            $blogowner=$row[0];
        }

        if($_POST['subscribe'] == $blogid){
            $sql = "INSERT INTO subscriptions SET blog=$blogid, user=$userid;";
            $out = mdq($bindung, $sql);
        }
        if($_POST['unsubscribe'] == $blogid){
            $sql = "DELETE FROM subscriptions WHERE blog=$blogid AND user=$userid;";
            $out = mdq($bindung, $sql);
        }
        
        if(isset($_POST['newpost'.$blogid]) and $blogowner == $userid and $_POST['newpost_title'] != "" and $_POST['newpost_text']){
            $newpost_title=str_replace("'", "\'", $_POST['newpost_title']);
            $newpost_text=str_replace("'", "\'", $_POST['newpost_text']);

            $sql = "INSERT INTO blogposts SET title='$newpost_title', post='$newpost_text', blog=$blogid, time=".time().", date='".date("d.m.Y h:i A")."';";
            $out = mdq($bindung, $sql);

            $sql = "INSERT INTO votes SET vote=2, type=0, type_id=LAST_INSERT_ID(), user=$userid, type_catid=$blogid;";
            $out = mdq($bindung, $sql);
        }

        if($_POST['deleteblogpost'] != 0){
            $sql = "DELETE FROM blogposts WHERE id=".$_POST['deleteblogpost']." AND blog=$blogid AND $blogowner=$userid;";
            $out = mdq($bindung, $sql);
        }

        if($_POST['editblogpost'] != 0 and $blogowner == $userid){
            $sql = "SELECT title, post FROM blogposts WHERE id=".$_POST['editblogpost'].";";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $editblog_title=$row[0];
                $editblog_post=$row[1];
            }
            echo "<input type='hidden' name='editblogpostid' value='".$_POST['editblogpost']."'><div class='artikel'><div class='title'><span class='white'>></span> Edit Post</div><input type='text' name='editpost_title' class='text' placeholder='Title' maxlength='64' value='$editblog_title' required><textarea name='editpost_text' class='textarea'>$editblog_post</textarea><input type='submit' name='editpost$blogid' value='edit' class='btn' $clickable_btn></div>";
            
        }
        else{

            if(isset($_POST['editpost'.$blogid]) and $blogowner == $userid){
                $editpost_title=str_replace("'", "\'", $_POST['editpost_title']);
                $editpost_text=str_replace("'", "\'", $_POST['editpost_text']);
                $editblogpostid=$_POST['editblogpostid'];
                
                $sql = "UPDATE blogposts SET title='$editpost_title', post='$editpost_text' WHERE id=$editblogpostid;";
                $out = mdq($bindung, $sql);
            }

            
            $sql = "SELECT blogs.name, user.username, user.id FROM blogs, user WHERE blogs.id=$blogid and blogs.owner=user.id;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogname=$row[0];
                $blogowner=$row[1];
                $blogownerid=$row[2];
                
                if($blogownerid == $userid){
                    echo "
<input type='hidden' name='deleteblogpost' id='deleteblogpost' value='0'>
<input type='hidden' name='editblogpost' id='editblogpost' value='0'>
<div class='artikel' id='newpost$blogid' style='display:none'><div class='title'><span class='white'>></span> New Post</div>
<input type='text' name='newpost_title' class='text' placeholder='Title' maxlength='64'>
<textarea name='newpost_text' class='textarea' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\">Text</textarea>
<input type='submit' name='newpost$blogid' value='create' class='btn' $clickable_btn>
</div>";
                }
                
                echo "<input type='hidden' name='subscribe' id='subscribe' value='0'><input type='hidden' name='unsubscribe' id='unsubscribe' value='0'><div class='artikel' id='blog$blogid'><div class='title'><span class='white'>></span> $blogname <span class='grey'>by <span $clickable_grey class='clickable bold'>$blogowner</span></span></div>";
                if($blogownerid == $userid){
                    echo "<div class='rightbtn' $clickable_btn onclick=\"newpost$blogid.style.display='block';blog$blogid.style.display='none';\">New Post</div>";
                }
                else{
                    $sub=0;
                    $sql = "SELECT id FROM subscriptions WHERE blog=$blogid AND user=$userid;";
                    $out = mdq($bindung, $sql);
                    while ($row = mysqli_fetch_row($out)) {
                        $sub=1;
                    }
                    if($sub == 0)
                        echo "<div class='rightbtn' $clickable_btn onclick=\"subscribe.value=$blogid;document.mainpage.submit();\">Subscribe</div>";
                    else
                        echo "<div class='rightbtn' $clickable_btn onclick=\"unsubscribe.value=$blogid;document.mainpage.submit();\">Unsubscribe</div>";
                }

                $gtb=explode('_', $_POST['gt_blogpost']);
                if($gtb[0] == $blogid and $gtb[1] != ''){
                    $newsql1=', CASE WHEN blogposts.id='.$gtb[1].' THEN 1 END AS readfirst';
                    $newsql2=' readfirst desc,';
                    $firstpost=$gtb[1];
                }

                echo "<input type='hidden' name='heartcomment' id='heartcomment' value='0'>";
                echo "<input type='hidden' name='deletecomment' id='deletecomment' value='0'>";
                echo "<input type='hidden' name='commentstat' id='commentstat' value='".$_POST['commentstat']."'>";
                echo "<input type='hidden' name='replystat' id='replystat' value='".$_POST['replystat']."'>";
                
                $in=0;
                $sql = "SELECT blogposts.id, title, post, date, COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END) AS zahl$newsql1 FROM blogposts, votes WHERE votes.type=0 AND votes.type_id=blogposts.id AND blog=$blogid AND post!='' AND title!='' GROUP BY blogposts.id ORDER by$newsql2 blogposts.id desc;";
                $out2 = mdq($bindung, $sql);
                while ($row2 = mysqli_fetch_row($out2)) {
                    $postid=$row2[0];
                    $title=$row2[1];

                    # POST EDIT
                    
                    $post=umlaute($row2[2]);
                    $post=str_replace('<a href=', '<a target="_blank" style="color:#00ff00" href=', Parsedown::instance()->text($post));
                    $post=str_replace('<img src=', "<img style='max-width:100%; max-height:300px;' src=", $post);
                    $i=1000;
                    while ( $i > 0 ){
                        $post=str_replace('[^'.$i.']:', "<a name='f$i' style='text-decoration:none; color:#40E0D0;font-weight:bold;'>&nbsp;&nbsp;&nbsp;$i:</a>", $post);
                        $post=str_replace('[^'.$i.']', "<a href='#f$i' style='text-decoration:none; color:#40E0D0'><sup>[$i]</sup></a>", $post);
                        $i--;
                    }

                    $origpost=nl2br(umlaute(dlt_html($row2[2])));
                    
                    # [END] POST EDIT

                    
                    if($_POST['commentstat'] == $postid){
                        $commentboxdisplay[$postid]='block';
                        $commentarrow_stylechanges='transform:rotate(0deg);';
                    }
                    else{
                        $commentboxdisplay[$postid]='none';
                        $commentarrow_stylechanges='';
                    }

                    
                    $date=$row2[3];
                    $votes=$row2[4];
                    $block='';
                    $sql = "SELECT vote FROM votes WHERE type_id=$postid AND type=0 AND user=$userid AND VOTE!=2;";
                    $out3 = mdq($bindung, $sql);
                    while ($row3 = mysqli_fetch_row($out3)) {
                        if($row3[0] == 0)
                            $block='border:1px solid #ff0000;';
                        else
                            $block='border:1px solid #00ff00;';
                    }
                    if($postid != ''){
                        if($_POST['votefor'.$postid] != 0){
                            $vote=$_POST['votefor'.$postid];
                            if($block == ""){
                                if($vote == -1){
                                    $vote=0;
                                    $votes--;
                                    $block='border:1px solid #ff0000;';
                                }
                                else{
                                    $votes++;
                                    $block='border:1px solid #00ff00;';
                                }
                                
                                $sql = "INSERT INTO votes SET type_id=$postid, type_catid=$blogid, type=0, user=$userid, VOTE=$vote;";
                                $out3 = mdq($bindung, $sql);
                            }
                        }

                        # COMMENTS

                        if(isset($_POST['comment'.$postid]) and $_POST['commenttext'.$postid] != "" and $_POST['commenttext'.$postid] != "Write Comment"){
                            $sql = "INSERT INTO comments SET user=$userid, content='".$_POST['commenttext'.$postid]."', type=0, type_id=$postid;";
                            $out3 = mdq($bindung, $sql);

                            $sql = "INSERT INTO hearts set user=-1, comment=LAST_INSERT_ID();";
                            $out3 = mdq($bindung, $sql);                            
                        }
                        
                        # [END] COMMENTS
                        
                        if($blogownerid == $userid)
                            $block='TRUE';

                        if($block != '')
                            $style='cursor:default;';
                        else
                            $style='';
                        
                        echo "<input type='hidden' name='votefor$postid' id='votefor$postid' value='0'><div class='post' onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(8%,8%,8%)';mousebtns$postid.style.display='block';bubbleview$postid.style.display='block';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='#000000';mousebtns$postid.style.display='none';bubbleview$postid.style.display='none';\">

<div style='float:left; cursor:pointer;' onmouseout=\"{$block}pnts$postid.style.display='block';{$block}voteup_$postid.style.display='none';{$block}votedown_$postid.style.display='none';\">
<img src='DATA/vote_white.png' class='pnts_up' style='display:none' id='voteup_$postid' onclick=\"votefor$postid.value=1;document.mainpage.submit();\" onmouseover=\"this.src='DATA/vote_green.png'\" onmouseout=\"this.src='DATA/vote_white.png'\">
<img src='DATA/vote_white.png' class='pnts_down' style='display:none' id='votedown_$postid' onclick=\"votefor$postid.value=-1;document.mainpage.submit();\" onmouseover=\"this.src='DATA/vote_red.png'\" onmouseout=\"this.src='DATA/vote_white.png'\">
</div>

<div class='pnts' style='$style$block' onmouseover=\"{$block}this.style.display='none';{$block}voteup_$postid.style.display='block';{$block}votedown_$postid.style.display='block';\" id='pnts$postid'>$votes</div>";

                        if($blogownerid == $userid){
                            echo "<div id='mousebtns$postid' style='display:none'><span class='grey greytxt right' onclick=\"deleteblogpost.value=$postid;document.mainpage.submit();\" ".str_replace('#ffffff', '#ff0000', $clickable_grey).">delete</span><span class='grey greytxt right' onclick=\"editblogpost.value=$postid;document.mainpage.submit();\" $clickable_grey>edit</span></div>

<span id='TRUEthis'></span><span id='TRUEvoteup_$postid'></span><span id='TRUEvotedown_$postid'></span><span id='TRUEpnts$postid'></span>";
                        }
                        else{
                            echo "<span id='mousebtns$postid'></span>";
                        }

                        $commentscount=0;
                        $commentplural='s';
                        $sql = "SELECT comments.id FROM comments WHERE type=0 AND type_id=$postid;";
                        $out3 = mdq($bindung, $sql);
                        while ($row3 = mysqli_fetch_row($out3)) {
                            $commentscount++;
                        }
                        if($commentscount == 1){
                            $commentplural='';
                        }
                        
                        echo "<div class='grey blogdate'>$date</div><div class='title posttitle'><span class='white'>#</span> $title</div>

<div class='postcontent'>
<div style='display:none' id='textpost$postid'>
$origpost
</div>

<div style='display:block;' id='htmlpost$postid'>
$post
</div>
</div>
&shy;
<span class='greytxt grey right' $clickable_grey style='display:none;' id='bubbleview$postid' onclick=\"if(textpost$postid.style.display == 'none'){ textpost$postid.style.display='block';htmlpost$postid.style.display='none';this.innerHTML='HTML-View'; }else{ textpost$postid.style.display='none';htmlpost$postid.style.display='block';this.innerHTML='Source-View'; }\">Source-View</span>

<hr class='commentline'>
<div class='opencomments' $clickable_txt onclick=\"if(comments$postid.style.display == 'none'){commentstat.value='$postid';comments$postid.style.display='block';einklappen$postid.style.transform='rotate(0deg)';}else{commentstat.value='0';comments$postid.style.display='none';einklappen$postid.style.transform='rotate(180deg)';}\"><img src='DATA/einklappen.png' class='einklappen' id='einklappen$postid' style='$commentarrow_stylechanges'>$commentscount Comment$commentplural</div>
<div class='comments' id='comments$postid' style='display:".$commentboxdisplay[$postid].";'>";
                            if($blogownerid != $userid){
                                echo "<textarea id='commentarea$postid' class='textarea commentarea' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='commenttext$postid'>Write Comment</textarea>
<input type='submit' name='comment$postid' value='send' class='btn commentsend' $clickable_btn>";
                            }


                            
                            $commentin=0;
                            $sql = "SELECT comments.content, user.username, user.id, comments.id, COUNT(CASE WHEN hearts.user!=-1 THEN 1 END) AS zahl FROM comments, user, hearts WHERE comments.user=user.id AND type=0 AND type_id=$postid AND hearts.comment=comments.id GROUP by comments.id ORDER by zahl desc, comments.id desc;";
                            $out3 = mdq($bindung, $sql);
                            while ($row3 = mysqli_fetch_row($out3)) {
                                if($row3[2] != ''){
                                    $commentid=$row3[3];
                                    
                                    # REPLYS

                                    if($_POST['replystat'] == $commentid){
                                        $replyboxdisplay[$commentid]='block';
                                        $replyarrow_stylechanges='transform:rotate(0deg);';
                                    }
                                    else{
                                        $replyboxdisplay[$commentid]='none';
                                        $replyarrow_stylechanges='';
                                    }
                                    
                                    if(isset($_POST['replysend'.$commentid]) and $_POST['replytext'.$commentid] != "" and $_POST['replytext'.$commentid] != "Write Reply"){
                                        $sql = "INSERT INTO comments SET user=$userid, content='".$_POST['replytext'.$commentid]."', type=1, type_id=$commentid;";
                                        $out4 = mdq($bindung, $sql);
                                    }
                                    
                                    # [END] REPLY

                                    
                                    
                                    if($_POST['heartcomment'] == $row3[3]){
                                        $heart_in=0;
                                        $sql = "SELECT id FROM hearts WHERE user=$userid AND comment=".$row3[3].";";
                                        $out4 = mdq($bindung, $sql);
                                        while ($row4 = mysqli_fetch_row($out4)) {
                                            $heart_in=1;
                                        }
                                        if($heart_in != 1){
                                            $sql = "INSERT INTO hearts SET comment=".$row3[3].", user=$userid;";
                                            $out4 = mdq($bindung, $sql);
                                            $row3[4]++;
                                        }
                                    }
                                    
                                    if($_POST['deletecomment'] == $row3[3] and $row3[2] == $userid){
                                        $sql = "DELETE FROM comments WHERE id=".$row3[3].";";
                                        $out4 = mdq($bindung, $sql);
                                    }else{

                                        $replyscount=0;
                                        $replyplural='s';
                                        $sql = "SELECT comments.id FROM comments WHERE type=1 AND type_id=$commentid;";
                                        $out4 = mdq($bindung, $sql);
                                        while ($row4 = mysqli_fetch_row($out4)) {
                                            $replyscount++;
                                        }
                                        if($replyscount == 1){
                                            $replyplural='';
                                        }
                                        if($replyscount == 0){
                                            $zeroreplysaddon='style="display:none;"';
                                            $zeroreplysaddon2='reply';
                                        }
                                        else{
                                            $zeroreplysaddon='';
                                            $zeroreplysaddon2='';
                                        }

                                        
                                        echo "<div class='post commentpost $zeroreplysaddon2' id='commentcontent$commentid' onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(16%,16%,16%)';mousebtns{$postid}_".$row3[3].".style.display='inline-block';mousebtns_2_{$postid}_".$row3[3].".style.display='inline-block';replys".$row3[3].".style.display='inline-block';replys2".$row3[3].".style.display='inline-block';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='transparent';mousebtns{$postid}_".$row3[3].".style.display='none';mousebtns_2_{$postid}_".$row3[3].".style.display='none';replys".$row3[3].".style.display='none';replys2".$row3[3].".style.display='none';\">";

                                        $full='';
                                        $heartcursor='';

                                        if($row3[2] != $userid){
                                            $clickable_heart="onmouseover=\"this.style.backgroundColor='#ffffff';this.style.color='#000000';heart".$row3[3].".style.filter='invert(1)';\" onmouseout=\"this.style.backgroundColor='transparent';this.style.color='#ffffff';heart".$row3[3].".style.filter='invert(0)';\"";
                                        }
                                        else{
                                            $clickable_heart='';
                                            $full='_full';
                                            $heartcursor='cursor:default;';
                                        }
                                    
                                        $sql = "SELECT id FROM hearts WHERE user=$userid AND comment=".$row3[3].";";
                                        $out4 = mdq($bindung, $sql);
                                        while ($row4 = mysqli_fetch_row($out4)) {
                                            $clickable_heart='';
                                            $full='_full';
                                            $heartcursor='cursor:default;';
                                        }
                                                                        
                                        echo "<div class='greytxt grey commentowner' $clickable_grey>".$row3[1]."</div>";

                                        if($row3[2] == $userid){
                                            echo "<span class='grey' style='display:none;font-size:14px;' id='mousebtns_2_{$postid}_".$row3[3]."'>&nbsp;&#183;&nbsp;</span><div style='display:none;cursor:pointer;color:rgb(60%, 60%, 60%);font-size:14px' style='grey greytxt' id='mousebtns{$postid}_".$row3[3]."' onclick=\"deletecomment.value=".$row3[3].";document.mainpage.submit();\" ".str_replace('#ffffff', '#ff0000', $clickable_grey).">delete</div>";
                                        }
                                        else{
                                            echo "<span class='grey' style='display:none;font-size:14px;' id='replys2".$row3[3]."'>&nbsp;&#183;&nbsp;</span><div style='display:none;cursor:pointer;color:rgb(60%, 60%, 60%);font-size:14px' style='grey greytxt' id='replys".$row3[3]."' onclick=\"replycontent$commentid.style.display='block';replystat.value='$commentid';replycontent$commentid.style.display='block';reply_einklappen$commentid.style.transform='rotate(0deg)';replyarea$commentid.focus();replyline$commentid.style.display='block';opencomments$commentid.style.display='block';commentcontent$commentid.classList.remove('reply');\" $clickable_grey>reply</div>
<span id='mousebtns_2_{$postid}_".$row3[3]."'></span><span id='mousebtns{$postid}_".$row3[3]."'></span>";
                                        }

                                        echo "<div class='commentbox'>".$row3[0]."</div><div class='heartfield' style='$heartcursor' $clickable_heart onclick=\"{$full}heartcomment.value='".$row3[3]."';document.mainpage.submit();\">".$row3[4]." <img src='DATA/heart{$full}.png' id='heart".$row3[3]."' style='margin-bottom:-3px;width:16px;'></div>";

                                        ### <------- REPLYS -------> ##
                                        
                                        echo "<hr class='commentline' id='replyline$commentid' $zeroreplysaddon><div class='opencomments' id='opencomments$commentid' $zeroreplysaddon $clickable_txt 

onclick=\"if(replycontent$commentid.style.display == 'none'){replycontent$commentid.style.display='block';replystat.value='$commentid';replycontent$commentid.style.display='block';reply_einklappen$commentid.style.transform='rotate(0deg)';}else{replystat.value='0';replycontent$commentid.style.display='none';reply_einklappen$commentid.style.transform='rotate(180deg)';}\"

><img src='DATA/einklappen.png' class='einklappen' id='reply_einklappen$commentid' style='$replyarrow_stylechanges'>$replyscount Reply$replyplural</div><div class='comments' id='replycontent$commentid' style='display:".$replyboxdisplay[$commentid]."'>";
                                        
                                        echo "<textarea id='replyarea$commentid' class='textarea commentarea' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='replytext$commentid'>Write Reply</textarea>
<input type='submit' name='replysend$commentid' value='send' class='btn commentsend' $clickable_btn>";
                                        
                                        
                                        $replyin=0;
                                        $sql = "SELECT comments.content, user.username, user.id, comments.id FROM comments, user WHERE comments.user=user.id AND comments.type=1 AND comments.type_id=$commentid ORDER by comments.id;";
                                        $out4 = mdq($bindung, $sql);
                                        while ($row4 = mysqli_fetch_row($out4)) {
                                        if($row4[3] != ''){
                                                $replyid=$row4[3];
                                                
                                                if($_POST['deletecomment'] == 'reply'.$row4[3] and $row4[2] == $userid){
                                                    $sql = "DELETE FROM comments WHERE id=".$row4[3].";";
                                                    $out5 = mdq($bindung, $sql);
                                                }else{
                                                    
                                                    echo "<div class='post commentpost reply' onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(24%,24%,24%)';mousebtns{$postid}_{$commentid}_".$row4[3].".style.display='inline-block';mousebtns_2_{$postid}_{$commentid}_".$row4[3].".style.display='inline-block';replys{$commentid}_".$row4[3].".style.display='inline-block';replys2{$commentid}_".$row4[3].".style.display='inline-block';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='transparent';mousebtns{$postid}_{$commentid}_".$row4[3].".style.display='none';mousebtns_2_{$postid}_{$commentid}_".$row4[3].".style.display='none';replys{$commentid}_".$row4[3].".style.display='none';replys2{$commentid}_".$row4[3].".style.display='none';\">";
                                                    
                                                    
                                                    echo "<div class='greytxt grey commentowner' $clickable_grey>".$row4[1]."</div>";
                                                    
                                                    if($row4[2] == $userid){
                                                        echo "<span class='grey' style='display:none;font-size:14px;' id='mousebtns_2_{$postid}_{$commentid}_".$row4[3]."'>&nbsp;&#183;&nbsp;</span><div style='display:none;cursor:pointer;color:rgb(60%, 60%, 60%);font-size:14px' style='grey greytxt' id='mousebtns{$postid}_{$commentid}_".$row4[3]."' onclick=\"deletecomment.value='reply".$row4[3]."';document.mainpage.submit();\" ".str_replace('#ffffff', '#ff0000', $clickable_grey).">delete</div>";
                                                    }
                                                    else{
                                                        echo "<span class='grey' style='display:none;font-size:14px;' id='replys2{$commentid}_".$row4[3]."'>&nbsp;&#183;&nbsp;</span><div style='display:none;cursor:pointer;color:rgb(60%, 60%, 60%);font-size:14px' style='grey greytxt' id='replys{$commentid}_".$row4[3]."' onclick=\"replycontent$commentid.style.display='block';replystat.value='$commentid';replycontent$commentid.style.display='block';reply_einklappen$commentid.style.transform='rotate(0deg)';replyarea$commentid.focus();\" $clickable_grey>reply</div>
<span id='mousebtns_2_{$postid}_{$commentid}_".$row4[3]."'></span><span id='mousebtns{$postid}_{$commentid}_".$row4[3]."'></span>";
                                                    }

                                                    echo "<div class='commentbox'>".$row4[0]."</div></div>";
                                                    $replyin=1;
                                                }                                        
                                            }
                                        }

                                        if($replyin == 0){
                                            echo "<center style='padding-bottom:15px;'>=== NO REPLYS ===</center>";
                                        }

                                        
                                        echo "</div>";
                                        
                                        ### <--END-- REPLYS -------> ##
                                        
                                        echo "</div>";
                                        
                                        $commentin=1;
                                    }
                                }
                            }
                            if($commentin == 0){
                                echo "<center style='padding-bottom:30px;'>=== NO COMMENTS ===</center>";
                            }
                            
                            echo "</div></div>";
                            
                            if($firstpost == $postid){
                                echo "<hr class='firstpostline'>";
                            }
                            
                            
                            
                            $in=1;
                            
                    }
                }
            
                if($in == 0)
                    echo "<center>=== NO POSTS ===</center>";

                if($blogownerid == $userid){
                    echo "<input type='hidden' name='deleteblog' id='deleteblog' value='0'><span class='right greytxt' onclick=\"deleteblog.value=$blogid;document.mainpage.submit();\" ".str_replace('#00ff00', '#ff0000', $clickable_txt).">Delete Blog</span><br></div>";
                }

            }
        }
    }
    else{
        $_POST['gt_blogpost']='';
    }

    if($password == 'NONE'){
#        echo "<div style='position:fixed;width:calc(".($sec/(60*60)*100)."% - 0px);left:0px;bottom:0px;background-color:#00ff00;color:black;text-align:center;'>$timeout</div>";
        echo "<div style='position:fixed;width:90px;right:0px;bottom:0px;background-color:#00ff00;color:black;text-align:center;'>$timeout</div>";
    }
}

echo "
<input type='hidden' name='gt_blogpost' id='gt_blogpost' value='".$_POST['gt_blogpost']."'>
<input type='hidden' name='catsite' id='catsite' value='$catsite'>
<input type='hidden' name='site' id='site' value='$site'>
<input type='hidden' name='userid' id='userid' value='$userid'>
<input type='hidden' name='username' id='username' value='$username'>
<input type='hidden' name='password' id='password' value='$password'>
";

# END OF FILE
echo "
</form>
</body>
</html>";
?>
