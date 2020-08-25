<?php

#! CONTRIBUTOR:
#! 
#! - Levi Jacobs (jcobs-engine)
#!
#! © Copyright by Levi Jacobs, All rights reserved


##############################################
##                                          ##
##              VERSION: v1                 ##
##                                          ##
##############################################

$version='v1';

include("CODEBLOCKS/functions.php");

$file='<html>
  <head>
    <title>MainPage</title>
    <meta name="description" content="A free, open source and anonymous social
				      network. Create and read blogs, forums and chats, or participate in
				      votings. Be a part of the community!">
    <meta name="keywords" content="mainpage, social network, network, anonymous,
				   community, blogs, blog, forum, forums, chat, chats, votings">
    <link rel="shortcut icon" type="image/x-icon" href="/DATA/icon.ico">
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link href="/font.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/stylesheet.css">
    <meta http-equiv="refresh" content="1; URL=/content.php?type=%4&id=%5">
  </head>
  <body style="background-color:black;color:#00ff00;font-family:Source Code Pro">Redirect...</body>
</html>';

# VARIABLES [POST]
$userid=$_POST['userid'];

$username=$_POST['username'];
$password=$_POST['password'];
$site=$_POST['site'];
$catsite=$_POST['catsite'];

if($userid == ""){
    $title='MainPage - A Free and Anonymous Social Network';
}
else
{
    $title='MainPage';
}

if($site == '')
    $site=0;
if($catsite == '')
    $catsite=0;


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
        $bodyaddon='setTimeout( function(){ document.mainpage.submit(); }, '.($sec+1).'000 );';
        if($sec <= 0){
            $userid='';
            $username='';
            $password='';
        }
    }
}

if(isset($_POST['editpubkey'])){
    $bodyaddon.="kd".$_POST['editpubkey_kd'].".click();";
}

if(isset($_POST['editdescription'])){
    $bodyaddon.="kd".$_POST['editdescription_kd'].".click();";
}

### DELETING BY TIMESTAMP ###

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
    $sql3 = "DELETE FROM votings WHERE type=1 AND typeid=$did;";
    $out3 = mdq($bindung, $sql3);
    $sql3 = "DELETE FROM subscriptions WHERE type=1 AND type_id=$did;";
    $out3 = mdq($bindung, $sql3);
    
    $sql2 = "SELECT id FROM blogs WHERE owner=$did;";
    $out2 = mdq($bindung, $sql2);
    while ($row2 = mysqli_fetch_row($out2)) {
        $sql3 = "DELETE FROM subscriptions WHERE type=0 AND type_id=".$row2[0].";";
        $out3 = mdq($bindung, $sql3);
        $sql3 = "DELETE FROM votings WHERE type=0 AND typeid=".$row2[0].";";
        $out3 = mdq($bindung, $sql3);

        $sql3 = "SELECT id FROM blogposts WHERE blog=".$row2[0].";";
        $out3 = mdq($bindung, $sql3);
        while ($row3 = mysqli_fetch_row($out3)) {
            $sql4 = "DELETE FROM votings WHERE type=2 AND typeid=".$row3[0].";";
            $out4 = mdq($bindung, $sql4);
        }
    
        $sql3 = "DELETE FROM blogposts WHERE blog=".$row2[0].";";
        $out3 = mdq($bindung, $sql3);
    }
    
    $sql3 = "DELETE FROM votings WHERE owner=$did;";
    $out3 = mdq($bindung, $sql3);
    $sql2 = "DELETE FROM blogs WHERE owner=$did;";
    $out2 = mdq($bindung, $sql2);
    $sql2 = "DELETE FROM user WHERE id=$did;";
    $out2 = mdq($bindung, $sql2);
}    

$sql = "SELECT id, type, typeid FROM votings WHERE time+60*60*24-".time()."<=0;";
$out = mdq($bindung, $sql);
while ($row = mysqli_fetch_row($out)) {
    $provote=0;
    $contravote=0;
    $sql = "SELECT vote FROM votes WHERE type=1 AND type_id=".$row[0].";";
    $out2 = mdq($bindung, $sql);
    while ($row2 = mysqli_fetch_row($out2)) {
        if($row2[0] == 0){
            $contravote++;
        }
        if($row2[0] == 1){
            $provote++;
        }
    }

    if( $provote > $contravote ){
        if($row[1] == 0){
            $sql = "SELECT id FROM blogposts WHERE blog=".$row[2].";";
            $out2 = mdq($bindung, $sql);
            while ($row2 = mysqli_fetch_row($out2)) {
                $sql = "DELETE FROM votings WHERE type=2 AND typeid=".$row2[0].";";
                $out3 = mdq($bindung, $sql);
            }
            
            $sql = "DELETE FROM blogs WHERE id=".$row[2].";";
            $out2 = mdq($bindung, $sql);

            $sql = "DELETE FROM blogposts WHERE blog=".$row[2].";";
            $out2 = mdq($bindung, $sql);
        }
        elseif($row[1] == 1){
            $sql = "DELETE FROM user WHERE id=".$row[2].";";
            $out2 = mdq($bindung, $sql);

            $sql = "SELECT id FROM blogs WHERE owner=".$row[2].";";
            $out2 = mdq($bindung, $sql);
            while ($row2 = mysqli_fetch_row($out2)) {
                $sql = "SELECT id FROM blogposts WHERE blog=".$row2[0].";";
                $out3 = mdq($bindung, $sql);
                while ($row3 = mysqli_fetch_row($out3)) {
                    $sql = "DELETE FROM votings WHERE type=2 AND typeid=".$row3[0].";";
                    $out4 = mdq($bindung, $sql);
                }
                $sql = "DELETE FROM blogposts WHERE blog=".$row2[0].";";
                $out3 = mdq($bindung, $sql);
                
                $sql = "DELETE FROM votings WHERE type=0 AND typeid=".$row2[0].";";
                $out3 = mdq($bindung, $sql);
            }
            
            $sql = "DELETE FROM blogs WHERE owner=".$row[2].";";
            $out2 = mdq($bindung, $sql);
            
        }
        elseif($row[1] == 2){
            $sql = "DELETE FROM blogposts WHERE id=".$row[2].";";
            $out2 = mdq($bindung, $sql);
        }
    }

    $sql = "DELETE FROM votings WHERE id=".$row[0].";";
    $out2 = mdq($bindung, $sql);
}


# HEAD
echo "<!DOCTYPE html>
<html>
<head>

<title>$title</title>
<meta name='description' content='A free, open source and anonymous social network. Create and read blogs, forums and chats, or participate in votings. Be a part of the community!'>
<meta name='keywords' content='mainpage, social network, network, anonymous, community, blogs, blog, forum, forums, chat, chats, votings'>

<meta charset='utf-8'>
<meta http-equiv='content-type' content='text/html; charset=utf-8'>

<script src='/jquery.min.js'></script>
<link href='/font.css' rel='stylesheet'>
<link rel='stylesheet' type='text/css' href='/stylesheet.css'>
<link rel='shortcut icon' type='image/x-icon' href='/DATA/icon.ico'>

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

</head>

<body onload=\"$bodyaddon\">
<form autocomplete='off' name='mainpage' id='mainpage' method='POST' action='main.php'>
<!-- Prevent implicit submission of the form -->
<button type='submit' disabled style='display: none' aria-hidden='true'></button>

";


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

    $sql = "SELECT LAST_INSERT_ID();";
    $out = mdq($bindung, $sql);
    while ($row = mysqli_fetch_row($out)) {
        $lastinsertid=$row[0];
    }

    $file=str_replace('%4', "2", $file);
    $file=str_replace('%5', "$lastinsertid", $file);
    
    $out=shell_exec("mkdir content/$username;");
    $out=shell_exec("echo '$file' > content/$username/index.html");

    $sql = "INSERT INTO subscriptions SET user=0, type=1, type_id=$lastinsertid;";
    $out = mdq($bindung, $sql);
    $sec=(time()+(60*60))-time();
    $timeout=sprintf('%02d:%02d:%02d', ($sec/3600),($sec/60%60), $sec%60);
    $bodyaddon='setTimeout( function(){ document.mainpage.submit(); }, '.($sec+1).'000 );';
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
                $out=shell_exec("mkdir content/$username;");
                    
                $password_orig=md5($password);
                $password=md5('#'.$password.'#');
                $sql = "INSERT INTO user SET username='$username', password='$password', password_orig='$password_orig';";
                $out = mdq($bindung, $sql);

                $sql = "SELECT LAST_INSERT_ID();";
                $out = mdq($bindung, $sql);
                while ($row = mysqli_fetch_row($out)) {
                    $lastinsertid=$row[0];
                }
                
                $file=str_replace('%4', "2", $file);
                $file=str_replace('%5', "$lastinsertid", $file);
                
                $out=shell_exec("mkdir content/$username;");
                $out=shell_exec("echo '$file' > content/$username/index.html");
                
                $sql = "INSERT INTO subscriptions SET user=0, type=1, type_id=$lastinsertid;";
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

        
    echo "<a href='$URL'><img id='login_logo' src='/DATA/logo_$version.png'></a>";
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

    ###       ADS ###

    echo "<iframe src='ADS/adcontent.php' frameborder='0' scrolling='no' class='ads' allowTransparency='true'></iframe>";
    
    ### [END] ADS ###

}else{

    # ==== BEGINNING FNCTNS ==== #

    if($_POST['deleteblog'] != 0){
        $sql = "DELETE FROM blogs WHERE id=".$_POST['deleteblog']." and owner=$userid;";
        $out = mdq($bindung, $sql);
        $sql = "DELETE FROM votings WHERE type=0 AND typeid=".$_POST['deleteblog'].";";
        $out = mdq($bindung, $sql);
        $sql = "SELECT id FROM blogposts WHERE blog=".$_POST['deleteblog'].";";
        $out = mdq($bindung, $sql);
        while ($row = mysqli_fetch_row($out)) {
            $sql = "DELETE FROM votings WHERE type=2 AND typeid=".$row[0].";";
            $out2 = mdq($bindung, $sql);
        }
        $catsite=1;
    }

    
    if(isset($_POST['report'])){
        $reportid=explode('#', $catsite);
        $reportid=explode(':', $reportid[1]);
        $reportype=$reportid[0];
        $reportid=$reportid[1];    
        $reportuser=$_POST['reportuser'];

        $reportdescription=$_POST['reportdescription'];
        if($reportdescription == 'Write description'){
            $reportdescription='';
        }
        
        if($reportype == 'blog'){
            $sql = "SELECT owner FROM blogs WHERE blogs.id=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $againstuserid=$row[0];
            }
            $in=0;
            $sql = "SELECT id FROM votings WHERE votings.type=0 AND votings.typeid=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $in=1;
            }
            if($in == 0){
                $sql = "INSERT INTO votings SET type=0, typeid=$reportid, description='$reportdescription', time=".time().", owner=$userid;";
                $out = mdq($bindung, $sql);
                
                $sql = "SELECT LAST_INSERT_ID();";
                $out = mdq($bindung, $sql);
                while ($row = mysqli_fetch_row($out)) {
                    $reportingid=$row[0];
                }
                
                $sql = "INSERT INTO votes SET vote=0, type=1, type_id=$reportingid, user=$againstuserid;";
                $out = mdq($bindung, $sql);
                
                $sql = "INSERT INTO votes SET vote=1, type=1, type_id=$reportingid, user=$userid;";
                $out = mdq($bindung, $sql);
            }
        }

        if($reportype == 'blogpost'){
            $sql = "SELECT blogs.owner FROM blogs, blogposts WHERE blogs.id=blogposts.blog AND blogposts.id=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $againstuserid=$row[0];
            }
            $in=0;
            $sql = "SELECT id FROM votings WHERE votings.type=2 AND votings.typeid=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $in=1;
            }
            if($in == 0){
                $sql = "INSERT INTO votings SET type=2, typeid=$reportid, description='$reportdescription', time=".time().", owner=$userid;";
                $out = mdq($bindung, $sql);
                
                $sql = "SELECT LAST_INSERT_ID();";
                $out = mdq($bindung, $sql);
                while ($row = mysqli_fetch_row($out)) {
                    $reportingid=$row[0];
                }
                
                $sql = "INSERT INTO votes SET vote=0, type=1, type_id=$reportingid, user=$againstuserid;";
                $out = mdq($bindung, $sql);
                
                $sql = "INSERT INTO votes SET vote=1, type=1, type_id=$reportingid, user=$userid;";
                $out = mdq($bindung, $sql);
            }
        }
        
        if($reportuser == 1 or $reportype == 'user'){
            if($reportype != "user"){
                $reportid=$againstuserid;
            }
            
            $in=0;
            $sql = "SELECT id FROM votings WHERE votings.type=1 AND votings.typeid=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $in=1;
            }
            if($in == 0){
                $sql = "INSERT INTO votings SET type=1, typeid=$reportid, description='$reportdescription', time=".time().", owner=$userid;";
                $out = mdq($bindung, $sql);
                
                $sql = "SELECT LAST_INSERT_ID();";
                $out = mdq($bindung, $sql);
                while ($row = mysqli_fetch_row($out)) {
                    $reportingid=$row[0];
                }
                
                $sql = "INSERT INTO votes SET vote=0, type=1, type_id=$reportingid, user=$reportid;";
                $out = mdq($bindung, $sql);
                
                $sql = "INSERT INTO votes SET vote=1, type=1, type_id=$reportingid, user=$userid;";
                $out = mdq($bindung, $sql);
                
            }
        }
        
        $site=0;
        $catsite=1;
    }

    if(isset($_POST['report_fail'])){
        $site=0;
        $catsite=1;
    }

    # ==== START ==== #

    # SET OWN-USERID TO MY PROFILE
    if(strpos($catsite,"user#")!==false){
        $profileid=explode('#', $catsite);
        $profileid=$profileid[1];
        if($profileid == $userid){
            $site=1;
            $catsite=0;
        }
    }
        
        
    
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
<div class='catheader_child' id='ch0' onclick=\"catsite.value=0;document.mainpage.submit();\" $clickable_txt_cat0><span style='border-bottom:${catsitepx0}px solid #00ff00'>Trends</span></div>
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
<div class='catheader_child' id='ch1' onclick=\"catsite.value=1;document.mainpage.submit();\" $clickable_txt_cat1><span style='border-bottom:${catsitepx1}px solid #00ff00'>Subscriptions</span></div>
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
<img class='logo clickable' src='/DATA/logo_$version.png' onclick=\"catsite.value=0;site.value=0;document.mainpage.submit();\" onmouseover=\"this.src='/DATA/greenlogo_$version.png';\" onmouseout=\"this.src='/DATA/logo_$version.png';\">
<!-- SITE NO. 1 --><div class='header_child' ".$clickable_txt1." onclick=\"catsite.value=0;site.value=1;document.mainpage.submit();\"><span style='border-bottom:${userpx}px solid #00ff00'>Users</span></div>
<!-- SITE NO. 2 --><div style='display:none' class='header_child' ".$clickable_txt2." onclick=\"catsite.value=0;site.value=2;document.mainpage.submit();\"><span style='border-bottom:${grouppx}px solid #00ff00'>Groups</span></div>
<!-- SITE NO. 3 --><div style='display:none' class='header_child' ".$clickable_txt3." onclick=\"catsite.value=0;site.value=3;document.mainpage.submit();\"><span style='border-bottom:${forumpx}px solid #00ff00'>Forums</span></div>
<!-- SITE NO. 4 --><div class='header_child' ".$clickable_txt4." onclick=\"catsite.value=0;site.value=4;document.mainpage.submit();\"><span style='border-bottom:${blogpx}px solid #00ff00'>Blogs</span></div>
</div>
<div id='catheader'>
<div id='charrow'>></div>$chsites
</div>
<div style='height:220px;'></div>
";

    # ===============  ALL FNCTNS  =============== #

    if($_POST['vote_voting_id'] != "" and $_POST['vote_type'] != ""){
        $in=0;
        $sql = "SELECT id FROM votes WHERE user=$userid AND type_id=".$_POST['vote_voting_id']." AND type=1;";
        $out = mdq($bindung, $sql);
        while ($row = mysqli_fetch_row($out)) {
            $in=1;
        }
        if($in == 0){
            $sql = "INSERT INTO votes SET vote=".$_POST['vote_type'].", type=1, type_id=".$_POST['vote_voting_id'].", user=$userid;";
            $out = mdq($bindung, $sql);
        }
    }
    
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

                $out=shell_exec("mv content/$username/ content/$editusername/ 2>&1");

                echo $out;
                
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

                # CREATE LINK
                
                $file=str_replace('%4', "0", $file);
                $file=str_replace('%5', "$bloginsertid", $file);
                                        
                $out=shell_exec("mkdir content/$username/$createblog_name/");
                $out=shell_exec("echo '$file' > content/$username/$createblog_name/index.html");
                
                # END create link
                
                $sql = "INSERT INTO subscriptions SET type=0, type_id=$bloginsertid, user=0;";
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

    if(isset($_POST['editpubkey']) and $_POST['pubkey'] != "Add GnuPG Public Key"){
        $sql = "UPDATE user SET pubkey='".$_POST['pubkey']."' WHERE id=$userid;";
        $out = mdq($bindung, $sql);
    }

    if(isset($_POST['editdescription']) and $_POST['description'] != "Add Description"){
        $description=str_replace("'", "\'", $_POST['description']);
        $sql = "UPDATE user SET description='".$description."' WHERE id=$userid;";
        $out = mdq($bindung, $sql);
    }

    if($_POST['votebackbtn'] == 1){
        echo "<div class='votings_opencontent votebackbtn' $clickable_showbtn onclick=\"site.value=0;catsite.value=1;document.mainpage.submit();\">Back to Votings</div>";
    }
    
    
    # ===============  ALL SITES   =============== #

    
    if($site == 0){
        # $$$ TRENDS $$$ #
        if($catsite == 0){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Trends</div>";

            echo "<input type='hidden' name='heartcomment' id='heartcomment' value='0'>";
            echo "<input type='hidden' name='deletecomment' id='deletecomment' value='0'>";
            echo "<input type='hidden' name='commentstat' id='commentstat' value='".$_POST['commentstat']."'>";
            echo "<input type='hidden' name='replystat' id='replystat' value='".$_POST['replystat']."'>";

            $in=0;
            $sql = "SELECT blogposts.id, title, post, date, COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END) AS zahl, blogposts.blog FROM blogposts, votes, blogs WHERE blogs.id=blogposts.blog AND votes.type=0 AND votes.type_id=blogposts.id AND post!='' AND title!='' GROUP BY blogposts.id ORDER by ROUND(blogposts.time/60/60/24/7) desc, zahl desc, blogposts.id desc LIMIT 5;";
            $out2 = mdq($bindung, $sql);
            while ($row2 = mysqli_fetch_row($out2)) {

                # MISSING VARIABLES #

                $sql="SELECT blogs.owner, blogs.id, user.username, blogs.name, user.id FROM blogs, user WHERE blogs.id=$row2[5] AND blogs.owner=user.id;";
                $out3 = mdq($bindung, $sql);
                while ($row3 = mysqli_fetch_row($out3)) {
                    $blogownerid=$row3[0];
                    $blogid=$row3[1];
                    $blogownername=$row3[2];
                    $blogname=$row3[3];
                    $profileid=$row3[4];
                }
                
                # [END] MISSING VARIABLES #

                $postid=$row2[0];
                $title=$row2[1];
                $SETTING['trenddesign']=1;
                $SETTING['contentdesign']=0;

                $blogowner=$blogownername;
                
                include("CODEBLOCKS/blogpost.php");
                
            }

            if($in == 0){
                echo "<center class='bold'>WELCOME TO</center><img src='/DATA/logo_$version.png' class='logo welcomemsg'>";
            }
            
            echo "</div>";
        }
        # $$$ VOTINGS $$$ #
        if($catsite == 1){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Votings</div>";

            $in=0;
            $sql = "SELECT votings.type, votings.typeid, votings.description, votings.time, user.username, votings.id, votings.owner FROM votings, user WHERE votings.owner=user.id ORDER by time;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $votetype=$row[0];
                $votetypeid=$row[1];
                $votedescription=$row[2];
                $votetime=(60*60*24)-(time()-$row[3]);
                $voteowner=$row[4];
                $votecountdown=sprintf('%02d:%02d:%02d', ($votetime/3600),($votetime/60%60), $votetime%60);
                $profileid=$row[6];
                
                if($votedescription != ""){
                    $votedescription="<tr><td class='reporttable'><span class='grey'>Description:</span> </td><td class='reporttable'>$votedescription</td></tr>";
                }
                
                if($votetype == 0){
                    $sql = "SELECT blogs.name, user.username FROM blogs, user WHERE blogs.owner=user.id AND blogs.id=$votetypeid;";
                    $out2 = mdq($bindung, $sql);
                    while ($row2 = mysqli_fetch_row($out2)) {
                        $votetitle="Deleting Blog <span class='white'>".$row2[0]."</span> by <span class='white'>".$row2[1]."</span>?";
                    }
                    $voting_showtxt='Blog';
                    $gotoreport="onclick=\"site.value=4;catsite.value='blog#$votetypeid';votebackbtn.value=1;document.mainpage.submit();\"";
                }
                elseif($votetype == 1){
                    $sql = "SELECT user.username FROM user WHERE user.id=$votetypeid;";
                    $out2 = mdq($bindung, $sql);
                    while ($row2 = mysqli_fetch_row($out2)) {
                        $votetitle="Kick User <span class='white'>".$row2[0]."</span>?";
                    }
                    $voting_showtxt='User';
                    $gotoreport="onclick=\"site.value=1;catsite.value='user#$votetypeid';votebackbtn.value=1;document.mainpage.submit();\"";
                }
                if($votetype == 2){
                    $sql = "SELECT user.username, blogs.id, blogposts.title FROM blogposts, blogs, user WHERE blogs.owner=user.id AND blogs.id=blogposts.blog AND blogposts.id=$votetypeid;";
                    $out2 = mdq($bindung, $sql);
                    while ($row2 = mysqli_fetch_row($out2)) {
                        $votetitle="Deleting Post <span class='white'>\"".$row2[2]."\"</span> by <span class='white'>".$row2[0]."</span>?";
                        $blogid=$row2[1];
                    }
                    $voting_showtxt='Post';
                    $gotoreport="onclick=\"site.value=4;catsite.value='blog#$blogid';gt_blogpost.value='{$blogid}_{$votetypeid}';votebackbtn.value=1;document.mainpage.submit();\"";
                }
                
                $clickable_votingbtn_green=$clickable_votingbtn_green_cap;
                $clickable_votingbtn_red=$clickable_votingbtn_red_cap;
                $voteaddonblock="";
                $voteaddonstyle1="";
                $voteaddonstyle2="";
                $sql = "SELECT vote FROM votes WHERE type=1 AND type_id=".$row[5]." AND user=$userid;";
                $out2 = mdq($bindung, $sql);
                while ($row2 = mysqli_fetch_row($out2)) {
                    if($row2[0] == 0){
                        $voteaddonstyle2="cursor:default;background-color:transparent;color:#ff0000;";
                        $voteaddonstyle1="cursor:default;background-color:transparent;color:#ffffff;";
                    }
                        
                    if($row2[0] == 1){
                        $voteaddonstyle1="cursor:default;background-color:transparent;color:#00ff00;";
                        $voteaddonstyle2="cursor:default;background-color:transparent;color:#ffffff;";
                    }

                    $clickable_votingbtn_green='';
                    $clickable_votingbtn_red='';
                    $voteaddonblock="block";
                }

                $provote=0;
                $contravote=0;
                $sql = "SELECT vote FROM votes WHERE type=1 AND type_id=".$row[5].";";
                $out2 = mdq($bindung, $sql);
                while ($row2 = mysqli_fetch_row($out2)) {
                    if($row2[0] == 0){
                        $contravote++;
                    }
                    if($row2[0] == 1){
                        $provote++;
                    }
                }

                $voteprozent_1=round($provote/($provote+$contravote)*100).'%';
                $voteprozent_2=round($contravote/($provote+$contravote)*100).'%';

                $vote_s_char1='s';
                $vote_s_char2='s';

                if($provote == 1){
                    $vote_s_char1='';
                }

                if($contravote == 1){
                    $vote_s_char2='';
                }

                echo "<div class='post' onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(8%,8%,8%)';mousebtns$postid.style.display='block';bubbleview$postid.style.display='block';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='#000000';mousebtns$postid.style.display='none';bubbleview$postid.style.display='none';\">

<div class='grey blogdate'>$votecountdown</div><div class='title posttitle'><span class='white'>#</span> $votetitle <span class='grey'>Voting by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$voteowner</span></span></div>
<table class='reporttable_element'>
$votedescription
<tr><td class='reporttable'><span class='grey'>Votes:</span> </td><td class='reporttable'>".($provote+$contravote)."</td></tr>
</table>
<div class='votings_opencontent' $gotoreport $clickable_showbtn>Show $voting_showtxt</div>
";
                    echo "<div class='votebalktxt back_white' $clickable_votingbtn_green onclick=\"{$voteaddonblock}vote_voting_id.value='".$row[5]."';vote_type.value='1';document.mainpage.submit();\" style='$voteaddonstyle1'>Yes</div>";
                    if($voteprozent_1 != "0%")
                        echo "<div class='votebalk back_green' style='width:calc($voteprozent_1 - 160px);'>$voteprozent_1</div>";
                    
                    echo " <span class='grey'>($provote Vote$vote_s_char1)</span><br><div class='votebalktxt back_white' $clickable_votingbtn_red onclick=\"{$voteaddonblock}vote_voting_id.value='".$row[5]."';vote_type.value='0';document.mainpage.submit();\" style='$voteaddonstyle2'>No</div>";
                    if($voteprozent_2 != "0%")
                        echo "<div class='votebalk back_red' style='width:calc($voteprozent_2 - 160px);'>$voteprozent_2</div>";

echo "
 <span class='grey'>($contravote Vote$vote_s_char2)</span>
<p>
</div>";
                $in=1;
            }

            if($in == 0){
                echo "<center>=== NO VOTINGS ===</span>";
            }
            
            echo "<input type='hidden' id='vote_voting_id' name='vote_voting_id'>
<input type='hidden' id='vote_type' name='vote_type'>";
            
            echo "</div>";
        }
        # $$$ ABOUT $$$ #
        if($catsite == 2){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> About</div>";
            echo "<div class='centerbox'><b>MainPage</b> is a free-speech and anonymous social network. You don't need to enter your email address to register. In addition, MainPage has no hierarchical structure. There are <i>no moderators or administrators</i>, everyone can start a voting on whether to kick a user or delete a blog post.<p>
There is a trending page that shows the latest posts with the most upvotes. Everyone can create their own blogs, discuss in forums or chat with friends.<p>
Please note that MainPage is currently still in a very <i>early development</i> phase and functions such as forums or groups have not yet been implemented. But that changes over time, because new updates come every week!<p>
MainPage is and remains free and open source! But the server's electricity costs are around 20 USD (0.0025 BTC) per month. We rely on your support, every donation helps us.
Thank you!<p><i>USER8</i> <img style='max-width:100%; max-height:300px;' src='DATA/github_emojis/v.png' class='emoji'><p>
<center><img class='donatewithpaypal' src='/DATA/donatewithpaypal.png' title='PayPal - The safer, easier way to pay online!' alt='Donate with PayPal' onclick=\"window.open('https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q2QPRUCG47DCN&source=url');return false;\"><img src='/DATA/donatewithbitcoin.png' class='donatewithbitcoin' onclick=\"bitcoincode.style.backgroundColor='#ff0000';\"></center>
<p>
<center><span class='bitcoincodetitle'>BTC-Address:</span><span class='bitcoincode' id='bitcoincode'>39ozz HRBx xgjj CJfE HNTh XsrY aKB8 AbyLA</span></center>
<p>
</div>";
            echo "<div class='linkbox'><img src='/DATA/jcobs-engine_logo.png' class='link_icon'><div class='link_text'>MAINPAGE<img src='/DATA/discord_verified.png' class='link_verified_symbol'></div><div class='link_invite_link' onclick=\"window.open('https://github.com/jcobs-engine/MAINPAGE');\" onmouseover=\"this.style.backgroundColor='#2ba06b';\" onmouseout=\"this.style.backgroundColor='#43b581';\">Open</div><img src='/DATA/github_logo.png' class='link_logo'><div class='link_title'>GitHub:</div></div>";
            echo "<div class='linkbox' style='margin-bottom:0px;'><img src='/DATA/discord_icon.png' class='link_icon'><div class='link_text'>MAINPAGE<img src='/DATA/discord_verified.png' class='link_verified_symbol'></div><div class='link_invite_link' onclick=\"window.open('https://discord.gg/tbwgRDh','targetWindow',`resizable=no,width=500,height=650`);return false;\" onmouseover=\"this.style.backgroundColor='#2ba06b';\" onmouseout=\"this.style.backgroundColor='#43b581';\">Join</div><img src='/DATA/discord_logo.png' class='link_logo'><div class='link_title'>Discord:</div></div>";
            echo "</div>";
        }
    }

    if($site == 1){
        # $$$ PROFILE $$$ #
        if($catsite == '0'){
            echo "<div class='artikel' id='showprofile' style='display:$showdisplay'><div class='title'><span class='white'>></span> My Profile<img src='/DATA/link.png' title='$URL_domain/content/$username' class='linkicon' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$username';\"></div><div class='bold text'>$username</div>";

            if($password != 'NONE'){
                echo "<div class='text'>".$password_orig."</div><span class='grey'>(The password shown is MD5 hashed.)</span><span class='right greytxt editbtn' $clickable_txt onclick=\"showprofile.style.display='none';editprofile.style.display='block';\">edit</span><br>";
            }
            echo "<div style='height:30px;'></div>";

            $sql = "SELECT COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) FROM user, subscriptions WHERE subscriptions.type=1 AND subscriptions.type_id=user.id AND user.id=$userid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $subs=$row[0];
            }

            
            $profileid=$userid;
            include("CODEBLOCKS/profile.php");

            echo "</div>";
            echo "<div class='box' id='editprofile' style='display:$editdisplay;'><div class='title'><span class='white'>></span> Edit Profile</div><div class='errorspan' style='display:$editerrordisplay;'><b class='errorspan_arz'>!</b> $ERROR_edit</div><input type='text' class='text' name='editusername' value='$username' placeholder='Username'><input type='password' placeholder='New password (optional)' name='editpassword' class='text'><input type='submit' name='editprofile' class='btn' value='edit' $clickable_btn></div>";

        }
        # $$$ SEARCH USER $$$ #
        if($catsite == '2'){
            $search=$_POST['search'];
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Search</div><input type='text' class='text' name='search' placeholder='Username' value='$search' autofocus><input type='submit' name='searchbtn' class='btn' $clickable_btn value='search' style='margin-bottom:30px;'>";

            if($search == ''){
                $searchstr='1=1';
                $end=' LIMIT 100';
            }
            else{
                $searchstr="LOWER(user.username) LIKE LOWER('%$search%')";
                $end='';
            }
            $in=0;
            $sql = "SELECT user.id, user.username, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) AS zahl FROM user, subscriptions WHERE subscriptions.type=1 AND subscriptions.type_id=user.id AND ($searchstr) GROUP by user.id ORDER BY zahl desc, user.username$end;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $searchid=$row[0];
                $searchname=$row[1];
                $searchsubs=$row[2];

                if($searchid != ""){
                    if($in == 0){
                        echo "<div class='tableheader'><span>name</span><span style='float:right;'>subscriber</span></div>";
                        
                        $fieldlistaddon="style='border-top:2px solid #ffffff;'";
                    }
                    else{
                        $fieldlistaddon="";
                    }

                    $openuser="catsite.value='user#$searchid';";
                    
                    echo "<div class='listfield' $clickable_field $fieldlistaddon onclick=\"{$openuser}document.mainpage.submit();\">$searchname<span class='vote right bold' style='padding-top:0px;'>$searchsubs</span>";
                    echo "</div>";
                    $in=1;
                }
            }
            
            if($in == 0)
                echo "<center>=== NO RESULTS ===</center>";

            
            echo "</div>";
        }
        # $$$ SUBSCRIPTIONS (USER) $$$ #
        if($catsite == 1){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Subscriptions</div>";

            $in=0;
            $sql = "SELECT user.id, user.username FROM user, subscriptions WHERE subscriptions.type=1 AND subscriptions.type_id=user.id AND subscriptions.user=$userid GROUP by user.id ORDER BY user.username;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $searchid=$row[0];
                $searchname=$row[1];

                $sql2 = "SELECT COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) FROM user, subscriptions WHERE subscriptions.type=1 AND subscriptions.type_id=user.id AND user.id=$searchid;";
                $out2 = mdq($bindung, $sql2);
                while ($row2 = mysqli_fetch_row($out2)) {
                    $searchsubs=$row2[0];
                }
         
                if($searchid != ""){
                    if($in == 0){
                        echo "<div class='tableheader'><span>name</span><span style='float:right;'>subscriber</span></div>";
                        
                        $fieldlistaddon="style='border-top:2px solid #ffffff;'";
                    }
                    else{
                        $fieldlistaddon="";
                    }

                    $openuser="catsite.value='user#$searchid';";
                    
                    echo "<div class='listfield' $clickable_field $fieldlistaddon onclick=\"{$openuser}document.mainpage.submit();\">$searchname<span class='vote right bold' style='padding-top:0px;'>$searchsubs</span>";
                    echo "</div>";
                    $in=1;
                }
            }
            
            if($in == 0)
                echo "<center>=== NO USER ===</center>";

            
            echo "</div>";

        }
    }

    if($site == 3){
        # $$$ ALL THREADS $$$ #
        if($catsite == '0'){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Forums</div><div class='rightbtn' $clickable_btn onclick=\"catsite.value='report#create_category';document.mainpage.submit();\">Create Category</div>";

            echo "<div style='border-bottom:2px solid white;'></div>";
            echo "<div class='listfield' $clickable_field onclick=\"catsite.value='category#-1';document.mainpage.submit();\">General</div>";
            
            
            $sql="SELECT id, title WHERE type=0 ORDER by title;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $category_id=$row[0];
                $category_title=$row[1];
                echo "<div class='listfield' $clickable_field onclick=\"catsite.value='category#$category_id';document.mainpage.submit();\">$category_title</div>";                
                
            }

            echo "</div>";
        }
    }
    
    if($site == 4){
        # $$$ SUBSCRIPTIONS (BLOGS) $$$ #
        if($catsite == '0'){
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Subscriptions</div><div class='rightbtn' $clickable_btn onclick=\"catsite.value=2;document.mainpage.submit();\">search</div>";
            
            $in=0;
            $sql = "SELECT blogs.id, blogs.name, user.username, ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT(DISTINCT blogposts.id)), 0) AS zahl, IF((MAX(blogposts.time)-MAX(timestamps.time))>0,1,0) AS reddit, user.id FROM blogs, user, blogposts, votes, subscriptions, timestamps WHERE blogposts.id=votes.type_id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND blogposts.title!='' AND subscriptions.type=0 AND subscriptions.type_id=blogs.id AND subscriptions.user=$userid AND timestamps.type=0 AND timestamps.type_id=blogs.id AND timestamps.user=$userid GROUP by blogs.id ORDER BY reddit desc, blogposts.id desc;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogid=$row[0];
                $searchname=$row[1];
                $searchuser=$row[2];
                $vote=$row[3];
                $profileid=$row[5];
                if($vote == ''){
                    $vote=0;
                }
                $styleaddon='';
                if($row[4] == 1){
                $styleaddon='font-weight:bold;';
                }

                if($in == 0){
                    echo "<div class='tableheader'><span>name</span><span style='float:right;'>average votes</span></div>";

                    $styleaddon.="border-top:2px solid #ffffff;";
                }

                echo "<div class='listfield' $clickable_field style='$styleaddon' onclick=\"catsite.value='blog#$blogid';document.mainpage.submit();\">$searchname <span class='grey'>by <span $clickable_grey class='clickable' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$searchuser</span></span><span class='vote right bold' style='padding-top:0px;'>$vote</span></div>";
                $in=1;
            }

            if($in != 1)
                echo "<center>=== NO BLOGS ===</center>";

            echo "</div>";
        }
        # $$$ MY BLOGS $$$ #
        if($catsite == '1'){
            echo "<div class='box' id='createblog' style='display:$createblogdisplay'><div class='title'><span class='white'>></span> Create Blog</div><div class='errorspan' style='display:$createblogerrordisplay;'><b class='errorspan_arz'>!</b> $ERROR_createblog</div><input type='text' maxlength='32' class='text' name='createblog_name' placeholder='Name'><input type='submit' name='createblog' class='btn' $clickable_btn value='create'></div><div class='artikel' id='myblogs' style='display:$blogsdisplay'><div class='title'><span class='white'>></span> My Blogs</div><div class='rightbtn' $clickable_btn onclick=\"createblog.style.display='block';myblogs.style.display='none';\">Create new</div>";

            
            $in=0;
            $sql = "SELECT blogs.id, blogs.name, ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT(DISTINCT blogposts.id)), 0) AS zahl, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) AS zahl2 FROM blogs, user, blogposts, votes, subscriptions WHERE subscriptions.type=0 AND subscriptions.type_id=blogs.id AND blogposts.id=votes.type_id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND user.id=$userid GROUP by blogs.id ORDER BY zahl2 desc, blogposts.id desc;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogid=$row[0];
                if($in == 0){
                    echo "<div class='tableheader'><span>name</span><span style='float:right;'>average votes</span></div>";

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
        # $$$ SEARCH BLOGS $$$ #
        if($catsite == '2'){
            $search=$_POST['search'];
            echo "<div class='artikel'><div class='title'><span class='white'>></span> Search</div><input type='text' class='text' name='search' placeholder='Blog, Post or User' value='$search' autofocus><input type='submit' name='searchbtn' class='btn' $clickable_btn value='search' style='margin-bottom:30px;'>";
            
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
                $end=' LIMIT 100';
            }

            $in=0;
            $sql = "SELECT blogs.id, blogs.name, user.username, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) AS zahl, user.id FROM blogs, user, blogposts, votes, subscriptions WHERE blogs.id=votes.type_catid AND votes.type_id=blogposts.id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND blogposts.title!='' AND subscriptions.type=0 AND subscriptions.type_id=blogs.id AND ($searchstr) GROUP by blogs.id ORDER BY zahl desc, blogposts.id desc$end;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogid=$row[0];
                $searchname=$row[1];
                $searchuser=$row[2];
                $profileid=$row[4];
                
                $sql = "SELECT ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT( DISTINCT blogposts.id)), 0) AS zahl FROM blogs, blogposts, votes WHERE blogs.id=$blogid AND blogs.id=votes.type_catid AND votes.type_id=blogposts.id AND votes.type=0 AND blogposts.blog=blogs.id AND blogposts.title!='' GROUP by blogs.id;";
                $out3 = mdq($bindung, $sql);
                while ($row3 = mysqli_fetch_row($out3)) {
                    $vote=$row3[0];
                    if($vote == ''){
                        $vote=0;
                    }
                }

                if($in == 0){
                    echo "<div class='tableheader'><span>name</span><span style='float:right;'>average votes</span></div>";
                    
                    $fieldlistaddon="style='border-top:2px solid #ffffff;'";
                }
                else{
                    $fieldlistaddon="";
                }

                echo "<div class='listfield' $clickable_field $fieldlistaddon onclick=\"catsite.value='blog#$blogid';document.mainpage.submit();\">$searchname <span class='grey'>by <span $clickable_grey class='clickable' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$searchuser</span></span><span class='vote right bold' style='padding-top:0px;'>$vote</span>";

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
    
    # $$$ BLOG-VIEW $$$ #
    if(strpos($catsite,"blog#")!==false){
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
            $sql = "INSERT INTO subscriptions SET type=0, type_id=$blogid, user=$userid;";
            $out = mdq($bindung, $sql);
        }
        if($_POST['unsubscribe'] == $blogid){
            $sql = "DELETE FROM subscriptions WHERE type=0 AND type_id=$blogid AND user=$userid;";
            $out = mdq($bindung, $sql);
        }
        
        if(isset($_POST['newpost'.$blogid]) and $blogowner == $userid and $_POST['newpost_title'] != "" and $_POST['newpost_text']){
            $newpost_title=str_replace("'", "\'", $_POST['newpost_title']);
            $newpost_text=str_replace("'", "\'", $_POST['newpost_text']);

            $sql = "INSERT INTO blogposts SET title='$newpost_title', post='$newpost_text', blog=$blogid, time=".time().", date='".date("d.m.Y h:i A")."';";
            $out = mdq($bindung, $sql);

            $sql = "SELECT LAST_INSERT_ID(), blogs.name FROM blogs, blogposts WHERE blogs.id=blogposts.blog AND blogposts.id=LAST_INSERT_ID();";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $blogpostid=$row[0];
                $blogname=$row[1];
            }
            
            $sql = "INSERT INTO votes SET vote=2, type=0, type_id=LAST_INSERT_ID(), user=$userid, type_catid=$blogid;";
            $out = mdq($bindung, $sql);

            $file=str_replace('%4', "1", $file);
            $file=str_replace('%5', "$blogpostid", $file);
            
            $out=shell_exec("echo '$file' > content/$username/$blogname/".setfilename($newpost_title).";");
                
        }

        if($_POST['deleteblogpost'] != 0){
            $sql = "DELETE FROM blogposts WHERE id=".$_POST['deleteblogpost']." AND blog=$blogid AND $blogowner=$userid;";
            $out = mdq($bindung, $sql);
            $sql = "DELETE FROM votings WHERE type=2 AND typeid=".$_POST['deleteblogpost'].";";
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

                $sql = "SELECT blogposts.title, blogs.name FROM blogposts, blogs WHERE blogs.id=blogposts.blog AND blogposts.id=$editblogpostid;";
                $out = mdq($bindung, $sql);
                while ($row = mysqli_fetch_row($out)) {
                    $out2=shell_exec("mv content/$username/".$row[1]."/".setfilename($row[0])." content/$username/".$row[1]."/".setfilename($editpost_title)."");
                }
                
                
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
<input type='text' name='newpost_title' class='text' placeholder='Title' maxlength='64' id='createposttitle'>
<textarea name='newpost_text' class='textarea' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';createposttitle.required='required';\">Text</textarea>
<input type='submit' name='newpost$blogid' value='create' class='btn' $clickable_btn>
</div>";
                }
                
                echo "<input type='hidden' name='subscribe' id='subscribe' value='0'><input type='hidden' name='unsubscribe' id='unsubscribe' value='0'><div class='artikel' id='blog$blogid'><div class='title'><span class='white'>></span> $blogname<img src='/DATA/link.png' title='$URL_domain/content/$blogowner/$blogname' class='linkicon' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$blogowner/$blogname';\"> <span class='grey'>by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$blogownerid';document.mainpage.submit();\">$blogowner</span></span></div>";
                if($blogownerid == $userid){
                    echo "<div class='rightbtn' $clickable_btn onclick=\"newpost$blogid.style.display='block';blog$blogid.style.display='none';\">New post</div>";
                }
                else{
                    $sub=0;
                    $sql = "SELECT id FROM subscriptions WHERE type=0 AND type_id=$blogid AND user=$userid;";
                    $out = mdq($bindung, $sql);
                    while ($row = mysqli_fetch_row($out)) {
                        $sub=1;
                    }
                    if($sub == 0)
                        echo "<div class='rightbtn' $clickable_btn onclick=\"subscribe.value=$blogid;document.mainpage.submit();\">subscribe</div>";
                    else
                        echo "<div class='rightbtn' $clickable_btn onclick=\"unsubscribe.value=$blogid;document.mainpage.submit();\">unsubscribe</div>";
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
                    
                    $SETTING['trenddesign']=0;
                    $SETTING['contentdesign']=0;
                    
                    include("CODEBLOCKS/blogpost.php");
                    
                }
            
                if($in == 0)
                    echo "<center>=== NO POSTS ===</center>";

                if($blogownerid == $userid){
                    echo "<input type='hidden' name='deleteblog' id='deleteblog' value='0'><span class='right greytxt' onclick=\"deleteblog.value=$blogid;document.mainpage.submit();\" ".str_replace('#00ff00', '#ff0000', $clickable_txt).">delete blog</span><br>";
                }
                else{
                    echo "<span class='right greytxt' onclick=\"catsite.value='report#blog:$blogid';document.mainpage.submit();\" ".str_replace('#00ff00', '#ff0000', $clickable_txt).">report blog</span><br>";
                }
                
                # END OF BLOG (CLASS=ARTIKEL) #
                echo "</div>";
            }
        }
    }
    else{
        $_POST['gt_blogpost']='';
    }

    # $$$ USER-VIEW $$$ #
    if(strpos($catsite,"user#")!==false){
        $profileid=explode('#', $catsite);
        $profileid=$profileid[1];


        if($_POST['subscribe_user'] == $profileid){
            $sql = "INSERT INTO subscriptions SET type=1, type_id=$profileid, user=$userid;";
            $out = mdq($bindung, $sql);
        }
        if($_POST['unsubscribe_user'] == $profileid){
            $sql = "DELETE FROM subscriptions WHERE type=1 AND type_id=$profileid AND user=$userid;";
            $out = mdq($bindung, $sql);
        }

        
        $sql = "SELECT user.username, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) FROM user, subscriptions WHERE subscriptions.type=1 AND subscriptions.type_id=user.id AND user.id=$profileid;";
        $out = mdq($bindung, $sql);
        while ($row = mysqli_fetch_row($out)) {
            $profilename=$row[0];
            $subs=$row[1];
        }

        echo "<input type='hidden' name='subscribe_user' id='subscribe' value='0'><input type='hidden' name='unsubscribe_user' id='unsubscribe' value='0'><div class='artikel'><div class='title'><span class='white'>></span> $profilename<img src='/DATA/link.png' title='$URL_domain/content/$profilename' class='linkicon' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$profilename';\"></div>";

        if($blogownerid != $userid){
            $sub=0;
            $sql = "SELECT id FROM subscriptions WHERE type=1 AND type_id=$profileid AND user=$userid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $sub=1;
            }
            if($sub == 0)
                echo "<div class='rightbtn' $clickable_btn onclick=\"subscribe.value=$profileid;document.mainpage.submit();\">subscribe</div>";
            else
                echo "<div class='rightbtn' $clickable_btn onclick=\"unsubscribe.value=$profileid;document.mainpage.submit();\">unsubscribe</div>";
        }
        

        include("CODEBLOCKS/profile.php");

        echo "<br><span class='right greytxt' onclick=\"catsite.value='report#user:$profileid';document.mainpage.submit();\" ".str_replace('#00ff00', '#ff0000', $clickable_txt).">report user</span><br>";
        
        echo "</div>";
    }


    if($password == 'NONE'){
#        echo "<div style='position:fixed;width:calc(".($sec/(60*60)*100)."% - 0px);left:0px;bottom:0px;background-color:#00ff00;color:black;text-align:center;'>$timeout</div>";
        echo "<div style='position:fixed;width:90px;right:0px;bottom:0px;background-color:#00ff00;color:black;text-align:center;'>$timeout</div>";
    }

    # $$$ REPORT-VIEW $$$ #
    if(strpos($catsite,"report#")!==false){
        $reportid=explode('#', $catsite);
        $reportid=explode(':', $reportid[1]);
        $reportype=$reportid[0];
        $reportid=$reportid[1];    

        if($reportype == 'blog'){
            $in=0;
            $sql = "SELECT blogs.name, user.username, user.id FROM blogs, user WHERE user.id=blogs.owner AND blogs.id=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $reportblogname=$row[0];
                $reportblogowner=$row[1];
                $profileid=$row[2];
                $in=1;
            }

            $sql = "SELECT id FROM votings WHERE votings.type=0 AND votings.typeid=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $in=0;
            }
            if($in == 1){
                echo "
<div class='box'><div class='title'><span class='white'>></span> Report Blog <span class='grey'><span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=4;catsite.value='blog#$reportid';document.mainpage.submit();\">$reportblogname</span> by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$reportblogowner</span></span></div>
<textarea class='textarea commentarea reporttextbox' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='reportdescription'>Write description</textarea>";
                    
                    echo "<input type='hidden' name='reportuser' id='reportuser' value='0'>
<div class='checkfield askreportuser' onmouseover=\"field3.style.color='#00ff00';\" onmouseout=\"field3.style.color='#ffffff';\" onclick=\"if(reportuser.value == 0){field3.innerHTML='[x]';reportuser.value=1;}else{field3.innerHTML='[ ]';reportuser.value=0;}\"><span id='field3'>[ ]</span> Report User</div>";
                        

echo "<input type='submit' name='report' class='btn' value='report' $clickable_btn>
</div>";
            }else{
                echo "
<div class='box'><div class='title'><span class='white'>></span> Report Blog <span class='grey'><span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=4;catsite.value='blog#$reportid';document.mainpage.submit();\">$reportblogname</span> by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$reportblogowner</span></span></div>

Blog already reported. See Votings.<p>

<input type='submit' name='report_fail' class='btn' value='Open Votings' $clickable_btn>
</div>";        
            }
        }

        if($reportype == 'blogpost'){
            $in=0;
            $sql = "SELECT user.username, user.id FROM blogposts, blogs, user WHERE user.id=blogs.owner AND blogs.id=blogposts.blog AND blogposts.id=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $reportblogpostowner=$row[0];
                $profileid=$row[1];                
                $in=1;
            }
                
            $sql = "SELECT id FROM votings WHERE votings.type=2 AND votings.typeid=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $in=0;
            }
            if($in == 1){
                echo "
<div class='box'><div class='title'><span class='white'>></span> Report Post <span class='grey'>by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$reportblogpostowner</span></span></div>
<textarea class='textarea commentarea reporttextbox' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='reportdescription'>Write description</textarea>";
                    
                    echo "<input type='hidden' name='reportuser' id='reportuser' value='0'>
<div class='checkfield askreportuser' onmouseover=\"field3.style.color='#00ff00';\" onmouseout=\"field3.style.color='#ffffff';\" onclick=\"if(reportuser.value == 0){field3.innerHTML='[x]';reportuser.value=1;}else{field3.innerHTML='[ ]';reportuser.value=0;}\"><span id='field3'>[ ]</span> Report User</div>";
                        

echo "<input type='submit' name='report' class='btn' value='report' $clickable_btn>
</div>";
            }else{
                echo "
<div class='box'><div class='title'><span class='white'>></span> Report Post <span class='grey'>by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$reportblogpostowner</span></span></div>

Post already reported. See Votings.<p>

<input type='submit' name='report_fail' class='btn' value='Open Votings' $clickable_btn>
</div>";        
            }

        }
        
        
        if($reportype == 'user'){
            $in=0;
            $sql = "SELECT user.username, user.id FROM user WHERE user.id=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $profileid=$row[1];
                $reportusername=$row[0];
                $in=1;
            }
            
            $sql = "SELECT id FROM votings WHERE votings.type=1 AND votings.typeid=$reportid;";
            $out = mdq($bindung, $sql);
            while ($row = mysqli_fetch_row($out)) {
                $in=0;
            }
            if($in == 1){
                echo "
<div class='box'><div class='title'><span class='white'>></span> Report User <span class='grey'><span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$reportusername</span></span></div>
<textarea class='textarea commentarea reporttextbox' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='reportdescription'>Write description</textarea>";
                    
                    echo "<input type='submit' name='report' class='btn' value='report' $clickable_btn></div>";
            }else{
                echo "
<div class='box'><div class='title'><span class='white'>></span> Report User <span class='grey'><span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$reportusername</span></span></div>

User already reported. See Votings.<p>

<input type='submit' name='report_fail' class='btn' value='Open Votings' $clickable_btn>
</div>";        
            }

        }
        
    }
}

echo "
<div id='copymessage' style='display:none;' onclick=\"this.style.display='none';\"><div id='copyfield' onclick=\"event.stopPropagation();\"></div></div>

<input type='hidden' name='gt_blogpost' id='gt_blogpost' value='".$_POST['gt_blogpost']."'>
<input type='hidden' name='votebackbtn' id='votebackbtn' value='0'>
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
