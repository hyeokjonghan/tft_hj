<?php

namespace App\Models\TFT;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TFTVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tft_version";
    protected $primaryKey = "version";
    protected $fillable = [
        'version',
        'now_version',
        'applicable_version',
        'creaetd_at',
        'updated_at'
    ];
    

}
