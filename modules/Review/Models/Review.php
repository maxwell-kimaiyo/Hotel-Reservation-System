<?php
namespace Modules\Review\Models;

use App\BaseModel;
use Modules\Review\Models\ReviewMeta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends BaseModel
{
    use SoftDeletes;
    protected $table    = 'review';
    protected $fillable = [
        'object_id',
        'object_model',
        'title',
        'content',
        'rate_number',
        'author_ip',
        'status',
        'vendor_id'
    ];

    /**
     * Get Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUserInfo()
    {
        return $this->hasOne("Modules\User\Models\User", "id", 'create_user')->withTrashed();
    }

    public static function getDisplayTextScoreByLever($lever)
    {
        switch ($lever) {
            case 5:
                return __("Excellent");
                break;
            case 4:
                return __("Very Good");
                break;
            case 3:
                return __("Average");
                break;
            case 2:
                return __("Poor");
                break;
            case 1:
            case 0:
                return __("Terrible");
                break;
            default:
                return __("Not rated");
                break;
        }
    }

    public function getService()
    {
        $allServices = get_bookable_services();
        $module = $allServices[$this->object_model];
        return $this->hasOne($module, "id", 'object_id');
    }

    public function getReviewMeta()
    {
        return ReviewMeta::where("review_id", $this->id)->get();
    }

    public static function countReviewByStatus($status = false)
    {
        $count = parent::query();
        if (!empty($status)) {
            $count->where("status", $status);
        }
        return $count->count("id");
    }

    public static function countReviewByServiceID($service_id = false, $user_id = false, $status = false,$service_type = '')
    {
        if (empty($service_id))
            return false;
        $count = parent::where("object_id", $service_id);
        if (!empty($status)) {
            $count->where("status", $status);
        }

        if($service_type){
            $count->where('object_model',$service_type);
        }
        if (!empty($user_id)) {
            $count->where("create_user", $user_id);
        }
        return $count->count("id");
    }

    public function save(array $options = [])
    {
        $check = parent::save($options); // TODO: Change the autogenerated stub
        if ($check) {
            Cache::forget("review_" . $this->object_model . "_" . $this->object_id);
        }
        return $check;
    }
}
