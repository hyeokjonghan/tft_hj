<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UploadFileInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request as FileRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    /* 업로드 파일 명 */
    public static $COMMON_IMG = "common";
    public static $DOCUMENT_IMG = "document";

    /* 이미지 리사이즈 규칙 */
    public static $SMALL = 's';                 // 375px
    public static $SMALL_MEDIUM ='s_m';         // 500px
    public static $MEDIUM = 'm';                // 640px
    public static $LARGE_MEDIUM = 'l_m';        // 800px
    public static $LARGE = 'l';                 // 1024px
    public static $ORIGIN = 'o';                // 원본

    public function fileUpload(Request $request, FileRequest $fileRequest, $uploadDivision) {
        switch ($uploadDivision) {
            case UploadController::$COMMON_IMG:
                $uploadFolderPath = "common/img";
                break;
            case UploadController::$DOCUMENT_IMG:
                $uploadFolderPath = "document/img";
                break;
        }

        $target = $request->get('target_no', null);
        $uploadFileInfo = UploadController::uploadMultiFile($fileRequest, $uploadFolderPath, $uploadDivision, $target);
        UploadFileInfo::insert($uploadFileInfo);
        return $uploadFileInfo;
    }

    public function uploadMultiFile(FileRequest $fileRequest, $uploadFolderPath, $uploadName, $target = null) {
        $uploadResult = array();
        if($fileRequest::hasfile($uploadName)) {
            $uploadFile = $fileRequest::file($uploadName);
            if(is_array($uploadFile)) {
                foreach($uploadFile as $i => $file) {
                    $fileSize = $file->getSize();
                    $fileRealName = $file->getClientOriginalName();
                    $fileExtension = $file->getClientOriginalExtension();
                    $fileTempName = uniqid();
                    $filePath = $uploadFolderPath.'/'.$fileTempName.'.'.$fileExtension;
                    Storage::disk('s3')->put($filePath, file_get_contents($file));
                    array_push($uploadResult, array(
                        'target_no'=>$target,
                        'upload_type'=>$uploadName,
                        'file_sort'=>$i+1,
                        'file_size'=>$fileSize,
                        'file_real_name'=>$fileRealName,
                        'file_extension'=>$fileExtension,
                        'file_temp_name'=>$fileTempName,
                        'file_path'=>'/'.$filePath,
                        'file_s3_path'=>env('AWS_CLOUDFRONT_S3_URL').'/'.$filePath
                    ));
                }
            } else {
                $fileSize = $fileRequest::file($uploadName)->getSize();                              // 파일 사이즈
                $fileRealName = $fileRequest::file($uploadName)->getClientOriginalName();                  // 원본 파일 명
                $fileExtension = $fileRequest::file($uploadName)->getClientOriginalExtension();            // 확장자
                $fileTempName = uniqid();                                        // 임시 파일 명
                $filePath = $uploadFolderPath.'/'.$fileTempName.'.'.$fileExtension;
                Storage::disk('s3')->put($filePath, file_get_contents($fileRequest::file($uploadName)));
                array_push($uploadResult, array(
                    'target_no' => $target,
                    'upload_type' => $uploadName,
                    'file_size'=>$fileSize,
                    'file_real_name'=>$fileRealName,
                    'file_extension'=>$fileExtension,
                    'file_temp_name'=>$fileTempName,
                    'file_path'=>'/'.$filePath,
                    'file_s3_path'=>env('AWS_CLOUDFRONT_S3_URL').'/'.$filePath
                ));
            }

            return $uploadResult;
        } else {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        
    }

    public function imageResize(Request $request, $size) {
        $validator = [
            'image_url'=>'required|string'
        ];

        $validatorCheck = Validator::make($request->all(), $validator);
        if($validatorCheck->fails()) {
            return response($validatorCheck->errors()->all(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if($request->image_url[0] != '/') {
            $request->merge(['image_url' => '/'.$request->get('image_url')]);
        }

        // 원본 이미지 확인
        $originImageExists = Storage::disk('s3')->exists($request->image_url);

        if(empty($originImageExists)) {
            return response()->cpas('file is not found.', Response::HTTP_NOT_FOUND);
        }

        $resizeWidth = 375;
        switch($size) {
            case UploadController::$SMALL:
                $resizeWidth = 375;
                break;
            case UploadController::$SMALL_MEDIUM:
                $resizeWidth = 500;
                break;
            case UploadController::$MEDIUM:
                $resizeWidth = 640;
                break;
            case UploadController::$LARGE_MEDIUM:
                $resizeWidth = 800;
                break;
            case UploadController::$LARGE:
                $resizeWidth = 1024;
                break;
            case UploadController::$ORIGIN:
                return Storage::disk('s3')->get($request->image_url);
                break;
            default:
                return response()->caps("target size is bad request.",Response::HTTP_METHOD_NOT_ALLOWED);
                break;
        }

        $resizeImageExists = Storage::disk('s3')->exists('/resize/'.$size.$request->image_url);
        if($resizeImageExists) {
            return response()->redirectTo(env('AWS_CLOUDFRONT_S3_URL').'/resize/'.$size.$request->image_url);
        } else {
            $originImage = Storage::disk('s3')->get($request->image_url);
            $getExtension = explode('.', $request->image_url);
            if(count($getExtension) < 2) {
                return response()->caps("request file can not resize", Response::HTTP_METHOD_NOT_ALLOWED);
            }

            $extension = $getExtension[count($getExtension)-1];
            if(strtolower($extension) != 'jpg' && strtolower($extension) != 'png' && strtolower($extension) !='jpeg' && strtolower($extension) !='gif') {
                return response(["message"=> "request file is not image file", "code"=>405], 405);
            }

            $originImageInfo = Image::make($originImage);
            $resizeRate = $resizeWidth/$originImageInfo->width();

            $resizeImage = Image::make($originImage)->resize($resizeWidth, intval($originImageInfo->height() * $resizeRate))->encode($extension);
            Storage::disk('s3')->put('resize/'.$size.'/'.$request->image_url,(string) $resizeImage);

            return response()->redirectTo(env('AWS_CLOUDFRONT_S3_URL').'/resize/'.$size.$request->image_url);
        }
    }
}
