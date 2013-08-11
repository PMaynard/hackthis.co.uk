<?php
    array_push($minifier->custom_js, 'comments.js');
?>

                <section id="comments" data-id="<?=$comments["id"];?>">
                    <h2><?=$comments["count"];?> response<?=$comments["count"] ==1?'':'s';?> to "<?=$comments["title"];?>"</h2>
                    <form class='no-js-hide'>
<?php
    if (!$app->user->loggedIn):
?>
                        <div class='msg msg-error'>
                            <i class='icon-error'></i>
                            You must be logged in to comment
                        </div>
<?php
    elseif (!$app->user->forum_priv):
?>
                        <div class='msg msg-warning'>
                            <i class='icon-warning'></i>
                            You have been banned from posting comments
                        </div>
<?php
    else:
?>
                        <?php $wysiwyg_placeholder = 'Add your comment here...'; include('elements/wysiwyg.php'); ?>
                        <input id="comment_submit" type="submit" value="Post Comment" class="submit button right"/>
<?php
    endif;
?>
                    </form>

                    <div class='msg msg-warning js-hide'>
                        <i class='icon-warning'></i>
                        This feature requires JavaScript
                    </div>
                    <br/>
                    <div id="comments_container" class='no-js-hide'>
                        <div class="comments_loading center">
                                <img src='/files/images/icons/loading.gif' class='icon'/> Loading comments...
                        </div>
                    </div>
                </section>
