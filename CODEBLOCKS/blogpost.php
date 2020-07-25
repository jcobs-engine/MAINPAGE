<?php
                            
if($firstpost == $oldpost and $firstpost != ""){
    echo "<hr class='firstpostline'>";
}

# POST EDIT

$post=trim($row2[2]);

#       VERIFY #

$sql = "SELECT user.pubkey FROM blogs, user WHERE blogs.id=$blogid AND user.id=blogs.owner;";
$out3= mdq($bindung, $sql);
while ($row3 = mysqli_fetch_row($out3)) {
    $pubkey_user=trim($row3[0]);
}

$beginn_of_str="-----BEGIN PGP SIGNED MESSAGE-----";
$end_of_str="-----END PGP SIGNATURE-----";
$htmlpostprefix="";

if(strpos($post, $beginn_of_str) === 0 and preg_match("#$end_of_str$#",$post) and $pubkey_user != ""){
    $rohpost=$post;
    $post=substr($post, 52);
    $post=explode("-----BEGIN PGP SIGNATURE-----", $post);
    $post=$post[0];
    $post=substr($post, 0, -2);
    file_put_contents('DATA/tmp1.asc', $pubkey_user);
    $out3=shell_exec("gpg --yes --batch --dearmor ./DATA/tmp1.asc");
    file_put_contents('DATA/tmp2.txt', $rohpost);
    $out3=shell_exec("dos2unix DATA/tmp2.txt");
    $out4=shell_exec("gpg --no-default-keyring --keyring ./DATA/tmp1.asc.gpg --verify DATA/tmp2.txt 2>&1");
    $out3=shell_exec("rm DATA/tmp*;");
    $out_lines=explode("\n", $out4);
    $sig_date=substr($out_lines[0], 20);
    $sig_uid=substr($out_lines[2], 28, -1);
    $sig_fingerprint=substr($out_lines[6], 24);
    if(strpos($out4,"gpg: Good signature")!==false){
        $htmlpostprefix="<div class='verifybox'><table><tr><th colspan='2' style='color:#00ff00;cursor:pointer' onclick=\"if(ex1_$postid.style.display == 'none'){ex1_$postid.style.display='table-row';ex2_$postid.style.display='table-row';ex3_$postid.style.display='table-row';}else{ex1_$postid.style.display='none';ex2_$postid.style.display='none';ex3_$postid.style.display='none';}\">Valid Signature</th></tr><tr id='ex1_$postid' style='display:none'><td class='grey'>UID: </td><td>$sig_uid</td></tr><tr id='ex2_$postid' style='display:none'><td class='grey'>Fingerprint: </td><td>$sig_fingerprint</td></tr><tr id='ex3_$postid' style='display:none'><td class='grey'>Date: </td><td>$sig_date</td></tr></table></div>";
    }else{
        $htmlpostprefix="<div class='verifybox'><table><tr><th colspan='2' style='color:#ff0000;'>Invalid Signature</th></tr></table></div>";
    }
}

# [END] VERIFY #
                    
$post=umlaute($post);
$post=Parsedown::instance()
     ->setBreaksEnabled(true)
     ->text($post);
$post=str_replace('<a href=', '<a target="_blank" style="color:#00ff00" href=', $post);
$post=str_replace('<img src=', "<img style='max-width:100%; max-height:300px;' src=", $post);
$i=1000;
while ( $i > 0 ){
    $post=str_replace('[^'.$i.']:', "<a name='f_post{$postid}_$i' style='text-decoration:none; color:#40E0D0;font-weight:bold;'>&nbsp;&nbsp;&nbsp;$i:</a>", $post);
    $post=str_replace('[^'.$i.']', "<a href='#f_post{$postid}_$i' style='text-decoration:none; color:#40E0D0'><sup>[$i]</sup></a>", $post);
    $i--;
}

$origpost=dlt_doublebr(nl2br(dlt_html(umlaute($row2[2]))));
                    
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

    if(isset($_POST['comment'.$postid]) and $_POST['commenttext'.$postid] != "" and $_POST['commenttext'.$postid] != "write comment"){
        $_POST['commenttext'.$postid]=str_replace("'", "\'", $_POST['commenttext'.$postid]);
                            
        $sql = "INSERT INTO comments SET user=$userid, content='".$_POST['commenttext'.$postid]."', type=0, type_id=$postid;";
        $out3 = mdq($bindung, $sql);

        $sql = "INSERT INTO hearts set user=-1, comment=LAST_INSERT_ID();";
        $out3 = mdq($bindung, $sql);                            
    }
                        
    # [END] COMMENTS
                        
    if($blogownerid == $userid or $SETTING['contentdesign'] == 1)
        $block='TRUE';

    if($block != '')
        $style='cursor:default;';
    else
        $style='';

#    if($SETTING['trenddesign'] == 1){
#        $styleaddon_marginbtm='margin-bottom:10px;';
#    }else{
#        $styleaddon_marginbtm='';
#    }
                        
    echo "<input type='hidden' name='votefor$postid' id='votefor$postid' value='0'><div class='post' style='$styleaddon_marginbtm' onmouseover=\"this.style.borderLeft='2px solid #00ff00';this.style.backgroundColor='rgb(8%,8%,8%)';mousebtns$postid.style.display='block';bubbleview$postid.style.display='block';bubbleview2_$postid.style.display='block';\" onmouseout=\"this.style.borderLeft='2px solid #ffffff';this.style.backgroundColor='#000000';mousebtns$postid.style.display='none';bubbleview$postid.style.display='none';bubbleview2_$postid.style.display='none';\">

<div style='float:left; cursor:pointer;' onmouseout=\"{$block}pnts$postid.style.display='block';{$block}voteup_$postid.style.display='none';{$block}votedown_$postid.style.display='none';\">
<img src='/DATA/vote_white.png' class='pnts_up' style='display:none' id='voteup_$postid' onclick=\"votefor$postid.value=1;document.mainpage.submit();\" onmouseover=\"this.src='/DATA/vote_green.png'\" onmouseout=\"this.src='/DATA/vote_white.png'\">
<img src='/DATA/vote_white.png' class='pnts_down' style='display:none' id='votedown_$postid' onclick=\"votefor$postid.value=-1;document.mainpage.submit();\" onmouseover=\"this.src='/DATA/vote_red.png'\" onmouseout=\"this.src='/DATA/vote_white.png'\">
</div>

<div class='pnts' style='$style$block' onmouseover=\"{$block}this.style.display='none';{$block}voteup_$postid.style.display='block';{$block}votedown_$postid.style.display='block';\" id='pnts$postid'>$votes</div>";

        if($blogownerid == $userid and $SETTING['trenddesign'] == 0){
            echo "<div id='mousebtns$postid' style='display:none'><span class='grey greytxt right' onclick=\"deleteblogpost.value=$postid;document.mainpage.submit();\" ".str_replace('#ffffff', '#ff0000', $clickable_grey).">delete</span><span class='grey greytxt right' onclick=\"editblogpost.value=$postid;document.mainpage.submit();\" $clickable_grey>edit</span></div>

<span id='TRUEthis'></span><span id='TRUEvoteup_$postid'></span><span id='TRUEvotedown_$postid'></span><span id='TRUEpnts$postid'></span>";
        }
        else{
            echo "<span id='mousebtns$postid'></span>";
            
            if($SETTING['trenddesign'] != 1 and $SETTING['contentdesign'] != 1)
                echo "<span class='right grey greytxt' id='bubbleview2_$postid' onclick=\"catsite.value='report#blogpost:$postid';document.mainpage.submit();\" ".str_replace('#00ff00', '#ff0000', $clickable_grey)." style='display:none'>report</span>";

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

        if($SETTING['trenddesign'] == 0){
            echo "<div class='grey blogdate'>$date</div><div class='title posttitle'><span class='white'>#</span> $title<img src='/DATA/link.png' title='$URL_domain/content/$blogowner/$blogname/".setfilename($title)."' class='linkicon_post' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$blogowner/$blogname/".setfilename($title)."';\"></div>";
        }else{
            echo "<div class='absolutetopright secondtitle' $clickable_secondtitle onclick=\"site.value=4;catsite.value='blog#$blogid';gt_blogpost.value='{$blogid}_{$postid}';document.mainpage.submit();\">$blogname</div><div class='grey blogdate'>$date</div><div class='title posttitle'><span class='white'>#</span> $title<img src='/DATA/link.png' title='$URL_domain/content/$blogowner/$blogname/".setfilename($title)."' class='linkicon_post' $clickable_linkicon onclick=\"copymessage.style.display='block';copyfield.innerHTML='$URL_domain/content/$blogowner/$blogname/".setfilename($title)."';\"> <span class='grey'>by <span $clickable_grey class='clickable bold' onclick=\"event.stopPropagation();site.value=1;catsite.value='user#$profileid';document.mainpage.submit();\">$blogownername</span></span></div>";
        }
        
        echo "<div class='postcontent'>
<div style='display:none' id='textpost$postid'>
$origpost
</div>

<div style='display:block;' id='htmlpost$postid'>
$htmlpostprefix
$post
</div>
</div>
&shy;
<span class='greytxt grey right' $clickable_grey style='display:none;' id='bubbleview$postid' onclick=\"if(textpost$postid.style.display == 'none'){ textpost$postid.style.display='block';htmlpost$postid.style.display='none';this.innerHTML='HTML-view'; }else{ textpost$postid.style.display='none';htmlpost$postid.style.display='block';this.innerHTML='source-view'; }\">source-view</span>

<hr class='commentline'>
<div class='opencomments' $clickable_txt onclick=\"if(comments$postid.style.display == 'none'){commentstat.value='$postid';comments$postid.style.display='block';einklappen$postid.style.transform='rotate(0deg)';}else{commentstat.value='0';comments$postid.style.display='none';einklappen$postid.style.transform='rotate(180deg)';}\"><img src='/DATA/einklappen.png' class='einklappen' id='einklappen$postid' style='$commentarrow_stylechanges'>$commentscount comment$commentplural</div>
<div class='comments' id='comments$postid' style='display:".$commentboxdisplay[$postid].";'>";
        if($blogownerid != $userid and $SETTING['contentdesign'] != 1){
            echo "<textarea id='commentarea$postid' class='textarea commentarea' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='commenttext$postid'>Write comment</textarea>
<input type='submit' name='comment$postid' value='send' class='btn commentsend' $clickable_btn>";
        }


                            
        $commentin=0;
        $sql = "SELECT comments.content, user.username, user.id, comments.id, COUNT(CASE WHEN hearts.user!=-1 THEN 1 END) AS zahl FROM comments, user, hearts WHERE comments.user=user.id AND type=0 AND type_id=$postid AND hearts.comment=comments.id GROUP by comments.id ORDER by zahl desc, comments.id desc;";
        $out3 = mdq($bindung, $sql);
        while ($row3 = mysqli_fetch_row($out3)) {
            if($row3[2] != ''){
                $commentid=$row3[3];

                                    
                $commenttext=Parsedown::instance()
                            ->setBreaksEnabled(true)
                            ->line(umlaute($row3[0]));
                                    
                $commenttext=str_replace('<a href=', '<a target="_blank" style="color:#00ff00" href=', $commenttext);
                $commenttext=str_replace('<img src=', "<img style='max-width:100%; max-height:300px;' src=", $commenttext);
                $i=1000;
                while ( $i > 0 ){
                    $commenttext=str_replace('[^'.$i.']:', "<a name='f_comment{$commentid}_$i' style='text-decoration:none; color:#40E0D0;font-weight:bold;'>&nbsp;&nbsp;&nbsp;$i:</a>", $commenttext);
                    $commenttext=str_replace('[^'.$i.']', "<a href='#f_comment{$commentid}_$i' style='text-decoration:none; color:#40E0D0'><sup>[$i]</sup></a>", $commenttext);
                    $i--;
                }

                                    
                # REPLYS

                if($_POST['replystat'] == $commentid){
                    $replyboxdisplay[$commentid]='block';
                    $replyarrow_stylechanges='transform:rotate(0deg);';
                }
                else{
                    $replyboxdisplay[$commentid]='none';
                    $replyarrow_stylechanges='';
                }
                                    
                if(isset($_POST['replysend'.$commentid]) and $_POST['replytext'.$commentid] != "" and $_POST['replytext'.$commentid] != "write reply"){
                    $_POST['replytext'.$commentid]=str_replace("'", "\'", $_POST['replytext'.$commentid]);
                                        
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
                    $replyplural='ies';
                    $sql = "SELECT comments.id FROM comments WHERE type=1 AND type_id=$commentid;";
                    $out4 = mdq($bindung, $sql);
                    while ($row4 = mysqli_fetch_row($out4)) {
                        $replyscount++;
                    }
                    if($replyscount == 1){
                        $replyplural='y';
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
                        if($SETTING['contentdesign'] != 1){
                            echo "<span class='grey' style='display:none;font-size:14px;' id='replys2".$row3[3]."'>&nbsp;&#183;&nbsp;</span><div style='display:none;cursor:pointer;color:rgb(60%, 60%, 60%);font-size:14px' style='grey greytxt' id='replys".$row3[3]."' onclick=\"replycontent$commentid.style.display='block';replystat.value='$commentid';replycontent$commentid.style.display='block';reply_einklappen$commentid.style.transform='rotate(0deg)';replyarea$commentid.focus();replyline$commentid.style.display='block';opencomments$commentid.style.display='block';commentcontent$commentid.classList.remove('reply');\" $clickable_grey>reply</div>
<span id='mousebtns_2_{$postid}_".$row3[3]."'></span><span id='mousebtns{$postid}_".$row3[3]."'></span>";
                        }
                    }

                    echo "<div class='commentbox'>".$commenttext."</div><div class='heartfield' style='$heartcursor' $clickable_heart onclick=\"{$full}heartcomment.value='".$row3[3]."';document.mainpage.submit();\">".$row3[4]." <img src='/DATA/heart{$full}.png' id='heart".$row3[3]."' style='margin-bottom:-3px;width:16px;'></div>";

                    ### <------- REPLYS -------> ##
                                        
                    echo "<hr class='commentline' id='replyline$commentid' $zeroreplysaddon><div class='opencomments' id='opencomments$commentid' $zeroreplysaddon $clickable_txt 

onclick=\"if(replycontent$commentid.style.display == 'none'){replycontent$commentid.style.display='block';replystat.value='$commentid';replycontent$commentid.style.display='block';reply_einklappen$commentid.style.transform='rotate(0deg)';}else{replystat.value='0';replycontent$commentid.style.display='none';reply_einklappen$commentid.style.transform='rotate(180deg)';}\"

><img src='/DATA/einklappen.png' class='einklappen' id='reply_einklappen$commentid' style='$replyarrow_stylechanges'>$replyscount repl$replyplural</div><div class='comments' id='replycontent$commentid' style='display:".$replyboxdisplay[$commentid]."'>";

                    if( $SETTING['contentdesign'] != 1 ){
                        echo "<textarea id='replyarea$commentid' class='textarea commentarea' style='color:grey;' onfocus=\"this.innerHTML='';this.style.color='#ffffff';\" name='replytext$commentid'>Write reply</textarea>
<input type='submit' name='replysend$commentid' value='send' class='btn commentsend' $clickable_btn>";
                    }
                                        
                    $replyin=0;
                    $sql = "SELECT comments.content, user.username, user.id, comments.id FROM comments, user WHERE comments.user=user.id AND comments.type=1 AND comments.type_id=$commentid ORDER by comments.id;";
                    $out4 = mdq($bindung, $sql);
                    while ($row4 = mysqli_fetch_row($out4)) {
                        if($row4[3] != ''){
                            $replyid=$row4[3];

                            $replytext=Parsedown::instance()
                                      ->setBreaksEnabled(true)
                                      ->line(umlaute($row4[0]));
                                                
                            $replytext=str_replace('<a href=', '<a target="_blank" style="color:#00ff00" href=', $replytext);
                            $replytext=str_replace('<img src=', "<img style='max-width:100%; max-height:300px;' src=", $replytext);
                            $i=1000;
                            while ( $i > 0 ){
                                $replytext=str_replace('[^'.$i.']:', "<a name='f_reply{$replyid}_$i' style='text-decoration:none; color:#40E0D0;font-weight:bold;'>&nbsp;&nbsp;&nbsp;$i:</a>", $replytext);
                                $replytext=str_replace('[^'.$i.']', "<a href='#f_reply{$replyid}_$i' style='text-decoration:none; color:#40E0D0'><sup>[$i]</sup></a>", $replytext);
                                $i--;
                            }

                                                
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
                                    if($SETTING['contentdesign'] != 1 ){
                                        echo "<span class='grey' style='display:none;font-size:14px;' id='replys2{$commentid}_".$row4[3]."'>&nbsp;&#183;&nbsp;</span><div style='display:none;cursor:pointer;color:rgb(60%, 60%, 60%);font-size:14px' style='grey greytxt' id='replys{$commentid}_".$row4[3]."' onclick=\"replycontent$commentid.style.display='block';replystat.value='$commentid';replycontent$commentid.style.display='block';reply_einklappen$commentid.style.transform='rotate(0deg)';replyarea$commentid.focus();\" $clickable_grey>reply</div>
<span id='mousebtns_2_{$postid}_{$commentid}_".$row4[3]."'></span><span id='mousebtns{$postid}_{$commentid}_".$row4[3]."'></span>";
                                    }
                                }

                                echo "<div class='commentbox'>".$replytext."</div></div>";
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
                            
        
        $oldpost=$postid;
        $in=1;
                            
}

?>
