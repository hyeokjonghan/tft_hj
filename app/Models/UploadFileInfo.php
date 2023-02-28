<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadFileInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "upload_file_info";
    protected $primaryKey = "id";
    protected $fillable = [
        'target_no',
        'file_sort',
        'upload_type',
        'file_size',
        'file_real_name',
        'file_extension',
        'file_temp_name',
        'file_path',
        'file_s3_path',
        'creaetd_at',
        'updated_at'
    ];
}
