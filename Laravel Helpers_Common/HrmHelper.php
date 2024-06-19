<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Zone;
use App\Models\Leave;
use App\Models\Media;
use App\Models\Shift;
use App\Models\Lockup;
use App\Models\Permit;
use App\Models\Company;
use App\Models\Holiday;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\ShortLeave;
use App\Models\CompanyModule;
use App\Models\OfficialLeave;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Services\Common\GenericServices;

if (!function_exists('get_client_logo_url')) {
    /**
     * generate company logo url.
     *
     * @return string
     */
    function get_client_logo_url()
    {
        $companySetting = CompanySetting::where('key', 'company_logo')->first();
        if (isset($companySetting->value) && !empty($companySetting->value)) {
            $db_name = Session::has('db_name') ? Session::get('db_name') : '';
            $response = url(config('image.company_logo_path_view') . $db_name . '/' . $companySetting->value);
        } else {
            $response = null;
        }
        return $response;
    }
}

if (!function_exists('get_company_setting')) {
    /**
     * get company setting.
     *
     * @return string
     */
    function get_company_setting($key)
    {
        return (new GenericServices())->getCompanySetting($key);
    }
}

if (!function_exists('get_lockup')) {
    /**
     * get lockup by key.
     *
     * @return collection
     */
    function get_lockup($key)
    {
        $lockups = Lockup::getByTypeKey($key);
        return !empty($lockups) ? $lockups : [];
    }
}

if (!function_exists('get_lockup_full')) {
    /**
     * get lockup by full data.
     *
     * @return collection
     */
    function get_lockup_full($key)
    {
        $lockups = Lockup::getLockupByKey($key);
        return !empty($lockups) ? $lockups : [];
    }
}

if (!function_exists('custom_encrypt')) {
    /**
     * encrypt value.
     *
     * @param  string  $value
     * @return string
     */
    function custom_encrypt($value)
    {
        $key            = env('CUSTOM_CRYPT_KEY') ? env('CUSTOM_CRYPT_KEY') : 'Du8Lh4hY0xtOPKK09i4kBLhog3td8r6L'; //32 character long
        $EncrypterObj   = new \Illuminate\Encryption\Encrypter($key, 'AES-256-CBC');
        return $EncrypterObj->encrypt($value);
    }
}

if (!function_exists('custom_decrypt')) {
    /**
     * decrypt value.
     *
     * @param  string  $value
     * @return string
     */
    function custom_decrypt($value)
    {
        $key            = env('CUSTOM_CRYPT_KEY', 'Du8Lh4hY0xtOPKK09i4kBLhog3td8r6L'); //32 character long
        $EncrypterObj   = new \Illuminate\Encryption\Encrypter($key, 'AES-256-CBC');
        return $EncrypterObj->decrypt($value);
    }
}

if (!function_exists('check_license_validity')) {
    /**
     * check license validity.
     *
     * @return boolean|integer
     */
    function check_license_validity()
    {
        $company = Company::first();
        if (isset($company->license_key) && !empty($company->license_key)) {
            $todayDate  = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            $expireDate = Carbon::createFromFormat('Y-m-d', custom_decrypt($company->license_key));
            $response   = $expireDate->gte($todayDate);
        } else {
            $response   = false;
        }
        return $response;
    }
}

if (!function_exists('get_license_validity_days')) {
    /**
     * get license validity days.
     *
     * @return integer
     */
    function get_license_validity_days($param = '')
    {
        $company = Company::first();
        if (isset($company->license_key) && !empty($company->license_key)) {
            $todayDate  = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            $expireDate = Carbon::createFromFormat('Y-m-d', custom_decrypt($company->license_key));
            if ($param == 'humans') {
                $response   = $expireDate->diffForHumans($todayDate);
            } else {
                $response   = $expireDate->diffInDays($todayDate);
            }
        } else {
            $response   = 0;
        }
        return $response;
    }
}

if (!function_exists('connect_db')) {
    /**
     * connect database (mysql).
     *
     * @return null
     */
    function connect_db($db_name)
    {
        #change db config
        DB::disconnect('mysql');
        Config::set('database.connections.mysql.database', $db_name);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}

if (!function_exists('connect_sqlsrv_ac_db')) {
    /**
     * connect sqlsrv_ac database (Biostar).
     *
     * @return null
     */
    function connect_sqlsrv_ac_db()
    {
        #change db config
        DB::disconnect('sqlsrv_ac');
        Config::set('database.connections.sqlsrv_ac.host', get_company_setting('biostar_database_ip'));
        Config::set('database.connections.sqlsrv_ac.port', get_company_setting('biostar_database_port'));
        Config::set('database.connections.sqlsrv_ac.username', get_company_setting('biostar_database_username'));
        Config::set('database.connections.sqlsrv_ac.password', get_company_setting('biostar_database_password'));
        Config::set('database.connections.sqlsrv_ac.database', get_company_setting('biostar_database_ac_name'));
        DB::purge('sqlsrv_ac');
        DB::reconnect('sqlsrv_ac');
    }
}

if (!function_exists('connect_sqlsrv_ta_db')) {
    /**
     * connect sqlsrv_ta database (Biostar).
     *
     * @return null
     */
    function connect_sqlsrv_ta_db()
    {
        #change db config
        DB::disconnect('sqlsrv_ta');
        Config::set('database.connections.sqlsrv_ta.host', get_company_setting('biostar_database_ip'));
        Config::set('database.connections.sqlsrv_ta.port', get_company_setting('biostar_database_port'));
        Config::set('database.connections.sqlsrv_ta.username', get_company_setting('biostar_database_username'));
        Config::set('database.connections.sqlsrv_ta.password', get_company_setting('biostar_database_password'));
        Config::set('database.connections.sqlsrv_ta.database', get_company_setting('biostar_database_ta_name'));
        DB::purge('sqlsrv_ta');
        DB::reconnect('sqlsrv_ta');
    }
}

if (!function_exists('check_company_active_module')) {
    /**
     * get license validity days.
     *
     * @return boolean|integer
     */
    function check_company_active_module($key)
    {
        return CompanyModule::where('module_key', $key)->exists();
    }
}

if (!function_exists('check_user_count')) {
    /**
     * check user count.
     *
     * @return boolean|integer
     */
    function check_user_count()
    {
        $totalUser = User::count();
        $company   = Company::select('admin_count')->first();
        if ($totalUser < $company->admin_count) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('check_zone_count')) {
    /**
     * check zone count.
     *
     * @return boolean|integer
     */
    function check_zone_count()
    {
        $totalZone = Zone::count();
        $company = Company::select('zone_count')->first();
        if ($totalZone < $company->zone_count) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('get_db_name')) {
    /**
     * check employee count.
     *
     * @return boolean|integer
     */
    function get_db_name()
    {
        return Session::has('db_name') ? Session::get('db_name') : '';
    }
}

if (!function_exists('auth_permission_check')) {
    /**
     * check user auth and permission.
     *
     * @return boolean|integer
     */
    function auth_permission_check($module, $permission)
    {
        if (!Auth::user() || !check_company_active_module($module)) {
            Session::flash('alert-error', __('message.User not authorized'));
            return false;
        }

        if (!Auth::user()->can($permission)) {
            Session::flash('alert-error', __('message.User does not have permission'));
            return false;
        }
        return true;
    }
}

if (!function_exists('auth_permission_check_api')) {
    /**
     * check user auth and permission.
     *
     * @return boolean|string
     */
    function auth_permission_check_api($module, $permission)
    {
        if (!Auth::user() || !check_company_active_module($module)) {
            return  __('message.User not authorized');
        }

        if (!Auth::user()->can($permission)) {
            return  __('message.User does not have permission');
        }
        return true;
    }
}

if (!function_exists('time_addition')) {
    /**
     * time addition.
     *
     * @return string
     */
    function time_addition($time1, $time2, $setting = false)
    {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time) {
            list($hour, $minute, $second) = explode(':', $time);
            $seconds += $hour * 3600;
            $seconds += $minute * 60;
            $seconds += $second;
        }
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes  = floor($seconds / 60);
        $seconds -= $minutes * 60;
        if ($seconds < 9) {
            $seconds = "0" . $seconds;
        }
        if ($minutes < 9) {
            $minutes = "0" . $minutes;
        }
        if ($hours < 9) {
            $hours = "0" . $hours;
        }

        #only H:m
        if ($setting) {
            return "{$hours}:{$minutes}";
        }

        #H:m:i
        return "{$hours}:{$minutes}:{$seconds}";
    }
}

if (!function_exists('convert_time_to_second')) {
    /**
     * convert time to second.
     *
     * @return integer
     */
    function convert_time_to_second($time = '00:00:00')
    {
        $timeArray      = explode(':', $time);
        $totalSecond    = 0;
        if (isset($timeArray[0])) {
            $totalSecond = $timeArray[0] * 3600;
        }
        if (isset($timeArray[1])) {
            $totalSecond = $totalSecond + $timeArray[1] * 60;
        }
        if (isset($timeArray[2])) {
            $totalSecond = $totalSecond + $timeArray[2];
        }
        return $totalSecond;
    }
}

if (!function_exists('convert_time_to_min')) {
    /**
     * convert time to min.
     *
     * @return integer|float
     */
    function convert_time_to_min($time = '00:00')
    {
        $timeArray = explode(':', $time);
        $totalMin  = 0;
        if (isset($timeArray[0])) {
            $totalMin = $totalMin + ($timeArray[0] * 60);
        }
        if (isset($timeArray[1])) {
            $totalMin = $totalMin + $timeArray[1];
        }
        if (isset($timeArray[2])) {
            $totalMin = $totalMin + ($timeArray[2] / 60);
        }
        return $totalMin;
    }
}

if (!function_exists('number_format_without_round')) {
    /**
     * number format without round.
     *
     * @return integer|float
     */
    function number_format_without_round($number = 0)
    {
        return number_format(floor(($number) * 100) / 100, 2, '.', '');
    }
}

if (!function_exists('decimal_hours')) {
    /**
     * convert time to decimal hours.
     *
     * @return integer|float
     */
    function decimal_hours($time = '00:00')
    {
        // return number_format_without_round(convert_time_to_min($time) / 60);
        return round((convert_time_to_min($time) / 60), 2);
    }
}

if (!function_exists('diff_between_two_time')) {
    /**
     * differenc between two time and convert to minutes.
     *
     * @return integer
     */
    function diff_between_two_time($start_time = '00:00', $end_time = '00:00')
    {
        if (!empty($start_time) && !empty($end_time)) {
            $start_time = Carbon::parse($start_time);
            $end_time   = Carbon::parse($end_time);
            return $start_time->diffInMinutes($end_time);
        } else {
            return 0;
        }
    }
}

if (!function_exists('diff_between_day_two_time')) {
    /**
     * differenc between two day time and convert to minutes.
     *
     * @return integer
     */
    function diff_between_day_two_time($start_time = '00:00', $end_time = '00:00')
    {
        if (!empty($start_time) && !empty($end_time)) {
            $start_time = Carbon::parse($start_time);
            $end_time   = Carbon::parse($end_time)->addDay();
            return $start_time->diffInMinutes($end_time);
        } else {
            return 0;
        }
    }
}

if (!function_exists('date_range_converter')) {
    /**
     * date range converter.
     *
     * @return boolean|array
     */
    function date_range_converter($date_range)
    {
        if (!empty($date_range)) {
            if (Auth::user()->lang == 'en') {
                $dateRangeArray = explode(' to ', $date_range);
            } else {
                $dateRangeArray = explode(' - ', $date_range);
            }

            $from_date = $to_date = null;
            if (count($dateRangeArray) == 2) {
                $from_date       = date('Y-m-d', strtotime($dateRangeArray[0]));
                $to_date         = date('Y-m-d', strtotime($dateRangeArray[1]));
            } else {
                $from_date       = date('Y-m-d', strtotime($dateRangeArray[0]));
                $to_date         = $from_date;
            }

            return [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ];
        } else {
            return null;
        }
    }
}

if (!function_exists('date_range_days_count')) {
    /**
     * days count between two dates.
     *
     * @return integer
     */
    function date_range_days_count($start_date, $end_date)
    {
        if (!empty($start_date) && !empty($end_date)) {
            $start_date = Carbon::parse($start_date);
            $end_date   = Carbon::parse($end_date);
            return $start_date->diffInDays($end_date) + 1;
        } else {
            return 0;
        }
    }
}

if (!function_exists('get_all_dates_range')) {
    /**
     * date range converter.
     *
     * @return array
     */
    function get_all_date_array($start_date, $end_date)
    {
        if (!empty($start_date) && !empty($end_date)) {
            $period     = CarbonPeriod::create($start_date, $end_date);
            $dateArray  = [];

            foreach ($period as $key => $date) {
                $dateArray[] = $date->format('Y-m-d');
            }
            return $dateArray;
        } else {
            return [];
        }
    }
}

if (!function_exists('auth_department_permission_check')) {
    /**
     * check auth department permission.
     *
     * @return boolean|integer|string
     */
    function auth_department_permission_check($uuid, $type)
    {
        #for Administrator always true
        if (in_array('Administrator', auth()->user()->assigned_roles->toArray())) return true;

        #for other roles
        if (!empty($uuid) && !empty($type)) {
            $department = Department::with('authUser')->where('uuid', $uuid)->first();
            if (isset($department->authUser[0]) && !empty($department->authUser[0])) {
                if ($type == 'read' && $department->authUser[0]->pivot->read) {
                    return true;
                }
                if ($type == 'edit' && $department->authUser[0]->pivot->edit) {
                    return true;
                }
                if ($type == 'delete' && $department->authUser[0]->pivot->delete) {
                    return true;
                }
            }
        }
        return false;
    }
}

if (!function_exists('user_manual_attendance_limit')) {
    /**
     * check user manual attendance limit.
     *
     * @return boolean|integer
     */
    function user_manual_attendance_limit($user_id)
    {
        $attendanceCount = Attendance::whereMonth('created_at', Carbon::now()->month)
            ->where('user_id', $user_id)
            ->where('created_by', auth()->user()->id)
            ->count();

        if ($attendanceCount < get_company_setting('manual_attendance_allowed_count')) {
            return true;
        } else {
            Session::flash('alert-error', __('message.Ooops, user manual attendance limit completed'));
            return false;
        }
    }
}

if (!function_exists('attendance_other_status')) {
    /**
     * filter attendance other status based.
     *
     * @return boolean|string
     */
    function attendance_other_status($date, $user_id)
    {
        if (!empty($date) && !empty($user_id)) {
            $user = User::with([
                'shifts',
                'primaryDepartment.shifts',
            ])->withCount([
                'leaves' => function ($q) use ($date) {
                    $q->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->where('status', 1);
                },
                'permits' => function ($q) use ($date) {
                    $q->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date);
                },
                'attendanceExclusions' => function ($q) use ($date) {
                    $q->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date);
                }
            ])->where('id', $user_id)->first();

            if (!empty($user)) {
                #check leave condition
                if (isset($user->leaves_count) && !empty($user->leaves_count)) {
                    return __('main.leave');
                }

                if (isset($user->permits_count) && !empty($user->permits_count)) {
                    return __('main.permit');
                }

                #check attendance exclusion
                if (isset($user->attendance_exclusions_count) && !empty($user->attendance_exclusions_count)) {
                    return __('main.excluded');
                }

                #check shift conditions
                #get shifts
                if ($user->shifts()->exists() && $user->shifts()->count()) {
                    $shifts = $user->shifts;
                } else {
                    $shifts = isset($user->primaryDepartment[0]->shifts) && $user->primaryDepartment[0]->shifts->isNotEmpty() ? $user->primaryDepartment[0]->shifts : null;
                }

                if (!empty($shifts)) {
                    #check priority
                    $priorityShifts = $shifts->where('priority', 2)->all();

                    if (!empty($priorityShifts) && count($priorityShifts)) {
                        $priorityShifts = array_values($priorityShifts);
                        $shifts = $priorityShifts[0];
                    } else {
                        $shifts = $shifts[0];
                    }

                    $carbanDate  = new Carbon($date);
                    $weekDayNum =  $carbanDate->dayOfWeekIso;
                    if (in_array($shifts->shift_type, [1, 2, 3, 4])) {
                        if ($shifts->day_off == $weekDayNum) {
                            return __('main.day_off');
                        }
                        if ($shifts->rest_day == $weekDayNum) {
                            return __('main.rest_day');
                        }
                    }

                    #business shift
                    if ($shifts->shift_type == 5) {
                        return __('main.rest_day');
                    }
                }
            }
        }
        return __('main.absent');
    }
}

if (!function_exists('attendance_summary')) {
    /**
     * filter attendance summary.
     *
     * @return array
     */
    function attendance_summary($date, $user_id, $summ)
    {
        if (!empty($date) && !empty($user_id)) {
            $shortLeave = ShortLeave::where('user_id', $user_id)->whereDate('short_leave_date', $date)->where('status', 1)->first();
            if (!empty($shortLeave)) {
                $text = __('main.short_leave') . ' (' . $shortLeave->shortLeaveType->slt_name . ')';
                array_push($summ, $text);
            };

            $officialLeave = OfficialLeave::where('user_id', $user_id)->whereDate('official_leave_date', $date)->first();
            if (!empty($officialLeave)) {
                $text = __('main.official_leave') . ' (' . $officialLeave->officialLeaveType->name . ')';
                array_push($summ, $text);
            };

            $leave = Leave::where('user_id', $user_id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->where('status', 1)
                ->first();

            if (!empty($leave)) {
                $text = __('main.leave') . ' (' . $leave->leaveType->name . ')';
                array_push($summ, $text);
            };

            $permit = Permit::where('user_id', $user_id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();
            if (!empty($permit)) {
                $text = __('main.permit') . ' (' . $permit->permitType->name . ')';
                array_push($summ, $text);
            };
        }
        return $summ;
    }
}

if (!function_exists('get_split_shift_slot')) {
    /**
     * get split shift slot.
     *
     * @return string
     */
    function get_split_shift_slot($shift_id, $shift_rule_index = 0)
    {
        $shift = Shift::where('id', $shift_id)->first();
        if (!empty($shift)) {
            return $shift->shift_rule[$shift_rule_index]['start_time'] . '-' . $shift->shift_rule[$shift_rule_index]['end_time'];

            // $slotTime = $shift->shift_rule[$shift_rule_index]['start_time'];
            // if (!empty($shift->shift_rule[$shift_rule_index]['end_time'])) {
            //     $slotTime = $slotTime . '-' . $shift->shift_rule[$shift_rule_index]['end_time'];
            // }
            // return $slotTime;
        }
        return '';
    }
}

if (!function_exists('add_date_time')) {
    /**
     * add date and time in one datetime.
     *
     * @return any
     */
    function add_date_time($date, $time)
    {
        $newDateTime = $date . $time;
        $newDateTime = new Carbon($newDateTime);
        return $newDateTime;
    }
}

if (!function_exists('conver_second_time')) {
    /**
     * convert seconds to H:i:s.
     *
     * @return string
     */
    function conver_second_time($seconds)
    {
        return sprintf("%02d%s%02d%s%02d", floor(abs($seconds) / 3600), ':', (abs($seconds) / 60) % 60, ":", abs($seconds) % 60);
    }
}

if (!function_exists('calcu_overtime_hours')) {
    /**
     * calculate overtime hours
     *
     * @return return any
     */
    function calcu_overtime_hours($dateObj)
    {
        $overTimeRate = 0;
        $overtimeScond = 0;
        $totalTimeSecond = convert_time_to_second($dateObj->total_time);
        $overtimeMaxHoursSecond = convert_time_to_second((float) $dateObj->overtime_max_hours);

        #check for overtime
        if (!empty($dateObj->apply_after_hours) || !empty($dateObj->apply_after_minutes)) {
            $applyAfterSecondes      = ($dateObj->apply_after_hours * 3600) + ($dateObj->apply_after_minutes * 60);
            $overtimeStartTimeSecond =  convert_time_to_second($dateObj->max_shift_hours) + $applyAfterSecondes;

            if ($totalTimeSecond < $overtimeStartTimeSecond) {
                return '00:00:00';
            } else {
                $overtimeScond = ($totalTimeSecond - $overtimeStartTimeSecond);
                #check for max overtime hours
                if ($overtimeMaxHoursSecond < $overtimeScond) {
                    $overtimeScond = $overtimeMaxHoursSecond;
                }
            }
        }

        #check holiday
        if ($dateObj->is_holiday == 1) {
            if (Holiday::whereDate('date', date("Y-m-d", strtotime($dateObj->checkin_time)))->exists()) {
                $overTimeRate = $dateObj->holiday_rate;
            }
        }

        #check weekend
        if ($dateObj->is_weekend == 1) {
            if (!empty($dateObj->day_off) && !empty($dateObj->rest_day)) {
                $weekDay = $dateObj->rest_day;
            } else if (!empty($dateObj->day_off)) {
                $weekDay = $dateObj->day_off;
            } else if (!empty($dateObj->rest_day)) {
                $weekDay = $dateObj->rest_day;
            } else {
                $weekDay = null;
            }

            if (!empty($weekDay) && $dateObj->day == today()->weekday($weekDay)->format('l')) {
                $overTimeRate = $dateObj->weekend_rate;
            }
        }

        #default rate
        if (empty($overTimeRate)) {
            $overTimeRate = $dateObj->hours_rate;
        }

        #final overtime calculation
        $totalOvertimeSecond = $overtimeScond * $overTimeRate;
        if (!empty($totalOvertimeSecond)) {
            return conver_second_time($totalOvertimeSecond);
        }
        return '00:00:00';
    }
}

if (!function_exists('year_filter')) {
    /**
     * year array
     *
     * @return array
     */
    function year_filter()
    {
        $years = [];
        $j = 0;
        for ($i = 2023; $i <= date('Y'); $i++) {
            $years[$j] = $i;
            $j++;
        }
        rsort($years);
        return $years;
    }
}

if (!function_exists('field_tran_tring')) {
    /**
     * create field translation string
     *
     * @return string
     */
    function field_tran_tring($fieldName, $lang)
    {
        return "( CASE WHEN( json_extract(" . $fieldName . ", '$." . $lang . "') != '' AND LOWER(json_extract(" . $fieldName . ", '$." . $lang . "')) != 'null' AND json_extract(" . $fieldName . ", '$." . $lang . "') IS NOT NULL) THEN json_unquote(json_extract(" . $fieldName . ", '$." . $lang . "')) ELSE json_unquote(json_extract(" . $fieldName . ", '$.en')) END )";
    }
}

if (!function_exists('get_thumbnail_image')) {
    /**
     * get thumbnail image
     *
     * @return string
     */
    function get_thumbnail_image($media_id)
    {
        if (!empty($media_id)) {
            $media = Media::where('id', $media_id)->first();
            return $media->thumbnail_name;
        }
    }
}

if (!function_exists('date_range_remove_friday')) {
    /**
     * remove all friday from date range
     *
     * @return array
     */
    function date_range_remove_friday($start_date, $end_date)
    {
        return (new GenericServices)->dateRangeRemoveFriday($start_date, $end_date);
    }
}

if (!function_exists('format_interval')) {
    /**
     * Format an interval to show all existing components.
     * If the interval doesn't have a time component (years, months, etc)
     * That component won't be displayed.
     *
     * @param DateInterval $interval The interval
     *
     * @return string Formatted interval string.
     */
    function format_interval(DateInterval $interval)
    {
        $result = "";
        if ($interval->y) {
            $result .= $interval->format("%y years ");
        }
        if ($interval->m) {
            $result .= $interval->format("%m months ");
        }
        if ($interval->d) {
            $result .= $interval->format("%d days ");
        }
        if ($interval->h) {
            $result .= $interval->format("%h hours ");
        }
        if ($interval->i) {
            $result .= $interval->format("%i minutes ");
        }
        if ($interval->s) {
            $result .= $interval->format("%s seconds ");
        }

        return $result;
    }
}

if (!function_exists('get_company_code')) {
    /**
     * get company code
     *
     * @return int|null
     */
    function get_company_code()
    {
        return (new GenericServices)->getCompanyCode();
    }
}

if (!function_exists('time_zone_format')) {
    /**
     * convert datetime to company time zone.
     *
     * @return datetime
     */
    function time_zone_format($date, $dateFormat = null)
    {
        $defaultTimeZone = (new GenericServices())->getCompanySetting('default_time_zone');
        // $dateUTC         = Carbon::parse($date)->setTimezone('-5:30');
        if (!empty($defaultTimeZone)) {
            if (!empty($dateFormat)) {
                $newDate = Carbon::parse($date)->setTimezone($defaultTimeZone)->format($dateFormat);
            } else {
                $newDate = Carbon::parse($date)->setTimezone($defaultTimeZone)->format('d-m-Y H:i:s');
            }
        }

        return isset($newDate) ? $newDate : $date;
    }
}

if (!function_exists('time_zone_date')) {
    /**
     * convert date to company time zone.
     *
     * @return date
     */
    function time_zone_date($dateFormat = null)
    {
        $defaultTimeZone = (new GenericServices())->getCompanySetting('default_time_zone');

        // $dateUTC         = Carbon::parse($date)->setTimezone('-5:30');
        if (!empty($defaultTimeZone)) {
            $newDate = Carbon::now()->setTimezone($defaultTimeZone);
        } else {
            $newDate = Carbon::now()->setTimezone('UTC');
        }

        if (!empty($dateFormat)) {
            $newDate = $newDate->format($dateFormat);
        } else {
            $newDate = $newDate->format('Y-m-d H:i:s');
        }

        return $newDate;
    }
}

if (!function_exists('dateByCalender')) {
    /**
     * convert date based on calender type.
     *
     * @return any
     */
    function dateByCalender($date, $format)
    {
        return (new GenericServices())->dateByCalender($date, $format);
    }
}

if (!function_exists('get_calender_type')) {
    /**
     * calender type.
     *
     * @return int|null
     */
    function get_calender_type()
    {
        return get_company_setting('calendar_type');
    }
}

if (!function_exists('get_week_day_name')) {
    /**
     * get week day name by day number.
     *
     * @return string|null
     */
    function get_week_day_name($key)
    {
        return (new GenericServices())->getWeekDayName($key);
    }
}

// if (!function_exists('generate_custom_uuid')) {
//     /**
//      * generate custom uuid code
//      *
//      * @return string
//      */
//     function generate_custom_uuid($table, $type = '')
//     {
//         return (new GenericServices())->generateCustomUuid($table, $type = '');
//     }
// }