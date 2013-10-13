<?php

    // Read the latest Sponsor List from GitHub
    $credit = file_get_contents('https://raw.github.com/lxcenter/hypervm/dev/hypervm/RELEASEINFO/SPONSORS-SYNC');
    
    // Import as an array
    $credit = json_decode($credit, true);
    
    // Import array to manageable format
    $j = 0;
    $total = 0;
    $array = array();
    
    for($i = 0; ; $i++) {
        if (empty($credit[$i]['weight'])) {
            break;
        }
        else {
            for($k = 0; $k < $credit[$i]['weight']; $k++) {
                $array[$j] = $i;
                $j = $j + 1;
            }
            $total = $total + $credit[$i]['weight'];
        }
    }
    
    $number = rand(0, $total - 1);
    
    $sponsor = $array[$number];
    $name = $credit[$sponsor]['name'];
    $url = $credit[$sponsor]['url'];
    
    if (!empty($name) && !empty($url)) {
?>
<br>
<center>
<div class="copy">
LxCenter is proudly sponsored by <a href="<?php echo $url; ?>"><?php echo $name; ?></a>.
</div>
</center>
<?php
    }