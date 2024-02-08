<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComplaintComment extends Model
{
    protected 	$table 		=	'customer_complaint_comment';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;
}
