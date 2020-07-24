<?php

include("CODEBLOCKS/functions.php");

$type=$_GET['type'];
$id=$_GET['id'];

# SET TITLE
$title="MainPage";

if($type == 0){
    $sql="SELECT blogs.owner, blogs.id, user.username, blogs.name FROM blogs, user WHERE blogs.id=$id AND blogs.owner=user.id;";
    $out3 = mdq($bindung, $sql);
    while ($row3 = mysqli_fetch_row($out3)) {
        $blogownername=$row3[2];
        $blogname=$row3[3];
        $title.=" - $blogname by $blogownername";
    }
}
elseif($type == 1){
    $sql="SELECT blogposts.title, user.username, blogs.id, blogs.name FROM blogs, user, blogposts WHERE blogposts.id=$id AND blogs.id=blogposts.blog AND blogs.owner=user.id;";
    $out3 = mdq($bindung, $sql);
    while ($row3 = mysqli_fetch_row($out3)) {
        $blogownername=$row3[1];
        $blogposttitle=$row3[0];
        $title.=" - \"$blogposttitle\" by $blogownername";
        $blogid=$row3[2];
        $blogname=$row3[3];
    }
}
elseif($type == 2){
    $sql="SELECT user.username, user.id FROM user WHERE user.id=$id;";
    $out3 = mdq($bindung, $sql);
    while ($row3 = mysqli_fetch_row($out3)) {
        $username=$row3[0];
        $profileid=$row3[1];
        $title.=" - $username";
    }
}


# VERSION
$version='v1';


# OVERLAY #

echo "
<!DOCTYPE html>
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

<body>

<div id='header'> 
<a href='$URL_domain'><img class='logo clickable' src='/DATA/logo_$version.png' onmouseover=\"this.src='/DATA/greenlogo_$version.png';\" onmouseout=\"this.src='/DATA/logo_$version.png';\"></a>
<a href='$URL_domain' class='white'><div class='header_child' ".$clickable_txt."><span style='border-bottom:0px solid #00ff00'>Join</span></div></a>
</div>

<div style='height:200px;'></div>                                                                                                                                                             

";
#<a href='$URL_domain'><img id='login_logo' src='/DATA/logo_$version.png' onmouseover=\"this.src='/DATA/greenlogo_$version.png';\" onmouseout=\"this.src='/DATA/logo_$version.png';\"></a>


# BLOG #
if($type == 0 or $type == 1){
    if($type == 1){
        $newsql1=', CASE WHEN blogposts.id='.$id.' THEN 1 END AS readfirst';
        $newsql2=' readfirst desc,';
        $firstpost=$id;
        $id=$blogid;
    }

    $blogowner=$blogownername;
    
    $linkaddon="<img src='/DATA/link.png' title='$URL_domain/content/$blogownername/$blogname' class='linkicon' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$blogownername/$blogname';\">";
    
    $userid=-1;
    $blogid=$id;
    
    $sql="SELECT blogs.owner, blogs.id, user.username, blogs.name FROM blogs, user WHERE blogs.id=$blogid AND blogs.owner=user.id;";
    $out3 = mdq($bindung, $sql);
    while ($row3 = mysqli_fetch_row($out3)) {
        $verify=$row3[1];
        $blogownername=$row3[2];
        $blogname=$row3[3];
    }

    if($verify != ""){
        echo "<div class='artikel contentartikel' id='blog$blogid'><div class='title'><span class='white'>></span> $blogname$linkaddon <span class='grey'>by <span $clickable_grey class='clickable bold' onclick=\"window.open('$URL_domain/content/$blogownername');\">$blogownername</span></span></div>";

    
        echo "<input type='hidden' name='commentstat' id='commentstat' value='0'>";
        echo "<input type='hidden' name='replystat' id='replystat' value='0'>";
    
        $in=0;
        $sql = "SELECT blogposts.id, title, post, date, COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END) AS zahl$newsql1 FROM blogposts, votes WHERE votes.type=0 AND votes.type_id=blogposts.id AND blog=$id AND post!='' AND title!='' GROUP BY blogposts.id ORDER by$newsql2 blogposts.id desc;";
        $out2 = mdq($bindung, $sql);
        while ($row2 = mysqli_fetch_row($out2)) {
            $postid=$row2[0];
            $title=$row2[1];
            $SETTING['trenddesign']=0;
            $SETTING['contentdesign']=1;

            include("CODEBLOCKS/blogpost.php");
            $in=1;
        }

        if($in != 1){
            echo "<center>=== NO POSTS ===</center><br><br>";
        }
        
        echo "</div>";
        
    }
    else
        $error=1;
}
elseif($type == 2){
    $sql="SELECT user.id, user.username, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) FROM user, subscriptions WHERE user.id=$id AND subscriptions.type=1 AND subscriptions.type_id=user.id;";
    $out3 = mdq($bindung, $sql);
    while ($row3 = mysqli_fetch_row($out3)) {
        $verify=$row3[0];
        $profileid=$row3[0];
        $profilename=$row3[1];
        $subs=$row3[2];
    }

    if($verify != ""){
        echo "<div class='artikel'><div class='title'><span class='white'>></span> $profilename<img src='/DATA/link.png' title='$URL_domain/content/$profilename' class='linkicon' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$profilename';\"></div>";

        $SETTING['contentdesign']=1;
        
        include("CODEBLOCKS/profile.php");
        
        echo "</div>";
    }
    else
        $error=1;
}

if($error == 1){
    echo "<center>Site does not exists.<p><b>Click the MAINPAGE logo above to go to the start page.</b></center>";
}

echo "
<div id='copymessage' style='display:none;' onclick=\"this.style.display='none';\"><div id='copyfield' onclick=\"event.stopPropagation();\"></div></div>                                       
</body></html>";

?>
