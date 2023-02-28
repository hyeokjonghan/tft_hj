<?php

namespace App\Http\Controllers\TFT;

use App\Http\Controllers\Controller;
use App\Models\TFT\TFTVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TFTVersionController extends Controller
{
    /**
     * 만들어야 할 것
     * 버전 최신화용 (주기적으로 스케줄링 돌아 가야 함)
     * 적용 가능한 버전 불러오는 얘
     * 현재 버전 불러 오는 얘
     * 현재 버전 적용
     * 적용 가능한 버전 업데이트용 (이건 다른거 다 작업 하고 진행 해야 함)
     * 
     */

     /** 버전 최신화 스케줄링 용 */
     public function autoSetVersion() {

     }

     /** 현재 적용 버전 가져오기 */
     public function getNowVersion() {
        return TFTVersion::where('now_version', true)->first();
     }

     /** 적용 가능한 버전 가져오기 */
     public function getApplicableVersion() {
        return TFTVersion::where('applicable_version', true)->get();
     }

     /** 버전 적용 */
     public function setNowVersion(Request $request) {
        /* Validation Check */
        $validator = [
            'version'=>'required|string'
        ];

        $validatorCheck = Validator::make($request->all(), $validator);
        if($validatorCheck->fails()) {
            return response($validatorCheck->errors()->all(), Response::HTTP_METHOD_NOT_ALLOWED);
        }
        /* 다른 데이터들 세팅 되어있는지 검증 절차 들어가야 함 */

        /* 적용 가능한 버전 있는지 체크 */
        $checkVersion = TFTVersion::where('version', $request->version)
        ->where('applicable_version',true)
        ->first();

        if($checkVersion) {
            TFTVersion::where('now_version',true)->update(['now_version'=>false]);
            TFTVersion::where('version', $request->version)->update(['now_version'=>true]);
        } else {
            return response()->caps('request version is not applicable.', Response::HTTP_BAD_REQUEST);
        }
        
     }

     /** 적용 가능한 버전 업데이트 */
     public function setApplicableVersionUpdate(Request $request) {
        
     }
}
