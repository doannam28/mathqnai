<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
class UsersController extends Controller
{
    private function grade9BirthDateRange(): array
    {
        $today = Carbon::today();

        return [
            $today->copy()->subYears(16)->addDay()->startOfDay(),
            $today->copy()->subYears(13)->endOfDay(),
        ];
    }

    public function login(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            return redirect('/dashboard');
        }
        return view('users.login');
    }

    public function forgotPasswordForm()
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            return redirect('/dashboard');
        }
        return view('users.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Email này chưa được đăng ký trong hệ thống.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            $message = 'Chúng tôi đã gửi email đặt lại mật khẩu. Vui lòng kiểm tra hộp thư của bạn.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return back()->with('success', $message);
        }

        $message = __($status);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['email' => $message])->withInput();
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        return view('users.reset_password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            return redirect('/dashboard');
        }
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'Mã xác thực không hợp lệ.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không trùng khớp.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return back()->withErrors([
                'email' => __($status),
            ])->withInput();
        }

        return redirect('/dang-nhap')->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback(Request $request)
    {
        try {
            // 1. Lấy thông tin từ Google
            $googleUser = Socialite::driver('google')->user();

            $googleId = $googleUser->getId();
            $email = $googleUser->getEmail();
            $name = $googleUser->getName();
            if (empty($googleId) || empty($email)) {
                return redirect('/dang-nhap')->with(
                    'error',
                    'Không thể lấy thông tin tài khoản Google.'
                );
            }

            // 2. Tìm tài khoản đã liên kết Google
            $user = User::where('google_id', $googleId)->first();

            if (!$user) {
                // 3. Tìm tài khoản theo email
                $existingUser = User::where('email', $email)->first();

                if ($existingUser) {

                    // Nếu đã liên kết Google khác thì không ghi đè
                    if (!empty($existingUser->google_id)) {
                        return redirect('/dang-nhap')->with(
                            'error',
                            'Tài khoản này đã liên kết với Google khác.'
                        );
                    }
                    // 4. Liên kết Google với tài khoản hiện tại
                    $existingUser->google_id = $googleId;
                    $existingUser->save();

                    $user = $existingUser;

                } else {
                    // 5. Chưa có tài khoản thì tạo mới
                    $user = new User();

                    $user->name = $name ?: $email;
                    $user->email = $email;
                    $user->google_id = $googleId;
                    $user->password = null;
                    $user->birth_date = null;

                    $user->save();
                }
            }

            // 6. Đăng nhập Laravel
            Auth::guard('web')->login($user);

            // 7. Tạo lại session
            $request->session()->regenerate();

            // 8. Chuyển về Dashboard
            return redirect('/dashboard');

        } catch (\Throwable $e) {

            Log::error('Google Login Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect('/dang-nhap')->with(
                'error',
                'Đăng nhập bằng Google thất bại. Vui lòng thử lại.'
            );
        }
    }


    public function submit_login(Request $request)
    {
        try {
            // 1. Validation dữ liệu
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Email không đúng định dạng.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
            ]);

            // 2. Trả về lỗi validation
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // 3. Lấy dữ liệu đăng nhập
            $credentials = [
                'email' => trim($request->input('email')),
                'password' => $request->input('password')
            ];

            // 4. Kiểm tra tài khoản và mật khẩu
            if (!Auth::guard('web')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không chính xác.'
                ], 401);
            }

            // 5. Tạo lại session để bảo mật
            $request->session()->regenerate();

            // 6. Lấy thông tin user
            $user = Auth::guard('web')->user();

            // 7. Trả về kết quả thành công
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'redirect' => url('/dashboard'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Submit login error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            return redirect('/dashboard');
        }
        return view('users.register');
    }

    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return redirect('/dang-nhap');
        }

        return view('users.account', [
            'user' => $user,
        ]);
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return redirect('/dang-nhap');
        }

        [$minBirthDate, $maxBirthDate] = $this->grade9BirthDateRange();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sex' => 'required|in:0,1',
            'birth_date' => [
                'nullable',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($minBirthDate, $maxBirthDate) {
                    try {
                        $date = Carbon::createFromFormat('Y-m-d', $value);

                        if ($date->format('Y-m-d') !== $value) {
                            throw new \RuntimeException('Invalid date');
                        }
                    } catch (\Throwable $e) {
                        $fail('Ngày sinh không hợp lệ.');
                        return;
                    }

                    if ($date->lt($minBirthDate) || $date->gt($maxBirthDate)) {
                        $fail('Ngày sinh phải nằm trong khoảng tuổi dành cho học sinh lớp 9.');
                    }
                },
            ],
            'avata' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'sex.required' => 'Vui lòng chọn giới tính.',
            'sex.in' => 'Giới tính không hợp lệ.',
            'birth_date.date_format' => 'Ngày sinh không hợp lệ.',
            'avata.image' => 'Ảnh đại diện phải là file hình ảnh.',
            'avata.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không trùng khớp.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $input = $validator->validated();

        $user->name = trim(strip_tags($input['name']));
        $user->sex = (int) $input['sex'];
        $user->birth_date = $input['birth_date'] ?? null;

        if ($request->hasFile('avata')) {
            $uploadPath = public_path('uploads/avata');

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file = $request->file('avata');
            $extension = $file->getClientOriginalExtension() ?: $file->extension();
            $fileName = $user->id . '_' . Str::random(12) . '.' . $extension;
            $relativePath = 'uploads/avata/' . $fileName;

            if (!empty($user->avata)) {
                $oldFile = public_path($user->avata);
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $file->move($uploadPath, $fileName);
            $user->avata = $relativePath;
        }

        if (!empty($input['password'])) {
            $user->password = Hash::make($input['password']);
        }

        $user->save();

        return back()->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function updateTargetScore(Request $request)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để cập nhật mục tiêu.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'target_score' => 'required|numeric|min:0|max:10',
        ], [
            'target_score.required' => 'Vui lòng nhập mục tiêu điểm số.',
            'target_score.numeric' => 'Mục tiêu điểm số phải là số.',
            'target_score.min' => 'Mục tiêu điểm số không được nhỏ hơn 0.',
            'target_score.max' => 'Mục tiêu điểm số không được lớn hơn 10.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user->target_score = (float) $request->input('target_score');
        $user->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật mục tiêu điểm số.',
                'target_score' => $user->target_score,
            ]);
        }

        return back()->with('success', 'Đã cập nhật mục tiêu điểm số.');
    }

    public function submit_register(Request $request)
    {
        try {
            [$minBirthDate, $maxBirthDate] = $this->grade9BirthDateRange();

            // 1. Validate dữ liệu đăng ký
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'sex' => 'required|in:0,1',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
                'birth_date' => [
                    'required',
                    'date_format:d/m/Y',
                    function ($attribute, $value, $fail) use ($minBirthDate, $maxBirthDate) {
                        try {
                            $date = Carbon::createFromFormat('d/m/Y', $value);

                            if ($date->format('d/m/Y') !== $value) {
                                throw new \RuntimeException('Invalid date');
                            }
                        } catch (\Throwable $e) {
                            $fail('Ngày sinh phải có định dạng DD/MM/YYYY.');
                            return;
                        }

                        if ($date->lt($minBirthDate) || $date->gt($maxBirthDate)) {
                            $fail('Trang đăng ký chỉ dành cho học sinh lớp 9.');
                        }
                    },
                ],
            ], [
                'full_name.required' => 'Vui lòng nhập họ và tên.',
                'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Email không đúng định dạng.',
                'email.unique' => 'Email đã tồn tại trong hệ thống.',
                'email.max' => 'Email không được vượt quá 255 ký tự.',

                'sex.required' => 'Vui lòng chọn giới tính.',
                'sex.in' => 'Giới tính không hợp lệ.',

                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
                'password.confirmed' => 'Mật khẩu nhập lại không trùng khớp.',

                'password_confirmation.required' => 'Vui lòng nhập lại mật khẩu.',
                'password_confirmation.min' => 'Mật khẩu nhập lại phải có ít nhất 8 ký tự.',

                'birth_date.required' => 'Vui lòng nhập ngày sinh.',
                'birth_date.date_format' => 'Ngày sinh phải có định dạng DD/MM/YYYY.',
            ]);

            // 2. Trả về lỗi validation
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // 3. Lấy dữ liệu đã validate
            $input = $validator->validated();

            // 4. Tạo tài khoản mới
            $user = new User();

            $user->name = trim(strip_tags($input['full_name']));
            $user->email = trim($input['email']);
            $user->sex = (int) $input['sex'];

            // Hash mật khẩu trước khi lưu
            $user->password = Hash::make($input['password']);

            $user->birth_date = $input['birth_date'];

            // 5. Lưu vào database
            $user->save();

            // 6. Trả về kết quả thành công
            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công!'
            ], 201);

        } catch (\Throwable $e) {

            Log::error('Submit register error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        // Đăng xuất tài khoản
        Auth::guard('web')->logout();

        // Hủy session hiện tại
        $request->session()->invalidate();

        // Tạo lại CSRF token
        $request->session()->regenerateToken();

        // Chuyển về trang đăng nhập
        return redirect('/');
    }
}
