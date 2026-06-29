<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Essa\APIToolKit\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ApiResponse;
    private const WEBP_QUALITY = 82;

    public function index(Request $request)
    {
        $status = $request->query('status');
        $pagination = $request->query('pagination');

        $User = User::orderBy('created_at', 'desc')
            ->useFilters()
            ->dynamicPaginate();

        if (!$pagination) {
            UserResource::collection($User);
        } else {
            $User = UserResource::collection($User);
        }
        return $this->responseSuccess('User display successfully', $User);
    }

    public function viewProfilePicture(Request $request, string $path)
    {
        $fullPath = 'profile_picture/' . $path;

        if (!Storage::disk('private')->exists($fullPath)) {
            abort(404, 'File not found');
        }

        $file     = Storage::disk('private')->get($fullPath);
        $mimeType = Storage::disk('private')->mimeType($fullPath);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'private, no-store, no-cache');
    }
    public function store(UserRequest $request)
    {
        $profilePicturePath = null;

        if ($request->file('profile_picture')) {
            $first_name  = $request["first_name"] ?? 'NA';
            $middle_name = $request["middle_name"] ?? '';
            $last_name   = $request["last_name"] ?? 'NA';
            $suffix      = $request["suffix"] ?? '';
            $date        = now()->format('Y-m-d_H-i-s');

            $parts = array_filter([
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
                $date,
            ], fn($part) => $part !== '' && $part !== null);

            $profile_name = implode('-', $parts);

            $file     = $request->file('profile_picture');
            $tempPath = $this->convertToWebp($file);

            $profilePicturePath = Storage::disk('private')->putFileAs(
                "profile_picture/",
                new \Illuminate\Http\File($tempPath),
                "{$profile_name}.webp"
            );

            @unlink($tempPath);
        }

        $create_user = User::create([
            "first_name"           => $request["first_name"],
            "middle_name"          => $request["middle_name"],
            "last_name"            => $request["last_name"],
            "suffix"               => $request["suffix"],
            "birthday"        => $request["birthday"],
            "mobile_number"        => $request["mobile_number"],
            "gender"               => $request["gender"],
            "username"             => $request["username"],
            "password"             => $request["username"],
            "email"              => $request["email"],
            "role"        => $request["role"],
            "profile_picture"      => $profilePicturePath,
        ]);

        return $this->responseCreated('User Successfully Created', $create_user);
    }

    public function update(UserRequest $request, User $user)
    {
        $profilePicturePath = $user->profile_picture;

        // Upload new profile picture only if provided
        if ($request->hasFile('profile_picture')) {
            $first_name  = $request->input('first_name', 'NA');
            $middle_name = $request->input('middle_name', '');
            $last_name   = $request->input('last_name', 'NA');
            $suffix      = $request->input('suffix', '');
            $date        = now()->format('Y-m-d_H-i-s');

            $parts = array_filter([
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
                $date,
            ]);

            $profileName = implode('-', $parts);

            $file = $request->file('profile_picture');
            $tempPath = $this->convertToWebp($file);

            $profilePicturePath = Storage::disk('private')->putFileAs(
                'profile_picture/',
                new \Illuminate\Http\File($tempPath),
                "{$profileName}.webp"
            );

            @unlink($tempPath);

            // Delete old profile picture
            if ($user->profile_picture && Storage::disk('private')->exists($user->profile_picture)) {
                Storage::disk('private')->delete($user->profile_picture);
            }
        }

        $data = [
            'first_name'      => $request->first_name,
            'middle_name'     => $request->middle_name,
            'last_name'       => $request->last_name,
            'suffix'          => $request->suffix,
            'birthday'        => $request->birthday,
            'mobile_number'   => $request->mobile_number,
            'gender'          => $request->gender,
            'username'        => $request->username,
            'email'           => $request->email,
            'role'            => $request->role,
            'profile_picture' => $profilePicturePath,
        ];

        $user->update($data);

        return $this->responseSuccess('User Successfully Updated', $user->fresh());
    }

    public function archived(Request $request, $id)
    {
        if ($id == auth('sanctum')->user()->id) {
            return $this->responseUnprocessable('', 'Unable to archive. You cannot archive your own account.');
        }

        $user = User::withTrashed()->find($id);

        if (!$user) {
            return $this->responseUnprocessable('', 'Invalid id please check the id and try again.');
        }

        if ($user->deleted_at) {

            $user->restore();
            return $this->responseSuccess('user successfully restore', $user);
        }

        if (!$user->deleted_at) {

            $user->delete();
            return $this->responseSuccess('user successfully archive', $user);
        }
    }

    public function convertToWebp(UploadedFile $file): string
    {
        $source = $this->loadImage($file->getPathname());

        if (!$source) {
            throw new \RuntimeException('Unable to load image for WebP conversion.');
        }

        if (in_array($file->getMimeType(), ['image/png', 'image/webp'])) {
            imagepalettetotruecolor($source);
            imagealphablending($source, true);
            imagesavealpha($source, true);
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid('webp_', true) . '.webp';
        $success  = imagewebp($source, $tempPath, self::WEBP_QUALITY);
        imagedestroy($source);

        if (!$success) {
            throw new \RuntimeException('WebP conversion failed. Ensure GD is compiled with WebP support.');
        }

        return $tempPath;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function loadImage(string $path): mixed
    {
        return match (mime_content_type($path)) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png'               => imagecreatefrompng($path),
            'image/webp'              => imagecreatefromwebp($path),
            default                   => null,
        };
    }
}
