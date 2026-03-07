<?php

namespace App\Http\Controllers\Function;

use App\Actions\Function\BuildAgendaPageDataAction;
use App\Actions\Function\BuildGalleryPageDataAction;
use App\Actions\Inscription\ApproveInscriptionByClassroomAction;
use App\Actions\Notification\MarkUserNotificationAsReadAction;
use App\Actions\Notification\SendSchoolCertificateNotificationAction;
use App\Http\Requests\ApproveLegacyInscriptionRequest;
use App\Http\Requests\MarkNotificationAsReadRequest;
use App\Http\Requests\NotifySchoolCertificateRequest;
use App\Models\AgendaScolaire\Grade;

use App\Models\Inscription\Inscription;
use App\Models\Inscription\StudentInfo;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Throwable;

class FunctionController extends Controller
{
    public function __construct(
        private ApproveInscriptionByClassroomAction $approveInscriptionByClassroomAction,
        private BuildAgendaPageDataAction $buildAgendaPageDataAction,
        private BuildGalleryPageDataAction $buildGalleryPageDataAction,
        private SendSchoolCertificateNotificationAction $sendSchoolCertificateNotificationAction,
        private MarkUserNotificationAsReadAction $markUserNotificationAsReadAction
    )
    {
    }

    public function showAgenda($id){
        $data = $this->buildAgendaPageDataAction->execute(Auth::user());
        $view = $data['view'];
        unset($data['view']);

        return view($view, $data);
    }

    public function showGallery(){
        return view('front-end.gallery', $this->buildGalleryPageDataAction->execute());
    }

    // Backward-compatible aliases for legacy method naming.
    public function getAgenda($id){
        return $this->showAgenda($id);
    }

    public function getAlbum(){
        return $this->showGallery();
    }

    public function listAgendaGrades($id = null)
    {
        return Grade::query()->pluck('name_grade', 'id');
    }

    // Backward-compatible alias for legacy route naming.
    public function getGrade($id = null){
        return $this->listAgendaGrades($id);
    }

    public function store(ApproveLegacyInscriptionRequest $request, $id){
        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validated();
        $schoolId = $this->currentSchoolId();

        try {
            $inscription = Inscription::query()
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->findOrFail((int) $validated['id']);
            $this->authorize('approve', $inscription);

            $this->approveInscriptionByClassroomAction->execute($inscription, $schoolId);
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Inscriptions.index');
    }


    public function notify(NotifySchoolCertificateRequest $request,$id){
        $validated = $request->validated();

        try {
            if(auth()->user()){
                if ((int) $validated['id'] !== (int) auth()->id()) {
                    abort(403);
                }

                $currentUser = auth()->user();
                $requestDetails = [
                    'year' => (string) $validated['year'],
                    'purpose' => (string) $validated['purpose'],
                    'copies' => (int) $validated['copies'],
                    'preferred_language' => (string) $validated['preferred_language'],
                    'delivery_method' => (string) $validated['delivery_method'],
                    'notes' => (string) ($validated['notes'] ?? ''),
                    'requested_at' => now()->toDateTimeString(),
                ];

                $this->sendSchoolCertificateNotificationAction->execute(
                    $currentUser,
                    (int) $validated['id'],
                    $requestDetails,
                    $this->translatedName($currentUser, 'fr'),
                    $this->translatedName($currentUser, 'ar')
                );
            }
            return back()->withSuccess('certificate_request_sent');
        }
  
        catch (\Exception $e){
            return redirect()->back()->withErrors(['تعذر إرسال طلب الشهادة المدرسية حاليًا. يرجى التواصل مع الإدارة.']);
        }
        
    }

    public function markAsRead(MarkNotificationAsReadRequest $request, $id){
        $validated = $request->validated();

        $notify = $this->markUserNotificationAsReadAction->execute((string) $validated['id'], (int) Auth::id());

        if (!$notify) {
            abort(404);
        }

        $requestData = json_decode($notify['data'] ?? '[]', true);
        if (!is_array($requestData)) {
            $requestData = [];
        }

        $studentInfoQuery = StudentInfo::query()->with(['user', 'section.classroom']);

        if (!empty($requestData['requester_user_id'])) {
            $studentInfoQuery->where('user_id', (int) $requestData['requester_user_id']);
        } elseif (!empty($requestData['email'])) {
            $email = (string) $requestData['email'];
            $studentInfoQuery->whereHas('user', fn ($query) => $query->where('email', $email));
        } else {
            $studentInfoQuery->whereRaw('1 = 0');
        }

        $data['notify'] = $this->notifications();
        $data['StudentInfo'] = $studentInfoQuery->first();
        $data['arryear'] = $requestData;

        return view('admin.StudentSchoolCertificateNotification', $data);
    }

    public function showChangePasswordPage(){
        $data['notify'] = $this->notifications();
        return view('admin.changepassword',$data);
    }

    // Backward-compatible alias for legacy method naming.
    public function changepass(){
        return $this->showChangePasswordPage();
    }

    private function translatedName($user, string $locale): string
    {
        if (method_exists($user, 'getTranslation')) {
            try {
                return (string) $user->getTranslation('name', $locale);
            } catch (Throwable $exception) {
                // Fallback to raw "name" when legacy rows are not JSON-translatable.
            }
        }

        $value = $user->name ?? '';
        if (is_array($value)) {
            return (string) ($value[$locale] ?? reset($value) ?? '');
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return (string) ($decoded[$locale] ?? reset($decoded) ?? $value);
            }
        }

        return (string) $value;
    }















}
