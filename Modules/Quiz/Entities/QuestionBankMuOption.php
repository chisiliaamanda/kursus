<?php

namespace Modules\Quiz\Entities;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;


class QuestionBankMuOption extends Model
{

    use Tenantable;

    protected $fillable = [];

    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id')->withDefault();
    }
}
