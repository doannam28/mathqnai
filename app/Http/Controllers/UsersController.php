<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
class UsersController extends Controller
{
    public function login(Request $request)
    {
        return view('users.login');
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
        return view('users.register');
    }

    public function submit_register(Request $request)
    {
        try {
            // 1. Validate dữ liệu đăng ký
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
                'birth_date' => 'required|date_format:d/m/Y|before_or_equal:today',
            ], [
                'full_name.required' => 'Vui lòng nhập họ và tên.',
                'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Email không đúng định dạng.',
                'email.unique' => 'Email đã tồn tại trong hệ thống.',
                'email.max' => 'Email không được vượt quá 255 ký tự.',

                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
                'password.confirmed' => 'Mật khẩu nhập lại không trùng khớp.',

                'password_confirmation.required' => 'Vui lòng nhập lại mật khẩu.',
                'password_confirmation.min' => 'Mật khẩu nhập lại phải có ít nhất 8 ký tự.',

                'birth_date.required' => 'Vui lòng nhập ngày sinh.',
                'birth_date.date_format' => 'Ngày sinh phải có định dạng DD/MM/YYYY.',
                'birth_date.before_or_equal' => 'Ngày sinh không được lớn hơn ngày hiện tại.',
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
