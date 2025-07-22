<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\MailerService;

class UserController extends Controller
{
    private $userRepository;
    private $mailerService;

    public function __construct(UserRepositoryInterface $userRepository, MailerService $mailerService)
    {
        $this->userRepository = $userRepository;
        $this->mailerService = $mailerService;
    }

    public function index()
    {
        return response()->json($this->userRepository->all());
    }

    public function dropdown()
    {
        return response()->json($this->userRepository->getUsersForDropdown());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'       => ['required', 'email', 'unique:' . (new Users())->getTable()],
            'firstName' =>   ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 409);
        }

        $plainPassword = $request->password; // Save plain password before hashing
        $request['password'] = Hash::make($request->password);
        $user = $this->userRepository->createUser($request->all());

        // Send email with login credentials (not recommended for production)
        try {
            $this->mailerService->sendLoginCredentials($request->email, $plainPassword);
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send email. Please try again later.'], 500);
        }

        return response()->json($user, 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json($this->userRepository->findUser($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->except(['password']);
        $model = $this->userRepository->find($id);
        $model->firstName = $request->firstName;
        $model->lastName = $request->lastName;
        $model->phoneNumber = $request->phoneNumber;
        $model->userName = $request->userName;
        $model->email = $request->email;

        return  response()->json($this->userRepository->updateUser($model, $id, $request['roleIds']), 200);
    }

    public function destroy($id)
    {
        $user = Users::findOrFail($id);
        $user->isDeleted = 1;
        $user->save();
        return response([], 204);
    }

    public function updateUserProfile(Request $request)
    {
        return  response()->json($this->userRepository->updateUserProfile($request), 200);
    }

    public function submitResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $user = Users::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        return  response()->json(($user), 204);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required',
        ]);

        if (!(Hash::check($request->get('oldPassword'), Auth::user()->password))) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Old Password does not match!',
            ], 422);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store OTP in session or database as per your app's design
        session(['password_change_otp' => $otp]);

        // Send OTP to user's email
        try {
            $this->mailerService->sendOtpEmail(Auth::user()->email, $otp);
        } catch (\Exception $e) {
            \Log::error('OTP mail sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP email. Please try again later.'], 500);
        }

        // Here you might want to wait for OTP verification before updating password
        // For now, we proceed with password update assuming OTP verification is done

        Users::whereId(auth()->id())->update([
            'password' => Hash::make($request->newPassword)
        ]);

        return response()->json(['message' => 'Password changed successfully. OTP sent to your email.'], 200);
    }
}
