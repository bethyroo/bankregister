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
        <title><?php echo $title; ?></title>
        <style type="text/css">
            
            .cell1 {
                width: 12em;
            }
            .cell2 {
                width: 20em;
            }
            .cell3 {
                width: 8em;
            }
            .cell4 {
                width: 8em;
            }
            .cell5 {
                width: 12em;
            }
            .foot {
                width: 60em;
            }
            
        </style>
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
            function frequency(unit) {
                // hide all
                document.getElementById('weekday').style.display = 'none';
                document.getElementById('weekend').style.display = 'none';
                document.getElementById('weekend_label').style.display = 'none';
                document.getElementById('on_label').style.display = 'none';
                // now show selected
                switch(unit) {
                    case '3':// year
                    case '2':// month
                    case '0':// day
                        // show weekend modifier
                        document.getElementById('weekend').style.display = '';
                        document.getElementById('weekend_label').style.display = '';
                    break;
                    case '1':// week
                        // show days of week
                        document.getElementById('weekday').style.display = '';
                        document.getElementById('on_label').style.display = '';
                    break;
                }
            }
        </script>
    </head>
    <body id="recurring">
    <?php include "nav.html.php"; ?>
    <?php echo $message; ?>
    <?php if(!$_SESSION['mobile']) include 'account_list.html.php'; ?>
    <div class="list">
        <form method="post" action="index.php?page=recurring">
            <input type="hidden" name="aID" value="<?php echo $aID; ?>">
            <table class="items">
            <thead class="fixed">
                <th class="cell1">Frequency</th>
                <th class="cell2">Description</th>
                <th class="cell3">Amount</th>
                <th class="cell4">Next</th>
                <th class="cell5"></th>
            </thead>
            <tbody class="scroll" id="transactions">
            <?php 
            $total = 0;
            foreach($transactions as $row) {
                $total += $row['value'];
                $row_last = $row['id'];
                $odd = !$odd; 
                $class = $odd?'odd':'even';
                if($row['outstanding']) $class .= ' outstanding';
                if($row['value'] < 0) $class .= ' negative';
                if($total < 0 && $account_info['type'] == 'bank') $class .= ' warning';
                ?>
            <tr class="<?php echo $class; ?>" id="row_<?php echo $row['id']; ?>">
                <td class="cell1"><?php echo $row['text']; ?></td>
                <td class="cell2"><?php echo $row['description']; ?></td>
                <td class="cell3"><?php echo money_format('%#10n', $row['value']); ?></td>
                <td class="cell4"><?php echo $row['next']; ?></td>
                <td class="cell5"><button type="button" onclick="window.location.href='?page=recurring&action=edit&aID=<?php echo $aID; ?>&id=<?php echo $row['id']; ?>'">Edit</button></td>
            </tr>
            <?php } ?> 
            </tbody>
            <tfoot class="fixed">
                <tr class="form">
                    <td colspan="5" class="foot">
                        <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                        <!-- Frequency -->
                        <label id="increment_label">Every:</label>
                        <select name="increment" id="increment">
                            <?php for($i = 1;$i < 32;$i++)
                            echo '<option value="'.$i.'">'.$i.'</option>'; ?>
                        </select>
                        <select name="unit" id="unit" onchange="frequency(this.value)">
                            <?php foreach($frequency_unit as $key => $value) 
                                echo '<option value="'.$key.'"'.($transaction['unit'] == $key?'selected="selected"':'').'>'.$value.'</option>'; ?>
                        </select>
                        <label id="on_label">On</label>
                        <select name="weekday" id="weekday">
                            <?php foreach($frequency_days as $key => $value)
                                echo '<option value="'.$key.'" '.($transaction['weekday']==$key?'selected="selected"':'').'>'.$value.'</option>'; ?>
                        </select>
                        <label id="weekend_label">Adjust date if weekend:</label>
                        <select name="weekend" id="weekend">
                            <?php foreach($frequency_weekend as $key => $value)
                                echo '<option value="'.$key.'" '.($transaction['weekend'] == $key?'selected="selected"':'').'>'.$value.'</option>'; ?>
                        </select>
                        <br>
                        <label>Starting</label>
                        <input type="date" name="start" value="<?php echo ($transaction['start']?$transaction['start']:date('Y-m-d')); ?>">
                        <!-- end recurring frequency -->
                        <input type="text" id="description" name="description" value="<?php echo $transaction['description']; ?>" size="30" placeholder="Description">
                        <!-- Primary Account id -->
                        <?php if($transaction['id'] && !$transaction['link']) { ?>
                        <select name="account">
                            <?php foreach($accounts_array as $account) { 
                                echo '<option value="'.$account['id'].'"'.($account['id']==$transaction['account']?' selected="selected"':'').'>'.$account['name'].'</option>';
                            }
                            ?>
                        </select>
                        <?php } else { ?>
                        <input type="hidden" name="account" value="<?php echo $aID; ?>">
                        <?php } ?>
                        <!-- Transfer form -->
                        <label>From:</label>
                        <select name="to" id="to" style="display:none;">
                            <?php foreach($accounts_array as $account) { 
                                echo '<option value="'.$account['id'].'"'.($account['id']==$transaction['to']?' selected="selected"':'').'>'.$account['name'].'</option>';
                            }
                            ?>
                        </select>
                        <!-- End transfer form -->

                        <input type="text" name="value" value="<?php echo $transaction['value']; ?>" placeholder="$ 0.00" size="8">
                        <input type="submit" name="action" value="<?php echo $transaction['id']?'save':'add';?>">
                        <?php if($transaction['id']) { ?>
                        <input type="submit" name="action" value="delete">
                        <?php }  ?>
                        <input type="checkbox" name="transfer" id="transfer" value="1" <?php if($transaction['transfer']) echo 'checked="checked"'; ?> onchange="toggleTransfer(this.checked)">
                        <label>Transfer</label>
                    </td>
                </tr>
            </tfoot>
        </table>
        </form>
  </div>
        <script>
            document.getElementById('transactions').scrollTop = 1000000;
            toggleTransfer(document.getElementById('transfer').checked);
            frequency('<?php echo ($transaction['unit']?$transaction['unit']:0); ?>');
        </script>
    </body>
</html>