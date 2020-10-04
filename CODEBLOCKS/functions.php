<?php

date_default_timezone_set('UTC');

include('Parsedown.php');

function setfilename($post){
    $post=preg_replace("/[^0-9a-zA-Z \-\_]/", "", $post);
    $post=str_replace(' ', '_', $post);
    return $post;
}

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

    return $post;
}

function dlt_doublebr($post){
    $post=preg_replace("/\r|\n/", "", $post);
    $post=str_replace("<br /><br />", '<p>', $post);
    return $post;
}

function dlt_html($post){
    $post=str_replace('<img src="DATA/github_emojis/', ':', $post);
    $post=str_replace('.png" class="emoji">', ':', $post);
    $post=str_replace('>', '&gt;', $post);
    $post=str_replace('<', '&lt;', $post);
    $post=str_replace(' ', '&nbsp;<wbr>', $post);

    return $post.'<p>';
}

function getpgpmetadata($pubkey){
    file_put_contents('DATA/tmp.txt', trim($pubkey));
    $var=shell_exec("( cat DATA/tmp.txt | gpg --with-colons --import-options show-only --import ) > DATA/tmp2.txt");
    $return[1]=shell_exec("cat DATA/tmp2.txt | head -n 2 | tail -n 1 | grep -oP '(?<=:).*?(?=:)' 2>&1");
    $arr = str_split(strtoupper($return[1]),4);
    $return[1]="";
    foreach ($arr as $a) {
        $return[1] .= $a .' ';
    }
    $return[0]=str_replace('<p>', '', dlt_html(shell_exec('var=$( cat DATA/tmp2.txt | head -n 3 | tail -n 1 ); var=${var:63}; echo ${var/:*/};')));
    $var=shell_exec("rm -f DATA/tmp.txt DATA/tmp2.txt;");
    
    return $return;
}

function parsehtml($post, $postid, $bindung, $type, $clickable_btn){
    $post=umlaute($post);
    
    if($type == 'post'){
    $post=Parsedown::instance()
         ->setBreaksEnabled(true)
         ->text($post);
}else{
        $post=Parsedown::instance()
         ->setBreaksEnabled(true)
         ->line($post);
}

    $post=str_replace('<a href=', '<a target="_blank" style="color:#00ff00" href=', $post);
    $post=str_replace('<img src=', "<img style='max-width:100%; max-height:300px;' src=", $post);

    $i=1000;
    while ( $i > 0 ){
        $post=str_replace('[^'.$i.']:', "<a name='f_{$type}{$postid}_$i' style='text-decoration:none; color:#40E0D0;font-weight:bold;'>&nbsp;&nbsp;&nbsp;$i:</a>", $post);
        $post=str_replace('[^'.$i.']', "<a href='#f_{$type}{$postid}_$i' style='text-decoration:none; color:#40E0D0'><sup>[$i]</sup></a>", $post);
        $i--;
    }

    $emojis=json_decode(shell_exec("cat github_emojis.json"));
    
    foreach( $emojis AS $emoji_txt => $emoji_url){
        $post=str_replace(':'.$emoji_txt.':', '<img src="'.$emoji_url.'" class="emoji">', $post);
    }

    $sql="select username, id FROM user WHERE timeout=0 ORDER BY LENGTH(username) desc;";
    $out = mdq($bindung, $sql);
    while ($row = mysqli_fetch_row($out)) {
        $post=str_ireplace('@'.$row[0], "<span class='userlink' onclick=\"site=1;catsite.value='user#".$row[1]."';document.mainpage.submit();\" $clickable_btn>@&shy;".$row[0].'</span>', $post);
    }
    
    return $post;
}

function parsetext($post){
    $post=dlt_doublebr(nl2br(dlt_html(umlaute($post))));
    return $post;
}

$clickable_txt="onmouseover=\"this.style.color='#00ff00';\" onmouseout=\"this.style.color='#ffffff';\"";
$clickable_opencomments="onmouseover=\"this.style.color='#00ff00';%POSTID%.src='/DATA/einklappen_green.png';\" onmouseout=\"this.style.color='#ffffff';%POSTID%.src='/DATA/einklappen.png';\"";
$clickable_greentxt="onmouseover=\"this.style.color='#ffffff';\" onmouseout=\"this.style.color='#00ff00';\"";
$clickable_btn="onmouseover=\"this.style.backgroundColor='#00ff00';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"";
$clickable_field="onmouseover=\"this.style.backgroundColor='rgb(8%,8%,8%)';this.style.color='#00ff00';\" onmouseout=\"this.style.backgroundColor='transparent';this.style.color='#ffffff';\"";
$clickable_grey="onmouseover=\"this.style.color='#ffffff';\" onmouseout=\"this.style.color='rgb(60%, 60%, 60%)';\"";
$clickable_secondtitle="onmouseover=\"this.style.color='#ffffff';\" onmouseout=\"this.style.color='rgba(100%, 100%, 100%, 0.5)';\"";
$clickable_post="onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(8%,8%,8%)';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='#000000';\"";
$clickable_votingbtn_green="onmouseover=\"this.style.color='#00ff00';this.style.backgroundColor='#000000';\" onmouseout=\"this.style.backgroundColor='#ffffff';this.style.color='#000000';\"";
$clickable_votingbtn_red="onmouseover=\"this.style.color='#ff0000';this.style.backgroundColor='#000000';\" onmouseout=\"this.style.backgroundColor='#ffffff';this.style.color='#000000';\"";
$clickable_showbtn="onmouseover=\"this.style.backgroundColor='#ffffff';\" onmouseout=\"this.style.backgroundColor='rgba(100%, 100%, 100%, 0.4)';\"";
$clickable_linkicon="onmouseover=\"this.style.opacity='1';\" onmouseout=\"this.style.opacity='0.4';\"";
$clickable_settings="onmouseover=\"this.src='DATA/settings_green.png';\" onmouseout=\"this.src='DATA/settings.png';\"";
$clickable_heart_optional="onmouseover=\"this.style.backgroundColor='#00ff00';this.style.border='1px solid #00ff00';this.style.color='#000000';heart%HEARTID%.style.filter='invert(1)';\" onmouseout=\"this.style.backgroundColor='transparent';this.style.color='#ffffff';heart%HEARTID%.style.filter='invert(0)';this.style.border='1px solid #ffffff';\"";
$clickable_close="onmouseover=\"this.style.opacity='1';\" onmouseout=\"this.style.opacity='0.7';\"";

$clickable_votingbtn_green_cap=$clickable_votingbtn_green;
$clickable_votingbtn_red_cap=$clickable_votingbtn_red;

$clickable_kd="onmouseover=\"this.style.backgroundColor='rgb(8%,8%,8%)';this.style.color='#00ff00';\" onmouseout=\"this.style.backgroundColor='transparent';if(kdc%id.style.display == 'none'){this.style.color='#ffffff';}\"";

$bubble_kd="onclick=\"if(kdc%id.style.display == 'none'){unactive_all.click();kda%id.style.transform='rotate(0deg)';kdc%id.style.display='block';kd%id.style.color='#00ff00';kd%id.style.fontWeight='bold';kd%id.style.borderBottom='2px solid #00ff00';kd%ido.style.borderBottom='2px solid #00ff00';}else{unactive_all.click();}\"";
$bubble_kd_unactive="kd%id.style.fontWeight='normal';kdc%id.style.display='none';kd%id.style.color='#ffffff';kd%id.style.borderBottom='2px solid #ffffff';kd%ido.style.borderBottom='2px solid #ffffff';kda%id.style.transform='rotate(180deg)';";

$host = "localhost";
$benutzer = 'MAINPAGE';
$passwort = 'MAINPAGE';
$bindung = mysqli_connect($host, $benutzer, $passwort) or die("Connection to database is broken.");
$db = 'MAINPAGE';

function mdq($bindung, $query)
{
    mysqli_select_db($bindung, 'MAINPAGE');
    return mysqli_query($bindung, $query);
}

$URL=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
$URL_domain=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER[HTTP_HOST];

?>
