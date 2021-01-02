<?php

class TrackingFaqClick extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tracking_faq_clicks';

    protected $primaryKey = 'id';

    protected $appends = ['answer'];

    public function getAnswerAttribute()
    {
        $scheme = Scheme::where('scheme_number', $this->scheme_number)->first();
        $faq = $scheme->FAQ;
        if (empty($faq) || $this->scheme_number == 0) {
            $neptune = Scheme::find(16);
            $faq = $neptune->FAQ;
        }
        $faq = json_decode($faq);

        foreach ($faq as $k => $v) {
            if ($v->question == $this->title) {
                return $v->answer;
            }
        }

        return '';
    }
}
