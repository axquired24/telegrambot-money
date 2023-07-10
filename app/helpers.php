<?php

function rupiahFormat($angka, $minusValue=false)
{

    $final = "Rp" . number_format(abs($angka), 0,',','.');
    if($minusValue && ($angka < 0)) {
        $final = "-" . $final;
    } // endif
    return $final;
}
