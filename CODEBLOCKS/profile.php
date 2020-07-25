<?php

$unactive="";

$kdid=0;
$unactive.="kd$kdid.style.borderBottom='2px solid white';";
echo "<div id='kd$kdid' style='border-bottom:2px solid white;'></div>";

# BLOGS #
$in=0;
$sql = "SELECT id FROM blogs WHERE owner=$profileid;";
$out = mdq($bindung, $sql);
while ($row = mysqli_fetch_row($out)) {
    $in=1;
}

if($in == 1){
    $kdid++;
    $unactive.=str_replace('%id', $kdid, str_replace('%ido', $kdid-1, $bubble_kd_unactive));
    echo "<div class='listfield' id='kd$kdid' ".str_replace('%id', $kdid, $clickable_kd)." ".str_replace('%id', $kdid, str_replace('%ido', $kdid-1, $bubble_kd))."><img id='kda$kdid' src='/DATA/einklappen.png' class='einklappen'>Blogs";
    echo "</div><div class='klappdivcontentbox' id='kdc$kdid' style='display:none;'>";
    echo "<div id='kdroh$kdid'>";
    $in2=0;
    $sql = "SELECT blogs.id, blogs.name, ROUND((COUNT(CASE WHEN votes.vote=1 THEN 1 END)-COUNT(CASE WHEN votes.vote=0 THEN 1 END))/(COUNT(blogposts.id)-COUNT(CASE WHEN blogposts.title='' THEN 1 END)), 0) AS zahl, COUNT(CASE WHEN subscriptions.user!=0 THEN 1 END) AS zahl2 FROM blogs, user, blogposts, votes, subscriptions WHERE subscriptions.type=0 AND subscriptions.type_id=blogs.id AND blogposts.id=votes.type_id AND votes.type=0 AND blogposts.blog=blogs.id AND blogs.owner=user.id AND user.id=$profileid GROUP by blogs.id ORDER BY zahl2 desc, blogposts.id desc;";
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
        
        if($in2 == 0){
            $fieldlistaddon="style='border-top:2px solid #ffffff;'";
        }
        else{
            $fieldlistaddon="";
        }

        if($SETTING['contentdesign'] == 1){
            $onclick="window.open('$url_domain/content/$profilename/$searchname');";
        }else{
            $onclick="site.value=4;catsite.value='blog#$blogid';document.mainpage.submit();";
        }
        
        echo "<div class='listfield' $clickable_field $fieldlistaddon onclick=\"$onclick\">$searchname<span class='vote right bold' style='padding-top:0px;'>$vote</span></div>";

        $in2=1;
    }    
    echo "</div></div>";
}

# Description #
$in=0;
$sql = "SELECT description FROM user WHERE id=$profileid;";
$out = mdq($bindung, $sql);
while ($row = mysqli_fetch_row($out)) {
    $description=$row[0];
    $descriptionroh=$row[0];
}

if($description != "" or $userid == $profileid){
    $kdid++;
    $unactive.=str_replace('%id', $kdid, str_replace('%ido', $kdid-1, $bubble_kd_unactive));
    echo "<input type='hidden' value='$kdid' name='editdescription_kd'>";
    if($description == ""){
        $descriptionroh="Add Description";
        $textareaaddon_bio="style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\"";
        $description="<center><span class='grey greytxt' $clickable_grey onclick=\"kdedit$kdid.style.display='block';kdroh$kdid.style.display='none';\">Add Description</span></center>";
    }
    else{
        $description=umlaute($description);
        $description=Parsedown::instance()
             ->setBreaksEnabled(true)
             ->text($description);
        $description=str_replace('<a href=', '<a target="_blank" style="color:#00ff00" href=', $description);
        $description=str_replace('<img src=', "<img style='max-width:100%; max-height:300px;' src=", $description);
        $i=1000;
        while ( $i > 0 ){
            $description=str_replace('[^'.$i.']:', "<a name='f_profile_$i' style='text-decoration:none; color:#40E0D0;font-weight:bold;'>&nbsp;&nbsp;&nbsp;$i:</a>", $description);
            $description=str_replace('[^'.$i.']', "<a href='#f_profile_$i' style='text-decoration:none; color:#40E0D0'><sup>[$i]</sup></a>", $description);
            $i--;
        }
        
    }
    echo "<div class='listfield' id='kd$kdid' ".str_replace('%id', $kdid, $clickable_kd)." ".str_replace('%id', $kdid, str_replace('%ido', $kdid-1, $bubble_kd))."><img id='kda$kdid' src='/DATA/einklappen.png' class='einklappen'>Description";
    if($userid == $profileid){
        if(trim($descriptionroh) != "Add Description" and trim($descriptionroh) != ""){
            $edittxt_bio="edit";
            $klappdivdescription="klappdivdescription";
        }
        else{
            $edittxt_bio="Add";
        }
        
        echo "<span class='right greytxt grey listfieldgreytxt' $clickable_grey onclick=\"event.stopPropagation();unactive_all.click();kd$kdid.click();kdroh$kdid.style.display='none';kdedit$kdid.style.display='block';\">$edittxt_bio</span>";
    }
    
    echo "</div><div class='klappdivcontentbox' id='kdc$kdid' style='display:none'><div class='klappdivcontent $klappdivdescription' $clickable_post>";
    echo "<div id='kdroh$kdid'>$description</div>";
    echo "<div id='kdedit$kdid' style='display:none'><textarea class='textarea' name='description' $textareaaddon_bio>$descriptionroh</textarea><input type='submit' name='editdescription' value='edit' class='btn' $clickable_btn></div>";
    echo "</div></div>";
}


# GnuPG Public Key #
$in=0;
$sql = "SELECT pubkey FROM user WHERE id=$profileid;";
$out = mdq($bindung, $sql);
while ($row = mysqli_fetch_row($out)) {
    $pubkey=$row[0];
    $pubkeyroh=$row[0];
}

if($pubkey != "" or $userid == $profileid){
    $kdid++;
    $unactive.=str_replace('%id', $kdid, str_replace('%ido', $kdid-1, $bubble_kd_unactive));
    echo "<input type='hidden' value='$kdid' name='editpubkey_kd'>";
    if($pubkey == ""){
        $pubkeyroh="Add GnuPG Public Key";
        $textareaaddon="style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\"";
        $pubkey="<center><span class='grey greytxt' $clickable_grey onclick=\"kdedit$kdid.style.display='block';kdroh$kdid.style.display='none';\">Add GnuPG Public Key</span></center>";
    }
    else{
        file_put_contents('DATA/tmp.txt', trim($pubkey));
        $var=shell_exec("( cat DATA/tmp.txt | gpg --with-colons --import-options show-only --import ) > DATA/tmp2.txt");
        $fingerprint=shell_exec("cat DATA/tmp2.txt | head -n 2 | tail -n 1 | grep -oP '(?<=:).*?(?=:)' 2>&1");
        $arr = str_split(strtoupper($fingerprint),4);
        $fingerprint="";
        foreach ($arr as $a) {
            $fingerprint .= $a .' ';
        }
        $uid=str_replace('<p>', '', dlt_html(shell_exec('var=$( cat DATA/tmp2.txt | head -n 3 | tail -n 1 ); var=${var:63}; echo ${var/:*/};')));
        $var=shell_exec("rm -f DATA/tmp.txt DATA/tmp2.txt;");

        if(trim($uid) != "" and trim($fingerprint) != ""){
            $pubkey="<div class='verifybox'><table><tr><th colspan='2' style='color:#00ff00;'>Valid Public Key</th></tr><tr><td class='grey'>UID: </td><td>$uid</td></tr><tr><td class='grey'>Fingerprint: </td><td>$fingerprint</td></tr></table></div><div class='pubkeyarea'>".nl2br($pubkey)."</div>";
        }
        else{
            $pubkey="<div class='verifybox'><table><tr><th colspan='2' style='color:#ff0000;'>Invalid Public Key</th></tr></table></div><div class='pubkeyarea'>".nl2br($pubkey)."</div>";
        }
    }
    echo "<div class='listfield' id='kd$kdid' ".str_replace('%id', $kdid, $clickable_kd)." ".str_replace('%id', $kdid, str_replace('%ido', $kdid-1, $bubble_kd))."><img id='kda$kdid' src='/DATA/einklappen.png' class='einklappen'>GnuPG Public Key";
    if($userid == $profileid){
        if(trim($pubkeyroh) != "Add GnuPG Public Key" and trim($pubkeyroh) != ""){
            $edittxt="edit";
        }
        else{
            $edittxt="Add";
        }
        
        echo "<span class='right greytxt grey listfieldgreytxt' $clickable_grey onclick=\"event.stopPropagation();kdc$kdid.style.display='block';unactive_all.click();kd$kdid.click();kdroh$kdid.style.display='none';kdedit$kdid.style.display='block';\">$edittxt</span>";
    }
    echo "</div><div class='klappdivcontentbox' id='kdc$kdid' style='display:none'><div class='klappdivcontent' $clickable_post>";
    echo "<div id='kdroh$kdid'>$pubkey</div>";
    echo "<div id='kdedit$kdid' style='display:none'><textarea class='textarea pubkeyarea' name='pubkey' $textareaaddon>$pubkeyroh</textarea><input type='submit' name='editpubkey' value='edit' class='btn' $clickable_btn></div>";
    echo "</div></div>";
}


echo "<div class='listfield' id='lastkd' style='cursor:default;' $clickable_field><span class='grey'>$subs Subscriber</span></div>";

echo "<a style='display:none' onclick=\"$unactive\" id='unactive_all'>unactive all</a>";
?>
