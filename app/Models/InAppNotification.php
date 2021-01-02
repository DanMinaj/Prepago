<?php
use Illuminate\Database\Eloquent\Model;

class InAppNotification extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'in_app_notifications';

    public function getStats()
    {
        $total = self::where('body', $this->body)->where('all_schemes', 1)->count();
        $views = self::where('body', $this->body)->where('all_schemes', 1)->where('delivered', 1)->count();

        return (object) [
            'total' => $total,
            'views' => $views,
        ];
    }
}
