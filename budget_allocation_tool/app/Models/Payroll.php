<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'date', 'fund_no','gl_account', 'type', 'amount_birr','amount_usd'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
