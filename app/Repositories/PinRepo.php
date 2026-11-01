<?php

namespace App\Repositories;

use App\Models\Pin;
use App\Support\CurrentSchool;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PinRepo
{

    public function create($data)
    {
        $school = CurrentSchool::id();
        if ($school) {
            $data = array_map(function ($row) use ($school) {
                return $row + ['school_id' => $school];
            }, $data);
        }

        return DB::table('pins')->insert($data);
    }

    public function countValid()
    {
        return $this->getPin(['used' => 0])->count();
    }

    public function update($id, $data)
    {
        return Pin::find($id)->update($data);
    }

    public function getUserPin($code, $user_id, $st_id)
    {
        return  $this->getPin(['code' => $code, 'user_id' => $user_id, 'student_id' => $st_id])->get();
    }

    public function findValidCode($code)
    {
        return  $this->getPin(['code' => $code, 'used' => 0])->get();
    }

    public function deleteUsed()
    {
        return $this->getPin(['used' => 1])->delete();
    }

    public function getPin($data)
    {
        return Pin::where($data);
    }

    public function getValid()
    {
        return $this->getPin(['used' => 0])->get();
    }

    public function getInValid()
    {
        return $this->getPin(['used' => 1])->with(['user', 'student'])->get();
    }
}