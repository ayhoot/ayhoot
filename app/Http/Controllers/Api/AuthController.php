<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthSignUpRequest;
use App\Http\Requests\AuthVerifyRequest;
use App\Http\Requests\AuthResendRequest;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\InviteStudentRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResetPasswordWithOTPRequest;
use App\Models\Admin;
use App\Models\Institute;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Activity;
use App\Models\Group;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\ValidateTokenRequest;
use App\Mail\EmailSend;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Service\SmsService;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function login(AuthLoginRequest $request)
{
    $models = [
        'student' => Student::class,
        'teacher' => Teacher::class,
        'institute' => Institute::class
    ];

    foreach ($models as $role => $model) {
        // Group the OR conditions inside a WHERE closure
        $user = $model::where(function($query) use ($request) {
            $query->where('username', $request->username)
                  ->orWhere('email', $request->username)
                  ->orWhere('phone', $request->username);
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // ... (rest of your existing logic)
            Auth::guard($role)->setUser($user);
            $token = $user->createToken("{$role}-token")->plainTextToken;
            
            Activity::create([
                "{$role}_id" => $user->id,
                'log' => 'Login'
            ]);

            $data = [
                'token' => $token,
                'user' => $user,
                'role' => $role,
                'success' => true,
                'message' => __('messages.login_success'),
            ];

            if ($role == 'teacher') {
                $data['user'] = $user->load('institutes');
            }

            return response($data);
        }
    }

    return response([
        'message' => __('messages.invalid_credentials'),
        'success' => false,
    ], 401);
}

    public function AdminLogin(Request $request)
    {
        $admin = Admin::where('username', $request->username)->first();
        if (!$admin) {
            return response(['error' => 'Invalid credentials', 'message' => __('messages.invalid_credentials')], 401);
        }
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response(['error' => 'Invalid credentials', 'message' => __('messages.invalid_credentials')], 401);
        }
        $token = $admin->createToken('admin-token')->plainTextToken;
        $data = ['token' => $token, 'user' => $admin, 'role' => 'admin'];
        return response($data);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response(['message' => __('messages.logout_success')]);
    }

    public function inviteStudent(InviteStudentRequest $request)
    {
        $inviteCode = $request->invite_code;
        $type = $request->type;

        if ($type == 'teacher') {
            $teacher = Teacher::where('invite_id', $inviteCode)->first();
            $teacher->students()->attach($request->student_id);
            return response(['message' => __('messages.invite_success')]);
        }
        if ($type == 'institute') {
            $institute = Institute::where('invite_id', $inviteCode)->first();
            $institute->students()->attach($request->student_id);
            return response(['message' => __('messages.invite_success')]);
        }
        return response(['message' => __('messages.invalid_invite')]);
    }

    public function signup(AuthSignUpRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class,
            'admin' => Admin::class,
        ];

        $role = $request->role;
        $model = $models[$role];
        $code = $this->generateRandomNumber();
        
        $user = $model::where(function($query) use ($request) {
                $query->where('username', $request->username);
                if ($request->email) {
                    $query->orWhere('email', $request->email);
                }
                if ($request->phone) {
                    $query->orWhere('phone', $request->phone);
                }
            })
            ->first();

        if ($user) {
            if ($user->verified_at !== null) {
                return response([
                    'message' => __('messages.user_already_registered'),
                    'success' => false
                ], 400);
            }
            
            $user->update([
                'password' => $request->password,
                'code' => $code,
                'city_id' => $request->city_id,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
            
            $this->sendVerificationCode($user, $code);
            
            // Hide sensitive data from response
            $userResponse = $user->makeHidden(['code', 'password']);
            
            return response([
                'message' => __('messages.verification_code_resent'),
                'user' => $userResponse,
                'role' => $role,
                'success' => true
            ], 200);
        }

        $user = $model::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'code' => $code,
            'city_id' => $request->city_id,
            'verified_at' => null,
        ]);

        $this->sendVerificationCode($user, $code);
        
        // Hide sensitive data from response
        $userResponse = $user->makeHidden(['code', 'password']);
        
        return response([
            'message' => __('messages.user_registered_successfully'),
            'user' => $userResponse,
            'role' => $role,
            'success' => true
        ], 200);
    }

    private function sendVerificationCode($user, $code)
    {
        if ($user->phone) {
            $this->smsService->sendOtp($user->phone, $code);
        }
        if ($user->email) {
            $templateContent = "<p>Your code : {$code}</p>";
            Mail::to($user->email)->send(new EmailSend('Code', $templateContent));
        }
    }

    public function verify(AuthVerifyRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $user = null;
        $role = null;
        foreach ($models as $r => $model) {
            $user = $model::where('code', $request->code)
                ->where(function($q) use ($request) {
                    $q->where('email', $request->email)
                      ->orWhere('phone', $request->email)
                      ->orWhere('username', $request->email);
                })
                ->first();
            if ($user) {
                $role = $r;
                break;
            }
        }

        if (!$user) {
            return response([
                'message' => __('messages.invalid_verification_code'),
                'success' => false
            ], 400);
        }

        $user->verified_at = Carbon::now();
        $user->code = null;
        $user->save();
        $token = $user->createToken($role . '-token')->plainTextToken;

        // Hide sensitive data from response
        $userResponse = $user->makeHidden(['code', 'password']);

        return response([
            'user' => $userResponse,
            'token' => $token,
            'role' => $role,
            'message' => __('messages.email_verified_successfully'),
            'success' => true
        ]);
    }

    public function resend(AuthResendRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $user = null;
        foreach ($models as $model) {
            $user = $model::where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->whereNull('verified_at')
                ->first();
            if ($user) {
                break;
            }
        }

        if (!$user) {
            return response([
                'message' => __('messages.user_not_found_or_already_verified'),
                'success' => false
            ], 409);
        }

        $code = $this->generateRandomNumber();
        $user->code = $code;
        $user->save();

        if ($user->phone) {
            $this->smsService->sendOtp($user->phone, $code);
        }

        if ($user->email) {
            $templateContent = "<p>Your code : {$code}</p>";
            Mail::to($user->email)->send(new EmailSend('Code', $templateContent));
        }

        return response([
            'message' => __('messages.verification_code_resent_successfully'),
            'success' => true
        ]);
    }

    public function validateToken(ValidateTokenRequest $request)
    {
        $token = $request->token;
        $group = Group::where('token', $token)->first();
        if(!$group){
return response([
            'message' => __('messages.not_found'),
            'success' => false
        ],404);
        }
        $student=Student::find(auth('student')->user()->id);
$group->students()->syncWithoutDetaching([$student->id]);        
    

        return response([
            'message' => __('messages.success'),
            'success' => true,
            'group'=>$group->load('students')
        ]);
    }

    public function checkUsername(Request $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];
        foreach ($models as $role => $model) {
            if ($model::findByUsername($request->username)) {
                return response(['available' => false, 'role' => $request->role]);
            }
        }
        return response(['available' => true, 'role' => $request->role]);
    }

    public function selectInstitute(Request $request)
    {
        $teacher = Teacher::find(auth('teacher')->user()->id);

        if (!$request->institute_id) {
            $teacher->tokens()->delete();
            $token = $teacher->createToken('auth-token', ['*']);
        } else {
            if (!$teacher->institutes->contains($request->institute_id)) {
                return response(['message' => __('messages.unauthorized_institute')], 403);
            }
            $teacher->tokens()->delete();
            $token = $teacher->createToken('auth-token', ['*']);
            $token->accessToken->forceFill([
                'institute_id' => $request->institute_id
            ])->save();
        }
        return response([
            'message' => __('messages.institute_selected_successfully'),
            'token' => $token->plainTextToken,
            'institute_id' => $request->institute_id
        ]);
    }

    public function generateRandomNumber()
    {
        $random = random_int(10000, 99999);
        return $random;
    }

    public function updateUser(UpdateUserRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $model = $models[$request->role];
        $user = $model::find(auth($request->role)->user()->id);

        if ($request->role == 'institute') {
            $user->update([
                'name' => $request->name,
            ]);
        } else {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name
            ]);
        }

        return response([
            'message' => __('messages.profile_updated_successfully'),
            'user' => $user,
            'role' => $request->role,
            'success' => true
        ]);
    }

    public function requestPasswordReset(ResetPasswordRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $user = null;
        $role = null;
        foreach ($models as $r => $model) {
            $user = $model::where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->first();
            if ($user) {
                $role = $r;
                break;
            }
        }

        if (!$user) {
            return response([
                'message' => __('messages.email_not_found'),
                'success' => false
            ], 404);
        }

        $code = $this->generateRandomNumber();
        $user->code = $code;
        $user->save();

        if ($user->phone) {
            $this->smsService->sendOtp($user->phone, $code);
        }

        if ($user->email) {
            $templateContent = "<p>Your password reset code : {$code}</p>";
            Mail::to($user->email)->send(new EmailSend('Password Reset Code', $templateContent));
        }

        return response([
            'message' => __('messages.password_reset_code_sent'),
            'success' => true
        ]);
    }

    public function resetPassword(ResetPasswordWithOTPRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $user = null;
        $role = null;
        foreach ($models as $r => $model) {
            $user = $model::where(function($q) use ($request) {
                    $q->where('email', $request->email)
                      ->orWhere('phone', $request->email);
                })
                ->where('code', $request->code)
                ->first();
            if ($user) {
                $role = $r;
                break;
            }
        }

        if (!$user) {
            return response([
                'message' => __('messages.invalid_verification_code'),
                'success' => false
            ], 400);
        }

        $user->password = $request->password;
        $user->code = null;
        $user->save();

        return response([
            'message' => __('messages.password_reset_successfully'),
            'success' => true
        ]);
    }

    public function sendOTPCode(ResetPasswordRequest $request)
    {
        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $user = null;
        $role = null;
        foreach ($models as $r => $model) {
            $user = $model::where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->first();
            if ($user) {
                $role = $r;
                break;
            }
        }
        if (!$user) {
            return response([
                'message' => __('messages.email_not_found'),
            ], 401);
        }

        $code = $this->generateRandomNumber();
        $user->code = $code;
        $user->save();

        if ($user->phone) {
            $this->smsService->sendOtp($user->phone, $code);
        }

        if ($user->email) {
            $templateContent = "<p>Your code : {$code}</p>";
            Mail::to($user->email)->send(new EmailSend('Code', $templateContent));
        }

        return response([
            'message' => __('messages.verification_code_resent_successfully'),
            'success' => true
        ]);
    }

    public function updatePassword(Request $request)
    {
        $auth_user = auth()->user();
        if (!$auth_user) {
            return response([
                'message' => __('messages.unauthorized'),
            ], 401);
        }

        $models = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class
        ];

        $user = null;
        foreach ($models as $model) {
            $user = $model::find($auth_user->id);
            if ($user) {
                break;
            }
        }

        if (!$user) {
            return response([
                'message' => __('messages.user_not_found'),
            ], 404);
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = $request->password;
        $user->save();

        return response([
            'message' => __('messages.password_updated_successfully'),
            'success' => true
        ]);
    }
}