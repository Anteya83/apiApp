<?php
namespace App\Traits;

use Carbon\Carbon;

trait DateToFormat
{
    public function dateToFormatBd(string $date)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $date);

        return $date->format('Y-m-d H:i:s');
    }

}
