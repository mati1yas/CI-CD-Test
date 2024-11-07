<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['emp_id','name', 'sector', 'location_name','first_process_date','last_process_date',];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
