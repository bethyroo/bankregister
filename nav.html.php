<?php

/*
 * @Project bankregister
 * @author akmjoe
 * @page nav.html.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
?>
<div id="menu">
    <form name="search" method="get">
        <?php foreach($_GET as $key => $value) echo '<input type=hidden name="'.$key.'" value="'.$value.'">'; ?>
    <ul id="menu-bar">
        <li id="hamburger"><a href="#"></a></li>
        <li id="search"><input type="text" name="q" id="search" placeholder="Search" value="<?php echo $_REQUEST['q']; ?>"><a href="#"></a></li>
    </ul>
    <div id="nav">
        <ul>
            <li><a href='?page=logout'>Logout</a></li>
            <li><a href='?page=list'>Transactions<img src="arrow.png"></a></li>
            <li><a href='?page=statement'>Statement<img src="arrow.png"></a></li>
            <li><a href='?page=recurring'>Recurring Transactions<img src="arrow.png"></a></li>
            <li><a href='?page=account'>Manage Accounts<img src="arrow.png"></a></li>
            <li><a href='?page=users'>Manage Users<img src="arrow.png"></a></li>
            <li><a href='?page=import'>Import Transactions<img src="arrow.png"></a></li>
            <li><a href='<?php echo query_string(array('mobile'), array('mobile' => ($_SESSION['mobile']?0:1)));?>'><?php echo $_SESSION['mobile']?'Desktop':'Mobile';?> Site</a></li>
        </ul>
    </div>
    </form>
</div>
<script>
    
    $('#hamburger a').click(function () {
        if($('#nav').hasClass('show')) {
            // hide
            $('#content').toggle();
            $('#nav').attr('class','hide transition').one("webkitAnimationEnd", function(){
                $('#nav').removeClass("transition");
            });
        } else {
            // show
            $('#nav').attr('class','show transition').one("webkitAnimationEnd", function(){
                $('#nav').removeClass("transition");
                $('#content').toggle();
            });
        }
        
    })
</script>