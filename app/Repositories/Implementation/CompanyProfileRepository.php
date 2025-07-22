<?php

namespace App\Repositories\Implementation;

use App\Models\CompanyProfiles;
use App\Models\Languages;
use App\Repositories\Implementation\BaseRepository;
use App\Repositories\Contracts\CompanyProfileRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Aws\S3\S3Client;

class CompanyProfileRepository extends BaseRepository implements CompanyProfileRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor..
     *
     *
     * @param Model $model
     */


    public static function model()
    {
        return CompanyProfiles::class;
    }

    public function getCompanyProfile()
    {
        $languages = Languages::select(['languages.code', 'languages.name', 'languages.imageUrl', 'languages.order', 'languages.isRTL'])->orderBy('languages.order')->get();
        try {
            $model = $this->model->first();
            if ($model == null) {
                $user = Auth::user();
                if ($user) {
                    $model = CompanyProfiles::create([
                        'logoUrl' =>  '',
                        'title' => 'Document Management System',
                        'location' =>  'local',
                    ]);
                    $model->save();
                    $result = $this->parseResult($model);
                    $result->languages = $languages;
                    return $result;
                }
                return [
                    'logoUrl' =>  '',
                    'title' => 'Document Management System',
                    'languages' => $languages,
                    'location' =>  'local',
                ];
            }

            $model->languages = $languages;
            $model->isS3Supported = true;
            if ($model->location == 'local') {
                $model->isS3Supported = env('AWS_ACCESS_KEY_ID') ? true : false;
            }
            return $model;
        } catch (\Throwable $th) {
            return [
                'logoUrl' =>  '',
                'title' => 'Document Management System',
                'languages' => $languages,
                'location' =>  'local',
            ];
        }
    }


    public function updateCompanyProfile($request)
    {
        $languages = Languages::select(['languages.code', 'languages.name', 'languages.imageUrl', 'languages.order', 'languages.isRTL'])->get();
        $model = $this->model->first();
        $logo = '';
        $banner = '';
        if ($request['imageData']) {
            $logo = $this->saveCompanyProfileImage($request['imageData']);
        } else {
            $logo = $model->logoUrl;
        }

        if ($request['bannerData']) {
            $banner = $this->saveCompanyProfileImage($request['bannerData']);
        } else {
            $banner = $model->bannerUrl;
        }

        if ($model == null) {
            $model = $this->model->newInstance($request);
            $model->title = $request['title'];
            $model->logoUrl = $logo;
            $model->bannerUrl = $banner;
            $model->save();
            $result = $this->parseResult($model);
            $result->languages = $languages;
            return $result;
        } else {
            $model->logoUrl = $logo;
            $model->bannerUrl = $banner;
            $model->title = $request['title'];
            $model->save();
            $result = $this->parseResult($model);
            $result->languages = $languages;
            return $result;
        }
    }

    public function updateStorage($request)
    {
        $model = $this->model->first();
        $oldLocation = $model->location;

        if ($request['location'] == 'local') {
            $model->location = $request['location'];
            $model->save();
            return response()->json([]);
        }

        $oldConfig = collect(
            [
                'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'),
                'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'),
                'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
                'AWS_BUCKET' => env('AWS_BUCKET'),
            ]
        );

        try {

            try {
                if (!Storage::disk('local')->exists('sample-test-file.txt')) {
                    Storage::disk('local')->put('sample-test-file.txt', 'this is test file to check connection');
                }

                $client = new S3Client([
                    'credentials' => [
                        'key'    => $request['amazonS3key'],
                        'secret' => $request['amazonS3secret'],
                    ],
                    'region' => $request['amazonS3region'],
                    'version' => 'latest',
                ]);

                $client->putObject([
                    'Bucket' => $request['amazonS3bucket'],
                    'Key'    => 'sample-test-file.txt',
                    'Body'   => Storage::disk('local')->get('sample-test-file.txt'),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error in connecting to s3 bucket with given config',
                ], 409);
            }

            // try {

            //     if (!Storage::disk('local')->exists('sample-test-file.txt')) {
            //         Storage::disk('local')->put('sample-test-file.txt', 'this is test file to check connection');
            //     }

            //     config([
            //         'filesystems.disks.s3.bucket' => $request['amazonS3bucket'],
            //         'filesystems.disks.s3.region' => $request['amazonS3region'],
            //         'filesystems.disks.s3.key' => $request['amazonS3key'],
            //         'filesystems.disks.s3.secret' => $request['amazonS3secret'],
            //     ]);

            //     $filePath = Storage::path('local') . 'sample-test-file.txt';
            //     Storage::disk('s3')->put('sample-test-file.txt', file_get_contents($filePath));
            // } catch (\Exception $th) {
            //     // $this->setEnvs($oldConfig);
            //     return response()->json([
            //         'message' => 'Error in connecting to s3 bucket with given config',
            //     ], 409);
            // }

            try {
                $config = collect(
                    [
                        'AWS_ACCESS_KEY_ID' => $request['amazonS3key'],
                        'AWS_SECRET_ACCESS_KEY' => $request['amazonS3secret'],
                        'AWS_DEFAULT_REGION' => $request['amazonS3region'],
                        'AWS_BUCKET' => $request['amazonS3bucket'],
                    ]
                );
                $this->setEnvs($config);
            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'Error in saving .evn File' . $th->getMessage(),
                ], 409);
            }

            $model->location = $request['location'];
            $model->save();

            return response()->json([]);
        } catch (\Throwable $th) {

            // revert config settings.
            $this->setEnvs($oldConfig);

            // revert location settings.
            $model->location = $oldLocation;
            $model->save();

            return response()->json([
                'message' => 'Error in saving data.' . $th->getMessage(),
            ], 409);
        }
    }

    public function getStorage()
    {
        $model = $this->model->first();
        if ($model == null) {
            return response()->json([]);
        }

        return response()->json([
            'location' => $model->location ?? 'local',
            'amazonS3key' => env('AWS_ACCESS_KEY_ID'),
            'amazonS3secret' => env('AWS_SECRET_ACCESS_KEY'),
            'amazonS3region' => env('AWS_DEFAULT_REGION'),
            'amazonS3bucket' => env('AWS_BUCKET'),
        ]);
    }

    private function saveCompanyProfileImage($image_64)
    {
        try {
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];

            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

            $image = str_replace($replace, '', $image_64);

            $image = str_replace(' ', '+', $image);

            $destinationPath = public_path() . '/images//';

            $imageName = Uuid::uuid4() . '.' . $extension;

            file_put_contents($destinationPath . $imageName,  base64_decode($image));
            return 'images/' . $imageName;
        } catch (\Exception $e) {
            return '';
        }
    }


    private function setEnvs($config)
    {
        $path = base_path('.env');

        $keys = $config->keys();
        foreach ($keys as $key) {
            if (is_bool(env($key))) {
                $old = env($key) ? 'true' : 'false';
            } elseif (env($key) === null) {
                $old = 'null';
            } else {
                $old = env($key);
            }

            $fileContents = file_get_contents($path);
            if (file_exists($path)) {
                $fileContents = str_replace(
                    "$key=" . $old,
                    "$key=" . $config[$key],
                    $fileContents
                );
            }
            file_put_contents($path, $fileContents);
        }
    }
}
