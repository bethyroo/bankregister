<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page list.html.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, minimum-scale=1,maximum-scale=1, user-scalable=no">
        <link rel="apple-touch-icon-precomposed" href="http://<?php echo $_SERVER['SERVER_NAME']. dirname($_SERVER['PHP_SELF']); ?>/apple-touch-icon-precomposed.png"/>
        <link rel="shortcut icon" href="favicon.ico" />
        <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['mobile']?'mobile.css':'main.css'; ?>">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script>
            function toggleTransfer(visible) {
                if(visible) {
                    document.getElementById('description').style.display = 'none';
                    document.getElementById('to').style.display = '';
                    document.getElementById('how').style.display = '';
                    
                } else {
                    document.getElementById('description').style.display = '';
                    document.getElementById('to').style.display = 'none';
                    document.getElementById('how').style.display = 'none';
                }
            }
            function toggleHighlight(item) {
                item.classList.toggle('highlight');
            }
        </script>
        <title><?php echo $title; ?></title>
    </head>
    <body id="list">
        <?php include "nav.html.php"; ?>
        <?php if(!$_SESSION['mobile']) { include 'account_list.html.php'; ?>
        <div id="content">      
        <?php echo $message; ?>
        <div class="list">
                    <?php if($recurring) { ?>
                    <span id="recurring">
                        There are <?php echo $recurring; ?> recurring transactions. 
                        <button type="button" onclick="window.location.href='?page=recurring&action=perform'">Perform</button>
                    </span>
                    <br>
                    <?php } ?>
                    <form method="post" action="index.php">
                        <input type="hidden" name="aID" value="<?php echo $aID; ?>">
                        <table class="items">
                        <thead class="fixed">
                            <th class="cell1">Date</th>
                            <th class="cell2">description</th>
                            <th class="cell3">Amount</th>
                            <th class="cell4">Balance</th>
                            <th class="cell5">Statement</th>
                        </thead>
                        <tbody class="scroll" id="transactions">
                        <?php 
                        $total = 0;
                        foreach($transactions as $row) {
                            // track total
                            if($row['statement']=='0') $total += $row['value'];
                            // track outstanding
                            if($row['outstanding']) $outstanding += $row['value'];
                            // imbalance amount
                            if($row['statement'] && $total-$outstanding != $row['value']) $imbalance = $total-$outstanding-$row['value'];
                            // class odd/even
                            $odd = !$odd; 
                            $class = $odd?'odd':'even';
                            // special classes
                            if($row['outstanding']) $class .= ' outstanding';
                            if($row['value'] < 0) $class .= ' negative';
                            if($total < 0 && $account_info['type'] == 'bank') $class .= ' warning';// overdrawn
                            if($row['id'] == $transaction['id']) $class .= ' edit';// line being edited
                            if($row['statement']) {
                                $class .= ' statement';
                                if($total-$outstanding != $row['value']) $class .= ' imbalance';
                            }
                            ?>
                            <tr class="<?php echo $class; ?>" id="row_<?php echo $row['id']; ?>" onclick="toggleHighlight(this)" ondblclick="window.location.href='?action=edit&aID=<?php echo $aID; ?>&id=<?php echo $row['id']; ?>'">
                            <td class="cell1"><?php echo $row['tran_date']; ?></td>
                            <td class="cell2"><?php echo $row['statement']?'Outstanding:'.money_format('%#10n',$outstanding):$row['description']; ?></td>
                            <td class="cell3"><?php echo $row['statement']?$imbalance:money_format('%#10n', $row['value']); ?></td>
                            <td class="cell4"><?php echo money_format('%#10n', $row['statement']?$row['value']:$total); ?></td>
                            <td class="cell5">
                                <button type="button" onclick="window.location.href='?action=edit&aID=<?php echo $aID; ?>&id=<?php echo $row['id']; ?>'">Edit</button>
                                <?php if($row['statement']&&$total-$outstanding != $row['value']) echo 'Imbalance'; ?>
                            </td>
                        </tr>
                        <?php } ?> 
                        </tbody>
                        <tbody class="fixed">
                            <tr class="form">
                                <td class="cell1">
                                    <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                    <input type="date" name="tran_date" value="<?php echo (isset($transaction['tran_date'])?$transaction['tran_date']:date('Y-m-d')); ?>">
                                </td>
                                <td class="cell2">
                                    <input type="text" id="description" name="description" value="<?php echo $transaction['description']; ?>" size="30" placeholder="Description">
                                    <!-- Transfer form -->
                                    <select name="how" id="how" style="display:none;">
                                        <option value="1" selected="selected">To</option>
                                        <option value="0">From</option>
                                    </select>
                                    <select name="to" id="to" style="display:none;">
                                        <?php foreach($accounts_array as $account) { 
                                            echo '<option value="'.$account['id'].'"'.($account['id']==$aID?' selected="selected"':'').'>'.$account['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <!-- End transfer form -->
                                    <?php if($transaction['id'] && !$transaction['link']) { ?>
                                    <select name="account">
                                        <?php foreach($accounts_array as $account) { 
                                            echo '<option value="'.$account['id'].'"'.($account['id']==$aID?' selected="selected"':'').'>'.$account['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <?php } else { ?>
                                    <input type="hidden" name="account" value="<?php echo $aID; ?>">
                                    <?php } ?>
                                </td>
                                <td class="cell3"><input type="text" name="value" value="<?php echo $transaction['value']; ?>" placeholder="$ 0.00" size="8"></td>
                                <td class="cell4"><input type="checkbox" name="outstanding" value="1"<?php echo ($transaction['outstanding']?'checked=checked':''); ?>" size="8"><label>Outstanding</label></td>
                                <td class="cell5">
                                    <input type="submit" name="action" value="<?php echo $transaction['id']?'save':'add';?>">
                                    <?php if($transaction['id']) { ?>
                                    <input type="submit" name="action" value="delete">
                                    <?php } else { ?>
                                    <input type="checkbox" name="transfer" id="transfer" value="1" onchange="toggleTransfer(this.checked)">
                                    <label>Transfer</label>
                                    <?php } ?>
                                    <br>
                                    <input type="checkbox" name="statement" value="1" <?php if($transaction['statement']) echo 'checked="checked"';?>>
                                    <label>Statement</label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </form>
              </div>
        </div>
        <script>
            // scroll to selected or last row
            document.getElementById('row_<?php echo $transaction['id']?$transaction['id']:$row['id']; ?>').scrollIntoView();
            // select description fields
            document.getElementById('description').focus();
        </script>
        <?php } else {// mobile version ?>
        <span id="title"><?php echo ($aID)?'Account: '.account_name($aID):''; ?></span>
        <div id="content">
        <?php if(isset($transaction['id'])) {// add/edit ?>
            <form name="transaction" action="<?php echo query_string(array('id', 'action')); ?>" method="post">
                <button type="button" onclick="window.location.href='<?php echo query_string(array('action', 'id')); ?>'">Back</button>
                <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                
                <input type="date" name="tran_date" value="<?php echo (isset($transaction['tran_date'])?$transaction['tran_date']:date('Y-m-d')); ?>">
                
                <input type="text" id="description" name="description" value="<?php echo $transaction['description']; ?>" size="30" placeholder="Description">
                <!-- Transfer form -->
                <select name="how" id="how" style="display:none;">
                    <option value="1" selected="selected">To</option>
                    <option value="0">From</option>
                </select>
                <select name="to" id="to" style="display:none;">
                    <?php foreach($accounts_array as $account) { 
                        echo '<option value="'.$account['id'].'"'.($account['id']==$aID?' selected="selected"':'').'>'.$account['name'].'</option>';
                    }
                    ?>
                </select>
                <!-- End transfer form -->
                <?php if($transaction['id'] && !$transaction['link']) { ?>
                <select name="account">
                    <?php foreach($accounts_array as $account) { 
                        echo '<option value="'.$account['id'].'"'.($account['id']==$aID?' selected="selected"':'').'>'.$account['name'].'</option>';
                    }
                    ?>
                </select>
                <?php } else { ?>
                <input type="hidden" name="account" value="<?php echo $aID; ?>">
                <?php } ?>
                <input type="text" name="value" value="<?php echo $transaction['value']; ?>" placeholder="$ 0.00" size="8">
                <?php if(!$transaction['id'] || !$transaction['statement']) { ?>
                <label id="outstanding_label">Outstanding</label>
                <input type="checkbox" name="outstanding" id="outstanding" value="1"<?php echo ($transaction['outstanding']||!$transaction['id']?'checked=checked':''); ?> size="8">
                <?php } ?>
                <button type="submit" name="action" value="<?php echo $transaction['id']?'save':'add';?>">Save</button>
                <?php if($transaction['id']) { ?>
                <button type="submit" name="action" value="delete">Delete</button>
                <?php } else { ?>
                <input type="hidden" name="transfer" id="transfer" value="0">
                <?php } ?>
                <br>
                <input type="hidden" name="statement" id="statement" value="<?php if($transaction['statement']) echo '1';?>">
            </form>
            <?php if(!$transaction['id']) { ?>
            <ul id="mode">
                <li><a href="#transfer">Transfer</a></li>
                <li class="selected"><a href="#normal">Normal</a></li>
                <li><a href="#statement">Statement</a></li>
            </ul>
            <?php } ?>
            <script>
                $('#mode a').click(function (e){
                    e.preventDefault();
                    $('#mode li').removeClass('selected');
                    $(this).parent('li').addClass('selected');
                    // now change parameters as needed
                    switch(e.target.hash) {
                        case '#transfer':
                            document.getElementById('description').style.display = 'none';
                            document.getElementById('to').style.display = '';
                            document.getElementById('how').style.display = '';
                            document.getElementById('outstanding').style.display = '';
                            document.getElementById('outstanding_label').style.display = '';
                            document.getElementById('statement').value = '0';
                            document.getElementById('transfer').value = '1';
                            break;
                        case '#statement':
                            document.getElementById('description').value = 'Statement';
                            document.getElementById('outstanding').style.display = 'none';
                            document.getElementById('outstanding_label').style.display = 'none';
                            document.getElementById('statement').value = '1';
                        case '#normal':
                            document.getElementById('transfer').value = '0';
                            document.getElementById('description').style.display = '';
                            document.getElementById('to').style.display = 'none';
                            document.getElementById('how').style.display = 'none';
                            if(e.target.hash == '#normal') {
                                document.getElementById('outstanding').style.display = '';
                                document.getElementById('outstanding_label').style.display = '';
                                document.getElementById('statement').value = '0';
                            }
                    }
                });
                $('.statement').addClass('marked');
                $('#list_item a.statement').click(function(e) {
                    e.preventDefault();
                    console.log('Mark');
                    var id = e.target.hash;
                    var out = $(this).parent('li').hasClass('marked')?'1':'2';
                    $.ajax({
                        url: "",
                        type: 'post',
                        data: {
                            page: 'ajax',
                            action: 'outstanding',
                            id: id,
                            outstanding: out 
                        },
                        success: function(result) {
                            xml = $.parseXML(result);
                            switch($(xml).find('outstanding').text()) {
                                case '1':
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').removeClass('marked');
                                break;
                                case '2':
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').addClass('marked');
                                break;
                                case '0':
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').removeClass('marked');
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').removeClass('outstanding');
                                break;
                            }
                        }
                    });
                });
            </script>
        <?php } else {// list transactions ?>
            <ul id="list_item">
                <!-- back button -->
                <li>
                    <a href="<?php echo query_string(array('aID', 'q')); ?>">Back</a>
                </li>
                <?php if($recurring) {// recurring transactions ?>
                    <li id="recurring">
                        <a href="?page=recurring&action=perform">
                        Perform <?php echo $recurring; ?> recurring transactions
                        <img src="arrow.png">
                        </a>
                    </li>
                <?php }// end recurring ?>
                    <li id="new">
                        <a href="<?php echo query_string(array(), array('action'=> 'new')); ?>">
                        Add New
                        <img src="arrow.png">
                        </a>
                    </li>
                <?php foreach($transactions as $row) {
                    $class = '';
                    if($row['statement']) $class .= ' statement';
                    if($row['outstanding']) $class .= ' outstanding';
                    if($row['outstanding']=='2') $class .= ' marked';
                    if($row['value']<0) $class .= ' negative';
                ?>
                    <li class="<?php echo $class; ?>">
                        <?php if($row['statement']) { ?>
                        <a href="<?php echo query_string(array(), array('id' => $row['id'],'action'=>'edit')); ?>">
                        <?php } else { ?>
                        <a href="#<?php echo $row['id']; ?>" class="statement">
                        <?php } ?>
                            <?php echo date('m/d/y',strtotime($row['tran_date'])) ?>
                            <span class="amount">$<?php echo money_format('%#10n', $row['value']); ?></span>
                            <br>
                            <span class="description"><?php echo $row['description']; ?></span>
                            <?php if(!$aID) { ?>
                            <br>
                            <span class="account">(<?php echo $row['name']; ?>)</span>
                            <?php } ?>
                        <?php if(!$row['statement']) { ?>
                        </a>
                        <a href="<?php echo query_string(array(), array('id' => $row['id'],'action'=>'edit')); ?>" class="inline">
                        <?php } ?>
                            <img src="arrow.png">
                        </a>
                    </li>
                <?php }// end foreach ?>
                    <li id="finalize"<?php $state = check_balance($aID); echo $state['total']!=$state['statement']?'class="outstanding"':''; ?>>
                        <a href="<?php echo query_string(array(), array('action'=>'done')); ?>">
                            Finalize Outstanding<br>
                            <span id="summary" class="description"><?php echo 'Total:$'.$state['total'].'<br> Statement:$'.$state['statement']; ?></span>
                        </a>
                    </li>
            </ul>
            <script>
                $('#list_item a.statement').click(function(e) {
                    e.preventDefault();
                    console.log('Mark');
                    var i = e.target.hash.substring(1);
                    var out = $(this).parent('li').hasClass('marked')?'1':'2';
                    $.ajax({
                        url: "?",
                        type: 'post',
                        data: {
                            page: 'ajax',
                            action: 'outstanding',
                            id: i,
                            outstanding: out,
                            aID: <?php echo $aID; ?>
                        },
                        success: function(result) {
                            xml = $.parseXML(result);
                            if($(xml).find('result').text() == 'fail') return;
                            switch($(xml).find('outstanding').text()) {
                                case '1':
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').removeClass('marked');
                                break;
                                case '2':
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').addClass('marked');
                                break;
                                case '0':
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').removeClass('marked');
                                    $('a[href="#'+$(xml).find('id').text()+'"').parent('li').removeClass('outstanding');
                                break;
                            }
                            total = $(xml).find('total').text();
                            statement = $(xml).find('statement').text();
                            document.getelementByID('summary').innerHTML = 'Total:$'+total+'<br> Statement:$'+statement;
                            if(total == statement) {
                                $('#finalize').removeClass('outstanding');
                            } else {
                                $('#finalize').addClass('outstanding');
                            }
                        }
                    });
                });
            </script>
            <?php }// end list ?>
        </div>
        <?php } ?>
    </body>
</html>