<?php
use Illuminate\Support\Carbon;

function changeFileName($file, $basic_file_name) {
    $file_name = $basic_file_name . "_" . Carbon::now()->timestamp;
    $file_extension = $file->getClientOriginalExtension();
    return md5($file_name ). "." . $file_extension;
}